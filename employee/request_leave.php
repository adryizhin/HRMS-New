<?php
require '../includes/auth_check.php';
checkRole(['employee']);
require '../config.php';
require_once __DIR__ . '/../includes/sidebar.php';
<<<<<<< HEAD
=======
require_once __DIR__ . '/../includes/leave.php';
ensureLeaveSchema($conn);
if (empty($_SESSION['leave_csrf'])) $_SESSION['leave_csrf'] = bin2hex(random_bytes(32));
if (empty($_SESSION['leave_request_key'])) $_SESSION['leave_request_key'] = bin2hex(random_bytes(32));
>>>>>>> ea5c816 (partial done system)

$employee_id = $_SESSION['user_id'];

// FETCH HR LIST
<<<<<<< HEAD
$hrs = $conn->query("SELECT id, full_name FROM users WHERE role='hr'");
=======
$hrs = $conn->query("SELECT id, full_name FROM users WHERE role='hr' AND status='active'");
>>>>>>> ea5c816 (partial done system)

// HANDLE SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

<<<<<<< HEAD
    $hr_id = $_POST['hr_id'];
    $type = $_POST['leave_type'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $reason = $_POST['reason'];

    $stmt = $conn->prepare("
        INSERT INTO leave_requests 
        (employee_id, hr_id, leave_type, start_date, end_date, reason)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("iissss", $employee_id, $hr_id, $type, $start, $end, $reason);

    if ($stmt->execute()) {
        $success = "Leave request sent successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}
=======
    try {
        if (!hash_equals($_SESSION['leave_csrf'], (string) ($_POST['csrf_token'] ?? ''))) throw new InvalidArgumentException('Reload the form and try again.');
        $key = (string) ($_POST['request_key'] ?? '');
        if (!hash_equals($_SESSION['leave_request_key'], $key)) throw new InvalidArgumentException('This form has already been submitted. Reload to make another request.');
        submitLeaveRequest($conn, (int) $employee_id, (int) ($_POST['hr_id'] ?? 0), (string) ($_POST['leave_type'] ?? ''), (string) ($_POST['start_date'] ?? ''), (string) ($_POST['end_date'] ?? ''), (string) ($_POST['reason'] ?? ''), $key);
        $_SESSION['leave_request_key'] = bin2hex(random_bytes(32));
        header('Location: request_leave.php?sent=1');
        exit;
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    } catch (Throwable $exception) {
        $error = 'Unable to submit your request. Your leave balance has not changed.';
    }
}
$remainingDays = remainingLeaveDays($conn, (int) $employee_id, (int) date('Y'));
$success = isset($_GET['sent']) ? 'Leave request sent successfully. Working days have been deducted.' : '';
>>>>>>> ea5c816 (partial done system)

$notifQuery = $conn->query("
    SELECT * FROM notifications 
    WHERE user_id = $employee_id 
    ORDER BY id DESC
");

$notifCount = $conn->query("
    SELECT COUNT(*) as total 
    FROM notifications 
    WHERE user_id = $employee_id AND is_read = 0
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
<link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
=======
<link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">
>>>>>>> ea5c816 (partial done system)
<title>Employee Dashboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="request_leave.css">
</head>

<body>

<?php renderSidebar('employee', 'employee.leave_request'); ?>

<!-- MAIN -->
<div class="main">

  <div class="topbar">
    <h1>Employee Dashboard</h1>
    <div>Welcome, <?php echo $_SESSION['name']; ?></div>
    <div class="icons">
      <div class="icons notification" style="position:relative;">

  <i class="fa-solid fa-bell" onclick="toggleNotif()"></i>

  <?php if($notifCount > 0): ?>
    <span style="
      position:absolute;
      top:-5px;
      right:40px;
<<<<<<< HEAD
      background:red;
=======
      background: #b91c1c;
>>>>>>> ea5c816 (partial done system)
      color:white;
      font-size:10px;
      padding:3px 6px;
      border-radius:50%;
    ">
      <?= $notifCount ?>
    </span>
  <?php endif; ?>

  <!-- DROPDOWN -->
  <div id="notifBox" style="
      display:none;
      position:absolute;
      right:0;
      top:30px;
      width:300px;
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
      background:var(--card);
      border:1px solid var(--border);
      border-radius:10px;
      box-shadow:0 10px 25px rgba(0,0,0,0.12);
<<<<<<< HEAD
=======
      background:#111827;
      border-radius:10px;
      box-shadow:0 10px 25px rgba(0,0,0,0.5);
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
      max-height:300px;
      overflow-y:auto;
      z-index:999;
  ">

    <?php if($notifQuery->num_rows > 0): ?>
      <?php while($n = $notifQuery->fetch_assoc()): ?>
        <div style="
            padding:10px;
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
            border-bottom:1px solid var(--border);
            font-size:13px;
            color:var(--text);
        ">
          <?= $n['message']; ?><br>
          <small style="color:var(--muted);">
<<<<<<< HEAD
=======
            border-bottom:1px solid #1f2937;
            font-size:13px;
        ">
          <?= $n['message']; ?><br>
          <small style="color:gray;">
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
            <?= date("M d, h:i A", strtotime($n['created_at'])); ?>
          </small>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div style="padding:10px;">No notifications</div>
    <?php endif; ?>

  </div>

</div>
    </div>
  </div>

    <!-- CARDS -->
    <div class="cards">
      <div class="card">
        <h3>Tasks</h3>
        <?php
        $result = $conn->query("SELECT COUNT(*) as total FROM tasks WHERE uploaded_by = $employee_id");
        $data = $result->fetch_assoc();
        ?>
        <h1><?php echo $data['total']; ?></h1>
        <i class="fa-solid fa-tasks"></i>
      </div>

      <div class="card">
        <h3>My Payroll</h3>
        <h1>8</h1>
        <i class="fa-solid fa-money-bill"></i>
      </div>

      <div class="card">
        <h3>Leaves Remaining</h3>
<<<<<<< HEAD
        <h1>15</h1>
=======
        <h1><?= $remainingDays; ?></h1>
        <small><?= date('Y'); ?> allowance: 15 working days. Pending and approved requests count; rejected requests restore the balance.</small>
>>>>>>> ea5c816 (partial done system)
        <i class="fa-solid fa-clock"></i>
      </div>

      <div class="card">
        <h3>System Logs</h3>
        <h1>25</h1>
        <i class="fa-solid fa-database"></i>
      </div>
    </div>

  <!-- REQUEST LEAVE FORM -->
  <div class="leave-container">
    <div class="leave-card">

      <h2 style="text-align:center; margin-bottom:15px;">Request Leave</h2>

      <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
<<<<<<< HEAD
        <div class="error"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST">
=======
        <div class="error"><?= htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['leave_csrf']); ?>">
        <input type="hidden" name="request_key" value="<?= htmlspecialchars($_SESSION['leave_request_key']); ?>">
>>>>>>> ea5c816 (partial done system)

        <select name="hr_id" required>
          <option value="">Select HR</option>
          <?php while($hr = $hrs->fetch_assoc()): ?>
            <option value="<?= $hr['id']; ?>">
              <?= $hr['full_name']; ?>
            </option>
          <?php endwhile; ?>
        </select>

        <select name="leave_type" required>
          <option value="">Leave Type</option>
          <option value="Sick Leave">Sick Leave</option>
          <option value="Vacation Leave">Vacation Leave</option>
        </select>

        <input type="date" name="start_date" required>
        <input type="date" name="end_date" required>

        <textarea name="reason" placeholder="Reason" required></textarea>

        <button type="submit">Submit Request</button>

      </form>

    </div>
  </div>

</div>
<script>
function toggleNotif() {
    let box = document.getElementById("notifBox");
    box.style.display = (box.style.display === "block") ? "none" : "block";

    // mark as read when opened
    if (box.style.display === "block") {
        fetch('mark_read.php');
    }
}
</script>
</body>
</html>
