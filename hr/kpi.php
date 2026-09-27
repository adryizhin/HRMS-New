<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['hr']);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/kpi.php';

$employees = $conn->query("
    SELECT users.id, users.employee_id, users.full_name, departments.name AS department_name
    FROM users
    LEFT JOIN departments ON users.department_id = departments.id
    WHERE users.role = 'employee'
    AND users.status = 'active'
    ORDER BY users.full_name ASC
");

$rows = [];
if ($employees) {
    while ($employee = $employees->fetch_assoc()) {
        $employee['kpi'] = calculateEmployeeKpi($conn, (int) $employee['id']);
        $rows[] = $employee;
    }
}

usort($rows, static function (array $a, array $b): int {
    return $b['kpi']['overall'] <=> $a['kpi']['overall'];
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee KPIs</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="dashboard.css">
<style>
:root {
  --kpi-table-bg: #ffffff;
  --kpi-table-alt: #f8fafc;
  --kpi-border: #e2e8f0;
  --kpi-text: #0f172a;
  --kpi-muted: #475569;
  --kpi-heading: #0f172a;
  --kpi-accent: #2563eb;
}

[data-theme="dark"] {
  --kpi-table-bg: #111827;
  --kpi-table-alt: #0b1220;
  --kpi-border: #1f2937;
  --kpi-text: #e5e7eb;
  --kpi-muted: #94a3b8;
  --kpi-heading: #f8fafc;
  --kpi-accent: #60a5fa;
}

.kpi-header {
  align-items: flex-end;
  display: flex;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 22px;
}
.kpi-header h1 { color: var(--kpi-heading); font-size: 28px; }
.kpi-header p { color: var(--kpi-muted); font-size: 14px; margin-top: 6px; max-width: 760px; }
.table-wrap {
  background: var(--kpi-table-bg);
  border: 1px solid var(--kpi-border);
  border-radius: 8px;
  overflow-x: auto;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
}
table { border-collapse: collapse; width: 100%; }
th, td {
  border-bottom: 1px solid var(--kpi-border);
  font-size: 13px;
  padding: 13px;
  text-align: left;
  vertical-align: middle;
}
th { color: var(--kpi-muted); font-weight: 700; text-transform: uppercase; }
td { color: var(--kpi-text); }
.employee-meta { color: var(--kpi-muted); display: block; font-size: 12px; margin-top: 3px; }
.score-pill {
  border-radius: 999px;
  display: inline-flex;
  font-weight: 800;
  min-width: 76px;
  padding: 7px 10px;
  justify-content: center;
}
.excellent { background: rgba(34, 197, 94, 0.13); color: #16a34a; }
.good { background: rgba(96, 165, 250, 0.13); color: var(--kpi-accent); }
.watch { background: rgba(245, 158, 11, 0.13); color: #d97706; }
.risk { background: rgba(239, 68, 68, 0.13); color: #dc2626; }
.muted { background: var(--kpi-table-alt); color: var(--kpi-muted); }
.mini-score { color: var(--kpi-text); font-weight: 700; }
.mini-score span { color: var(--kpi-muted); font-weight: 500; }
.empty {
  color: var(--kpi-muted);
  padding: 30px;
  text-align: center;
}
@media (max-width: 760px) {
  .kpi-header { align-items: flex-start; flex-direction: column; }
}
</style>
</head>
<body>
<?php renderSidebar('hr', 'hr.kpi'); ?>

<div class="main">
  <div class="kpi-header">
    <div>
      <p class="eyebrow">Performance Indicator</p>
      <h1>Employee KPIs</h1>
      <p>KPI combines attendance, HR-rated work, completion, deadline discipline, and overdue task health. Scores are recalculated from current records whenever this page loads.</p>
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employee</th>
          <th>KPI</th>
          <th>Attendance</th>
          <th>HR Rating</th>
          <th>Completion</th>
          <th>Deadline</th>
          <th>Open Tasks</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($rows)): ?>
          <?php foreach ($rows as $row): ?>
            <?php $kpi = $row['kpi']; ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($row['full_name']); ?></strong>
                <span class="employee-meta">
                  <?= htmlspecialchars($row['employee_id'] ?: 'No employee ID'); ?>
                  <?= $row['department_name'] ? ' - ' . htmlspecialchars($row['department_name']) : ''; ?>
                </span>
              </td>
              <td><span class="score-pill <?= htmlspecialchars($kpi['color_class']); ?>"><?= number_format((float) $kpi['overall'], 1); ?>%</span></td>
              <?php foreach (['attendance', 'hr_rating', 'completion', 'deadline'] as $factorKey): ?>
                <?php $score = $kpi['factors'][$factorKey]['score']; ?>
                <td class="mini-score"><?= $score === null ? '<span>N/A</span>' : number_format(clampScore((float) $score), 1) . '%'; ?></td>
              <?php endforeach; ?>
              <td class="mini-score"><?= (int) $kpi['stats']['overdue_pending']; ?> <span>overdue</span></td>
              <td><?= htmlspecialchars($kpi['label']); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8" class="empty">No active employees found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
