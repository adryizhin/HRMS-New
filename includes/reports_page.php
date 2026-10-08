<?php
declare(strict_types=1);
require_once __DIR__ . '/sidebar.php';
require_once __DIR__ . '/kpi.php';

function reportPeriod(string $period, int $year, int $month, int $quarter): array
{
    if (!in_array($period, ['monthly', 'quarterly', 'yearly'], true) || $year < 2000 || $year > 2100 || $month < 1 || $month > 12 || $quarter < 1 || $quarter > 4) {
        throw new InvalidArgumentException('Choose a valid report period.');
    }
    $firstMonth = $period === 'yearly' ? 1 : ($period === 'quarterly' ? (($quarter - 1) * 3 + 1) : $month);
    $span = $period === 'yearly' ? 12 : ($period === 'quarterly' ? 3 : 1);
    $start = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $firstMonth));
    return [$start->format('Y-m-d'), $start->modify("+$span months")->modify('-1 day')->format('Y-m-d')];
}

function renderReportsPage(mysqli $conn, string $role): void
{
    $period = (string) ($_GET['period'] ?? 'monthly');
    $year = (int) ($_GET['year'] ?? date('Y'));
    $month = (int) ($_GET['month'] ?? date('n'));
    $quarter = (int) ($_GET['quarter'] ?? ceil((int) date('n') / 3));
    try { [$start, $end] = reportPeriod($period, $year, $month, $quarter); }
    catch (InvalidArgumentException $error) { http_response_code(400); exit($error->getMessage()); }
    $chart = ($_GET['chart'] ?? '') === 'pie' ? 'pie' : 'bar';
    ensureAttendanceTable($conn);
    ensureTaskSchema($conn);
    $staff = $conn->query("SELECT id, full_name, employee_id, role FROM users WHERE role IN ('employee','admin','hr') ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);
    $selected = $role === 'employee' ? (int) $_SESSION['user_id'] : (int) ($_GET['user_id'] ?? 0);
    $visible = array_values(array_filter($staff, static fn(array $user): bool => $selected === 0 || (int) $user['id'] === $selected));
    // Employees always have a personal scope, regardless of supplied query parameters.
    if ($role === 'employee') $visible = array_values(array_filter($staff, static fn(array $user): bool => (int) $user['id'] === (int) $_SESSION['user_id']));
    $rows = [];
    $accomplishments = [];
    $totals = ['assigned' => 0, 'submitted' => 0, 'reviewed' => 0, 'present' => 0, 'late' => 0, 'absent' => 0];
    $scores = [];
    $completed = $conn->prepare("SELECT title, submitted_at, status, hr_score, hr_feedback FROM tasks WHERE employee_id = ? AND submission_file IS NOT NULL AND submission_file != '' AND submitted_at >= ? AND submitted_at < DATE_ADD(?, INTERVAL 1 DAY) ORDER BY submitted_at DESC");
    foreach ($visible as $user) {
        $id = (int) $user['id'];
        $kpi = calculateEmployeeKpi($conn, $id, $start, $end);
        $completed->bind_param('iss', $id, $start, $end);
        $completed->execute();
        $tasks = $completed->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($tasks as $task) { $task['full_name'] = $user['full_name']; $accomplishments[] = $task; }
        $counts = [];
        foreach (['present', 'late', 'absent'] as $status) {
            // Attendance details are already calculated by the same period-aware KPI query.
            $counts[$status] = $kpi['stats'][$status];
            $totals[$status] += $counts[$status];
        }
        $totals['assigned'] += $kpi['stats']['assigned'];
        $totals['submitted'] += count($tasks);
        $reviewed = count(array_filter($tasks, static fn(array $task): bool => $task['status'] === 'reviewed'));
        $totals['reviewed'] += $reviewed;
        if ($kpi['available_weight'] > 0) $scores[] = $kpi['overall'];
        $rows[] = ['user' => $user, 'kpi' => $kpi, 'submitted' => count($tasks), 'reviewed' => $reviewed, 'attendance' => $counts];
    }
    usort($accomplishments, static fn(array $a, array $b): int => strcmp($b['submitted_at'], $a['submitted_at']));
    $average = $scores ? round(array_sum($scores) / count($scores), 1) : null;
    if (($_GET['export'] ?? '') === 'csv') {
        header('Cache-Control: no-store');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="accomplishments-' . $start . '-to-' . $end . '.csv"');
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF"); // Keep names readable when the download is opened in Excel.
        $writeCsv = static function (array $cells) use ($output): void {
            $cells = array_map(static function ($cell) {
                return is_string($cell) && preg_match('/^[=+@\-\t\r\n]/', $cell) ? "'" . $cell : $cell;
            }, $cells);
            fputcsv($output, $cells, ',', '"', '');
        };
        $writeCsv(['Period start', 'Period end', 'Name', 'Role', 'Assigned in period', 'Submitted in period', 'Reviewed submissions', 'Present', 'Late', 'Absent', 'KPI / 100']);
        foreach ($rows as $row) {
            $name = $row['user']['full_name'];
            $writeCsv([$start, $end, $name, $row['user']['role'], $row['kpi']['stats']['assigned'], $row['submitted'], $row['reviewed'], $row['attendance']['present'], $row['attendance']['late'], $row['attendance']['absent'], $row['kpi']['available_weight'] ? $row['kpi']['overall'] : 'No data']);
        }
        $writeCsv([]);
        $writeCsv(['Accomplishments']);
        $writeCsv(['Staff member', 'Evaluation / task', 'Submitted', 'Status', 'HR score', 'HR feedback']);
        foreach ($accomplishments as $task) {
            $writeCsv([$task['full_name'], $task['title'], date('M d, Y h:i A', strtotime($task['submitted_at'])), ucfirst($task['status']), $task['hr_score'] ?? 'Not rated', $task['hr_feedback'] ?? '']);
        }
        fclose($output);
        return;
    }
    $exportQuery = ['period' => $period, 'year' => $year, 'month' => $month, 'quarter' => $quarter, 'user_id' => $selected, 'chart' => $chart];
    $exportQuery['export'] = 'csv';
    $csvUrl = route($role . '.reports') . '?' . http_build_query($exportQuery);
    $exportQuery['export'] = 'print';
    $printUrl = route($role . '.reports') . '?' . http_build_query($exportQuery);
    $isPrint = ($_GET['export'] ?? '') === 'print';
    $h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Accomplishments and KPI Reports</title>
<link rel="stylesheet" href="dashboard.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.report-panel{background:var(--card-bg);border:1px solid var(--sidebar-border);border-radius:12px;padding:20px;margin:18px 0;overflow:auto}.report-filters{display:flex;flex-wrap:wrap;gap:12px;align-items:end}.report-filters label{display:flex;flex-direction:column;gap:5px}.report-filters select,.report-filters input,.report-filters button{padding:9px;border:1px solid var(--btn-border);border-radius:7px;color:var(--text-color);background:var(--card-bg)}.report-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px}.report-cards strong{display:block;font-size:28px;color:var(--accent)}.report-cards small{color:var(--muted)}.report-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:18px}table{width:100%;border-collapse:collapse}td,th{text-align:left;padding:12px;border-bottom:1px solid var(--sidebar-border)}.report-bar{height:18px;background:var(--accent);border-radius:4px;min-width:2px}.bar-row{margin:14px 0}.pie{width:180px;height:180px;border-radius:50%;margin:18px auto}.legend{display:flex;flex-wrap:wrap;gap:14px}.legend i{display:inline-block;width:12px;height:12px;margin-right:5px}.report-note{color:var(--muted);line-height:1.6}.report-actions{display:flex;gap:18px;margin-top:14px}a{color:var(--accent)}@media print{.sidebar,.report-filters,.report-actions{display:none!important}.main{margin-left:0!important;width:100%!important}.report-panel{break-inside:avoid}body{background:white!important;color:black!important}}
</style></head><body<?= $isPrint ? ' class="report-print"' : '' ?>>
<?php if ($isPrint) { renderSidebarStyles(); } else { renderSidebar($role, $role . '.reports'); } ?>
<link rel="stylesheet" href="<?= $h(app_url('includes/reports.css')) ?>">
<main class="main" style="padding:24px;">
<h1>Accomplishments and KPI Reports</h1>
<?php if (!$isPrint): ?><form class="report-panel report-filters" method="get">
<label>Period<select name="period"><?php foreach (['monthly','quarterly','yearly'] as $option): ?><option value="<?= $option ?>" <?= $period === $option ? 'selected' : '' ?>><?= ucfirst($option) ?></option><?php endforeach ?></select></label>
<label>Year<input name="year" type="number" min="2000" max="2100" value="<?= $year ?>" required></label>
<label>Month<select name="month"><?php for ($i = 1; $i <= 12; $i++): ?><option value="<?= $i ?>" <?= $month === $i ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$i,1,2026)) ?></option><?php endfor ?></select></label>
<label>Quarter<select name="quarter"><?php for ($i = 1; $i <= 4; $i++): ?><option value="<?= $i ?>" <?= $quarter === $i ? 'selected' : '' ?>>Q<?= $i ?></option><?php endfor ?></select></label>
<?php if ($role !== 'employee'): ?><label>Staff<select name="user_id"><option value="0">All employees, admins and HR</option><?php foreach ($staff as $user): ?><option value="<?= (int) $user['id'] ?>" <?= $selected === (int) $user['id'] ? 'selected' : '' ?>><?= $h($user['full_name'] . ' (' . strtoupper($user['role']) . ')') ?></option><?php endforeach ?></select></label><?php endif ?>
<label>Chart<select name="chart"><option value="bar" <?= $chart === 'bar' ? 'selected' : '' ?>>Bar graph</option><option value="pie" <?= $chart === 'pie' ? 'selected' : '' ?>>Pie graph</option></select></label><button type="submit">Create report</button>
</form>
<?php endif ?>
<h2><?= ucfirst($period) ?> report: <?= $h($start) ?> to <?= $h($end) ?></h2>
<p class="report-note">Accomplishments include evaluations submitted during this period, even if assigned earlier. KPI uses attendance during the period and tasks assigned during the period, with their latest submission and review results. It is not a historical score snapshot. Missing KPI factors are excluded from the weighted score.</p>
<div class="report-actions">
<?php if (!$isPrint): ?>
<a class="report-action" href="<?= $h($csvUrl) ?>" download="accomplishments-<?= $h($start) ?>-to-<?= $h($end) ?>.csv">Download CSV</a>
<a class="report-action" href="<?= $h($printUrl) ?>" target="_blank" rel="noopener">Print / Save PDF</a>
<?php else: ?><button class="report-action" type="button" onclick="window.print()">Print / Save PDF</button><?php endif ?>
</div>
<div class="report-cards">
<?php foreach (['assigned'=>'Assigned in period','submitted'=>'Accomplishments submitted','reviewed'=>'Reviewed accomplishments'] as $key => $label): ?><div class="report-panel"><small><?= $label ?></small><strong><?= $totals[$key] ?></strong></div><?php endforeach ?>
<div class="report-panel"><small>Average staff KPI</small><strong><?= $average === null ? 'No data' : $average . '%' ?></strong></div>
</div>
<div class="report-grid"><section class="report-panel"><h2>Attendance distribution</h2>
<?php $attendanceTotal = $totals['present'] + $totals['late'] + $totals['absent']; $colors = ['present'=>'#3b82f6','late'=>'#b45309','absent'=>'#f87171']; ?>
<?php if (!$attendanceTotal): ?><p>No attendance data for this period.</p>
<?php elseif ($chart === 'pie'): $p = $totals['present'] / $attendanceTotal * 100; $l = $p + $totals['late'] / $attendanceTotal * 100; ?>
<div class="pie" role="img" aria-label="Attendance: <?= $totals['present'] ?> present, <?= $totals['late'] ?> late, <?= $totals['absent'] ?> absent" style="background:conic-gradient(<?= $colors['present'] ?> 0% <?= $p ?>%,<?= $colors['late'] ?> <?= $p ?>% <?= $l ?>%,<?= $colors['absent'] ?> <?= $l ?>% 100%);"></div>
<?php else: foreach ($colors as $status => $color): ?><div class="bar-row"><?= ucfirst($status) ?>: <?= $totals[$status] ?><div class="report-bar" style="background:<?= $color ?>;width:<?= $totals[$status] / $attendanceTotal * 100 ?>%"></div></div><?php endforeach; endif ?>
<div class="legend"><?php foreach ($colors as $status => $color): ?><span><i style="background:<?= $color ?>"></i><?= ucfirst($status) ?> <?= $totals[$status] ?></span><?php endforeach ?></div>
</section><section class="report-panel"><h2>KPI by staff member</h2>
<?php if (!$rows): ?><p>No staff matched the selected scope.</p><?php endif ?>
<?php foreach ($rows as $row): ?><div class="bar-row"><?= $h($row['user']['full_name']) ?>: <?= $row['kpi']['available_weight'] ? $row['kpi']['overall'] . '%' : 'No data' ?><?php if ($row['kpi']['available_weight']): ?><div class="report-bar" style="width:<?= $row['kpi']['overall'] ?>%"></div><?php endif ?></div><?php endforeach ?>
</section></div>
<section class="report-panel"><h2>KPI statistics</h2><table><thead><tr><th>Name / role</th><th>Assigned</th><th>Submitted in period</th><th>Attendance</th><th>HR rating</th><th>Completion</th><th>Deadline</th><th>Task health</th><th>Overall KPI</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><?= $h($row['user']['full_name']) ?><br><small><?= $h(strtoupper($row['user']['role'])) ?></small></td><td><?= $row['kpi']['stats']['assigned'] ?></td><td><?= $row['submitted'] ?></td><?php foreach ($row['kpi']['factors'] as $factor): ?><td><?= $factor['score'] === null ? 'No data' : round($factor['score'],1) . '%' ?></td><?php endforeach ?><td><?= $row['kpi']['available_weight'] ? $row['kpi']['overall'] . '%' : 'No data' ?></td></tr><?php endforeach ?>
<?php if (!$rows): ?><tr><td colspan="9">No staff matched the selected scope.</td></tr><?php endif ?>
</tbody></table></section>
<section class="report-panel"><h2>Accomplishments</h2><table><thead><tr><th>Staff member</th><th>Evaluation / task</th><th>Submitted</th><th>Status</th><th>HR score</th><th>HR feedback</th></tr></thead><tbody>
<?php foreach ($accomplishments as $task): ?><tr><td><?= $h($task['full_name']) ?></td><td><?= $h($task['title']) ?></td><td><?= $h(date('M d, Y h:i A', strtotime($task['submitted_at']))) ?></td><td><?= $h(ucfirst($task['status'])) ?></td><td><?= $task['hr_score'] === null ? 'Not rated' : $h($task['hr_score']) . '%' ?></td><td><?= $h($task['hr_feedback'] ?? '') ?></td></tr><?php endforeach ?>
<?php if (!$accomplishments): ?><tr><td colspan="6">No accomplishments submitted during this period.</td></tr><?php endif ?>
</tbody></table></section></main>
<?php if ($isPrint): ?><script>window.addEventListener('load', function () { window.print(); });</script><?php endif ?>
</body></html>
<?php
}
