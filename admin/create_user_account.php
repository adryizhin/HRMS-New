<?php
require '../config.php';
require '../includes/auth_check.php';
require_once __DIR__ . '/../includes/sidebar.php';

checkRole(['admin']);
$_SESSION['employee_department_csrf'] ??= bin2hex(random_bytes(32));

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

<button type="button" class="account-management-trigger" data-open-account-management aria-haspopup="dialog" aria-controls="account-management-dialog"><i class="fa-solid fa-users-gear" aria-hidden="true"></i> Employee Account Management</button>

<!-- TABLE -->
<div class="table-card">
  <br>
  <h2>Pagecom's Employees List</h2>
  <label class="employee-search-label" for="employee-account-search"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Search employee accounts</label>
  <input id="employee-account-search" class="employee-account-search" type="search" placeholder="Search by name, email, role, or department" autocomplete="off">
  <p id="employee-search-status" class="employee-search-status" aria-live="polite"></p>
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
      <tr class="employee-account-row">
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

<dialog id="account-management-dialog" class="account-management-dialog" aria-labelledby="account-management-title">
  <header><h2 id="account-management-title">Employee Account Management</h2><button type="button" data-close-account-management aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header>
  <p>HR submits employee account requests. Review new requests before they become active accounts.</p>
  <a class="account-requests-link" href="<?= htmlspecialchars(route('admin.account_requests')); ?>"><i class="fa-solid fa-inbox" aria-hidden="true"></i> Review Account Requests</a>
  <?php if (!empty($error)): ?><p class="account-management-error" role="alert"><?= htmlspecialchars($error); ?></p><?php endif; ?>
</dialog>
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
<script>
const accountManagementDialog = document.getElementById('account-management-dialog');
document.querySelector('[data-open-account-management]')?.addEventListener('click', () => accountManagementDialog.showModal());
document.querySelector('[data-close-account-management]')?.addEventListener('click', () => accountManagementDialog.close());
accountManagementDialog?.addEventListener('click', event => { if (event.target === accountManagementDialog) accountManagementDialog.close(); });
const employeeSearch = document.getElementById('employee-account-search');
employeeSearch?.addEventListener('input', () => {
  const query = employeeSearch.value.trim().toLocaleLowerCase();
  const rows = [...document.querySelectorAll('.employee-account-row')];
  let visible = 0;
  rows.forEach(row => { const match = row.innerText.toLocaleLowerCase().includes(query); row.hidden = !match; if (match) visible++; });
  document.getElementById('employee-search-status').textContent = query ? `${visible} matching account${visible === 1 ? '' : 's'}` : '';
});
</script>
</body>
</html>
