<?php
require_once __DIR__.'/../includes/auth_check.php';checkRole(['admin']);
require_once __DIR__.'/../includes/evaluation_access.php';
if(!isSystemAdministrator($conn,(int)$_SESSION['user_id'])){http_response_code(403);exit('Only the System Administrator can assign Human Resource evaluations.');}
$query=$_GET;$query['tab']='assign';
header('Location: '.route('admin.hr_evaluations').'?'.http_build_query($query));exit;
