<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['hr']);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/chat_contacts.php';
require_once __DIR__ . '/../includes/task_schema.php';

ensureTaskSchema($conn);

$hr_id = (int) $_SESSION['user_id'];
$_SESSION['evaluation_assignment_csrf'] ??= bin2hex(random_bytes(32));
$todayDate = (new DateTimeImmutable('today', new DateTimeZone('Asia/Manila')))->format('Y-m-d');
$employees = $conn->query("
    SELECT users.id, users.employee_id, users.full_name, departments.name AS department_name
    FROM users
    LEFT JOIN departments ON users.department_id = departments.id
    WHERE users.role IN ('employee', 'admin')
    AND users.status = 'active'
    ORDER BY users.full_name ASC
");

$taskBatches = $conn->query("
    SELECT
        COALESCE(t.assignment_batch, CONCAT('single-', t.id)) AS batch_key,
        MIN(t.id) AS sample_task_id,
        t.title,
        t.description,
        t.file_attachment,
        t.deadline,
        MIN(t.created_at) AS created_at,
        COUNT(*) AS recipient_count,
        SUM(t.status = 'pending') AS pending_count,
        SUM(t.status = 'submitted') AS submitted_count,
        SUM(t.status = 'reviewed') AS reviewed_count
    FROM tasks t
    WHERE t.hr_id = $hr_id
    GROUP BY batch_key, t.title, t.description, t.file_attachment, t.deadline
    ORDER BY created_at DESC
");


$result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='employee'");
$totalEmployees = $result ? $result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='hr'");
$totalHR = $result ? $result->fetch_assoc()['total'] : 0;

$result = $conn->query("
    SELECT COUNT(*) AS total 
    FROM tasks
    WHERE hr_id = $hr_id AND status='pending'
");
$pendingRequests = $result ? $result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE hr_id = $hr_id");
$systemLogs = $result ? $result->fetch_assoc()['total'] : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HR Dashboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="dashboard.css">

<link rel="stylesheet" href="dashboard_widgets.css?v=<?= filemtime(__DIR__ . '/dashboard_widgets.css') ?>">
<link rel="stylesheet" href="../admin/chat.css?v=<?= filemtime(__DIR__ . '/../admin/chat.css') ?>">
</head>

<body>

<?php renderSidebar('hr', 'hr.dashboard'); ?>

<!-- MAIN -->
<div class="main">

<div class="topbar">
  <h1>Welcome, <?= $_SESSION['name']; ?></h1>
  <div class="activity-header-slot"></div>
</div>

<!-- CARDS -->
<div class="cards">
    <div class="card">
      <h3>Total Employees</h3>
      <h1><?= $totalEmployees; ?></h1>
      <i class="fa-solid fa-users"></i>
    </div>

    <div class="card">
      <h3>HR Staff</h3>
      <h1><?= $totalHR; ?></h1>
      <i class="fa-solid fa-user-tie"></i>
    </div>

    <div class="card">
      <h3>Pending Tasks</h3>
      <h1><?= $pendingRequests; ?></h1>
      <i class="fa-solid fa-clock"></i>
    </div>

    <div class="card">
      <h3>Total Records</h3>
      <h1><?= $systemLogs; ?></h1>
      <i class="fa-solid fa-database"></i>
    </div>
</div>

<div class="hr-dashboard-panels">
<section class="chat-contacts" aria-labelledby="chat-contacts-heading">
<header class="contacts-header"><div class="contacts-title"><div><h2 id="chat-contacts-heading">Messages</h2><p>Connect with your administrators.</p></div></div></header>
<div class="contacts-search"><label class="contacts-sr-only" for="contact-search">Search contacts</label><input id="contact-search" type="search" placeholder="Search contacts" autocomplete="off"></div>
<div class="contact-groups"><?php $admins = $conn->query("SELECT id, full_name, profile_picture FROM users WHERE role='admin' AND status='active' ORDER BY full_name")->fetch_all(MYSQLI_ASSOC); renderChatContactGroup('Chat with admins', $admins, 'admin'); ?></div>
<p class="contacts-no-results" role="status" hidden>No contacts match your search.</p>
<p class="contacts-footer">Select a contact to start a conversation.</p>
</section>
<div class="hr-evaluation-panel">
<div class="task-composer" id="send-evaluation">
  <h2><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Send Evaluation</h2>


  <?php if (isset($_GET['task_sent'])): ?>
    <div class="alert success">
      Evaluation sent to <?= (int) $_GET['task_sent']; ?> recipient<?= (int) $_GET['task_sent'] === 1 ? '' : 's'; ?>.
      <?php if ((int) ($_GET['task_duplicate'] ?? 0) > 0): ?>
        <?= (int) $_GET['task_duplicate']; ?> identical evaluation<?= (int) $_GET['task_duplicate'] === 1 ? ' was' : 's were'; ?> skipped.
      <?php endif; ?>
    </div>
  <?php elseif (isset($_GET['task_error'])): ?>
    <div class="alert error">Task was not sent. Please select at least one active recipient and complete the required fields.</div>
  <?php elseif (isset($_GET['task_date_error'])): ?>
    <div class="alert error">Evaluation was not sent. A deadline in the past is not allowed.</div>
  <?php endif; ?>

  <form action="post_task.php" method="POST" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['evaluation_assignment_csrf']); ?>">
    <div class="form-grid">
      <div>
        <div class="field">
          <label for="title">Evaluation Title</label>
          <input type="text" id="title" name="title" placeholder="Evaluation Title..." required>
        </div>

        <div class="field">
          <label for="description">Instructions</label>
          <textarea id="description" name="description" placeholder="Instruction here..." required></textarea>
        </div>

        <div class="field">
          <label for="deadline">Deadline</label>
          <input type="date" id="deadline" name="deadline" min="<?= htmlspecialchars($todayDate); ?>" required>
        </div>

        <div class="field">
          <label for="file">Attachment</label>
          <input type="file" id="file" name="file">
        </div>
      </div>

      <div>
        <div class="composer-actions" style="margin-top:0;margin-bottom:8px;">
          <label style="color:var(--muted);font-size:12px;">Recipients</label>
          <button type="button" class="select-all app-button secondary" onclick="toggleEmployees()">Select All</button>
        </div>

        <div class="employee-picker">
          <?php if ($employees && $employees->num_rows > 0): ?>
            <?php while ($employee = $employees->fetch_assoc()): ?>
              <label class="employee-option">
                <input type="checkbox" name="employee_ids[]" value="<?= $employee['id']; ?>">
                <span>
                  <?= htmlspecialchars($employee['full_name']); ?>
                  <span class="employee-meta">
                    <?= htmlspecialchars($employee['employee_id'] ?: 'No employee ID'); ?>
                    <?= $employee['department_name'] ? ' - ' . htmlspecialchars($employee['department_name']) : ''; ?>
                  </span>
                </span>
              </label>
            <?php endwhile; ?>
          <?php else: ?>
            <div style="color:var(--muted);padding:10px;">No active employees found.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="composer-actions">
      <span style="color:var(--muted);font-size:12px;">Each selected recipient gets their own submission slot.</span>
      <button class="send-task-btn" type="submit">
        <i class="fa-solid fa-paper-plane"></i> Send Evaluation
      </button>
    </div>
  </form>
</div>

<details class="sent-tasks">
  <summary><i class="fa-solid fa-list-check" aria-hidden="true"></i> Sent Evaluations</summary>

  <div class="task-grid">
    <?php if ($taskBatches && $taskBatches->num_rows > 0): ?>
      <?php while ($batch = $taskBatches->fetch_assoc()): ?>
        <div class="task-card">
          <h3><?= htmlspecialchars($batch['title']); ?></h3>

          <p style="color:var(--muted);font-size:13px;margin-top:8px;">
            <?= nl2br(htmlspecialchars($batch['description'])); ?>
          </p>

          <div class="batch-stats">
            <span class="batch-pill"><?= (int) $batch['recipient_count']; ?> recipient<?= (int) $batch['recipient_count'] === 1 ? '' : 's'; ?></span>
            <span class="batch-pill"><?= (int) $batch['pending_count']; ?> pending</span>
            <span class="batch-pill"><?= (int) $batch['submitted_count']; ?> submitted</span>
            <span class="batch-pill"><?= (int) $batch['reviewed_count']; ?> reviewed</span>
          </div>

          <p style="color:var(--status-warning);font-size:12px;">
            <i class="fa-solid fa-calendar"></i>
            Due: <?= $batch['deadline'] ? date("M d, Y", strtotime($batch['deadline'])) : 'No deadline'; ?>
          </p>

          <?php if (!empty($batch['file_attachment'])): ?>
            <a href="../uploads/tasks/<?= htmlspecialchars($batch['file_attachment']); ?>" target="_blank">
              <i class="fa-solid fa-paperclip"></i> View Attachment
            </a>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="task-card">
        <h3>No tasks sent yet</h3>
        <p style="color:var(--muted);font-size:13px;margin-top:8px;">Sent task batches will appear here.</p>
      </div>
    <?php endif; ?>
  </div>
</details>


</div>
</div>
<script src="<?= htmlspecialchars(app_url('admin/chat_contacts.js')); ?>" defer></script>
</div>

<?php require __DIR__ . '/../includes/chat_widget.php'; ?>

<script>let employeesSelected=false; function toggleEmployees(){employeesSelected=!employeesSelected;document.querySelectorAll('input[name="employee_ids[]"]').forEach(box=>box.checked=employeesSelected);}</script>
</body></html>
