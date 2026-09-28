<?php
declare(strict_types=1);

require_once __DIR__ . '/attendance.php';
require_once __DIR__ . '/sidebar.php';

<<<<<<< HEAD
function renderAttendancePage(mysqli $conn, string $role): void
{
    ensureAttendanceTable($conn);
    ensureUserShiftColumn($conn);
    markMissingAttendanceAbsent($conn);

    $currentUserId = (int) $_SESSION['user_id'];
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
    } else {
        $stmt = $conn->prepare("
            SELECT a.*, u.full_name, u.role, u.employee_id
            FROM attendance a
            JOIN users u ON a.user_id = u.id
            WHERE a.user_id = ?
            ORDER BY a.attendance_date DESC, a.login_time DESC
        ");
        $stmt->bind_param("i", $currentUserId);
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
=======
body {
    background:#0b1220;
    color:#e5e7eb;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
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
    background: linear-gradient(145deg, var(--attendance-card), var(--attendance-card-soft));
    border:1px solid var(--attendance-border);
    border-radius:12px;
    padding:18px;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
}
.card h3 {
    color: var(--attendance-muted);
=======
    background:#111827;
    border:1px solid #1f2937;
    border-radius:12px;
    padding:18px;
}
.card h3 {
    color:#9ca3af;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
    font-size:13px;
    margin-bottom:8px;
}
.card h1 {
<<<<<<< HEAD
    color: var(--attendance-accent);
    font-size:30px;
}
.attendance-note {
    color: var(--attendance-muted);
=======
    color:#3251ff;
    font-size:30px;
}
.attendance-note {
    color:#9ca3af;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
    font-size:13px;
    margin-top:6px;
}
.table-wrap {
<<<<<<< HEAD
    background: linear-gradient(145deg, var(--attendance-card), var(--attendance-card-soft));
    border:1px solid var(--attendance-border);
    border-radius:12px;
    overflow-x:auto;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
=======
    background:#111827;
    border:1px solid #1f2937;
    border-radius:12px;
    overflow-x:auto;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
}
table {
    width:100%;
    border-collapse:collapse;
}
th, td {
<<<<<<< HEAD
    border-bottom:1px solid var(--attendance-border);
=======
    border-bottom:1px solid #1f2937;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
    padding:12px;
    text-align:left;
    font-size:13px;
}
th {
<<<<<<< HEAD
    color: var(--attendance-muted);
=======
    color:#9ca3af;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
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
.present { background:#dcfce7; color:#166534; }
.late { background:#fef3c7; color:#92400e; }
.absent { background:#fee2e2; color:#991b1b; }
=======
.present { background:#14532d; color:#86efac; }
.late { background:#78350f; color:#fcd34d; }
.absent { background:#7f1d1d; color:#fecaca; }
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
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
        <?php if ($canViewAll): ?>
=======
        <?php if ($showAll): ?>
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
            <div class="card">
                <h3>My Absences</h3>
                <h1><?= $mySummary['absent']; ?></h1>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
<<<<<<< HEAD
                    <?php if ($canViewAll): ?>
=======
                    <?php if ($showAll): ?>
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
                        <th>Name</th>
                        <th>Role</th>
                    <?php endif; ?>
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
                        <?php
                        $lateMinutes = (int) ($row['late_minutes'] ?? 0);
                        if (($row['status'] ?? '') === 'late' && $lateMinutes === 0) {
                            $lateMinutes = lateMinutesForLogin((string) $row['login_time']);
                        }
                        ?>
                        <tr>
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
=======
                            <td><?= date("h:i A", strtotime($row['login_time'])); ?></td>
                            <td><span class="badge <?= htmlspecialchars($row['status']); ?>"><?= htmlspecialchars($row['status']); ?></span></td>
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
<<<<<<< HEAD
                        <td colspan="<?= $canViewAll ? 6 : 4; ?>" style="text-align:center;color:#9ca3af;">No attendance records yet.</td>
=======
                        <td colspan="<?= $showAll ? 6 : 4; ?>" style="text-align:center;color:#9ca3af;">No attendance records yet.</td>
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
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
