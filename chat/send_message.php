<?php
require_once __DIR__.'/../includes/auth_check.php';checkRole(['admin','hr']);
require_once __DIR__.'/../includes/chat_attachments.php';ensureChatAttachments($conn);
header('Content-Type: text/plain; charset=utf-8');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Use POST.');}
$sender=(int)$_SESSION['user_id'];$receiver=(int)($_POST['receiver_id']??0);$message=trim((string)($_POST['message']??''));$file=$_FILES['attachment']??null;$hasFile=$file&&$file['error']!==UPLOAD_ERR_NO_FILE;
$stored=null;
try {
    if($hasFile&&(empty($_SESSION['chat_upload_csrf'])||!hash_equals($_SESSION['chat_upload_csrf'],(string)($_POST['csrf_token']??'')))){http_response_code(400);throw new InvalidArgumentException('Reload the chat before uploading.');}
    if(($message===''&&!$hasFile)||strlen($message)>20000)throw new InvalidArgumentException('Enter a message or choose a file.');
    $stmt=$conn->prepare("SELECT id FROM users WHERE id=? AND role IN ('admin','hr') AND status='active'");$stmt->bind_param('i',$receiver);$stmt->execute();if($sender===$receiver||!$stmt->get_result()->fetch_assoc())throw new InvalidArgumentException('Choose an active HR or admin contact.');
    $name=null;$size=null;
    if($hasFile){$ext=validateChatAttachment($file);$stored=bin2hex(random_bytes(16)).'.'.$ext;$name=mb_substr(str_replace(["\r","\n",'\\','/'], '_', basename($file['name'])),0,255);$size=(int)$file['size'];if(!move_uploaded_file($file['tmp_name'],__DIR__.'/../uploads/private_chat/'.$stored))throw new RuntimeException('Upload failed.');}
    $stmt=$conn->prepare("INSERT INTO chat_messages (sender_id,receiver_id,message,status,is_delivered,is_seen,created_at,attachment_path,attachment_name,attachment_size) VALUES (?,?,?,'sent',0,0,NOW(),?,?,?)");$stmt->bind_param('iisssi',$sender,$receiver,$message,$stored,$name,$size);$stmt->execute();echo 'success';
}catch(Throwable $e){if($stored&&is_file(__DIR__.'/../uploads/private_chat/'.$stored))unlink(__DIR__.'/../uploads/private_chat/'.$stored);if(http_response_code()<400)http_response_code($e instanceof InvalidArgumentException?400:500);echo $e instanceof InvalidArgumentException?$e->getMessage():'Message could not be sent.';}
