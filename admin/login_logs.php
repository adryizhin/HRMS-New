<?php
date_default_timezone_set('Asia/Manila');
require '../config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/sidebar.php';
checkRole(['admin']);

/* CLEAN DUPLICATE ACTIVE SESSIONS */
if (isset($_POST['cleanup_duplicates'])) {

    $query = "
    DELETE l1 FROM login_logs l1
    INNER JOIN login_logs l2 
    ON l1.user_id = l2.user_id 
    AND l1.id < l2.id
    WHERE l1.logout_time IS NULL 
    AND l2.logout_time IS NULL
    ";

    $conn->query($query);

    header("Location: login_logs.php");
    exit();
}

/* SEARCH AND STATUS FILTERS (shared by the table and export). */
$searchInput = $_GET['q'] ?? $_POST['q'] ?? '';
$search = is_string($searchInput) ? mb_substr(trim($searchInput), 0, 160) : '';
$statusInput = $_GET['status'] ?? $_POST['status'] ?? 'all';
$statusFilter = is_string($statusInput) && in_array($statusInput, ['all', 'timeout', 'active', 'offline', 'logout'], true) ? $statusInput : 'all';
$query = "SELECT logs.* FROM (
    SELECT l.*, (l.logout_time IS NULL AND l.last_seen > DATE_SUB(NOW(), INTERVAL 90 SECOND)
        AND l.last_ping > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND l.session_token IS NOT NULL
        AND l.session_token <> '' AND u.status = 'active') AS is_online
    FROM login_logs l LEFT JOIN users u ON u.id = l.user_id
) AS logs WHERE 1 = 1";
$params = [];
if ($search !== '') {
    $query .= " AND LOWER(CONCAT_WS(' ', username, role, login_time, logout_time,
        DATE_FORMAT(login_time, '%b %d, %Y %h:%i %p'), DATE_FORMAT(logout_time, '%b %d, %Y %h:%i %p'),
        logout_reason, CASE WHEN logout_reason = 'timeout' THEN 'timeout time out timed out'
            WHEN logout_time IS NOT NULL THEN 'inactive logout'
            WHEN is_online = 1 THEN 'active session open' ELSE 'offline session open' END)) LIKE ? ESCAPE '!'";
    $params[] = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($search)) . '%';
}
switch ($statusFilter) {
    case 'timeout': $query .= " AND logout_reason = 'timeout'"; break;
    case 'active': $query .= " AND is_online = 1"; break;
    case 'offline': $query .= " AND logout_time IS NULL AND (is_online = 0 OR is_online IS NULL)"; break;
    case 'logout': $query .= " AND logout_time IS NOT NULL AND (logout_reason <> 'timeout' OR logout_reason IS NULL)"; break;
}
$query .= " ORDER BY id DESC";
$statement = $conn->prepare($query);
if ($params) $statement->bind_param('s', $params[0]);
$statement->execute();
$logs = $statement->get_result();

