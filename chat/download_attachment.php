<?php
$refreshSessionActivity=false;require_once __DIR__.'/../includes/auth_check.php';
require_once __DIR__.'/../includes/chat_attachments.php';ensureChatAttachments($conn);
$id=(int)($_GET['id']??0);$user=(int)$_SESSION['user_id'];
$stmt=$conn->prepare('SELECT attachment_path,attachment_name FROM chat_messages WHERE id=? AND (sender_id=? OR receiver_id=?)');$stmt->bind_param('iii',$id,$user,$user);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();
if(!$row||!preg_match('/\A[a-f0-9]{32}\.[a-z0-9]+\z/',$row['attachment_path']??'')){http_response_code(404);exit;}
$file=__DIR__.'/../uploads/private_chat/'.$row['attachment_path'];if(!is_file($file)){http_response_code(404);exit;}
header('Content-Type: application/octet-stream');header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');header("Content-Disposition: attachment; filename=\"attachment\"; filename*=UTF-8''".rawurlencode($row['attachment_name']));header('Content-Length: '.filesize($file));readfile($file);
