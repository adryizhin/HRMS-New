<?php
require_once __DIR__.'/../includes/auth_check.php'; checkRole(['admin']);
require_once __DIR__.'/../includes/sidebar.php';
if (!isSystemAdministrator($conn,(int)$_SESSION['user_id'])) {http_response_code(403);exit('Only the System Administrator can assign Human Resource evaluations.');}
require_once __DIR__.'/../includes/task_schema.php';ensureTaskSchema($conn);
$tab=($_GET['tab']??'assign')==='review'?'review':'assign';
$hr_id=(int)$_SESSION['user_id'];
$reviewStmt=$conn->prepare("SELECT t.*,u.full_name AS employee_name,u.employee_id AS emp_id FROM tasks t JOIN users u ON u.id=t.employee_id WHERE t.hr_id=? AND u.role='hr' AND t.submission_file IS NOT NULL AND t.submission_file<>'' ORDER BY t.submitted_at DESC,t.id DESC");
$reviewStmt->bind_param('i',$hr_id);$reviewStmt->execute();$submissions=$reviewStmt->get_result();
$_SESSION['evaluation_assignment_csrf'] ??= bin2hex(random_bytes(32));
$staff=$conn->query("SELECT id,full_name FROM users WHERE role='hr' AND status='active' ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);
$h=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Human Resource Evaluations</title><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"><link rel="stylesheet" href="dashboard.css"><link rel="stylesheet" href="password_tickets.css"><link rel="stylesheet" href="../hr/submissions.css?v=<?= filemtime(__DIR__.'/../hr/submissions.css') ?>"><link rel="stylesheet" href="hr_evaluations.css?v=<?= filemtime(__DIR__.'/hr_evaluations.css') ?>"></head><body>
<?php renderSidebar('admin','admin.hr_evaluations'); ?><main class="main tickets-main"><div class="topbar"><div><h1>Human Resource Evaluations</h1><p>Assign work and review submissions from your Human Resource team.</p></div></div>
<nav class="hr-evaluation-tabs" aria-label="Human Resource evaluation views"><a href="<?= $h(route('admin.hr_evaluations')) ?>" <?= $tab==='assign'?'aria-current="page"':'' ?>><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Assign Evaluations</a><a href="<?= $h(route('admin.review_hr_evaluations')) ?>" <?= $tab==='review'?'aria-current="page"':'' ?>><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i> Review Evaluations</a></nav>
<?php if($tab==='assign'): ?>
<?php if(isset($_GET['task_sent'])): ?><div class="ticket-notice" role="status">Evaluations assigned: <?= (int)$_GET['task_sent'] ?>. Duplicate assignments skipped: <?= (int)($_GET['task_duplicate']??0) ?>.</div><?php elseif(isset($_GET['task_error'])||isset($_GET['task_date_error'])): ?><div class="ticket-notice" role="alert">Complete the required fields, select active staff and use a valid deadline.</div><?php endif ?>
<section class="ticket-card"><form class="ticket-form" action="<?= $h(app_url('hr/post_task.php')) ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= $h($_SESSION['evaluation_assignment_csrf']) ?>">
<label for="title">Evaluation title</label><input id="title" name="title" required>
<label for="description">Instructions</label><textarea id="description" name="description" required rows="4"></textarea>
<label for="deadline">Deadline</label><input type="date" id="deadline" name="deadline" min="<?= date('Y-m-d') ?>" required>
<label for="attachment">Attachment (optional)</label><input type="file" id="attachment" name="file">
<fieldset style="padding:16px;border:1px solid var(--btn-border);border-radius:8px"><legend>Human Resource recipients</legend>
<?php foreach($staff as $user): ?><label style="display:block;padding:8px"><input type="checkbox" name="employee_ids[]" value="<?= (int)$user['id'] ?>"> <?= $h($user['full_name']) ?></label><?php endforeach ?>
<?php if(!$staff): ?><p>No active Human Resource staff available.</p><?php endif ?></fieldset>
<button type="submit">Assign evaluations</button></form></section><?php else: ?><section aria-label="Human Resource submissions"><?php require __DIR__.'/../includes/evaluation_submissions.php'; ?></section><?php endif ?></main></body></html>
