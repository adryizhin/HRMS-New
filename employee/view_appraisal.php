<?php
require_once __DIR__.'/../includes/auth_check.php';checkRole(['employee','hr','admin']);
require_once __DIR__.'/../includes/sidebar.php';require_once __DIR__.'/../includes/task_schema.php';require_once __DIR__.'/../includes/appraisal.php';
ensureTaskSchema($conn);ensureAppraisalSchema($conn);
$id=(int)($_GET['id']??0);$user=(int)$_SESSION['user_id'];
$get=$conn->prepare('SELECT t.*,u.full_name AS employee_name,u.employee_id AS emp_id,e.full_name AS evaluator_name,a.payload FROM task_appraisals a JOIN tasks t ON t.id=a.task_id JOIN users u ON u.id=t.employee_id JOIN users e ON e.id=a.evaluator_id WHERE t.id=? AND t.employee_id=?');
$get->bind_param('ii',$id,$user);$get->execute();$task=$get->get_result()->fetch_assoc();if(!$task){http_response_code(404);exit('Appraisal not found.');}
$data=json_decode($task['payload'],true);$readOnly=true;$h=static fn($v)=>htmlspecialchars(is_scalar($v)?(string)$v:'',ENT_QUOTES,'UTF-8');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>My Employee Performance Appraisal</title><link rel="stylesheet" href="<?= $h(app_url('hr/dashboard.css')) ?>"><link rel="stylesheet" href="<?= $h(app_url('hr/appraisal.css').'?v='.filemtime(__DIR__.'/../hr/appraisal.css')) ?>"></head><body>
<?php renderSidebar($_SESSION['role'],$_SESSION['role'].'.'.($_SESSION['role']==='employee'?'tasks':'evaluations')); ?><main class="main appraisal-main"><header class="appraisal-toolbar"><a class="app-button secondary" href="<?= $h(route($_SESSION['role'].'.'.($_SESSION['role']==='employee'?'tasks':'evaluations'))) ?>">Back to My Evaluations</a><button type="button" onclick="window.print()"><i class="fa-solid fa-print" aria-hidden="true"></i> Print / Save PDF</button></header><?php require __DIR__.'/../includes/appraisal_form.php'; ?></main><script src="<?= $h(app_url('hr/appraisal.js')) ?>" defer></script></body></html>
