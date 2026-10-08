<?php
require_once __DIR__ . '/../includes/auth_check.php';
<<<<<<< HEAD
checkRole(['employee']);
=======
checkRole(['employee', 'admin', 'hr']);
>>>>>>> ea5c816 (partial done system)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/task_schema.php';

ensureTaskSchema($conn);

$user_id = (int) $_SESSION['user_id'];
<<<<<<< HEAD
<<<<<<< HEAD
$today = new DateTimeImmutable('today', new DateTimeZone('Asia/Manila'));
$todayDate = $today->format('Y-m-d');
=======
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
$today = new DateTimeImmutable('today', new DateTimeZone('Asia/Manila'));
$todayDate = $today->format('Y-m-d');
>>>>>>> ea5c816 (partial done system)

/* NOTIFICATIONS */
$notifQuery = $conn->query("
    SELECT * FROM notifications 
    WHERE user_id = $user_id 
    ORDER BY id DESC
");

$notifCountResult = $conn->query("
    SELECT COUNT(*) as total 
    FROM notifications 
    WHERE user_id = $user_id AND is_read = 0
");

$notifCount = $notifCountResult ? $notifCountResult->fetch_assoc()['total'] : 0;

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
        created_at
    FROM tasks
    WHERE employee_id = ?
    ORDER BY id DESC
");
$assignments->bind_param("i", $user_id);
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
<<<<<<< HEAD
<link rel="stylesheet" href="dashboard.css">
=======
<link rel="stylesheet" href="<?= htmlspecialchars(app_url('employee/dashboard.css')); ?>">
>>>>>>> ea5c816 (partial done system)
</head>

<body>

<<<<<<< HEAD
<?php renderSidebar('employee', 'employee.dashboard'); ?>
=======
<?php renderSidebar($_SESSION['role'], $_SESSION['role'] === 'employee' ? 'employee.dashboard' : $_SESSION['role'] . '.evaluations'); ?>
>>>>>>> ea5c816 (partial done system)

<div class="main">

<div class="topbar">
    <h2>Welcome, <?= $_SESSION['name']; ?></h2>
  <div style="display:flex; justify-content:flex-end;">
  <div style= "display:inline-block;
   padding:8px 18px;
    background:green;
    border-radius:20px;
<<<<<<< HEAD
    color:#fffff;
    font-weight:bold;
<<<<<<< HEAD
    font-size:15px;">Employee</div></div>
=======
    color:#ffffff;
    font-weight:bold;
    font-size:15px;"><?= htmlspecialchars(ucfirst($_SESSION['role'])); ?></div></div>
>>>>>>> ea5c816 (partial done system)
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
<<<<<<< HEAD
=======
    font-size:15px;">Employee</div>
</div>
</div>

<?php if (isset($_GET['success'])): ?>
  <div class="task-alert success"><i class="fa-solid fa-circle-check"></i> Task submitted successfully.</div>
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
<?php endif; ?>

<div class="task-section-header">
  <div>
    <p class="eyebrow">Assigned Work</p>
    <h2>My Tasks</h2>
  </div>
  <span class="task-count"><?= $assignments ? (int) $assignments->num_rows : 0; ?> active</span>
</div>

<div class="task-grid">

<?php if ($assignments && $assignments->num_rows > 0): ?>
<?php while($a = $assignments->fetch_assoc()): 
    $status = $a['status'] ?: 'pending';
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
    $submissionRequestKey = bin2hex(random_bytes(32));
    $statusLabel = $status === 'reviewed' ? 'Reviewed' : ($status === 'submitted' ? 'Submitted' : 'Pending');
    $statusIcon = $status === 'reviewed' ? 'fa-circle-check' : ($status === 'submitted' ? 'fa-clock' : 'fa-hourglass-half');
    $deadlineText = $a['deadline'] ? date("M d, Y", strtotime($a['deadline'])) : 'No deadline';
    $isOverdue = $a['deadline'] && $a['deadline'] < $todayDate;
<<<<<<< HEAD
=======
    $statusLabel = $status === 'reviewed' ? 'Reviewed' : ($status === 'submitted' ? 'Submitted' : 'Pending');
    $statusIcon = $status === 'reviewed' ? 'fa-circle-check' : ($status === 'submitted' ? 'fa-clock' : 'fa-hourglass-half');
    $deadlineText = $a['deadline'] ? date("M d, Y", strtotime($a['deadline'])) : 'No deadline';
    $isOverdue = $a['deadline'] && $status === 'pending' && strtotime($a['deadline'] . ' 23:59:59') < time();
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
?>

<article class="task-card <?= htmlspecialchars($status); ?>">

    <div class="task-card-head">
        <div>
            <span class="task-batch"><?= htmlspecialchars($a['assignment_batch'] ?: 'TASK-' . $a['id']); ?></span>
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
                <span class="score-value"><?= (int) $a['hr_score']; ?>%</span>
                <span class="score-label">HR Score</span>
            </div>
            <?php if (!empty($a['hr_feedback'])): ?>
                <p><?= nl2br(htmlspecialchars($a['hr_feedback'])); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
    <?php if ($isOverdue): ?>
        <div class="task-alert error">
            <i class="fa-solid fa-ban"></i>
            Submission is not allowed because the evaluation deadline has passed.
        </div>
    <?php else: ?>
<<<<<<< HEAD
    <form class="submission-form" action="submit_assignment.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="assignment_id" value="<?= $a['id']; ?>">
        <input type="hidden" name="submission_request_key" value="<?= htmlspecialchars($submissionRequestKey); ?>">
=======
    <form class="submission-form" action="submit_assignment.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="assignment_id" value="<?= $a['id']; ?>">
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
    <form class="submission-form" action="<?= htmlspecialchars(app_url('employee/submit_assignment.php')); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="assignment_id" value="<?= $a['id']; ?>">
        <input type="hidden" name="submission_request_key" value="<?= htmlspecialchars($submissionRequestKey); ?>">
>>>>>>> ea5c816 (partial done system)

        <label>
            <span><?= !empty($a['submission_file']) ? 'Replace Submission' : 'Upload Submission'; ?></span>
            <input type="file" name="submission_file" required>
        </label>

        <button type="submit" <?= $status === 'reviewed' ? 'disabled' : ''; ?>>
            <i class="fa-solid fa-upload"></i>
            <?= !empty($a['submission_file']) ? 'Resubmit' : 'Submit to HR'; ?>
        </button>
    </form>
<<<<<<< HEAD
<<<<<<< HEAD
    <?php endif; ?>
=======
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
    <?php endif; ?>
>>>>>>> ea5c816 (partial done system)

</article>

<?php endwhile; ?>
<?php else: ?>
  <div class="empty-tasks">
    <i class="fa-solid fa-clipboard-list"></i>
    <h3>No assigned tasks</h3>
    <p>New HR assignments will appear here.</p>
  </div>
<?php endif; ?>

</div>

</div>

</body>
</html>
