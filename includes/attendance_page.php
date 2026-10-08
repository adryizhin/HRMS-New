<?php
declare(strict_types=1);

require_once __DIR__ . '/attendance.php';
require_once __DIR__ . '/sidebar.php';

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
function renderAttendancePage(mysqli $conn, string $role): void
{
    ensureAttendanceTable($conn);
    ensureUserShiftColumn($conn);
    markMissingAttendanceAbsent($conn);

    $currentUserId = (int) $_SESSION['user_id'];
<<<<<<< HEAD
    $canViewAll = $role === 'hr';
    $summary = attendanceSummary($conn, $canViewAll ? null : $currentUserId);
    $mySummary = $canViewAll ? attendanceSummary($conn, $currentUserId) : null;

    if ($canViewAll) {
=======
function renderAttendancePage(mysqli $conn, string $role, bool $showAll): void
{
    ensureAttendanceTable($conn);
    ensureUserShiftColumn($conn);

    $currentUserId = (int) $_SESSION['user_id'];
    $summary = attendanceSummary($conn, $showAll ? null : $currentUserId);
    $mySummary = attendanceSummary($conn, $currentUserId);

    if ($showAll) {
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
        $records = $conn->query("
            SELECT a.*, u.full_name, u.role, u.employee_id
            FROM attendance a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.attendance_date DESC, a.login_time DESC
        ");
=======
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
>>>>>>> ea5c816 (partial done system)
    } else {
        $stmt = $conn->prepare("
            SELECT a.*, u.full_name, u.role, u.employee_id
            FROM attendance a
            JOIN users u ON a.user_id = u.id
<<<<<<< HEAD
            WHERE a.user_id = ?
            ORDER BY a.attendance_date DESC, a.login_time DESC
        ");
        $stmt->bind_param("i", $currentUserId);
=======
            WHERE a.user_id = ? AND (u.full_name LIKE ? ESCAPE '!' OR u.employee_id LIKE ? ESCAPE '!' OR u.role LIKE ? ESCAPE '!')
            ORDER BY a.attendance_date DESC, a.login_time DESC
        ");
        $stmt->bind_param("isss", $currentUserId, $namePattern, $namePattern, $namePattern);
>>>>>>> ea5c816 (partial done system)
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
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
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
<<<<<<< HEAD
=======
body {
    background:#0b1220;
    color:#e5e7eb;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
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
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
    background: linear-gradient(145deg, var(--attendance-card), var(--attendance-card-soft));
    border:1px solid var(--attendance-border);
    border-radius:12px;
    padding:18px;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
}
.card h3 {
    color: var(--attendance-muted);
<<<<<<< HEAD
=======
    background:#111827;
    border:1px solid #1f2937;
    border-radius:12px;
    padding:18px;
}
.card h3 {
    color:#9ca3af;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
    font-size:13px;
    margin-bottom:8px;
}
.card h1 {
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
    color: var(--attendance-accent);
    font-size:30px;
}
.attendance-note {
    color: var(--attendance-muted);
<<<<<<< HEAD
=======
    color:#3251ff;
    font-size:30px;
}
.attendance-note {
    color:#9ca3af;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
    font-size:13px;
    margin-top:6px;
}
.table-wrap {
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
    background: linear-gradient(145deg, var(--attendance-card), var(--attendance-card-soft));
    border:1px solid var(--attendance-border);
    border-radius:12px;
    overflow-x:auto;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
<<<<<<< HEAD
=======
    background:#111827;
    border:1px solid #1f2937;
    border-radius:12px;
    overflow-x:auto;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
}
table {
    width:100%;
    border-collapse:collapse;
}
th, td {
<<<<<<< HEAD
<<<<<<< HEAD
    border-bottom:1px solid var(--attendance-border);
=======
    border-bottom:1px solid #1f2937;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
    border-bottom:1px solid var(--attendance-border);
>>>>>>> ea5c816 (partial done system)
    padding:12px;
    text-align:left;
    font-size:13px;
}
th {
<<<<<<< HEAD
<<<<<<< HEAD
    color: var(--attendance-muted);
=======
    color:#9ca3af;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
    color: var(--attendance-muted);
>>>>>>> ea5c816 (partial done system)
    font-weight:600;
}
.badge {
    border-radius:999px;
    display:inline-block;
    font-size:12px;
    padding:5px 9px;
    text-transform:uppercase;
}
<<<<<<< HEAD
<<<<<<< HEAD
.present { background:#dcfce7; color:#166534; }
.late { background:#fef3c7; color:#92400e; }
.absent { background:#fee2e2; color:#991b1b; }
=======
.present { background:#14532d; color:#86efac; }
.late { background:#78350f; color:#fcd34d; }
.absent { background:#7f1d1d; color:#fecaca; }
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
.present { background:#dcfce7; color:#166534; }
.late { background:#fef3c7; color:#92400e; }
.absent { background:#fee2e2; color:#991b1b; }
>>>>>>> ea5c816 (partial done system)
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
<<<<<<< HEAD
<<<<<<< HEAD
        <?php if ($canViewAll): ?>
=======
        <?php if ($showAll): ?>
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
        <?php if ($canViewAll): ?>
>>>>>>> ea5c816 (partial done system)
            <div class="card">
                <h3>My Absences</h3>
                <h1><?= $mySummary['absent']; ?></h1>
            </div>
        <?php endif; ?>
    </div>

<<<<<<< HEAD
=======
    <form method="get" style="margin:18px 0;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <label for="attendance-search">Search attendance</label>
        <input id="attendance-search" type="search" name="search" placeholder="Name, employee ID or role" value="<?= htmlspecialchars($search); ?>" style="padding:10px;background:var(--attendance-card);color:var(--attendance-text);border:1px solid var(--attendance-border);border-radius:8px;">
        <button type="submit">Search</button>
        <a class="app-button secondary" href="<?= htmlspecialchars(route($activeRoute)); ?>">Clear</a>
    </form>
    <p class="attendance-note"><?= $canViewAll ? 'Search includes employees, admins and HR. Summary cards show all records.' : 'Search is limited to your own attendance records.'; ?></p>
>>>>>>> ea5c816 (partial done system)
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
<<<<<<< HEAD
<<<<<<< HEAD
                    <?php if ($canViewAll): ?>
=======
                    <?php if ($showAll): ?>
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
                        <th>Name</th>
                        <th>Role</th>
                    <?php endif; ?>
=======
                        <th>Name</th>
                        <th>Role</th>
>>>>>>> ea5c816 (partial done system)
                    <th>Date</th>
                    <th>Schedule</th>
                    <th>Login Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($records && $records->num_rows > 0): ?>
                    <?php while ($row = $records->fetch_assoc()): ?>
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
                        <?php
                        $lateMinutes = (int) ($row['late_minutes'] ?? 0);
                        if (($row['status'] ?? '') === 'late' && $lateMinutes === 0) {
                            $lateMinutes = lateMinutesForLogin((string) $row['login_time']);
                        }
                        ?>
                        <tr>
<<<<<<< HEAD
                            <?php if ($canViewAll): ?>
=======
                        <tr>
                            <?php if ($showAll): ?>
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
                                <td><?= htmlspecialchars($row['full_name']); ?></td>
                                <td><?= strtoupper(htmlspecialchars($row['role'])); ?></td>
                            <?php endif; ?>
                            <td><?= date("M d, Y", strtotime($row['attendance_date'])); ?></td>
                            <td>8:00 AM - 5:00 PM</td>
<<<<<<< HEAD
=======
                                <td><?= htmlspecialchars($row['full_name']); ?></td>
                                <td><?= strtoupper(htmlspecialchars($row['role'])); ?></td>
                            <td><?= date("M d, Y", strtotime($row['attendance_date'])); ?></td>
                            <td>8:00 AM - 5:00 PM</td>
>>>>>>> ea5c816 (partial done system)
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
<<<<<<< HEAD
=======
                            <td><?= date("h:i A", strtotime($row['login_time'])); ?></td>
                            <td><span class="badge <?= htmlspecialchars($row['status']); ?>"><?= htmlspecialchars($row['status']); ?></span></td>
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
<<<<<<< HEAD
<<<<<<< HEAD
                        <td colspan="<?= $canViewAll ? 6 : 4; ?>" style="text-align:center;color:#9ca3af;">No attendance records yet.</td>
=======
                        <td colspan="<?= $showAll ? 6 : 4; ?>" style="text-align:center;color:#9ca3af;">No attendance records yet.</td>
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
                        <td colspan="6" style="text-align:center;color:var(--muted);">No attendance records match your search.</td>
>>>>>>> ea5c816 (partial done system)
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
