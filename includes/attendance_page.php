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
    $recordRows=$records->fetch_all(MYSQLI_ASSOC);
    $monthValue=(string)($_GET['month']??date('Y-m'));
    if(!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$monthValue))$monthValue=date('Y-m');
    $calendarMonth=DateTimeImmutable::createFromFormat('!Y-m-d',$monthValue.'-01')?:new DateTimeImmutable('first day of this month');
    $calendarDays=(int)$calendarMonth->format('t');$calendarOffset=(int)$calendarMonth->format('N')-1;$calendarRecords=[];
    if(!$canViewAll){
        $outStmt=$conn->prepare("SELECT DATE(login_time) AS log_date, MAX(logout_time) AS time_out FROM login_logs WHERE user_id=? AND login_time >= ? AND login_time < DATE_ADD(?, INTERVAL 1 MONTH) GROUP BY DATE(login_time)");
        $monthStart=$calendarMonth->format('Y-m-01');$nextMonth=$calendarMonth->modify('+1 month')->format('Y-m-01');
        $outStmt->bind_param('iss',$currentUserId,$monthStart,$monthStart);$outStmt->execute();
        foreach($outStmt->get_result() as $outRow)$calendarOut[$outRow['log_date']]=$outRow['time_out'];
        foreach($recordRows as $attendanceRow)$calendarRecords[$attendanceRow['attendance_date']][]=$attendanceRow;
    }
    $holidayNames=[];
    if(!$canViewAll){$holidayStart=$calendarMonth->format('Y-m-01');$holidayEnd=$calendarMonth->format('Y-m-t');$holidayStmt=$conn->prepare('SELECT holiday_date,name FROM holidays WHERE holiday_date BETWEEN ? AND ?');$holidayStmt->bind_param('ss',$holidayStart,$holidayEnd);$holidayStmt->execute();foreach($holidayStmt->get_result() as $holiday)$holidayNames[$holiday['holiday_date']]=$holiday['name'];}
    $chartMonth=(string)($_GET['month']??date('Y-m'));if(!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$chartMonth))$chartMonth=date('Y-m');$chartStart=$chartMonth.'-01';$chartEnd=(new DateTimeImmutable($chartStart))->modify('last day of this month')->format('Y-m-d');$staffAttendance=[];
    if($role==='hr'){$chartStmt=$conn->prepare("SELECT u.full_name,SUM(a.status='present') present_count,SUM(a.status='late') late_count,SUM(a.status='absent') absent_count FROM users u LEFT JOIN attendance a ON a.user_id=u.id AND a.attendance_date BETWEEN ? AND ? WHERE u.status='active' AND u.role IN ('employee','hr','admin') AND (u.full_name LIKE ? ESCAPE '!' OR u.employee_id LIKE ? ESCAPE '!' OR u.role LIKE ? ESCAPE '!') GROUP BY u.id,u.full_name ORDER BY u.full_name");$chartStmt->bind_param('sssss',$chartStart,$chartEnd,$namePattern,$namePattern,$namePattern);$chartStmt->execute();$staffAttendance=$chartStmt->get_result()->fetch_all(MYSQLI_ASSOC);}

    $hrCalendar=[];
    if($role==='hr'){
        $dayStmt=$conn->prepare("SELECT u.id user_id,u.full_name,u.employee_id,u.role,a.status,a.login_time,(SELECT MAX(l.logout_time) FROM login_logs l WHERE l.user_id=u.id AND DATE(l.login_time)=?) time_out FROM users u LEFT JOIN attendance a ON a.user_id=u.id AND a.attendance_date=? WHERE u.status='active' AND u.role IN ('employee','hr','admin') AND (u.full_name LIKE ? ESCAPE '!' OR u.employee_id LIKE ? ESCAPE '!' OR u.role LIKE ? ESCAPE '!') ORDER BY u.full_name");
        for($d=1;$d<=$calendarDays;$d++){$key=$calendarMonth->format('Y-m-').str_pad((string)$d,2,'0',STR_PAD_LEFT);$dayStmt->bind_param('sssss',$key,$key,$namePattern,$namePattern,$namePattern);$dayStmt->execute();$hrCalendar[$key]=$dayStmt->get_result()->fetch_all(MYSQLI_ASSOC);}
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
    --attendance-present-bg:#123324;
    --attendance-present-text:#86efac;
    --attendance-late-bg:#3a2d0b;
    --attendance-late-text:#fde047;
    --attendance-late-border:#a16207;
    --attendance-absent-bg:#3b1720;
    --attendance-absent-text:#fda4af;
    --attendance-absent-border:#be123c;
    --attendance-holiday-bg:#312e81;
    --attendance-holiday-text:#c4b5fd;
    --attendance-holiday-border:#7c3aed;
}
:root {
    --attendance-present-bg:#dcfce7;
    --attendance-present-text:#166534;
    --attendance-late-bg:#fef3c7;
    --attendance-late-text:#92400e;
    --attendance-late-border:#facc15;
    --attendance-absent-bg:#fee2e2;
    --attendance-absent-text:#991b1b;
    --attendance-absent-border:#fca5a5;
    --attendance-holiday-bg:#ede9fe;
    --attendance-holiday-text:#5b21b6;
    --attendance-holiday-border:#c4b5fd;
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
.attendance-calendar{background:var(--attendance-card);border:1px solid var(--attendance-border);border-radius:14px;padding:20px;box-shadow:0 10px 22px rgba(15,23,42,.05)}
.calendar-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px}.calendar-heading h2{margin:0;font-size:20px}.calendar-nav{display:flex;gap:8px}.calendar-nav a{display:inline-flex;align-items:center;justify-content:center;min-width:38px;height:38px;padding:0 12px;border:1px solid var(--attendance-border);border-radius:8px;background:var(--attendance-card-soft);color:var(--attendance-text);text-decoration:none;font-weight:700}.calendar-nav a:hover{border-color:var(--attendance-accent)}
.calendar-weekdays,.calendar-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:7px}.calendar-weekdays{margin-bottom:7px;text-align:center;color:var(--attendance-muted);font-size:12px;font-weight:700}.calendar-day,.calendar-blank{min-height:92px;border-radius:9px}.calendar-day{padding:8px;border:1px solid var(--attendance-border);background:var(--attendance-card-soft)}.calendar-day .day-number{font-weight:700;font-size:14px}.calendar-day .day-status{display:block;margin-top:8px;padding:5px 6px;border-radius:6px;font-size:11px;font-weight:700;line-height:1.25}.calendar-day.present-day .day-status{background:#dcfce7;color:#166534}.calendar-day.late-day{background:#fffbeb;border-color:#facc15}.calendar-day.late-day .day-status{background:#fef3c7;color:#92400e}.calendar-day.absent-day{background:#fff1f2;border-color:#fca5a5}.calendar-day.absent-day .day-status{background:#fee2e2;color:#991b1b}.calendar-legend{display:flex;flex-wrap:wrap;gap:10px 18px;margin-top:16px;color:var(--attendance-muted);font-size:12px}.calendar-legend span{display:inline-flex;align-items:center;gap:7px}.calendar-legend i{width:11px;height:11px;border-radius:3px}.calendar-legend .legend-absent{background:#ef4444}.calendar-legend .legend-late{background:#eab308}.calendar-legend .legend-present{background:#22c55e}
.hr-calendar-day{min-height:78px;padding:8px;border:1px solid var(--attendance-border);border-radius:9px;background:var(--attendance-card-soft);color:var(--attendance-text);text-align:left;cursor:pointer}.hr-calendar-day:hover{border-color:var(--attendance-accent)}.hr-attendance-modal{position:fixed;inset:50% auto auto 50%;transform:translate(-50%,-50%);margin:0;box-sizing:border-box;width:min(900px,calc(100vw - 28px));max-height:85vh;border:1px solid var(--attendance-border);border-radius:14px;background:var(--attendance-card);color:var(--attendance-text)}.hr-attendance-modal::backdrop{background:#0f172a99}.hr-modal-head{display:flex;justify-content:space-between;padding:16px;border-bottom:1px solid var(--attendance-border)}.hr-modal-head h2{margin:0}.hr-modal-content{padding:16px;overflow:auto;max-height:70vh}.hr-modal-content .badge.present{background:var(--attendance-present-bg);color:var(--attendance-present-text)}.hr-modal-content .badge.late{background:var(--attendance-late-bg);color:var(--attendance-late-text)}.hr-modal-content .badge.absent{background:var(--attendance-absent-bg);color:var(--attendance-absent-text)}.calendar-day.present-day .day-status{background:var(--attendance-present-bg);color:var(--attendance-present-text)}
.calendar-day.late-day{background:color-mix(in srgb,var(--attendance-late-bg) 60%,var(--attendance-card));border-color:var(--attendance-late-border)}
.calendar-day.late-day .day-status{background:var(--attendance-late-bg);color:var(--attendance-late-text)}
.calendar-day.absent-day{background:color-mix(in srgb,var(--attendance-absent-bg) 60%,var(--attendance-card));border-color:var(--attendance-absent-border)}
.calendar-day.absent-day .day-status{background:var(--attendance-absent-bg);color:var(--attendance-absent-text)}
.calendar-day.holiday-day{background:color-mix(in srgb,var(--attendance-holiday-bg) 65%,var(--attendance-card));border-color:var(--attendance-holiday-border)}.calendar-day .holiday-name{display:block;width:100%;margin-top:6px;color:var(--attendance-holiday-text);font-size:10px;font-weight:700;line-height:1.2;overflow-wrap:anywhere}.calendar-legend .legend-holiday{background:#8b5cf6}
.attendance-chart{background:var(--attendance-card);border:1px solid var(--attendance-border);border-radius:14px;padding:18px;margin:20px 0;box-shadow:0 10px 22px rgba(15,23,42,.05)}.attendance-chart-header{display:flex;justify-content:space-between;align-items:end;gap:12px;flex-wrap:wrap;margin-bottom:14px}.attendance-chart-header h2{margin:0 0 5px}.attendance-chart-header p{margin:0;color:var(--attendance-muted);font-size:13px}.attendance-chart-header form{display:flex;align-items:center;gap:8px}.attendance-chart-header input,.attendance-chart-header button{padding:8px 10px;border:1px solid var(--attendance-border);border-radius:7px;background:var(--attendance-card-soft);color:var(--attendance-text)}.attendance-chart-scroll{overflow-x:auto;padding-bottom:8px}.attendance-bars{--bar-height:250px;display:flex;align-items:stretch;gap:14px;min-width:max-content;padding:12px 8px 0 44px;background:repeating-linear-gradient(to bottom,transparent 0,transparent calc(var(--bar-height)/4 - 1px),var(--attendance-border) calc(var(--bar-height)/4 - 1px),var(--attendance-border) calc(var(--bar-height)/4));border-bottom:1px solid var(--attendance-border)}.attendance-bar-group{width:74px;height:calc(var(--bar-height) + 54px);display:flex;flex-direction:column;justify-content:flex-end;align-items:center;flex-shrink:0}.attendance-bar-values{height:var(--bar-height);width:100%;display:flex;align-items:flex-end;justify-content:center;gap:4px}.attendance-bar{position:relative;width:16px;min-height:2px;border-radius:4px 4px 0 0}.attendance-bar.present{background:#22c55e}.attendance-bar.late{background:#eab308}.attendance-bar.absent{background:#ef4444}.attendance-bar span{position:absolute;bottom:calc(100% + 3px);left:50%;transform:translateX(-50%);font-size:10px;font-weight:700;color:var(--attendance-text)}.attendance-bar-name{height:42px;padding-top:8px;text-align:center;font-size:11px;font-weight:700;color:var(--attendance-text);white-space:nowrap}.attendance-chart-legend{display:flex;flex-wrap:wrap;gap:16px;margin-top:14px;font-size:12px;color:var(--attendance-muted)}.attendance-chart-legend span{display:inline-flex;align-items:center;gap:7px}.attendance-chart-legend i{width:10px;height:10px;border-radius:3px}.attendance-chart-legend .present-key{background:#22c55e}.attendance-chart-legend .late-key{background:#eab308}.attendance-chart-legend .absent-key{background:#ef4444}
@media(max-width:600px){.attendance-calendar{padding:12px}.calendar-weekdays,.calendar-grid{gap:3px}.calendar-day,.calendar-blank{min-height:66px}.calendar-day{padding:5px 3px}.calendar-day .day-status{font-size:9px;padding:4px 2px;margin-top:5px}.calendar-heading h2{font-size:17px}}
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

    <?php if($role==='hr'): ?><section class="attendance-calendar" aria-label="Staff attendance calendar"><header class="calendar-heading"><h2><?= htmlspecialchars($calendarMonth->format('F Y')) ?></h2><nav class="calendar-nav"><a href="?month=<?= $calendarMonth->modify('-1 month')->format('Y-m') ?><?= $search!==''?'&search='.rawurlencode($search):'' ?>">‹</a><a href="?month=<?= date('Y-m') ?><?= $search!==''?'&search='.rawurlencode($search):'' ?>">Today</a><a href="?month=<?= $calendarMonth->modify('+1 month')->format('Y-m') ?><?= $search!==''?'&search='.rawurlencode($search):'' ?>">›</a></nav></header><div class="calendar-weekdays"><?php foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $weekday): ?><span><?= $weekday ?></span><?php endforeach ?></div><div class="calendar-grid"><?php for($i=0;$i<$calendarOffset;$i++): ?><span class="calendar-blank"></span><?php endfor;for($d=1;$d<=$calendarDays;$d++):$key=$calendarMonth->format('Y-m-').str_pad((string)$d,2,'0',STR_PAD_LEFT);$people=$hrCalendar[$key]??[];$counts=['present'=>0,'late'=>0,'absent'=>0];foreach($people as $p)if(isset($counts[$p['status']]))$counts[$p['status']]++; ?><button class="hr-calendar-day" type="button" data-date="<?= htmlspecialchars($key) ?>"><strong><?= $d ?></strong><small><?= $counts['present'] ?> present · <?= $counts['late'] ?> late · <?= $counts['absent'] ?> absent</small></button><?php endfor ?></div><p class="attendance-note">Select a date to view staff attendance and time in/out.</p></section><dialog id="hr-attendance-modal" class="hr-attendance-modal"><header class="hr-modal-head"><h2 id="hr-modal-title"></h2><button type="button" aria-label="Close" onclick="this.closest('dialog').close()">×</button></header><div class="hr-modal-content" id="hr-modal-content"></div></dialog><script>const hrAttendanceDays=<?= json_encode($hrCalendar,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;document.querySelectorAll('.hr-calendar-day').forEach(b=>b.addEventListener('click',()=>{const date=b.dataset.date,rows=hrAttendanceDays[date]||[],esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])),time=s=>s?new Date(String(s).replace(' ','T')).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}):'';document.getElementById('hr-modal-title').textContent='Attendance · '+date;document.getElementById('hr-modal-content').innerHTML='<div class="table-wrap"><table><thead><tr><th>Name</th><th>Role</th><th>Status</th><th>Time in</th><th>Time out</th></tr></thead><tbody>'+rows.map(r=>'<tr><td>'+esc(r.full_name)+' <small>'+esc(r.employee_id||'')+'</small></td><td>'+esc(r.role)+'</td><td><span class="badge '+(r.status==='present'?'present':r.status==='late'?'late':r.status==='absent'?'absent':'')+'">'+esc(r.status||'No record')+'</span></td><td>'+(r.status==='absent'?'':time(r.login_time))+'</td><td>'+time(r.time_out)+'</td></tr>').join('')+'</tbody></table></div>';document.getElementById('hr-attendance-modal').showModal()}));</script><?php endif ?>
    <?php if($role==='hr'): ?><section class="attendance-chart"><header class="attendance-chart-header"><div><h2>Monthly attendance by staff</h2><p>Present, late, and absent record counts for each staff member.</p></div><form method="get"><input type="hidden" name="search" value="<?= htmlspecialchars($search,ENT_QUOTES,'UTF-8') ?>"><label for="attendance-chart-month">Month</label><input id="attendance-chart-month" type="month" name="month" value="<?= htmlspecialchars($chartMonth,ENT_QUOTES,'UTF-8') ?>"><button type="submit">View</button></form></header><?php $chartMax=1;foreach($staffAttendance as $person)$chartMax=max($chartMax,(int)$person['present_count'],(int)$person['late_count'],(int)$person['absent_count']); ?><div class="attendance-chart-scroll"><div class="attendance-bars" style="--bar-height:250px"><?php foreach($staffAttendance as $person):$parts=preg_split('/\s+/',trim($person['full_name']));$shortName=mb_substr($parts[0]??'',0,1).'. '.($parts[count($parts)-1]??''); ?><div class="attendance-bar-group" title="<?= htmlspecialchars($person['full_name'],ENT_QUOTES,'UTF-8') ?>"><div class="attendance-bar-values"><?php foreach(['present'=>'present_count','late'=>'late_count','absent'=>'absent_count'] as $status=>$field):$count=(int)$person[$field];$height=$count?max(4,round($count/$chartMax*230)):0; ?><div class="attendance-bar <?= $status ?>" style="height:<?= $height ?>px" aria-label="<?= ucfirst($status) ?> <?= $count ?>"><span><?= $count ?></span></div><?php endforeach ?></div><div class="attendance-bar-name"><?= htmlspecialchars($shortName,ENT_QUOTES,'UTF-8') ?></div></div><?php endforeach ?><?php if(!$staffAttendance): ?><p>No staff attendance matches the search for this month.</p><?php endif ?></div></div><div class="attendance-chart-legend"><span><i class="present-key"></i>Present</span><span><i class="late-key"></i>Late</span><span><i class="absent-key"></i>Absent</span></div></section><?php endif ?>
    <?php if($canViewAll): ?><form method="get" style="margin:18px 0;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <?php if($role==='hr'): ?><input type="hidden" name="month" value="<?= htmlspecialchars($chartMonth,ENT_QUOTES,'UTF-8') ?>"><?php endif ?>
        <label for="attendance-search"></label>
        <input id="attendance-search" type="search" name="search" placeholder="Name, employee ID or role" value="<?= htmlspecialchars($search); ?>" style="padding:10px;background:var(--attendance-card);color:var(--attendance-text);border:1px solid var(--attendance-border);border-radius:8px;">
        <button type="submit">Search</button>
        <a class="app-button secondary" href="<?= htmlspecialchars(route($activeRoute).($role==='hr'?'?month='.rawurlencode($chartMonth):'')); ?>">Clear</a>
    </form>
    <p class="attendance-note">Search includes employees, admins and HR. Summary cards show all records.</p>
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
                <?php if ($recordRows): ?>
                    <?php foreach ($recordRows as $row): ?>
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
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:var(--muted);">No attendance records match your search.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <section class="attendance-calendar" aria-label="My attendance calendar">
        <header class="calendar-heading"><h2><?= htmlspecialchars($calendarMonth->format('F Y')) ?></h2><nav class="calendar-nav" aria-label="Calendar month navigation"><a href="?month=<?= htmlspecialchars($calendarMonth->modify('-1 month')->format('Y-m')) ?>" aria-label="Previous month">‹</a><a href="?month=<?= htmlspecialchars(date('Y-m')) ?>" aria-label="Current month">Today</a><a href="?month=<?= htmlspecialchars($calendarMonth->modify('+1 month')->format('Y-m')) ?>" aria-label="Next month">›</a></nav></header>
        <div class="calendar-weekdays" aria-hidden="true"><?php foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $weekday): ?><span><?= $weekday ?></span><?php endforeach ?></div>
        <div class="calendar-grid"><?php for($blank=0;$blank<$calendarOffset;$blank++): ?><span class="calendar-blank" aria-hidden="true"></span><?php endfor;for($dayNumber=1;$dayNumber<=$calendarDays;$dayNumber++):$dateKey=$calendarMonth->format('Y-m-').str_pad((string)$dayNumber,2,'0',STR_PAD_LEFT);$dayRows=$calendarRecords[$dateKey]??[];$statuses=array_column($dayRows,'status');$isHoliday=isset($holidayNames[$dateKey]);$dayClass=$isHoliday?'holiday-day':(in_array('absent',$statuses,true)?'absent-day':(in_array('late',$statuses,true)?'late-day':(in_array('present',$statuses,true)?'present-day':''))); ?><div class="calendar-day <?= $dayClass ?>" aria-label="<?= htmlspecialchars($calendarMonth->format('F ').$dayNumber.($isHoliday?' · Holiday: '.$holidayNames[$dateKey]:($statuses?' · '.implode(', ',$statuses):' · No attendance record'))) ?>"><span class="day-number"><?= $dayNumber ?></span><?php if($isHoliday): ?><span class="holiday-name"><?= htmlspecialchars($holidayNames[$dateKey]) ?></span><?php endif ?><small class="day-times">In: <?= !empty($dayRows[0]['login_time']) && $dayRows[0]['status']!=='absent'?date('h:i A',strtotime($dayRows[0]['login_time'])):'—' ?><br>Out: <?= !empty($calendarOut[$dateKey])?date('h:i A',strtotime($calendarOut[$dateKey])):'—' ?></small><?php if(in_array('absent',$statuses,true)): ?><span class="day-status absent">Absent</span><?php elseif(in_array('late',$statuses,true)): ?><span class="day-status late">Late<?= count(array_filter($dayRows,static fn($r)=>$r['status']==='late'))>1?' · '.count(array_filter($dayRows,static fn($r)=>$r['status']==='late')):'' ?></span><?php elseif(in_array('present',$statuses,true)): ?><span class="day-status present">Present</span><?php endif ?></div><?php endfor ?></div>
        <div class="calendar-legend"><span><i class="legend-absent"></i>Absent</span><span><i class="legend-late"></i>Late</span><span><i class="legend-present"></i>Present</span><span><i class="legend-holiday"></i>Holiday</span></div>
    </section>
    <?php endif ?>
</div>
</body>
</html>
<?php
}
?>
