<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

if (isset($conn) && $conn instanceof mysqli) {
    return;
}

define('DB_HOST', hrmsHostingSetting('HRMS_DB_HOST', 'localhost') ?: 'localhost');
define('DB_USER', hrmsHostingSetting('HRMS_DB_USER', 'root') ?: 'root');
define('DB_PASS', hrmsHostingSetting('HRMS_DB_PASS', ''));
define('DB_NAME', hrmsHostingSetting('HRMS_DB_NAME', 'hris') ?: 'hris');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>