/* EXPORT TO CSV (EXCEL FORMAT) */
if (isset($_POST['export_excel'])) {

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=login_logs.csv');

    $output = fopen('php://output', 'w');

    // headers
    fputcsv($output, [
        'Name',
        'Role',
        'Login Time',
        'Logout Time',
        'Logout Reason',
        'Status'
    ]);

    $result = $logs;

    while ($row = $result->fetch_assoc()) {

        $reason = $row['logout_reason'] ?? '';
        $status = !$row['logout_time'] ? (!empty($row['is_online']) ? 'Active' : 'Offline') : ($reason === 'timeout' ? 'Timed Out' : 'Inactive');

        fputcsv($output, [
            $row['username'],
            strtoupper($row['role']),
            $row['login_time'],
            $row['logout_time'] ?? 'Session open',
            $reason === 'timeout' ? 'Timeout' : ($reason === 'logout' ? 'Logout' : ''),
            $status
        ]);
    }

    fclose($output);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Logs</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="login_logs.css?v=20261012">
</head>

<body>

<?php renderSidebar('admin', 'admin.login_logs'); ?>

<!-- MAIN -->
<div class="main">

<h1>Login Logs</h1>
<br>

<form method="GET" class="log-filters" role="search">
    <div class="log-search-field">
        <label for="log-search">Search login logs</label>
        <div class="log-search-input">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input id="log-search" name="q" type="search" maxlength="160"
                   value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="Name, date, time, or time out">
        </div>
    </div>
    <div class="log-status-field">
        <label for="log-status">Session status</label>
        <select id="log-status" name="status">
            <?php foreach (['all' => 'All sessions', 'timeout' => 'Timed Out', 'active' => 'Active', 'offline' => 'Offline', 'logout' => 'Logged Out / Inactive'] as $value => $label): ?>
                <option value="<?= $value; ?>" <?= $statusFilter === $value ? 'selected' : ''; ?>><?= $label; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="clean-btn"><i class="fa-solid fa-filter" aria-hidden="true"></i> Search</button>
    <?php if ($search !== '' || $statusFilter !== 'all'): ?>
        <a href="login_logs.php" class="log-clear">Clear filters</a>
    <?php endif; ?>
</form>
<p class="log-results" role="status"><?= $logs->num_rows; ?> <?= $logs->num_rows === 1 ? 'session' : 'sessions'; ?> found</p>

<div class="top-actions">

    <!-- CLEAN DUPLICATES -->
    <form method="POST" onsubmit="return confirm('Clean duplicate active sessions?');">
        <button name="cleanup_duplicates" class="clean-btn">
            <i class="fa-solid fa-broom"></i> Clean Sessions
        </button>
    </form>

    <!-- EXPORT -->
    <form method="POST">
        <input type="hidden" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="status" value="<?= $statusFilter; ?>">
        <button name="export_excel" class="clean-btn export-btn">
            <i class="fa-solid fa-file-excel"></i> Export to Excel
        </button>
    </form>

</div>

<div class="log-table-wrap">
<table class="table">
<thead>
<tr>
  <th>Name</th>
  <th>Role</th>
  <th>Login Time</th>
  <th>Logout Time</th>
  <th>Logout Reason</th>
  <th>Status</th>
</tr>
</thead>

<tbody>
<?php if ($logs->num_rows === 0): ?>
<tr><td colspan="6" class="log-empty">No login sessions match your search. Try another search or clear the filters.</td></tr>
<?php endif; ?>
<?php while($row = $logs->fetch_assoc()): ?>
<tr>
  <td><?= htmlspecialchars($row['username']); ?></td>

  <td>
    <span class="badge <?= $row['role']; ?>">
      <?= strtoupper($row['role']); ?>
    </span>
  </td>

  <td><?= date("M d, Y h:i A", strtotime($row['login_time'])); ?></td>

  <td style="text-align:center;">
    <?= $row['logout_time']
         ? date("M d, Y h:i A", strtotime($row['logout_time']))
         : 'Session open';
    ?>
  </td>

  <td style="text-align:center;">
    <?php if (($row['logout_reason'] ?? '') === 'timeout'): ?>
        Time Out
    <?php elseif (($row['logout_reason'] ?? '') === 'logout'): ?>
        Logout
    <?php else: ?>
        -
    <?php endif; ?>
  </td>

  <td>
    <?php if (($row['logout_reason'] ?? '') === 'timeout'): ?>
        <span class="status inactive">Timed Out</span>
    <?php elseif ($row['logout_time']): ?>
        <span class="status inactive">Inactive</span>
    <?php elseif (empty($row['is_online'])): ?>
        <span class="status inactive">Offline</span>
    <?php else: ?>
        <span class="status active">Active</span>
    <?php endif; ?>
  </td>

</tr>
<?php endwhile; ?>
</tbody>

</table>
</div>

</div>

</body>
</html>
