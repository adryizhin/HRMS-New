<?php
require '../includes/auth_check.php';
checkRole(['hr']);
require '../config.php';
require_once __DIR__ . '/../includes/sidebar.php';

$hr_id = $_SESSION['user_id'];

<<<<<<< HEAD
// HANDLE APPROVE / REJECT
if (isset($_GET['id']) && isset($_GET['status'])) {

    $id = intval($_GET['id']);
    $status = $_GET['status'];

    if (in_array($status, ['approved', 'rejected'])) {

        /* 1. UPDATE LEAVE STATUS */
        $stmt = $conn->prepare("
            UPDATE leave_requests 
            SET status=? 
            WHERE id=? AND hr_id=?
        ");
        $stmt->bind_param("sii", $status, $id, $hr_id);
        $stmt->execute();
        $stmt->close();

        /* 2. GET EMPLOYEE DATA */
        $get = $conn->prepare("
            SELECT employee_id, leave_type, start_date, end_date
            FROM leave_requests
            WHERE id=? AND hr_id=?
        ");
        $get->bind_param("ii", $id, $hr_id);
        $get->execute();
        $result = $get->get_result();
        $data = $result->fetch_assoc();
        $get->close();

        if ($data) {

            $employee_id = $data['employee_id'];
            $type = $data['leave_type'];

            $start = date("M d", strtotime($data['start_date']));
            $end = date("M d", strtotime($data['end_date']));

            /* 3. MESSAGE */
            if ($status == 'approved') {
                $message = "✅ Your {$type} leave ({$start} - {$end}) has been APPROVED.";
            } else {
                $message = "❌ Your {$type} leave ({$start} - {$end}) has been REJECTED.";
            }

            /* 4. INSERT NOTIFICATION */
            $notif = $conn->prepare("
                INSERT INTO notifications (user_id, message)
                VALUES (?, ?)
            ");
            $notif->bind_param("is", $employee_id, $message);
            $notif->execute();
            $notif->close();
        }

        header("Location: hr_leave_requests.php");
        exit();
    }
}

=======
require_once __DIR__ . '/../includes/leave.php';
ensureLeaveSchema($conn);
if (empty($_SESSION['leave_review_csrf'])) $_SESSION['leave_review_csrf'] = bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['leave_review_csrf'], (string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(400);
        exit('Reload the leave requests page and try again.');
    }
    $id = (int) ($_POST['id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');
    if (in_array($status, ['approved', 'rejected'], true)) {
        $conn->begin_transaction();
        try {
            $get = $conn->prepare("SELECT employee_id, leave_type, start_date, end_date FROM leave_requests WHERE id = ? AND hr_id = ? AND status = 'pending' FOR UPDATE");
            $get->bind_param('ii', $id, $hr_id);
            $get->execute();
            $data = $get->get_result()->fetch_assoc();
            if ($data) {
                $stmt = $conn->prepare("UPDATE leave_requests SET status = ? WHERE id = ? AND hr_id = ? AND status = 'pending'");
                $stmt->bind_param('sii', $status, $id, $hr_id);
                $stmt->execute();
                $message = 'Your ' . $data['leave_type'] . ' request (' . $data['start_date'] . ' to ' . $data['end_date'] . ') has been ' . strtoupper($status) . '.';
                if ($status === 'rejected') $message .= ' Reserved leave days have been restored.';
                $notif = $conn->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
                $notif->bind_param('is', $data['employee_id'], $message);
                $notif->execute();
            }
            $conn->commit();
        } catch (Throwable $exception) {
            $conn->rollback();
            http_response_code(500);
            exit('Unable to update this request. Please try again.');
        }
    }
    header('Location: hr_leave_requests.php');
    exit;
}
>>>>>>> ea5c816 (partial done system)
// FETCH REQUESTS FOR THIS HR
$query = "
SELECT lr.*, u.full_name 
FROM leave_requests lr
JOIN users u ON lr.employee_id = u.id
WHERE lr.hr_id = $hr_id
ORDER BY lr.id DESC
";

$result = $conn->query($query);

// COUNT CARDS
$total = $conn->query("SELECT COUNT(*) as total FROM leave_requests WHERE hr_id=$hr_id")->fetch_assoc()['total'];
$pending = $conn->query("SELECT COUNT(*) as total FROM leave_requests WHERE hr_id=$hr_id AND status='pending'")->fetch_assoc()['total'];
$approved = $conn->query("SELECT COUNT(*) as total FROM leave_requests WHERE hr_id=$hr_id AND status='approved'")->fetch_assoc()['total'];
$rejected = $conn->query("SELECT COUNT(*) as total FROM leave_requests WHERE hr_id=$hr_id AND status='rejected'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave Requests</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="hr_leave_requests.css">
</head>

<body>

<?php renderSidebar('hr', 'hr.leave_requests'); ?>

<!-- MAIN -->
<div class="main">

  <div class="topbar">
    <h1>Leave Requests</h1>
    <div>Welcome, <?php echo $_SESSION['name']; ?></div>
  </div>

  <!-- CARDS -->
  <div class="cards">
    <div class="card">
      <h3>Total Requests</h3>
      <h1><?= $total; ?></h1>
    </div>

    <div class="card">
      <h3>Pending</h3>
      <h1><?= $pending; ?></h1>
    </div>

    <div class="card">
      <h3>Approved</h3>
      <h1><?= $approved; ?></h1>
    </div>

    <div class="card">
      <h3>Rejected</h3>
      <h1><?= $rejected; ?></h1>
    </div>
  </div>

  <!-- TABLE -->
  <div class="card">
    <h2 style="margin-bottom:15px;">Employee Leave Requests</h2>

    <table class="table">
      <tr>
        <th>Employee</th>
        <th>Type</th>
        <th>Dates</th>
        <th>Reason</th>
        <th>Status</th>
        <th>Action</th>
      </tr>

      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['full_name']; ?></td>
        <td><?= $row['leave_type']; ?></td>
        <td><?= $row['start_date']; ?> → <?= $row['end_date']; ?></td>
        <td><?= $row['reason']; ?></td>

        <td>
          <?php if($row['status'] == 'approved'): ?>
            <span class="badge green">Approved</span>
          <?php elseif($row['status'] == 'rejected'): ?>
            <span class="badge red">Rejected</span>
          <?php else: ?>
            <span class="badge gray">Pending</span>
          <?php endif; ?>
        </td>

        <td style="display:flex; gap:5px;">
          <?php if($row['status'] == 'pending'): ?>
<<<<<<< HEAD
            <a href="?id=<?= $row['id']; ?>&status=approved"
               onclick="return confirm('Approve this request?')"
               class="badge green">Approve</a>

            <a href="?id=<?= $row['id']; ?>&status=rejected"
               onclick="return confirm('Reject this request?')"
               class="badge red">Reject</a>
=======
            <form method="post" style="display:flex;gap:5px;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['leave_review_csrf']); ?>">
              <input type="hidden" name="id" value="<?= (int) $row['id']; ?>">
              <button name="status" value="approved" class="badge green">Approve</button>
              <button name="status" value="rejected" class="badge red">Reject</button>
            </form>
>>>>>>> ea5c816 (partial done system)
          <?php else: ?>
            <span>-</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>

    </table>
  </div>

</div>

</body>
</html>
<<<<<<< HEAD
=======

>>>>>>> ea5c816 (partial done system)
