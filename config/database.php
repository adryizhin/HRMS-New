<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

if (isset($conn) && $conn instanceof mysqli) {
    return;
}

define('DB_HOST', getenv('HRMS_DB_HOST') ?: 'localhost');
define('DB_USER', getenv('HRMS_DB_USER') ?: 'root');
define('DB_PASS', getenv('HRMS_DB_PASS') !== false ? getenv('HRMS_DB_PASS') : '');
define('DB_NAME', getenv('HRMS_DB_NAME') ?: 'hris');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>
