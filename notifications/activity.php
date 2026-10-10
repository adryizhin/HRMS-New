<?php
$refreshSessionActivity=false;
require_once __DIR__.'/../includes/auth_check.php';checkRole(['admin','hr','employee']);
require_once __DIR__.'/../includes/notifications.php';
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
$userId=(int)$_SESSION['user_id'];ensureActivitySchema($conn);
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!hash_equals((string)($_SESSION['activity_csrf']??''),(string)($_POST['csrf_token']??''))||empty($_SESSION['activity_csrf'])){http_response_code(400);echo json_encode(['error'=>'Reload the page and try again.']);exit;}
    $ids=array_values(array_filter(array_map('intval',explode(',',(string)($_POST['ids']??''))),static fn($id)=>$id>0));
    if(count($ids)>10){http_response_code(400);exit;}
    if($ids){$sql='UPDATE notifications SET is_read=1 WHERE user_id=? AND id IN ('.implode(',',array_fill(0,count($ids),'?')).')';$stmt=$conn->prepare($sql);$values=[$userId,...$ids];$stmt->bind_param(str_repeat('i',count($values)),...$values);$stmt->execute();}
}elseif($_SERVER['REQUEST_METHOD']!=='GET'){http_response_code(405);exit;}
echo json_encode(activitySummary($conn,$userId,(string)$_SESSION['role']),JSON_THROW_ON_ERROR);
