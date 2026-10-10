<?php
$refreshSessionActivity = false;
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['admin','hr']);
header('Cache-Control: no-store'); header('Content-Type: application/json; charset=utf-8');
$stmt=$conn->prepare("SELECT u.id,u.full_name,COUNT(*) AS unread_count FROM chat_messages m JOIN users u ON u.id=m.sender_id WHERE m.receiver_id=? AND m.status <> 'seen' GROUP BY u.id,u.full_name ORDER BY MAX(m.created_at) DESC");
$stmt->bind_param('i',$_SESSION['user_id']);$stmt->execute();$senders=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode(['count'=>array_sum(array_column($senders,'unread_count')),'senders'=>$senders]);
