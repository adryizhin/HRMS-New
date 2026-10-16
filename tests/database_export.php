<?php
// Restore the shipped export into an isolated database and compare all table data/schema.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/config.php';
$source = $conn;
$database = 'hrms_feature_test_' . bin2hex(random_bytes(5));
$source->query("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
$restore = new mysqli(DB_HOST, DB_USER, DB_PASS, $database);
$restore->set_charset('utf8mb4');
function tableDigest(mysqli $db, string $name): string {
    $quoted = '`' . str_replace('`', '``', $name) . '`';
    $keys = array_column($db->query("SHOW KEYS FROM $quoted WHERE Key_name='PRIMARY'")->fetch_all(MYSQLI_ASSOC), 'Column_name');
    $order = $keys ? ' ORDER BY ' . implode(',', array_map(static fn($v) => '`' . str_replace('`','``',$v) . '`', $keys)) : '';
    $hash = hash_init('sha256');
    foreach ($db->query("SELECT * FROM $quoted$order") as $row) hash_update($hash, serialize($row));
    return hash_final($hash);
}
try {
    $sql = file_get_contents(dirname(__DIR__) . '/hris.sql');
    if (preg_match('/^(?:DROP TABLE|CREATE DATABASE|USE |.*DEFINER=)|enable the sandbox mode/m', $sql)) throw new RuntimeException('Nonportable or destructive export directive.');
    $restore->multi_query($sql);
    do { if ($result=$restore->store_result()) $result->free(); } while ($restore->more_results() && $restore->next_result());
    $tables = array_column($source->query('SHOW TABLES')->fetch_all(MYSQLI_NUM), 0);
    $restored = array_column($restore->query('SHOW TABLES')->fetch_all(MYSQLI_NUM), 0);
    sort($tables); sort($restored);
    if ($tables !== $restored) throw new RuntimeException('Restored tables differ from the source.');
    $rows = 0;
    foreach ($tables as $name) {
        if (tableDigest($source, $name) !== tableDigest($restore, $name)) throw new RuntimeException('Data mismatch in ' . $name . '; refresh export if the source changed during verification.');
        $quoted = '`' . str_replace('`', '``', $name) . '`';
        $schema = $source->query("SHOW CREATE TABLE $quoted")->fetch_row()[1];
        $copy = $restore->query("SHOW CREATE TABLE $quoted")->fetch_row()[1];
        if ($schema !== $copy) throw new RuntimeException('Schema, indexes or AUTO_INCREMENT differ in ' . $name);
        $rows += (int)$restore->query("SELECT COUNT(*) FROM $quoted")->fetch_row()[0];
    }
    echo 'PASS: restored ', count($tables), ' tables and ', $rows, " rows; exact data, schema, indexes, foreign keys and AUTO_INCREMENT match.\n";
    echo 'Verified on ', $restore->server_info, '. Other server versions require their own import test.', PHP_EOL;
} finally {
    $restore->close();
    if (preg_match('/\Ahrms_feature_test_[a-f0-9]{10}\z/', $database)) $source->query("DROP DATABASE `$database`");
}
