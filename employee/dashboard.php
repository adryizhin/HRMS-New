<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['employee', 'admin', 'hr']);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/task_schema.php';

ensureTaskSchema($conn);
require_once __DIR__.'/../includes/appraisal.php';
ensureAppraisalSchema($conn);

$user_id = (int) $_SESSION['user_id'];
$today = new DateTimeImmutable('today', new DateTimeZone('Asia/Manila'));
$todayDate = $today->format('Y-m-d');

$evaluationSearch=is_string($_GET['q']??null)?mb_substr(trim($_GET['q']),0,160):'';
$evaluationFilter=in_array($_GET['evaluation_status']??'all',['all','not_submitted','submitted','reviewed'],true)?($_GET['evaluation_status']??'all'):'all';
$evaluationPattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$evaluationSearch).'%';
$evaluationWhere=match($evaluationFilter){'not_submitted'=>" AND COALESCE(submission_file,'')=''",'submitted'=>" AND COALESCE(submission_file,'')<>''",'reviewed'=>" AND status='reviewed'",default=>''};
/* ASSIGNMENTS */
$assignments = $conn->prepare("
    SELECT 
        id,
        assignment_batch,
        title,
        description,
        deadline,
        file_attachment,
        file_name,
        file_path,
        file_type,
        uploaded_by,
        submission_file,
        submitted_at,
        status,
        hr_score,
        hr_feedback,
        created_at,
        EXISTS(SELECT 1 FROM task_appraisals WHERE task_id=tasks.id) AS has_appraisal
    FROM tasks
    WHERE employee_id = ? AND CONCAT_WS(' ',title,description) LIKE ? ESCAPE '!' $evaluationWhere
    ORDER BY id DESC
");
$assignments->bind_param("is", $user_id, $evaluationPattern);
$assignments->execute();
$assignments = $assignments->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Dashboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= htmlspecialchars(app_url('employee/dashboard.css')); ?>">
</head>

<body>

<?php renderSidebar($_SESSION['role'], $_SESSION['role'] === 'employee' ? 'employee.dashboard' : $_SESSION['role'] . '.evaluations'); ?>

<div class="main">

<div class="topbar">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['name'],ENT_QUOTES,'UTF-8'); ?></h2>
  <div class="activity-header-slot"></div>
</div>

<?php if (isset($_GET['success'])): ?>
  <div class="task-alert success"><i class="fa-solid fa-circle-check"></i>
    <?= isset($_GET['duplicate'])
        ? 'Duplicate upload ignored; the existing file and submission date were kept.'
        : 'Evaluation submitted successfully.'; ?>
  </div>
<?php endif; ?>
<?php if (isset($_GET['expired'])): ?>
  <div class="task-alert error"><i class="fa-solid fa-triangle-exclamation"></i>
    Submission is not allowed because the evaluation deadline has passed.
  </div>
<?php endif; ?>

<div class="task-section-header">
  <div>
    <p class="eyebrow">Assigned Work</p>
    <h2>My Tasks</h2>
  </div>
  <span class="task-count"><?= $assignments ? (int) $assignments->num_rows : 0; ?> active</span>
</div>

<link rel="stylesheet" href="<?= htmlspecialchars(app_url('includes/evaluation_search.css')) ?>">
<form method="get" class="evaluation-search" role="search"><label for="evaluation-search">Search my evaluations<input id="evaluation-search" type="search" name="q" value="<?= htmlspecialchars($evaluationSearch,ENT_QUOTES,'UTF-8') ?>" placeholder="Evaluation title or instructions"></label><label for="evaluation-status">Show<select id="evaluation-status" name="evaluation_status"><?php foreach(['all'=>'All evaluations','not_submitted'=>'Not submitted','submitted'=>'Submitted (including reviewed)','reviewed'=>'Reviewed'] as $key=>$label): ?><option value="<?= $key ?>" <?= $evaluationFilter===$key?'selected':'' ?>><?= $label ?></option><?php endforeach ?></select></label><button type="submit"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Search</button><a href="<?= htmlspecialchars(route($_SESSION['role'].'.'.($_SESSION['role']==='employee'?'tasks':'evaluations'))) ?>">Clear</a></form>
<div class="task-grid">

