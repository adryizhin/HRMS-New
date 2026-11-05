<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['hr']);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/kpi.php';

$employees = $conn->query("
    SELECT users.id, users.employee_id, users.full_name, users.role, departments.name AS department_name
    FROM users
    LEFT JOIN departments ON users.department_id = departments.id
    WHERE users.role IN ('employee', 'admin', 'hr')
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
    return ($b['kpi']['overall'] <=> $a['kpi']['overall']) ?: strcasecmp($a['full_name'],$b['full_name']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Key Performance Indicators</title>
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
.excellent { background: rgba(34, 197, 94, 0.13); color: var(--status-success); }
.good { background: rgba(96, 165, 250, 0.13); color: var(--status-info); }
.watch { background: rgba(245, 158, 11, 0.13); color: var(--status-warning); }
.risk { background: rgba(239, 68, 68, 0.13); color: var(--status-danger); }
.muted { background: var(--kpi-table-alt); color: var(--kpi-muted); }
.mini-score { color: var(--kpi-text); font-weight: 700; }
.mini-score span { color: var(--kpi-muted); font-weight: 500; }
.empty {
  color: var(--kpi-muted);
  padding: 30px;
  text-align: center;
}
.kpi-method { padding:18px; margin-bottom:20px; background:var(--kpi-table-bg); border:1px solid var(--kpi-border); border-radius:8px; color:var(--kpi-text); font-size:13px; line-height:1.7; }
.kpi-method summary { cursor:pointer; font-weight:600; }
.kpi-method ul { padding-left:20px; margin-top:12px; }
.kpi-method p { margin-top:12px; color:var(--kpi-muted); }
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
      <p class="eyebrow">Staff performance</p>
      <h1>Key Performance Indicators</h1>
      <p>Key Performance Indicators (KPIs) are measurable values used to track staff work performance. Compare attendance reliability, reviewed work quality, task completion, and on-time delivery. Scores update from current records whenever this page loads.</p>
    </div>
  </div>

  <details class="kpi-method"><summary>Understand the indicators and scoring</summary><ul>
    <li><strong>Reviewed work quality (40%):</strong> average final reviewer score for reviewed, rated submissions.</li>
    <li><strong>Task completion (25%):</strong> submitted tasks divided by assignments already due or submitted.</li>
    <li><strong>Attendance reliability (20%):</strong> present days plus 75% of late days, divided by all recorded attendance days.</li>
    <li><strong>On-time delivery (15%):</strong> on-time submissions divided by deadline-tracked tasks already due or submitted. Missing submission timestamps receive no on-time credit.</li>
  </ul><p>Overdue unsubmitted work reduces completion and on-time delivery. Future and undated pending work are excluded until an outcome is measurable. Unrated work does not count toward quality. Scores range from 0 to 100.</p><p>Overall performance starts at zero on a fixed 100-point scale. Missing measures contribute zero points, and weights are never redistributed. Attendance at 100% with all other measures missing earns 20% overall. Coverage shows the combined weight of measures with data. Ratings apply to the overall score: Excellent 85–100%, On Track 70–84.9%, Needs Support 50–69.9%, At Risk below 50%.</p></details>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Staff member</th>
          <th>Overall performance</th>
          <th>Attendance reliability</th>
          <th>Reviewed work quality</th>
          <th>Task completion</th>
          <th>On-time delivery</th>
          <th>Overdue pending tasks</th>
          <th>Performance rating</th>
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
                  <?= htmlspecialchars($row['employee_id'] ?: 'No employee ID'); ?> · <?= strtoupper(htmlspecialchars($row['role'])); ?>
                  <?= $row['department_name'] ? ' - ' . htmlspecialchars($row['department_name']) : ''; ?>
                </span>
              </td>
              <td><span class="score-pill <?= htmlspecialchars($kpi['color_class']); ?>"><?= number_format((float)$kpi['overall'],1); ?>%</span></td>
              <?php foreach (['attendance', 'hr_rating', 'completion', 'deadline'] as $factorKey): ?>
                <?php $score = $kpi['factors'][$factorKey]['score']; ?>
                <td class="mini-score"><?= number_format($score===null ? 0.0 : clampScore((float)$score),1); ?>%<?php if($score===null): ?> <span>(no data)</span><?php endif; ?></td>
              <?php endforeach; ?>
              <td class="mini-score"><?= (int) $kpi['stats']['overdue_pending']; ?> <span>overdue</span></td>
              <td><?= htmlspecialchars($kpi['label']); ?><span class="employee-meta"><?= (int)$kpi['available_weight']; ?>% coverage</span></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8" class="empty">No active staff found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
