<?php
require '../config.php';
require '../includes/auth_check.php';
require_once __DIR__ . '/../includes/sidebar.php';

checkRole(['admin']);
$_SESSION['employee_department_csrf'] ??= bin2hex(random_bytes(32));

/* =========================
   ADD USER
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['full_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $role        = $_POST['role'] ?? '';
    $department_id = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
    $work_shift = 'morning';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 191
        || $password === '' || !in_array($role, ['admin', 'hr', 'employee'], true)) {
        $error = 'Enter a name, valid email (up to 191 characters), password, and role.';
    } else {

    // Auto-generate employee_id
    $last = $conn->query("SELECT MAX(id) AS max_id FROM users")->fetch_assoc();
    $employee_id = 'EMP-' . str_pad(($last['max_id'] ?? 0) + 1, 5, '0', STR_PAD_LEFT);

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (employee_id, full_name, email, password, role, department_id, work_shift)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("sssssis", $employee_id, $name, $email, $hashed, $role, $department_id, $work_shift);

    try {
      if ($stmt->execute()) {
        header("Location: create_user_account.php");
        exit();
      } else {
        $error = $stmt->error;
      }
    } catch (mysqli_sql_exception $exception) {
        if ($exception->getCode() !== 1062) {
            throw $exception;
        }
        $error = 'An account with this email or employee ID already exists.';
    }
    }
}

/* =========================
   DELETE USER
========================= */
if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: create_user_account.php?deleted=1");
            exit();
        } else {
            $error = $stmt->error;
        }
    }
}

/* =========================
   FETCH USERS
========================= */
$users = $conn->query("
    SELECT users.*, departments.name AS department_name
    FROM users
    LEFT JOIN departments ON users.department_id = departments.id
    ORDER BY users.id DESC
");

$departments = $conn->query("
    SELECT id, name FROM departments ORDER BY name ASC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">
<title>Pagecom HRIS</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="create_user_account.css?v=<?= filemtime(__DIR__.'/create_user_account.css') ?>">
</head>

<body>

<?php renderSidebar('admin', 'admin.users'); ?>

<div class="main">
<div class="container">

<!-- FORM -->
<div class="form-card">
  <h2>Create User Account</h2>

  <?php if (!empty($error)): ?>
    <div style="background:#fee2e2;color:#991b1b;padding:10px;border-radius:8px;margin-bottom:10px;">
      <?= $error; ?>
    </div>
  <?php endif; ?>

  <form method="POST">

    
    <div class="form-group">
      <input type="text" name="full_name" placeholder="Full Name" required>
    </div>

    <div class="form-group">
      <input type="email" name="email" placeholder="Email" required>
    </div>

    <div class="form-group">
      <input type="password" name="password" placeholder="Password" required>
    </div>

    <div class="form-group">
      <select name="role" required>
        <option value="">Select Role</option>
        <option value="admin">Admin</option>
        <option value="hr">HR</option>
        <option value="employee">Employee</option>
      </select>
    </div>

    <div class="form-group">
      <select name="department_id">
        <option value="">Select Department</option>
        <?php foreach ($departments as $department): ?>
          <option value="<?= $department['id']; ?>">
            <?= htmlspecialchars($department['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit">Create Account</button>
  </form>
</div>

<!-- TABLE -->
<div class="table-card">
  <br>
  <h2>Pagecom's Employees List</h2>
  <br>

  <?php if (isset($_GET['deleted'])): ?>
    <div style="background:#dcfce7;color:#166534;padding:10px;border-radius:8px;margin-bottom:10px;">
      User deleted successfully!
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['department_updated'])): ?><p class="department-update-notice" role="status">Employee department updated successfully.</p><?php endif ?>
  <?php if (isset($_GET['department_error'])): ?><p class="department-update-notice" role="alert">The department could not be updated. Refresh the page and try again.</p><?php endif ?>
  <div class="table-wrapper">

    <table>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Department</th>
        <th>Schedule</th>
        <th>Action</th>
      </tr>

      <?php while ($row = $users->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['full_name']); ?></td>
        <td><?= htmlspecialchars($row['email']); ?></td>
        <td>
          <span class="badge <?= $row['role']; ?>">
            <?= strtoupper($row['role']); ?>
          </span>
        </td>
        <td><?= htmlspecialchars($row['department_name'] ?? 'Unassigned'); ?></td>
        <td>8:00 AM - 5:00 PM</td>
        <td>
          <div class="employee-row-actions">
          <button type="button" class="edit-department-btn" data-edit-department data-user-id="<?= (int)$row['id']; ?>" data-user-name="<?= htmlspecialchars($row['full_name'],ENT_QUOTES,'UTF-8'); ?>" data-department-id="<?= (int)($row['department_id']??0); ?>" aria-haspopup="dialog" aria-controls="employee-department-dialog"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> Edit</button>
          <a class="delete-btn"
             href="?delete=<?= $row['id']; ?>"
             onclick="return confirm('Delete this user?')">
             Delete
          </a>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>

    </table>

  </div>

</div>

</div>
</div>

<dialog id="employee-department-dialog" aria-labelledby="department-dialog-heading">
  <header><h2 id="department-dialog-heading">Edit Department</h2><button type="button" data-close-department aria-label="Close department editor"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header>
  <p id="department-employee-name"></p>
  <form method="post" action="<?= htmlspecialchars(app_url('admin/update_employee_department.php')); ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['employee_department_csrf']); ?>">
    <input type="hidden" id="department-user-id" name="user_id">
    <label for="edit-department-id">Department</label>
    <select id="edit-department-id" name="department_id"><option value="">Unassigned</option><?php foreach($departments as $department): ?><option value="<?= (int)$department['id']; ?>"><?= htmlspecialchars($department['name']); ?></option><?php endforeach ?></select>
    <div class="department-dialog-actions"><button type="button" class="department-cancel" data-close-department>Cancel</button><button type="submit">Save Department</button></div>
  </form>
</dialog>
<script src="<?= htmlspecialchars(app_url('admin/employee_department.js').'?v='.filemtime(__DIR__.'/employee_department.js')); ?>" defer></script>
</body>
</html>
