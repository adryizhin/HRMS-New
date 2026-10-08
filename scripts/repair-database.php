<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../config/database.php';
$conn->query("SET SESSION sql_mode = CONCAT_WS(',', @@sql_mode, 'NO_AUTO_VALUE_ON_ZERO')");
$max = (int) $conn->query('SELECT MAX(CHAR_LENGTH(email)) AS length FROM users')->fetch_assoc()['length'];
if ($max > 191) { throw new RuntimeException('An email exceeds 191 characters. Upgrade MySQL before repairing its unique index.'); }
$conn->query('ALTER TABLE users MODIFY email VARCHAR(191) DEFAULT NULL');
$schema = file_get_contents(__DIR__ . '/../hris.sql');
preg_match_all('/ALTER TABLE `([^`]+)`\s+([^;]+);/s', $schema, $statements, PREG_SET_ORDER);
$changes = 0;
foreach ($statements as $statement) {
    $table = $statement[1];
    $indexes = [];
    foreach ($conn->query("SHOW INDEX FROM `$table`") as $index) { $indexes[$index['Key_name']] = true; }
    $constraints = [];
    $query = $conn->prepare('SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $query->bind_param('s', $table);
    $query->execute();
    foreach ($query->get_result() as $row) { $constraints[$row['CONSTRAINT_NAME']] = true; }
    foreach (preg_split('/,\s*(?=ADD |MODIFY )/', trim($statement[2])) as $clause) {
        if (preg_match('/^ADD PRIMARY KEY/', $clause) && isset($indexes['PRIMARY'])) { continue; }
        if (preg_match('/^ADD (?:UNIQUE )?KEY `([^`]+)`/', $clause, $match) && isset($indexes[$match[1]])) { continue; }
        if (preg_match('/^ADD CONSTRAINT `([^`]+)`/', $clause, $match) && isset($constraints[$match[1]])) { continue; }
        if (str_starts_with($clause, 'MODIFY')) {
            $column = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'id'")->fetch_assoc();
            if (str_contains($column['Extra'], 'auto_increment')) { continue; }
            $clause = preg_replace('/,\s*AUTO_INCREMENT=\d+/', '', $clause);
        }
        $conn->query("ALTER TABLE `$table` $clause");
        $changes++;
    }
}
echo "Schema repaired: $changes changes in " . DB_NAME . PHP_EOL;
