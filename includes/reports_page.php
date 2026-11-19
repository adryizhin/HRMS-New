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
    $staffSearch=mb_strtolower(trim((string)($_GET['q']??'')));if($staffSearch!=='')$staff=array_values(array_filter($staff,static fn(array $u):bool=>str_contains(mb_strtolower($u['full_name'].' '.$u['employee_id'].' '.$u['role']),$staffSearch)));
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
        $scores[] = $kpi['overall'];
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
            $cells = array_map(static function (mixed $cell): mixed {
                return is_string($cell) && preg_match('/^[=+@\-\t\r\n]/', $cell) ? "'" . $cell : $cell;
            }, $cells);
            fputcsv($output, $cells, ',', '"', '');
        };
        $writeCsv(['Period start', 'Period end', 'Name', 'Role', 'Assigned in period', 'Submitted in period', 'Reviewed submissions', 'Present', 'Late', 'Absent', 'Performance / 100', 'Assessment coverage %', 'Assessment']);
        foreach ($rows as $row) {
            $name = $row['user']['full_name'];
            $writeCsv([$start, $end, $name, $row['user']['role'], $row['kpi']['stats']['assigned'], $row['submitted'], $row['reviewed'], $row['attendance']['present'], $row['attendance']['late'], $row['attendance']['absent'], $row['kpi']['overall'], $row['kpi']['available_weight'], $row['kpi']['label']]);
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
    $exportQuery = ['period' => $period, 'year' => $year, 'month' => $month, 'quarter' => $quarter, 'user_id' => $selected, 'q' => $staffSearch];
    $exportQuery['export'] = 'csv';
    $csvUrl = route($role . '.reports') . '?' . http_build_query($exportQuery);
    $exportQuery['export'] = 'print';
    $printUrl = route($role . '.reports') . '?' . http_build_query($exportQuery);
    $isPrint = ($_GET['export'] ?? '') === 'print';
    $h = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Accomplishments and KPI Reports</title>
<link rel="stylesheet" href="dashboard.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.report-panel{background:var(--card-bg);border:1px solid var(--sidebar-border);border-radius:12px;padding:20px;margin:18px 0;overflow:auto}.report-filters{display:flex;flex-wrap:wrap;gap:12px;align-items:end}.report-filters label{display:flex;flex-direction:column;gap:5px}.report-filters select,.report-filters input,.report-filters button{padding:9px;border:1px solid var(--btn-border);border-radius:7px;color:var(--text-color);background:var(--card-bg)}.report-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px}.report-cards strong{display:block;font-size:28px;color:var(--accent)}.report-cards small{color:var(--muted)}.report-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:18px}table{width:100%;border-collapse:collapse}td,th{text-align:left;padding:12px;border-bottom:1px solid var(--sidebar-border)}.report-bar{height:18px;background:var(--accent);border-radius:4px;min-width:2px}.bar-row{margin:14px 0}.pie{width:180px;height:180px;border-radius:50%;margin:18px auto}.legend{display:flex;flex-wrap:wrap;gap:14px}.legend i{display:inline-block;width:12px;height:12px;margin-right:5px}.report-note{color:var(--muted);line-height:1.6}.report-actions{display:flex;gap:18px;margin-top:14px}a{color:var(--accent)}.attendance-calendar{min-width:270px}.calendar-weekdays,.calendar-days{display:grid;grid-template-columns:repeat(7,minmax(28px,1fr));gap:4px}.calendar-weekdays{text-align:center;color:var(--muted);font-size:12px}.calendar-day{min-height:64px;padding:5px;border:1px solid var(--sidebar-border);border-radius:6px;display:flex;flex-wrap:wrap;align-content:flex-start;gap:3px}.calendar-day b{width:100%;font-size:12px}.calendar-counts{display:flex;flex-wrap:wrap;gap:4px}.calendar-count{font-size:11px;font-weight:700;line-height:1.2}.calendar-count.present{color:#2563eb}.calendar-count.late{color:#b45309}.calendar-count.absent{color:#dc2626}.calendar-empty{min-height:64px}.kpi-line{width:100%;min-width:300px;height:240px}.kpi-dialog{position:fixed;inset:0;margin:auto;width:min(1180px,calc(100vw - 40px));max-width:none;max-height:min(82vh,820px);padding:0;overflow:hidden;border:1px solid var(--sidebar-border);border-radius:16px;background:var(--card-bg);color:var(--text-color);box-shadow:0 28px 90px rgba(15,23,42,.28)}.kpi-dialog::backdrop{background:rgba(15,23,42,.62);backdrop-filter:blur(3px)}.kpi-dialog header{position:sticky;top:0;z-index:1;display:flex;justify-content:space-between;align-items:center;gap:20px;padding:20px 24px;background:var(--card-bg);border-bottom:1px solid var(--sidebar-border)}.kpi-dialog header>div{display:flex;align-items:center;justify-content:space-between;gap:24px;flex:1}.kpi-dialog header h2{margin:0;font-size:21px;letter-spacing:-.02em}.kpi-dialog header label{display:flex;align-items:center;gap:10px;color:var(--muted);font-size:13px;font-weight:600}.kpi-dialog header input{width:min(300px,36vw);padding:10px 12px;border:1px solid var(--sidebar-border);border-radius:9px;background:var(--card-bg);color:var(--text-color);font:inherit}.kpi-dialog header button{display:grid;place-items:center;width:40px;height:40px;border:1px solid var(--sidebar-border);border-radius:10px;background:var(--card-bg);color:var(--muted);font-size:22px;cursor:pointer}.kpi-dialog header button:hover{background:var(--hover-bg);color:var(--text-color)}.kpi-dialog-content{max-height:calc(min(82vh,820px) - 82px);overflow:auto;padding:8px 24px 24px}.kpi-dialog table{min-width:920px;font-size:13px}.kpi-dialog th{position:sticky;top:0;z-index:1;background:var(--card-bg);color:var(--muted);font-size:11px;letter-spacing:.04em;text-transform:uppercase}.kpi-dialog td,.kpi-dialog th{padding:14px 12px}.kpi-dialog tbody tr:nth-child(even){background:color-mix(in srgb,var(--card-bg) 88%,var(--accent))}.kpi-dialog tbody tr:hover{background:color-mix(in srgb,var(--card-bg) 80%,var(--accent))}.kpi-dialog :is(input,button):focus-visible{outline:3px solid color-mix(in srgb,var(--accent) 55%,transparent);outline-offset:2px}@media(max-width:700px){.kpi-dialog{width:calc(100vw - 20px);max-height:88vh;border-radius:12px}.kpi-dialog header{padding:14px;align-items:flex-start}.kpi-dialog header>div{align-items:flex-start;flex-direction:column;gap:12px}.kpi-dialog header label{width:100%;align-items:flex-start;flex-direction:column;gap:6px}.kpi-dialog header input{width:100%}.kpi-dialog-content{max-height:calc(88vh - 120px);padding:4px 14px 14px}}@media print{.sidebar,.report-filters,.report-actions{display:none!important}.main{margin-left:0!important;width:100%!important}.report-panel{break-inside:avoid}body{background:white!important;color:black!important}}
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
<label>Search staff<input type="search" name="q" value="<?= $h($staffSearch) ?>" placeholder="Name, ID or role"></label>
<button type="submit" name="search" value="1"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Search report</button>
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
<div class="report-panel"><small>Average staff performance</small><strong><?= $average === null ? 'No staff' : $average . '%' ?></strong></div>
</div>
<div class="report-grid"><section class="report-panel"><h2>Attendance distribution</h2>
<?php $attendanceTotal = $totals['present'] + $totals['late'] + $totals['absent']; $colors = ['present'=>'#3b82f6','late'=>'#b45309','absent'=>'#f87171']; ?>
<?php $calendarStart=new DateTimeImmutable($start);$calendarEnd=new DateTimeImmutable($end);$firstOffset=(int)$calendarStart->format('N')-1;$daily=[];$calendarQuery=$conn->prepare('SELECT attendance_date,status,COUNT(*) count FROM attendance WHERE attendance_date BETWEEN ? AND ? GROUP BY attendance_date,status');$calendarQuery->bind_param('ss',$start,$end);$calendarQuery->execute();foreach($calendarQuery->get_result() as $r)$daily[$r['attendance_date']][$r['status']]=(int)$r['count']; ?>
<div class="attendance-calendar"><div class="calendar-weekdays"><?php foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $weekday): ?><b><?= $weekday ?></b><?php endforeach ?></div><div class="calendar-days"><?php for($i=0;$i<$firstOffset;$i++): ?><span class="calendar-empty"></span><?php endfor;for($date=$calendarStart;$date<=$calendarEnd;$date=$date->modify('+1 day')):$key=$date->format('Y-m-d');$day=$daily[$key]??[]; ?><div class="calendar-day" title="<?= $h($key) ?> · Present <?= $day['present']??0 ?> · Late <?= $day['late']??0 ?> · Absent <?= $day['absent']??0 ?>"><b><?= $date->format('j') ?></b><div class="calendar-counts"><?php foreach(['present'=>'Present','late'=>'Late','absent'=>'Absent'] as $status=>$label):if(($day[$status]??0)>0): ?><span class="calendar-count <?= $status ?>" aria-label="<?= $label ?>: <?= $day[$status] ?>"><?= $label==='Present'?'P':($label==='Late'?'L':'A') ?> <?= $day[$status] ?></span><?php endif;endforeach ?></div></div><?php endfor ?></div><p class="report-note">Daily counts: <span class="calendar-count absent">Absent</span> · <span class="calendar-count late">Late</span> · <span class="calendar-count present">Present</span>.</p></div>
<div class="legend"><?php foreach ($colors as $status => $color): ?><span><i style="background:<?= $color ?>"></i><?= ucfirst($status) ?> <?= $totals[$status] ?></span><?php endforeach ?></div>
</section><section class="report-panel"><h2>KPI by staff member</h2>
<?php if (!$rows): ?><p>No staff matched the selected scope.</p><?php endif ?>
<?php $plotRows=array_values(array_filter($rows,static fn($row)=>$row['kpi']['overall']!==null)); ?><div class="kpi-by-staff-scroll"><div class="kpi-by-staff-bars"><?php foreach($plotRows as $row):$parts=preg_split('/\s+/',trim($row['user']['full_name']));$shortName=mb_substr($parts[0]??'',0,1).'. '.($parts[count($parts)-1]??'');$score=(float)$row['kpi']['overall']; ?><div class="kpi-by-staff-item" title="<?= $h($row['user']['full_name'].' · '.$score.'%') ?>"><div class="kpi-by-staff-bar-wrap"><div class="kpi-by-staff-bar <?= $score>=85?'excellent':($score>=70?'good':($score>=50?'watch':'risk')) ?>" style="height:<?= max(3,round($score*2.3)) ?>px"><strong><?= $score ?>%</strong></div></div><span><?= $h($shortName) ?></span></div><?php endforeach ?><?php if(!$plotRows): ?><p>No KPI scores to display.</p><?php endif ?></div></div><div class="kpi-by-staff-legend"><span><i class="excellent"></i>Excellent</span><span><i class="good"></i>On Track</span><span><i class="watch"></i>Needs Support</span><span><i class="risk"></i>At Risk</span></div>
</section></div>
<section class="report-panel"><h2>KPI statistics <button type="button" data-open-kpi>View statistics</button></h2><dialog class="kpi-dialog" data-kpi-dialog><header><div><h2>KPI statistics</h2><?php if(!$isPrint): ?><label>Search staff <input type="search" data-kpi-search placeholder="Name or role"></label><?php endif ?></div><button type="button" data-close-kpi aria-label="Close">×</button></header><div class="kpi-dialog-content"><table><thead><tr><th>Name / role</th><th>Assigned</th><th>Submitted in period</th><th>Attendance reliability</th><th>Reviewed work quality</th><th>Task completion</th><th>On-time delivery</th><th>Assessment coverage</th><th>Overall KPI</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr data-kpi-row><td><?= $h($row['user']['full_name']) ?><br><small><?= $h(strtoupper($row['user']['role'])) ?></small></td><td><?= $row['kpi']['stats']['assigned'] ?></td><td><?= $row['submitted'] ?></td><?php foreach ($row['kpi']['factors'] as $factor): ?><td><?= $factor['score'] === null ? '0% (no data)' : round($factor['score'],1) . '%' ?></td><?php endforeach ?><td><?= (int)$row['kpi']['available_weight'] ?>% · <?= $h($row['kpi']['label']) ?></td><td><?= $row['kpi']['overall'] . '%' ?></td></tr><?php endforeach ?>
<?php if (!$rows): ?><tr><td colspan="9">No staff matched the selected scope.</td></tr><?php endif ?>
</tbody></table></div></dialog></section>
<section class="report-panel"><h2>Accomplishments</h2><table><thead><tr><th>Staff member</th><th>Evaluation / task</th><th>Submitted</th><th>Status</th><th>HR score</th><th>HR feedback</th></tr></thead><tbody>
<?php foreach ($accomplishments as $task): ?><tr><td><?= $h($task['full_name']) ?></td><td><?= $h($task['title']) ?></td><td><?= $h(date('M d, Y h:i A', strtotime($task['submitted_at']))) ?></td><td><?= $h(ucfirst($task['status'])) ?></td><td><?= $task['hr_score'] === null ? 'Not rated' : $h($task['hr_score']) . '%' ?></td><td><?= $h($task['hr_feedback'] ?? '') ?></td></tr><?php endforeach ?>
<?php if (!$accomplishments): ?><tr><td colspan="6">No accomplishments submitted during this period.</td></tr><?php endif ?>
</tbody></table></section></main>
<?php if (!$isPrint): ?><script>const d=document.querySelector('[data-kpi-dialog]');document.querySelector('[data-open-kpi]')?.addEventListener('click',()=>d.showModal());document.querySelector('[data-close-kpi]')?.addEventListener('click',()=>d.close());d?.addEventListener('click',e=>{if(e.target===d)d.close()});document.querySelector('[data-kpi-search]')?.addEventListener('input',e=>{const q=e.target.value.toLocaleLowerCase();d.querySelectorAll('[data-kpi-row]').forEach(row=>row.hidden=!row.cells[0].innerText.toLocaleLowerCase().includes(q))});</script><?php endif ?><?php if ($isPrint): ?><script>window.addEventListener('load', function () { window.print(); });</script><?php endif ?>
</body></html>
<?php
}
