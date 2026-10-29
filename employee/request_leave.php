<?php
require '../includes/auth_check.php';
checkRole(['employee']);
require '../config.php';
require_once __DIR__.'/../includes/sidebar.php';
require_once __DIR__.'/../includes/leave.php';
ensureLeaveSchema($conn);
$_SESSION['leave_csrf'] ??= bin2hex(random_bytes(32));
$_SESSION['leave_request_key'] ??= bin2hex(random_bytes(32));
$employee_id=(int)$_SESSION['user_id'];
$h=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
$values=['hr_id'=>'','leave_type'=>'','start_date'=>'','end_date'=>'','reason'=>''];
foreach($values as $name=>$default) if($_SERVER['REQUEST_METHOD']==='POST') $values[$name]=is_string($_POST[$name]??null)?$_POST[$name]:'';
$error='';$preview=null;
$hrs=$conn->query("SELECT id,full_name FROM users WHERE role='hr' AND status='active' ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);
if($_SERVER['REQUEST_METHOD']==='POST') {
 try {
  if(!hash_equals($_SESSION['leave_csrf'],(string)($_POST['csrf_token']??''))) throw new InvalidArgumentException('Reload the form and try again.');
  $dates=leaveWorkingDates($conn,$values['start_date'],$values['end_date']);
  if(!$dates) throw new InvalidArgumentException('The request must include at least one working day. Weekends and holidays do not use leave days.');
  $preview=['days'=>count($dates),'years'=>[]];
  foreach($dates as $date){$year=(int)substr($date,0,4);$preview['years'][$year]=($preview['years'][$year]??0)+1;}
  if(($_POST['action']??'submit')!=='preview') {
   $key=(string)($_POST['request_key']??'');
   if(!hash_equals($_SESSION['leave_request_key'],$key)) throw new InvalidArgumentException('This form has already been submitted. Reload to make another request.');
   submitLeaveRequest($conn,$employee_id,(int)$values['hr_id'],$values['leave_type'],$values['start_date'],$values['end_date'],$values['reason'],$key);
   $_SESSION['leave_request_key']=bin2hex(random_bytes(32));
   $_SESSION['leave_notice']='Leave request sent successfully. '.$preview['days'].' working day(s) reserved. Your assigned Human Resource reviewer will review it.';
   header('Location: request_leave.php');exit;
  }
 } catch(InvalidArgumentException $e){$error=$e->getMessage();}
 catch(Throwable $e){$error='Unable to submit your request. Your leave balance has not changed. Please try again.';}
}
$success=(string)($_SESSION['leave_notice']??'');unset($_SESSION['leave_notice']);
$remainingDays=remainingLeaveDays($conn,$employee_id,(int)date('Y'));
$stmt=$conn->prepare("SELECT r.*,hr.full_name AS reviewer_name,reviewer.full_name AS decision_by,
 (SELECT COUNT(*) FROM leave_request_days d WHERE d.request_id=r.id) AS working_days
 FROM leave_requests r LEFT JOIN users hr ON hr.id=r.hr_id LEFT JOIN users reviewer ON reviewer.id=r.reviewed_by
 WHERE r.employee_id=? ORDER BY r.id DESC");
$stmt->bind_param('i',$employee_id);$stmt->execute();$history=$stmt->get_result();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Request Leave</title><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"><link rel="stylesheet" href="request_leave.css?v=20261029"></head><body>
<?php renderSidebar('employee','employee.leave_request'); ?>
<main class="main employee-leave-main">
<header class="topbar"><div><h1><i class="fa-solid fa-calendar-plus" aria-hidden="true"></i> Request leave</h1><p>Submit a request and track your Human Resource review.</p></div></header>
<?php if($success): ?><div class="success" role="status"><?= $h($success) ?></div><?php endif ?>
<?php if($error): ?><div class="error" role="alert"><?= $h($error) ?></div><?php endif ?>
<section class="leave-balance"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i><div><h2><?= $remainingDays ?> working days remaining · <?= date('Y') ?></h2><p>Annual allowance: 15 working days shared by Sick Leave and Vacation Leave. Pending and approved requests reserve days. Rejected requests restore them. Weekends and calendar holidays are excluded; cross-year requests use each year's balance.</p></div></section>
<div class="employee-leave-layout"><section class="leave-card"><h2>New leave request</h2>
<?php if(!$hrs): ?><p class="error" role="alert">No active Human Resource reviewer is available. Contact your administrator before submitting.</p><?php endif ?>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= $h($_SESSION['leave_csrf']) ?>"><input type="hidden" name="request_key" value="<?= $h($_SESSION['leave_request_key']) ?>">
<label for="hr_id">Human Resource reviewer</label><select id="hr_id" name="hr_id" required><option value="">Choose your reviewer</option><?php foreach($hrs as $hr): ?><option value="<?= (int)$hr['id'] ?>" <?= (int)$values['hr_id']===(int)$hr['id']?'selected':'' ?>><?= $h($hr['full_name']) ?></option><?php endforeach ?></select>
<label for="leave_type">Leave type</label><select id="leave_type" name="leave_type" required><option value="">Choose leave type</option><?php foreach(['Sick Leave','Vacation Leave'] as $type): ?><option <?= $values['leave_type']===$type?'selected':'' ?>><?= $type ?></option><?php endforeach ?></select>
<div class="leave-date-fields"><div><label for="start_date">Start date</label><input id="start_date" name="start_date" type="date" value="<?= $h($values['start_date']) ?>" required></div><div><label for="end_date">End date</label><input id="end_date" name="end_date" type="date" value="<?= $h($values['end_date']) ?>" required></div></div>
<label for="reason">Detailed reason</label><textarea id="reason" name="reason" rows="5" maxlength="2000" placeholder="Explain why you need leave and any relevant details for your reviewer." required><?= $h($values['reason']) ?></textarea><p class="leave-help">Maximum 2,000 characters. Dates cannot overlap another pending or approved request.</p>
<?php if($preview): ?><div class="leave-preview" role="status"><strong><?= $preview['days'] ?> requested working day(s)</strong><?php foreach($preview['years'] as $year=>$days): $balance=remainingLeaveDays($conn,$employee_id,$year); ?><p><?= $year ?>: <?= $days ?> requested / <?= $balance ?> available. <?= $days>$balance?'Insufficient balance.':'Remaining after submission: '.($balance-$days).'.' ?></p><?php endforeach ?><small>Checking days does not submit or reserve leave.</small></div><?php endif ?>
<div class="leave-form-actions"><button type="submit" name="action" value="preview" class="leave-preview-button"><i class="fa-solid fa-calculator" aria-hidden="true"></i> Check working days</button><button type="submit" name="action" value="submit" <?= !$hrs?'disabled':'' ?>><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Submit Request</button></div>
</form></section>
<section class="leave-history"><h2><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i> My leave requests</h2><p class="leave-help">Your submission, reserved days, and review details appear here.</p>
<?php if(!$history->num_rows): ?><p class="leave-empty">You have not submitted a leave request yet.</p><?php endif ?>
<?php while($row=$history->fetch_assoc()): ?><article class="leave-history-card"><header><h3><?= $h($row['leave_type']) ?> · #<?= (int)$row['id'] ?></h3><span class="leave-history-status <?= $h($row['status']) ?>"><?= $h(ucfirst($row['status'])) ?></span></header><dl><dt>Dates</dt><dd><?= $h($row['start_date']) ?> to <?= $h($row['end_date']) ?></dd><dt>Working days</dt><dd><?= (int)$row['working_days'] ?> <?= $row['status']==='rejected'?'(restored; not charged)':'reserved' ?></dd><dt>Reviewer</dt><dd><?= $h($row['reviewer_name']??'Unavailable') ?></dd><dt>Submitted</dt><dd><?= $h($row['created_at']) ?></dd><?php if($row['reviewed_at']): ?><dt>Reviewed</dt><dd><?= $h($row['reviewed_at']) ?> · <?= $h($row['decision_by']??$row['reviewer_name']) ?></dd><?php endif ?></dl><h4>Reason</h4><p><?= nl2br($h($row['reason'])) ?></p><?php if($row['review_notes']): ?><h4>Human Resource note</h4><p><?= nl2br($h($row['review_notes'])) ?></p><?php endif ?></article><?php endwhile ?>
</section></div></main></body></html>
