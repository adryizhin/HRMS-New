<?php
// Refresh the portable SQL snapshot from the configured database. CLI only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/config.php';
$binary = getenv('HRMS_DUMP_BIN') ?: dirname(PHP_BINARY, 2) . '/mysql/bin/mysqldump.exe';
if (!is_file($binary)) throw new RuntimeException('Set HRMS_DUMP_BIN to the mysqldump or mariadb-dump executable.');
$tables = $conn->query('SHOW FULL TABLES')->fetch_all(MYSQLI_NUM);
foreach ($tables as $table) {
    if ($table[1] !== 'BASE TABLE') throw new RuntimeException('Review views before exporting this database.');
    $name = str_replace('`', '``', $table[0]);
    $create = $conn->query("SHOW CREATE TABLE `$name`")->fetch_row()[1];
    if (!str_contains($create, 'ENGINE=InnoDB') || !str_contains($create, 'utf8mb4_general_ci')) throw new RuntimeException('Review engine/collation compatibility for ' . $table[0]);
}
if ($conn->query('SHOW TRIGGERS')->num_rows) throw new RuntimeException('Review triggers before exporting this database.');
$options = tempnam(sys_get_temp_dir(), 'hrms-db-options-');
$raw = tempnam(sys_get_temp_dir(), 'hrms-db-export-');
$stage = dirname(__DIR__) . '/hris.sql.tmp';
try {
    $quote = static fn(string $value): string => '"' . str_replace(["\\", '"', "\n", "\r"], ["\\\\", '\\"', '\\n', '\\r'], $value) . '"';
    file_put_contents($options, "[client]\nhost=" . $quote(DB_HOST) . "\nuser=" . $quote(DB_USER) . "\npassword=" . $quote(DB_PASS) . "\n");
    chmod($options, 0600);
    $command = [$binary, '--defaults-extra-file=' . $options, '--single-transaction', '--quick', '--skip-lock-tables', '--skip-add-drop-table', '--skip-add-locks', '--skip-disable-keys', '--skip-comments', '--skip-dump-date', '--skip-extended-insert', '--complete-insert', '--hex-blob', '--no-tablespaces', '--default-character-set=utf8mb4', '--result-file=' . $raw, DB_NAME];
    $process = proc_open($command, [0=>['pipe','r'], 1=>['pipe','w'], 2=>['pipe','w']], $pipes);
    if (!is_resource($process)) throw new RuntimeException('Unable to start database exporter.');
    fclose($pipes[0]); stream_get_contents($pipes[1]); fclose($pipes[1]);
    $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
    if (proc_close($process) !== 0) throw new RuntimeException('Export failed; the previous hris.sql was retained. ' . $error);
    $sql = file_get_contents($raw);
    // Recent MariaDB clients add a sandbox directive unrecognized by MySQL/older clients.
    $sql = preg_replace('/^\/\*M![^\r\n]*enable the sandbox mode[^\r\n]*\R?/m', '', $sql);
    if (substr_count($sql, 'CREATE TABLE `') !== count($tables)) throw new RuntimeException('Export table count does not match the source.');
    $header = "-- HRMS database snapshot: " . gmdate('Y-m-d H:i:s') . " UTC\n"
        . "-- Import into an EMPTY database selected in phpMyAdmin, or: mysql -u root -p hris < hris.sql\n"
        . "-- Includes private system records. Copy uploads/ separately for attachments and photos.\n"
        . "-- InnoDB, utf8mb4_general_ci; no DROP TABLE, CREATE DATABASE, USE or DEFINER directives.\n\n";
    if (file_put_contents($stage, $header . $sql) === false || !rename($stage, dirname(__DIR__) . '/hris.sql')) throw new RuntimeException('Could not replace hris.sql.');
    echo 'Exported ', count($tables), ' tables to hris.sql (', filesize(dirname(__DIR__) . '/hris.sql'), " bytes).\n";
} finally {
    foreach ([$options, $raw, $stage] as $file) if (is_file($file)) unlink($file);
}
