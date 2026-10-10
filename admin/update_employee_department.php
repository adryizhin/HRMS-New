<?php
require_once __DIR__.'/../includes/auth_check.php';checkRole(['admin']);
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);header('Allow: POST');exit('Use the department editor to update an employee.');}
$csrf=$_POST['csrf_token']??'';
if(!is_string($csrf)||empty($_SESSION['employee_department_csrf'])||!hash_equals($_SESSION['employee_department_csrf'],$csrf)){http_response_code(400);exit('Reload the employee list and try again.');}
$userId=filter_var($_POST['user_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$rawDepartment=$_POST['department_id']??'';
$departmentId=$rawDepartment===''?null:filter_var($rawDepartment,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if(!$userId||$departmentId===false){http_response_code(400);exit('Select a valid employee and department.');}
$check=$conn->prepare('SELECT id FROM users WHERE id=?');$check->bind_param('i',$userId);$check->execute();
if(!$check->get_result()->fetch_assoc()){http_response_code(404);exit('Employee not found.');}
if($departmentId!==null){$check=$conn->prepare('SELECT id FROM departments WHERE id=?');$check->bind_param('i',$departmentId);$check->execute();if(!$check->get_result()->fetch_assoc()){http_response_code(400);exit('Select an existing department.');}}
try{$stmt=$conn->prepare('UPDATE users SET department_id=? WHERE id=?');$stmt->bind_param('ii',$departmentId,$userId);$stmt->execute();$result='department_updated=1';}
catch(mysqli_sql_exception $error){$result='department_error=1';}
header('Location: '.route('admin.users').'?'.$result, true, 303);exit;
