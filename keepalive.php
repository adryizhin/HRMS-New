<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/session_activity.php';
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST'); http_response_code(405); exit;
}
if (!isset($_SESSION['user_id'], $_SESSION['session_token']) ||
    !refreshLoginSession($conn, (int) $_SESSION['user_id'], (string) $_SESSION['session_token'], ($_POST['presence'] ?? '') !== '1')) {
    session_unset(); session_destroy(); http_response_code(401); exit;
}
http_response_code(204);
