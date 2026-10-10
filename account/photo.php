<?php
require_once __DIR__.'/../includes/auth_check.php';
require_once __DIR__.'/../includes/account_profile.php';ensureAccountSchema($conn);
$id=(int)($_GET['id']??0);$stmt=$conn->prepare('SELECT profile_picture FROM users WHERE id=?');$stmt->bind_param('i',$id);$stmt->execute();$name=$stmt->get_result()->fetch_assoc()['profile_picture']??'';
if(!preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/',$name)){http_response_code(404);exit;}
$file=__DIR__.'/../uploads/profile_photos/'.$name;if(!is_file($file)){http_response_code(404);exit;}
header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-cache');header('Content-Type: '.(new finfo(FILEINFO_MIME_TYPE))->file($file));readfile($file);
