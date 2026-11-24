<?php
require_once __DIR__.'/../includes/auth_check.php';checkRole(['hr','admin']);
require_once __DIR__.'/../includes/sidebar.php';
require_once __DIR__.'/../includes/task_schema.php';
require_once __DIR__.'/../includes/appraisal.php';
if($_SESSION['role']==='admin'&&!isSystemAdministrator($conn,(int)$_SESSION['user_id'])){http_response_code(403);exit('Access denied.');}
ensureTaskSchema($conn);ensureActivitySchema($conn);ensureAppraisalSchema($conn);
$evaluator=(int)$_SESSION['user_id'];$taskId=(int)($_GET['id']??0);
$get=$conn->prepare('SELECT t.*,u.full_name AS employee_name,u.employee_id AS emp_id,u.role AS employee_role,d.name AS department_name,e.full_name AS evaluator_name FROM tasks t JOIN users u ON u.id=t.employee_id LEFT JOIN departments d ON d.id=u.department_id JOIN users e ON e.id=t.hr_id WHERE t.id=? AND t.hr_id=?');
$get->bind_param('ii',$taskId,$evaluator);$get->execute();$task=$get->get_result()->fetch_assoc();
if(!$task){http_response_code(404);exit('Task not found or unauthorized.');}
$_SESSION['appraisal_csrf']??=bin2hex(random_bytes(32));
$_SESSION['appraisal_submission_key']??=bin2hex(random_bytes(32));
$saved=$conn->prepare('SELECT * FROM task_appraisals WHERE task_id=? ORDER BY appraisal_id DESC LIMIT 1');$saved->bind_param('i',$taskId);$saved->execute();$latestAppraisal=$saved->get_result()->fetch_assoc();
$appraisalId=(int)($_GET['appraisal_id']??0);$appraisal=$latestAppraisal;
if($appraisalId>0){$selected=$conn->prepare('SELECT * FROM task_appraisals WHERE task_id=? AND appraisal_id=? LIMIT 1');$selected->bind_param('ii',$taskId,$appraisalId);$selected->execute();$appraisal=$selected->get_result()->fetch_assoc();if(!$appraisal){http_response_code(404);exit('Appraisal not found.');}}
$historyQuery=$conn->prepare('SELECT appraisal_id,total_score,created_at FROM task_appraisals WHERE task_id=? ORDER BY appraisal_id DESC');$historyQuery->bind_param('i',$taskId);$historyQuery->execute();$appraisalHistory=$historyQuery->get_result()->fetch_all(MYSQLI_ASSOC);
$addAnother=($_GET['new']??'')==='1';
$today=(new DateTimeImmutable('now',new DateTimeZone('Asia/Manila')))->format('Y-m-d');
$data=($appraisal&&!$addAnother)?json_decode($appraisal['payload'],true):['position'=>$task['employee_role']==='hr'?'Human Resource':ucfirst($task['employee_role']),'department'=>$task['department_name']?:'Unassigned','period_start'=>substr($today,0,7).'-01','period_end'=>$today,'accomplished_date'=>$today,'evaluator_date'=>$today,'evaluator_position'=>$_SESSION['role']==='hr'?'Human Resource':'System Administrator','potential'=>$task['hr_feedback']??''];
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $data=$_POST;
 try{
  if(!is_string($_POST['csrf_token']??null)||!hash_equals($_SESSION['appraisal_csrf'],$_POST['csrf_token'])){http_response_code(400);throw new InvalidArgumentException('Reload the appraisal form and try again.');}
  if(!is_string($_POST['submission_key']??null)||!hash_equals($_SESSION['appraisal_submission_key'],$_POST['submission_key']))throw new InvalidArgumentException('This appraisal was already saved. Review the saved copy below.');
  $newAppraisalId=saveTaskAppraisal($conn,$taskId,$evaluator,$_POST);
  $_SESSION['appraisal_submission_key']=bin2hex(random_bytes(32));
  $_SESSION['appraisal_notice']='Appraisal saved as a new record. Earlier appraisals remain unchanged.';
  header('Location: score_submission.php?id='.$taskId.'&appraisal_id='.$newAppraisalId,true,303);exit;
 }catch(InvalidArgumentException $e){$error=$e->getMessage();}
 catch(Throwable $e){$error='Unable to save the appraisal. No score was changed. Please try again.';}
}
$notice=(string)($_SESSION['appraisal_notice']??'');unset($_SESSION['appraisal_notice']);$readOnly=(bool)$appraisal&&!$addAnother;
$h=static fn($v)=>htmlspecialchars(is_scalar($v)?(string)$v:'',ENT_QUOTES,'UTF-8');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Employee Performance Appraisal</title><link rel="stylesheet" href="<?= $h(app_url('hr/dashboard.css')) ?>"><link rel="stylesheet" href="<?= $h(app_url('hr/appraisal.css').'?v='.filemtime(__DIR__.'/appraisal.css')) ?>"></head><body>
<?php renderSidebar($_SESSION['role'],$_SESSION['role']==='admin'?'admin.hr_evaluations':'hr.task_submissions'); ?>
<main class="main appraisal-main"><header class="appraisal-toolbar"><a class="app-button secondary" href="<?= $h(route($_SESSION['role']==='admin'?'admin.review_hr_evaluations':'hr.task_submissions')) ?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to evaluations</a><?php if($appraisal&&!$addAnother): ?><a class="app-button secondary" href="<?= $h(app_url('hr/score_submission.php?id='.$taskId.'&new=1')) ?>">Add another appraisal</a><?php endif ?><button type="button" onclick="window.print()"><i class="fa-solid fa-print" aria-hidden="true"></i> Print / Save PDF</button></header>
<?php if($notice): ?><p class="appraisal-notice" role="status"><?= $h($notice) ?></p><?php endif ?><?php if($error): ?><p class="appraisal-notice" role="alert"><?= $h($error) ?></p><?php endif ?>
<?php if(!$appraisal&&$task['hr_score']!==null): ?><p class="appraisal-notice">Existing evaluation score: <?= $h($task['hr_score']) ?>%. Saving an appraisal creates a new record; existing appraisal records remain unchanged.</p><?php endif ?>
<?php if(count($appraisalHistory)>1): ?><section class="appraisal-history" aria-label="Saved appraisal history"><h2>Saved appraisals</h2><ul><?php foreach($appraisalHistory as $savedCopy): ?><li><a href="<?= $h(app_url('hr/score_submission.php?id='.$taskId.'&appraisal_id='.(int)$savedCopy['appraisal_id'])) ?>" <?= (int)$savedCopy['appraisal_id']===(int)($appraisal['appraisal_id']??0)?'aria-current="page"':'' ?>>Appraisal #<?= (int)$savedCopy['appraisal_id'] ?> · <?= $h(date('M j, Y g:i A',strtotime($savedCopy['created_at']))) ?> · <?= number_format((float)$savedCopy['total_score'],2) ?>%</a></li><?php endforeach ?></ul></section><?php endif ?>
<?php require __DIR__.'/../includes/appraisal_form.php'; ?></main><script src="<?= $h(app_url('hr/appraisal.js').'?v='.filemtime(__DIR__.'/appraisal.js')) ?>" defer></script></body></html>