<?php if ($assignments && $assignments->num_rows > 0): ?>
<?php while($a = $assignments->fetch_assoc()): 
    $status = $a['status'] ?: 'pending';
    $submissionRequestKey = bin2hex(random_bytes(32));
    $statusLabel = $status === 'reviewed' ? 'Reviewed' : ($status === 'submitted' ? 'Submitted' : 'Pending');
    $statusIcon = $status === 'reviewed' ? 'fa-circle-check' : ($status === 'submitted' ? 'fa-clock' : 'fa-hourglass-half');
    $deadlineText = $a['deadline'] ? date("M d, Y", strtotime($a['deadline'])) : 'No deadline';
    $isOverdue = $a['deadline'] && $a['deadline'] < $todayDate;
    $batchLabel = $a['assignment_batch'] ?: 'TASK-' . $a['id'];
    if (preg_match('/\Ademo-evaluation-v1-(employee|hr|admin)-(\d+)\z/', $batchLabel, $batchParts)) {
        $batchLabel = 'EVAL-' . strtoupper($batchParts[1]) . '-' . str_pad((string)((int)$batchParts[2] + 1), 2, '0', STR_PAD_LEFT);
    }
?>

<article class="task-card <?= htmlspecialchars($status); ?>">

    <div class="task-card-head">
        <div>
            <span class="task-batch"><?= htmlspecialchars($batchLabel); ?></span>
            <h3><?= htmlspecialchars($a['title']); ?></h3>
        </div>
        <span class="status-chip <?= htmlspecialchars($status); ?>">
            <i class="fa-solid <?= $statusIcon; ?>"></i> <?= $statusLabel; ?>
        </span>
    </div>

    <p class="task-description"><?= nl2br(htmlspecialchars($a['description'] ?: 'No instructions provided.')); ?></p>

    <div class="task-meta-list">
        <span class="<?= $isOverdue ? 'overdue' : ''; ?>">
            <i class="fa-solid fa-calendar-day"></i> Due <?= htmlspecialchars($deadlineText); ?>
        </span>
        <span>
            <i class="fa-solid fa-paper-plane"></i>
            <?= $a['submitted_at'] ? 'Submitted ' . date("M d, Y h:i A", strtotime($a['submitted_at'])) : 'Not submitted yet'; ?>
        </span>
    </div>

    <div class="task-actions">
        <?php if($a['has_appraisal']): ?><a class="task-link" href="<?= htmlspecialchars(app_url('employee/view_appraisal.php?id='.(int)$a['id'])); ?>"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i> View Appraisal</a><?php endif; ?>
        <?php if (!empty($a['file_attachment'])): ?>
            <a class="task-link" href="../uploads/tasks/<?= htmlspecialchars($a['file_attachment']); ?>" target="_blank">
                <i class="fa-solid fa-paperclip"></i> View Attachment
            </a>
        <?php elseif (!empty($a['file_path'])): ?>
            <a class="task-link" href="../<?= htmlspecialchars(ltrim($a['file_path'], './')); ?>" target="_blank">
                <i class="fa-solid fa-paperclip"></i> View Attachment
            </a>
        <?php else: ?>
            <span class="task-muted"><i class="fa-solid fa-paperclip"></i> No attachment</span>
        <?php endif; ?>

        <?php if (!empty($a['submission_file'])): ?>
            <a class="task-link secondary" href="../uploads/submissions/<?= htmlspecialchars($a['submission_file']); ?>" target="_blank">
                <i class="fa-solid fa-file-arrow-up"></i> My Submission
            </a>
        <?php endif; ?>
    </div>

    <?php if ($a['hr_score'] !== null): ?>
        <div class="score-panel">
            <div>
                <span class="score-value"><?= rtrim(rtrim(number_format((float)$a['hr_score'],2,'.',''),'0'),'.'); ?>%</span>
                <span class="score-label">HR Score</span>
            </div>
            <?php if (!empty($a['hr_feedback'])): ?>
                <p><?= nl2br(htmlspecialchars($a['hr_feedback'])); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($isOverdue): ?>
        <div class="task-alert error">
            <i class="fa-solid fa-ban"></i>
            Submission is not allowed because the evaluation deadline has passed.
        </div>
    <?php else: ?>
    <form class="submission-form" action="<?= htmlspecialchars(app_url('employee/submit_assignment.php')); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="assignment_id" value="<?= $a['id']; ?>">
        <input type="hidden" name="submission_request_key" value="<?= htmlspecialchars($submissionRequestKey); ?>">

        <label>
            <span><?= !empty($a['submission_file']) ? 'Replace Submission' : 'Upload Submission'; ?></span>
            <input type="file" name="submission_file" required>
        </label>

        <button type="submit" <?= $status === 'reviewed' ? 'disabled' : ''; ?>>
            <i class="fa-solid fa-upload"></i>
            <?= !empty($a['submission_file']) ? 'Resubmit' : 'Submit to HR'; ?>
        </button>
    </form>
    <?php endif; ?>

</article>

<?php endwhile; ?>
<?php else: ?>
  <div class="empty-tasks">
    <i class="fa-solid fa-clipboard-list"></i>
    <h3>No assigned tasks</h3>
    <p>No evaluations match the current search and status. Clear the filters to see all assignments.</p>
  </div>
<?php endif; ?>

</div>

</div>

</body>
</html>
