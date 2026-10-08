<?php
declare(strict_types=1);

require_once __DIR__ . '/attendance.php';
require_once __DIR__ . '/sidebar.php';

function renderAttendancePage(mysqli $conn, string $role): void
{
    ensureAttendanceTable($conn);
    ensureUserShiftColumn($conn);
    markMissingAttendanceAbsent($conn);

    $currentUserId = (int) $_SESSION['user_id'];
    $canViewAll = in_array($role, ['hr', 'admin'], true);
    $search = trim((string) ($_GET['search'] ?? ''));
    $summary = attendanceSummary($conn, $canViewAll ? null : $currentUserId);
    $mySummary = $canViewAll ? attendanceSummary($conn, $currentUserId) : null;

    $namePattern = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    if ($canViewAll) {
        $stmt = $conn->prepare("
            SELECT a.*, u.full_name, u.role, u.employee_id
            FROM attendance a
            JOIN users u ON a.user_id = u.id
            WHERE (u.full_name LIKE ? ESCAPE '!' OR u.employee_id LIKE ? ESCAPE '!' OR u.role LIKE ? ESCAPE '!')
            ORDER BY a.attendance_date DESC, a.login_time DESC
        ");
        $stmt->bind_param('sss', $namePattern, $namePattern, $namePattern);
        $stmt->execute();
        $records = $stmt->get_result();
    } else {
        $stmt = $conn->prepare("
            SELECT a.*, u.full_name, u.role, u.employee_id
            FROM attendance a
            JOIN users u ON a.user_id = u.id
            WHERE a.user_id = ? AND (u.full_name LIKE ? ESCAPE '!' OR u.employee_id LIKE ? ESCAPE '!' OR u.role LIKE ? ESCAPE '!')
            ORDER BY a.attendance_date DESC, a.login_time DESC
        ");
        $stmt->bind_param("isss", $currentUserId, $namePattern, $namePattern, $namePattern);
        $stmt->execute();
        $records = $stmt->get_result();
    }

    $activeRoute = $role . '.attendance';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= htmlspecialchars($role === 'admin' ? 'dashboard.css' : 'dashboard.css'); ?>">
<style>
:root {
    --attendance-bg: #f3f4f6;
    --attendance-card: #ffffff;
    --attendance-card-soft: #f8fafc;
    --attendance-text: #0f172a;
    --attendance-muted: #475569;
    --attendance-border: #e2e8f0;
    --attendance-accent: #2563eb;
}

[data-theme="dark"] {
    --attendance-bg: #0b1220;
    --attendance-card: #111827;
    --attendance-card-soft: #1e293b;
    --attendance-text: #e5e7eb;
    --attendance-muted: #cbd5e1;
    --attendance-border: #1f2937;
    --attendance-accent: #60a5fa;
}

body {
    background: var(--attendance-bg);
    color: var(--attendance-text);
}
.main {
    margin-left:240px;
    width:calc(100% - 240px);
    padding:20px;
}
.sidebar a.active {
    background:#22c55e;
    color:#000;
}
.cards {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:18px;
    margin:22px 0;
}
.card {
    background: linear-gradient(145deg, var(--attendance-card), var(--attendance-card-soft));
    border:1px solid var(--attendance-border);
    border-radius:12px;
    padding:18px;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
}
.card h3 {
    color: var(--attendance-muted);
    font-size:13px;
    margin-bottom:8px;
}
.card h1 {
    color: var(--attendance-accent);
    font-size:30px;
}
.attendance-note {
    color: var(--attendance-muted);
    font-size:13px;
    margin-top:6px;
}
.table-wrap {
    background: linear-gradient(145deg, var(--attendance-card), var(--attendance-card-soft));
    border:1px solid var(--attendance-border);
    border-radius:12px;
    overflow-x:auto;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
}
table {
    width:100%;
    border-collapse:collapse;
}
th, td {
    border-bottom:1px solid var(--attendance-border);
    padding:12px;
    text-align:left;
    font-size:13px;
}
th {
    color: var(--attendance-muted);
    font-weight:600;
}
.badge {
    border-radius:999px;
    display:inline-block;
    font-size:12px;
    padding:5px 9px;
    text-transform:uppercase;
}
.present { background:#dcfce7; color:#166534; }
.late { background:#fef3c7; color:#92400e; }
.absent { background:#fee2e2; color:#991b1b; }
</style>
</head>
<body>
<?php renderSidebar($role, $activeRoute); ?>

<div class="main">
    <h1>Attendance</h1>
    <p class="attendance-note">
        Schedule: 8:00 AM to 5:00 PM. Logins at 8:30 AM or later are marked late.
    </p>

    <div class="cards">
        <div class="card">
            <h3>Total Records</h3>
            <h1><?= $summary['total']; ?></h1>
        </div>
        <div class="card">
            <h3>Present</h3>
            <h1><?= $summary['present']; ?></h1>
        </div>
        <div class="card">
            <h3>Late</h3>
            <h1><?= $summary['late']; ?></h1>
        </div>
        <div class="card">
            <h3>Absent</h3>
            <h1><?= $summary['absent']; ?></h1>
        </div>
        <?php if ($canViewAll): ?>
            <div class="card">
                <h3>My Absences</h3>
                <h1><?= $mySummary['absent']; ?></h1>
            </div>
        <?php endif; ?>
    </div>

    <form method="get" style="margin:18px 0;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <label for="attendance-search">Search attendance</label>
        <input id="attendance-search" type="search" name="search" placeholder="Name, employee ID or role" value="<?= htmlspecialchars($search); ?>" style="padding:10px;background:var(--attendance-card);color:var(--attendance-text);border:1px solid var(--attendance-border);border-radius:8px;">
        <button type="submit">Search</button>
        <a class="app-button secondary" href="<?= htmlspecialchars(route($activeRoute)); ?>">Clear</a>
    </form>
    <p class="attendance-note"><?= $canViewAll ? 'Search includes employees, admins and HR. Summary cards show all records.' : 'Search is limited to your own attendance records.'; ?></p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                        <th>Name</th>
                        <th>Role</th>
                    <th>Date</th>
                    <th>Schedule</th>
                    <th>Login Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($records && $records->num_rows > 0): ?>
                    <?php while ($row = $records->fetch_assoc()): ?>
                        <?php
                        $lateMinutes = (int) ($row['late_minutes'] ?? 0);
                        if (($row['status'] ?? '') === 'late' && $lateMinutes === 0) {
                            $lateMinutes = lateMinutesForLogin((string) $row['login_time']);
                        }
                        ?>
                        <tr>
                                <td><?= htmlspecialchars($row['full_name']); ?></td>
                                <td><?= strtoupper(htmlspecialchars($row['role'])); ?></td>
                            <td><?= date("M d, Y", strtotime($row['attendance_date'])); ?></td>
                            <td>8:00 AM - 5:00 PM</td>
                            <td>
                                <?= ($row['status'] ?? '') === 'absent'
                                    ? 'No login recorded'
                                    : date("h:i A", strtotime($row['login_time'])); ?>
                            </td>
                            <td>
                                <span class="badge <?= htmlspecialchars($row['status']); ?>"><?= htmlspecialchars($row['status']); ?></span>
                                <?php if (($row['status'] ?? '') === 'late'): ?>
                                    <br><small style="color:var(--attendance-muted); font-weight:600;">Late: <?= formatLateDuration($lateMinutes); ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:var(--muted);">No attendance records match your search.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php
}
?>
