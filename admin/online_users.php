<?php
// Polling the online list is background work, not user activity.
$refreshSessionActivity = false;
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['admin']);
header('Cache-Control: no-store');
header('Content-Type: text/html; charset=utf-8');
renderOnlineUsers(getOnlineUsers($conn));
