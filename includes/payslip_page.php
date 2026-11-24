<?php
declare(strict_types=1);

require_once __DIR__ . '/attendance.php';
require_once __DIR__ . '/sidebar.php';
require_once __DIR__ . '/payroll_profiles.php';

function money(float $amount): string
{
    return 'PHP ' . number_format($amount, 2);
}

function payslipDefaultCutoff(): array
{
    $today = new DateTimeImmutable('today', new DateTimeZone('Asia/Manila'));
    $day = (int) $today->format('d');

    if ($day <= 15) {
        $start = $today->modify('first day of this month');
        $end = $today->setDate((int) $today->format('Y'), (int) $today->format('m'), 15);
    } else {
        $start = $today->setDate((int) $today->format('Y'), (int) $today->format('m'), 16);
        $end = $today->modify('last day of this month');
    }

    return [$start->format('Y-m-d'), $end->format('Y-m-d')];
}

function payslipCountWorkdays(mysqli $conn, string $startDate, string $endDate): int
{
    $start = new DateTimeImmutable($startDate);
    $end = new DateTimeImmutable($endDate);
    $days = 0;
    $holidayDates = holidayDatesBetween($conn, $startDate, $endDate);

    for ($date = $start; $date <= $end; $date = $date->modify('+1 day')) {
        $dayOfWeek = (int) $date->format('N');
        if ($dayOfWeek <= 5 && !isset($holidayDates[$date->format('Y-m-d')])) {
            $days++;
        }
    }

    return $days;
}

function payslipAttendanceSummary(mysqli $conn, int $employeeId, string $startDate, string $endDate): array
{
    ensureAttendanceTable($conn);

    $stmt = $conn->prepare("
        SELECT
            SUM(status = 'present') AS present_count,
            SUM(status = 'late') AS late_count,
            SUM(status = 'absent') AS absent_count,
            COUNT(*) AS recorded_count
        FROM attendance
        WHERE user_id = ?
        AND attendance_date BETWEEN ? AND ?
    ");
    $stmt->bind_param("iss", $employeeId, $startDate, $endDate);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: [];

    return [
        'present' => (int) ($row['present_count'] ?? 0),
        'late' => (int) ($row['late_count'] ?? 0),
        'absent' => (int) ($row['absent_count'] ?? 0),
        'recorded' => (int) ($row['recorded_count'] ?? 0),
    ];
}

function ensurePayslipTable(mysqli $conn): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $conn->query("
        CREATE TABLE IF NOT EXISTS payslips (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            cutoff_start DATE NOT NULL,
            cutoff_end DATE NOT NULL,
            payout_date DATE NOT NULL,
            daily_rate DECIMAL(10,2) NOT NULL DEFAULT 755.00,
            paid_days INT NOT NULL DEFAULT 0,
            present_days INT NOT NULL DEFAULT 0,
            late_days INT NOT NULL DEFAULT 0,
            absent_days INT NOT NULL DEFAULT 0,
            unrecorded_days INT NOT NULL DEFAULT 0,
            allowance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            deduction DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            tax DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            gross_pay DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            total_deductions DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            net_pay DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            generated_by INT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_user_cutoff (user_id, cutoff_start, cutoff_end),
            KEY user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $checked = true;
}

function payslipFind(mysqli $conn, int $userId, string $cutoffStart, string $cutoffEnd): ?array
{
    ensurePayslipTable($conn);

    $stmt = $conn->prepare("
        SELECT *
        FROM payslips
        WHERE user_id = ?
        AND cutoff_start = ?
        AND cutoff_end = ?
        LIMIT 1
    ");
    $stmt->bind_param("iss", $userId, $cutoffStart, $cutoffEnd);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return $row ?: null;
}

function payslipSave(mysqli $conn, array $data): void
{
    ensurePayslipTable($conn);

    $stmt = $conn->prepare("
        INSERT INTO payslips (
            user_id, cutoff_start, cutoff_end, payout_date, daily_rate,
            paid_days, present_days, late_days, absent_days, unrecorded_days,
            allowance, deduction, tax, gross_pay, total_deductions, net_pay, generated_by
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            payout_date = VALUES(payout_date),
            daily_rate = VALUES(daily_rate),
            paid_days = VALUES(paid_days),
            present_days = VALUES(present_days),
            late_days = VALUES(late_days),
            absent_days = VALUES(absent_days),
            unrecorded_days = VALUES(unrecorded_days),
            allowance = VALUES(allowance),
            deduction = VALUES(deduction),
            tax = VALUES(tax),
            gross_pay = VALUES(gross_pay),
            total_deductions = VALUES(total_deductions),
            net_pay = VALUES(net_pay),
            generated_by = VALUES(generated_by)
    ");

    $userId = (int) $data['user_id'];
    $cutoffStart = (string) $data['cutoff_start'];
    $cutoffEnd = (string) $data['cutoff_end'];
    $payoutDate = (string) $data['payout_date'];
    $dailyRate = (float) $data['daily_rate'];
    $paidDays = (int) $data['paid_days'];
    $presentDays = (int) $data['present_days'];
    $lateDays = (int) $data['late_days'];
    $absentDays = (int) $data['absent_days'];
    $unrecordedDays = (int) $data['unrecorded_days'];
    $allowance = (float) $data['allowance'];
    $deduction = (float) $data['deduction'];
    $tax = (float) $data['tax'];
    $grossPay = (float) $data['gross_pay'];
    $totalDeductions = (float) $data['total_deductions'];
    $netPay = (float) $data['net_pay'];
    $generatedBy = (int) $data['generated_by'];

    $stmt->bind_param(
        "isssdiiiiiddddddi",
        $userId,
        $cutoffStart,
        $cutoffEnd,
        $payoutDate,
        $dailyRate,
        $paidDays,
        $presentDays,
        $lateDays,
        $absentDays,
        $unrecordedDays,
        $allowance,
        $deduction,
        $tax,
        $grossPay,
        $totalDeductions,
        $netPay,
        $generatedBy
    );
    $stmt->execute();
}

function payslipApplySavedValues(array $savedPayslip, string &$payoutDate, float &$dailyRate, int &$paidDays, array &$attendance, int &$unrecordedDays, float &$allowance, float &$deduction, float &$tax, float &$grossPay, float &$totalDeductions, float &$netPay): void
{
    $payoutDate = $savedPayslip['payout_date'];
    $dailyRate = (float) $savedPayslip['daily_rate'];
    $paidDays = (int) $savedPayslip['paid_days'];
    $attendance = [
        'present' => (int) $savedPayslip['present_days'],
        'late' => (int) $savedPayslip['late_days'],
        'absent' => (int) $savedPayslip['absent_days'],
        'recorded' => (int) $savedPayslip['present_days'] + (int) $savedPayslip['late_days'] + (int) $savedPayslip['absent_days'],
    ];
    $unrecordedDays = (int) $savedPayslip['unrecorded_days'];
    $allowance = (float) $savedPayslip['allowance'];
    $deduction = (float) $savedPayslip['deduction'];
    $tax = (float) $savedPayslip['tax'];
    $grossPay = (float) $savedPayslip['gross_pay'];
    $totalDeductions = (float) $savedPayslip['total_deductions'];
    $netPay = (float) $savedPayslip['net_pay'];
}

function renderPayslipPage(mysqli $conn, string $role): void
{
    ensureAttendanceTable($conn);ensureUserShiftColumn($conn);ensurePayslipTable($conn);ensurePayrollProfiles($conn);ensureActivitySchema($conn);
    [$defaultStart,$defaultEnd]=payslipDefaultCutoff();[$defaultStart,$defaultEnd]=payrollCutoff($defaultStart);
    $canGenerate=$role==='hr';$currentUserId=(int)$_SESSION['user_id'];$input=$_SERVER['REQUEST_METHOD']==='POST'?$_POST:$_GET;
    $_SESSION['payroll_csrf']??=bin2hex(random_bytes(32));$error='';$notice='';$dailyRate=695.00;
    $employeeId=$canGenerate?(int)($input['employee_id']??0):$currentUserId;
    $cutoffStart=$defaultStart;$cutoffEnd=$defaultEnd;
    try {$anchor=($input['cutoff_anchor']??'start')==='end'?'end':'start';$selectedDate=$input[$anchor==='end'?'cutoff_end':'cutoff_start']??$defaultStart;if(!is_string($selectedDate))throw new InvalidArgumentException('Select a valid payroll period.');$selectedDate=payrollDate($selectedDate);if($canGenerate&&!in_array((int)substr($selectedDate,8,2),$anchor==='end'?[15,30]:[1,16],true))throw new InvalidArgumentException('Choose the fixed 1–15 or 16–30 payroll period.');[$cutoffStart,$cutoffEnd]=payrollCutoff($selectedDate,$anchor);}catch(InvalidArgumentException $e){$error=$e->getMessage();$cutoffStart=$defaultStart;$cutoffEnd=$defaultEnd;}
    $payoutDate=$cutoffEnd;
    $deduction=0.0;$tax=0.0;
    foreach(['deduction','tax'] as $key){$raw=$input[$key]??'0';if(!is_scalar($raw)||!preg_match('/\A\d{1,8}(?:\.\d{1,2})?\z/',(string)$raw))$error='Enter valid non-negative deductions and tax amounts.';else $$key=$canGenerate?(float)$raw:0.0;}
    if($_SERVER['REQUEST_METHOD']==='POST'&&$canGenerate){
        if(!is_string($_POST['csrf_token']??null)||!hash_equals($_SESSION['payroll_csrf'],$_POST['csrf_token'])){http_response_code(400);$error='Reload the payroll page and try again.';}
        if(!$error&&($_POST['action']??'generate')==='save_allowance'){
            try{savePayrollProfile($conn,$currentUserId,$employeeId,$_POST);$notice='Allowance settings saved. They apply automatically to newly generated payroll; saved payslips are unchanged.';}catch(InvalidArgumentException $e){$error=$e->getMessage();}
        }
    }
    $profile=payrollProfile($conn,$employeeId);$allowance=$canGenerate?payrollAllowance($profile,$cutoffEnd):0.0;
    $employee=null;
    if($employeeId>0){$q=$conn->prepare("SELECT u.id,u.employee_id,u.full_name,u.role,d.name AS department_name FROM users u LEFT JOIN departments d ON d.id=u.department_id WHERE u.id=?".($canGenerate?" AND u.status='active'":''));$q->bind_param('i',$employeeId);$q->execute();$employee=$q->get_result()->fetch_assoc();}
    $history=[];$selectedSaved=null;
    if(!$canGenerate){
        $q=$conn->prepare('SELECT * FROM payslips WHERE user_id=? ORDER BY cutoff_end DESC,id DESC');$q->bind_param('i',$currentUserId);$q->execute();$history=$q->get_result()->fetch_all(MYSQLI_ASSOC);
        if(isset($_GET['payslip_id']))foreach($history as $slip)if((int)$slip['id']===(int)$_GET['payslip_id'])$selectedSaved=$slip;
        if(isset($_GET['payslip_id'])&&!$selectedSaved){http_response_code(404);$error='Payslip not found for your account.';}
        if($selectedSaved){$cutoffStart=$selectedSaved['cutoff_start'];$cutoffEnd=$selectedSaved['cutoff_end'];}
    }
    $staffSearch=is_string($_GET['q']??null)?mb_substr(trim($_GET['q']),0,160):'';
    $staffFilter=in_array($_GET['allowance_filter']??'all',['all','eligible','with_allowance'],true)?($_GET['allowance_filter']??'all'):'all';
    $staffSort=($_GET['sort']??'name')==='tenured'?'tenured':'name';$staff=[];
    if($canGenerate){
        $pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$staffSearch).'%';
        $sql="SELECT u.id,u.full_name,u.employee_id,u.role,d.name AS department_name,COALESCE(p.is_tenured,0) AS is_tenured,COALESCE(p.allowance,0) AS allowance,p.effective_from FROM users u LEFT JOIN departments d ON d.id=u.department_id LEFT JOIN payroll_profiles p ON p.user_id=u.id WHERE u.status='active' AND u.role IN ('employee','hr','admin') AND CONCAT_WS(' ',u.full_name,u.employee_id,u.role,d.name,IF(u.role='hr','Human Resource','')) LIKE ? ESCAPE '!'";
        if($staffFilter!=='all')$sql.=' AND p.is_tenured=1';if($staffFilter==='with_allowance')$sql.=' AND p.allowance>0';
        $sql.=$staffSort==='tenured'?' ORDER BY is_tenured DESC,allowance DESC,u.full_name':' ORDER BY u.full_name';
        $q=$conn->prepare($sql);$q->bind_param('s',$pattern);$q->execute();$staff=$q->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $attendance = $employee
        ? payslipAttendanceSummary($conn, $employeeId, $cutoffStart, $cutoffEnd)
        : ['present' => 0, 'late' => 0, 'absent' => 0, 'recorded' => 0];

    $workdays = payslipCountWorkdays($conn, $cutoffStart, $cutoffEnd);
    $paidDays = $attendance['present'] + $attendance['late'];
    $unrecordedDays = max(0, $workdays - $attendance['recorded']);

    $standardPagibig = 0.02;
    $standardPhilhealth = 0.015;
    $standardSss = 0.045;
    $standardDeductionPercent = $standardPagibig + $standardPhilhealth + $standardSss;
    $requiredDeductions = ($paidDays * $dailyRate) * $standardDeductionPercent;
    $overtimeHours = $attendance['late'] * 2;
    $overtimePay = ($overtimeHours / 2) * 100;
    $grossPay = ($paidDays * $dailyRate) + $overtimePay + $allowance;
    $totalDeductions = $deduction + $tax + $requiredDeductions;
    $netPay = max(0, $grossPay - $totalDeductions);
    if(max($grossPay,$totalDeductions,$netPay)>99999999.99)$error='Payroll totals exceed the supported amount. Reduce the allowance or deductions.';
    $savedPayslip = $selectedSaved ?? ($employee ? payslipFind($conn, $employeeId, $cutoffStart, $cutoffEnd) : null);

    $manilaToday=new DateTimeImmutable('today',new DateTimeZone('Asia/Manila'));
    $periodClosed=new DateTimeImmutable($cutoffEnd,new DateTimeZone('Asia/Manila'))<=$manilaToday;
    if($canGenerate&&$_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'generate')==='generate'&&!$periodClosed)$error='This payroll period is still open. Payslips can be generated after the cut-off ends.';
    if ($canGenerate && $_SERVER['REQUEST_METHOD'] === 'POST' && $employee && !$error && ($_POST['action']??'generate')==='generate') {
        $conn->begin_transaction();
        try {
            $lock=$conn->prepare('SELECT id FROM users WHERE id=? FOR UPDATE');$lock->bind_param('i',$employeeId);$lock->execute();$lock->get_result();
            $isNewPayslip=payslipFind($conn,$employeeId,$cutoffStart,$cutoffEnd)===null;
        payslipSave($conn, [
            'user_id' => $employeeId,
            'cutoff_start' => $cutoffStart,
            'cutoff_end' => $cutoffEnd,
            'payout_date' => $payoutDate,
            'daily_rate' => $dailyRate,
            'paid_days' => $paidDays,
            'present_days' => $attendance['present'],
            'late_days' => $attendance['late'],
            'absent_days' => $attendance['absent'],
            'unrecorded_days' => $unrecordedDays,
            'allowance' => $allowance,
            'deduction' => $deduction,
            'tax' => $tax,
            'gross_pay' => $grossPay,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'generated_by' => $currentUserId,
        ]);
        $savedPayslip = payslipFind($conn, $employeeId, $cutoffStart, $cutoffEnd);
            if($isNewPayslip){$message='A payslip is ready for '.$cutoffStart.' to '.$cutoffEnd.'.';$notify=$conn->prepare("INSERT INTO notifications(user_id,message,activity_type) VALUES(?,?,'payroll')");$notify->bind_param('is',$employeeId,$message);$notify->execute();}
            $conn->commit();
        } catch(Throwable $exception){$conn->rollback();throw $exception;}
        $notice = 'Payslip generated and saved.';
    }

    if ($savedPayslip && (!$canGenerate || $_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action']??'')==='save_allowance')) {
        payslipApplySavedValues(
            $savedPayslip,
            $payoutDate,
            $dailyRate,
            $paidDays,
            $attendance,
            $unrecordedDays,
            $allowance,
            $deduction,
            $tax,
            $grossPay,
            $totalDeductions,
            $netPay
        );
    }

    $activeRoute = $role . '.payroll';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payslip Generator</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= htmlspecialchars(app_url('hr/payroll_tools.css').'?v='.filemtime(__DIR__.'/../hr/payroll_tools.css')) ?>">
<style>
:root {
    --payroll-bg:#f3f4f6;
    --payroll-panel:#ffffff;
    --payroll-panel-soft:#f8fafc;
    --payroll-line:#dfe7f1;
    --payroll-text:#0f172a;
    --payroll-muted:#475569;
    --payroll-accent:#2563eb;
    --on-accent:#ffffff;
    --status-success:#15803d;
    --payroll-green:#15803d;
    --payroll-red:#be123c;
    --payroll-yellow:#b45309;
    --payroll-input-bg:#ffffff;
    --payroll-header-start:#eff6ff;
    --payroll-header-mid:#e2e8f0;
    --payroll-header-end:#f8fafc;
}
[data-theme="dark"] {
    --payroll-bg:#0b1220;
    --payroll-panel:#111827;
    --payroll-panel-soft:#172033;
    --payroll-line:#22304a;
    --payroll-text:#e5e7eb;
    --payroll-muted:#9ca3af;
    --payroll-accent:#60a5fa;
    --on-accent:#0b1220;
    --status-success:#22c55e;
    --payroll-green:#22c55e;
    --payroll-red:#fb7185;
    --payroll-yellow:#facc15;
    --payroll-input-bg:#0f172a;
    --payroll-header-start:#0b1220;
    --payroll-header-mid:#111827;
    --payroll-header-end:#172033;
}
* { box-sizing:border-box; }
body {
    margin:0;
    min-height:100vh;
    background:var(--payroll-bg);
    color:var(--payroll-text);
    font-family:Arial, sans-serif;
}
.main {
    padding:24px;
}
.topbar {
    display:flex;
    justify-content:space-between;
    gap:16px;
    align-items:flex-start;
    margin-bottom:18px;
}
.topbar h1 {
    margin:0 0 6px;
    font-size:28px;
}
.topbar p {
    margin:0;
    color:var(--payroll-muted);
    font-size:14px;
}
.generator {
    display:grid;
    grid-template-columns:minmax(280px, 380px) minmax(0, 1fr);
    gap:20px;
    align-items:start;
}
.form-panel,
.payslip {
    background:var(--payroll-panel);
    border:1px solid var(--payroll-line);
    border-radius:8px;
}
.form-panel {
    padding:18px;
}
.form-panel h2 {
    margin:0 0 16px;
    font-size:17px;
}
.field {
    margin-bottom:13px;
}
.field label {
    display:block;
    margin-bottom:7px;
    color:var(--payroll-muted);
    font-size:12px;
    font-weight:700;
    text-transform:uppercase;
}
.field input,
.field select {
    width:100%;
    border:1px solid var(--payroll-line);
    border-radius:8px;
    background:var(--payroll-input-bg);
    color:var(--payroll-text);
    padding:11px 12px;
    outline:none;
}
.field input:focus,
.field select:focus {
    border-color:var(--payroll-accent);
}
.actions {
    display:flex;
    gap:10px;
    margin-top:16px;
}
.btn {
    border:0;
    border-radius:8px;
    padding:11px 14px;
    cursor:pointer;
    font-weight:700;
    color: var(--on-accent);
    background:var(--payroll-accent);
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
}
.btn.secondary {
    background:var(--payroll-panel-soft);
    color:var(--payroll-text);
    border:1px solid var(--payroll-line);
}
.payslip-header {
    padding:24px;
    background:linear-gradient(135deg, var(--payroll-header-start) 0%, var(--payroll-header-mid) 55%, var(--payroll-header-end) 100%);
    border-bottom:1px solid var(--payroll-line);
}
.payslip-meta {
    display:flex;
    justify-content:space-between;
    gap:16px;
    align-items:flex-start;
}
.eyebrow {
    display:flex;
    gap:10px;
    align-items:center;
    color:var(--payroll-muted);
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:0;
}
.paid-badge {
    color:var(--status-success);
    background:rgba(34,197,94,.14);
    border:1px solid rgba(34,197,94,.35);
    border-radius:999px;
    padding:5px 9px;
}
.employee-name {
    margin:12px 0 6px;
    font-size:30px;
    line-height:1.05;
}
.subtitle {
    margin:0;
    color:var(--payroll-muted);
    font-size:14px;
}
.month-pill {
    background:var(--payroll-panel-soft);
    border:1px solid var(--payroll-line);
    border-radius:8px;
    min-width:88px;
    padding:10px;
    text-align:center;
    font-weight:800;
    color:var(--payroll-text);
}
.month-pill span {
    display:block;
    color:var(--payroll-muted);
    font-size:12px;
    margin-top:3px;
}
.summary-grid {
    display:grid;
    grid-template-columns:repeat(4, minmax(0, 1fr));
    gap:12px;
    margin-top:24px;
}
.metric {
    min-height:96px;
    background:var(--payroll-panel-soft);
    border:1px solid var(--payroll-line);
    border-radius:8px;
    padding:14px;
}
.metric label {
    display:block;
    color:var(--payroll-muted);
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
    margin-bottom:8px;
}
.metric strong {
    display:block;
    font-size:22px;
    line-height:1.15;
}
.metric small {
    color:var(--payroll-green);
    display:block;
    margin-top:7px;
    font-size:12px;
}
.metric.warning strong,
.metric.warning small { color:var(--payroll-yellow); }
.metric.danger strong,
.metric.danger small { color:var(--payroll-red); }
.payslip-body {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
    background:var(--payroll-panel-soft);
    color:var(--payroll-text);
    padding:24px;
}
.breakdown {
    background:var(--payroll-panel);
    border:1px solid var(--payroll-line);
    border-radius:8px;
    overflow:hidden;
}
.breakdown-head,
.line-item {
    display:grid;
    grid-template-columns:1fr auto;
    gap:12px;
    align-items:start;
}
.breakdown-head {
    background:var(--payroll-panel-soft);
    color:var(--payroll-muted);
    padding:12px 14px;
    font-size:12px;
    font-weight:800;
    text-transform:uppercase;
}
.line-item {
    padding:13px 14px;
    border-top:1px solid var(--payroll-line);
    font-size:13px;
    color:var(--payroll-text);
}
.line-item small {
    display:block;
    color:var(--payroll-muted);
    margin-top:3px;
}
.line-item strong {
    white-space:nowrap;
}
.line-item.danger {
    color:var(--payroll-red);
    background:rgba(190, 18, 60, 0.06);
}
.payslip-footer {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    color:var(--payroll-muted);
    background:var(--payroll-panel);
    border-top:1px solid var(--payroll-line);
    padding:14px 24px;
    font-size:12px;
}
.empty-note {
    color:var(--payroll-muted);
    padding:18px;
}
@media (max-width: 1100px) {
    .generator,
    .payslip-body {
        grid-template-columns:1fr;
    }
    .summary-grid {
        grid-template-columns:repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 640px) {
    .main { padding:16px; }
    .topbar,
    .payslip-meta,
    .payslip-footer {
        flex-direction:column;
    }
    .summary-grid {
        grid-template-columns:1fr;
    }
    .employee-name {
        font-size:24px;
    }
}
.payee-search { margin-bottom:16px; }
.payee-search-control { position:relative; }
.payee-search-control > i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--payroll-muted); pointer-events:none; }
.field .payee-search-control input { padding-left:36px; padding-right:42px; }
.payee-search-control button { position:absolute; right:3px; top:50%; transform:translateY(-50%); width:34px; min-height:34px; padding:0; background:transparent; color:var(--payroll-muted); border:0; }
.payee-search-control button[hidden] { display:none; }
.payee-search-help { color:var(--payroll-muted); font-size:12px; line-height:1.5; margin:8px 0 0; }
.main { max-width:1800px; margin-right:auto; }
.topbar { align-items:center; padding:4px 2px 12px; border-bottom:1px solid var(--payroll-line); }
.topbar h1 { font-size:clamp(24px,3vw,32px); letter-spacing:-.025em; }
.generator { gap:22px; }
.form-panel,.payslip { border-radius:12px; box-shadow:0 4px 18px rgba(15,23,42,.045); }
.form-panel { padding:22px; }
.form-panel h2 { font-size:18px; padding-bottom:12px; border-bottom:1px solid var(--payroll-line); }
.field input,.field select { min-height:44px; }
.actions { flex-wrap:wrap; }
.actions .btn { min-height:44px; }
.btn:focus-visible,.period-selector a:focus-visible { outline:3px solid color-mix(in srgb,var(--payroll-accent) 45%,transparent); outline-offset:2px; }
.btn:disabled { opacity:.58; cursor:not-allowed; filter:saturate(.55); }
.period-selector { display:flex; flex-wrap:wrap; align-items:center; gap:8px; padding:12px; border:1px solid var(--payroll-line); border-radius:10px; background:var(--payroll-panel-soft); }
.period-selector .btn { min-height:38px; padding:8px 12px; }
.period-selector strong { flex:1 1 180px; text-align:center; font-size:13px; }
.payslip { overflow:hidden; }
.payslip-header { padding:26px; }
.payslip-standard-title { margin-bottom:12px;color:var(--payroll-muted);font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase; }
.paid-badge { display:inline-flex; align-items:center; gap:6px; font-size:11px; }
.summary-grid { gap:12px; }
.metric { display:flex; flex-direction:column; justify-content:center; }
.metric strong { font-size:clamp(18px,2vw,23px); overflow-wrap:anywhere; }
.payslip-body { padding:22px; }
.payslip-footer { flex-wrap:wrap; }
/* Plain payroll statement layout. */
.payslip { border:1px solid var(--payroll-line)!important; border-radius:0!important; box-shadow:none!important; background:#fff!important; color:var(--payroll-text)!important; }
.payslip-header { background:#fff!important; border:0!important; padding:18px 22px 10px!important; }
.payslip-header .employee-name { color:var(--payroll-text)!important; font-size:20px!important; }
.payslip-header .summary-grid,.payslip-header .month-pill,.payslip-header .paid-badge { display:none!important; }
.payslip-header .eyebrow { display:none!important; }
.payslip-header .employee-name { margin:0 0 8px!important; }
.payslip-header .subtitle { font-size:13px!important; color:var(--payroll-muted)!important; }
.payslip-standard-title { margin:0 0 10px!important; font-size:17px!important; font-weight:700!important; letter-spacing:0!important; text-transform:none!important; }
.payslip-month { margin:0 0 4px;color:#258355;font-size:16px;font-weight:700; }
.payslip-body { display:block!important; padding:0 22px 12px!important; background:#fff!important; }
.payslip-body .breakdown { margin:0 0 10px;background:transparent!important; border:0!important; border-radius:0!important; }
.payslip-body .breakdown-head,.payslip-body .line-item { display:grid;grid-template-columns:minmax(0,1fr) minmax(95px,.45fr) minmax(100px,.55fr);gap:8px;align-items:center; }
.payslip-body .breakdown-head { background:transparent!important; border-bottom:1px solid var(--payroll-line)!important; }
.payslip-body .line-item { background:transparent!important; border:0!important; padding:8px!important; }
.payslip-body .line-item>div { grid-column:1/3; }
.payslip-body .line-item strong { grid-column:3;text-align:right;font-weight:500!important; }
.payslip-body .line-item small { display:block;color:var(--payroll-muted)!important; }
.payslip-totals { margin:0 22px 14px; border-top:1px solid var(--payroll-line); }
.payslip-totals .line-item { display:grid; grid-template-columns:1fr auto; padding:9px 8px!important; border-bottom:1px solid var(--payroll-line)!important; font-weight:700; }
.payslip-totals .line-item strong { font-size:14px; }
.payslip-totals .net-total { font-size:16px; }
.payslip-footer { border-top:1px solid var(--payroll-line)!important; background:transparent!important; }
.payslip .metric { border-radius:0!important; box-shadow:none!important; }
@media(max-width:760px) {
    .main { padding:16px !important; }
    .form-panel { padding:16px; }
    .payslip-header,.payslip-body { padding:16px; }
    .payslip-meta { align-items:flex-start; }
    .month-pill { min-width:72px; }
    .summary-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .payslip-body { grid-template-columns:1fr; }
}
@media(max-width:440px) {
    .summary-grid { grid-template-columns:1fr; }
    .actions .btn { flex:1 1 100%; }
    .period-selector strong { order:-1; flex-basis:100%; }
    .employee-name { overflow-wrap:anywhere; }
}
@page { size: 6.5in 8.5in; margin: 0.35in; }
@media print {
    /* Print only the payroll slip, regardless of shared navigation styles. */
    html body > :not(.main),
    html body .main > :not(.generator):not(#payroll-preview),
    html body .generator > :not(.payslip) {
        display:none !important;
    }
    html body {
        display:block !important;
        min-height:0 !important;
        background:#fff !important;
        color:#0f172a !important;
        --payroll-panel:#fff;
        --payroll-panel-soft:#f8fafc;
        --payroll-line:#dfe7f1;
        --payroll-text:#0f172a;
        --payroll-muted:#475569;
        --payroll-accent:#2563eb;
        --payroll-green:#15803d;
        --payroll-red:#be123c;
        --payroll-yellow:#b45309;
        --payroll-header-start:#eff6ff;
        --payroll-header-mid:#e2e8f0;
        --payroll-header-end:#f8fafc;
    }
    html body .main {
        margin:0 !important;
        width:100% !important;
        padding:0 !important;
    }
    html body .generator { display:block !important; }
    html body .payslip {
        width:100% !important;
        max-width:none !important;
        border:1px solid #111827 !important;
        border-radius:0;
        box-shadow:none;
        background:#fff !important;
        color:#111 !important;
        break-inside:avoid;
    }
    .payslip .payslip-header { padding:9px 12px 5px !important; background:#fff !important; }
    .payslip .payslip-standard-title { margin-bottom:5px;padding-bottom:4px;border-bottom:1px solid #111;font-size:10px;letter-spacing:0;color:#111;text-align:center; }
    .payslip .employee-name { margin:0 0 4px !important; color:#111 !important;font-size:15px !important; }
    .payslip .subtitle { font-size:9px !important; }
    .payslip .payslip-body { display:block !important; padding:0 12px 6px !important; background:#fff !important; }
    .payslip .breakdown { margin-bottom:7px;border:0 !important; border-radius:0 !important; }
    .payslip .breakdown-head { padding:4px 7px !important; border-bottom:1px solid #111 !important;color:#111 !important; font-size:9px !important; }
    .payslip .line-item { padding:4px 8px !important; font-size:9px !important; line-height:1.2; break-inside:avoid; }
    .payslip .line-item.danger { color:#111 !important;background:#fff !important; }
    .payslip .line-item small { display:none !important; }
    .payslip .payslip-totals { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:0 12px; margin:0 12px 6px !important; border-top:1px solid #cbd5e1; }
    .payslip .payslip-totals .line-item { padding:5px 8px !important; font-size:9px !important; }
    .payslip .payslip-totals .line-item strong { font-size:10px !important; }
    .payslip .payslip-totals .net-total { grid-column:1 / -1; font-size:12px !important; }
    .payslip .payslip-totals .net-total strong { font-size:13px !important; }
    .payslip .payslip-footer { padding:6px 12px !important; border-top:1px solid #111 !important;color:#111 !important;font-size:8px !important; }
    .payslip .summary-grid { display:none !important; }
    .payslip .payslip-meta, .payslip .payslip-footer { flex-direction:row; }
}

</style>
</head>
<body>
<?php renderSidebar($role, $activeRoute); ?>

<main class="main">
    <div class="topbar">
        <div>
            <h1><?= $canGenerate ? 'Payslip Generator' : 'My Payroll'; ?></h1>
            <p><?= $canGenerate ? 'Generate and save cut-off payslips using a fixed daily salary of ' . money($dailyRate) . '.' : 'View payroll slips generated by HR for your account.'; ?></p>
        </div>
        <?php if (!$canGenerate && $employee && $savedPayslip): ?>
            <button class="btn secondary" type="button" onclick="window.print()">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </button>
        <?php endif; ?>
    </div>

    <?php if(!$canGenerate || !$employee): ?>
        <?php if($notice): ?><div class="payroll-feedback" role="status"><?= htmlspecialchars($notice) ?></div><?php endif ?>
        <?php if($error): ?><p class="payroll-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif ?>
    <?php endif ?>
    <?php if($canGenerate): require __DIR__.'/payroll_directory.php'; else: require __DIR__.'/payroll_history.php'; endif; ?>
    <?php if($canGenerate && $employee): ?>
    <dialog id="payroll-preview" aria-labelledby="payroll-preview-title" data-employee="<?= $employeeId ?>">
      <header class="payroll-preview-header"><div><h2 id="payroll-preview-title">Payroll preview</h2><p><?= htmlspecialchars($employee['full_name']) ?> · <?= htmlspecialchars($employee['employee_id']??'') ?></p></div><div class="payroll-preview-actions"><button type="button" class="btn secondary" onclick="window.print()"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Print / Save PDF</button><button type="button" data-payroll-close aria-label="Close payroll preview" autofocus><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></div></header>
      <div class="payroll-preview-body">
        <?php if($notice): ?><p class="payroll-feedback" role="status"><?= htmlspecialchars($notice) ?></p><?php endif ?>
        <?php if($error): ?><p class="payroll-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif ?>
        <details class="payroll-allowance"><summary><i class="fa-solid fa-sliders" aria-hidden="true"></i> Allowance settings</summary><?php require __DIR__.'/payroll_allowance_settings.php'; ?></details>
    <?php endif ?>
    <?php if(!$canGenerate || $employee): ?>
    <div class="generator">
        <section class="form-panel">
            <h2><?= $canGenerate ? 'Generate Payslip' : 'Payroll Cut-off'; ?></h2>
            <form method="<?= $canGenerate ? 'POST' : 'GET'; ?>">
                <?php if($canGenerate): ?><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['payroll_csrf']) ?>"><input type="hidden" name="action" value="generate"><input type="hidden" name="employee_id" value="<?= $employeeId ?>"><input type="hidden" name="cutoff_start" value="<?= htmlspecialchars($cutoffStart) ?>"><input type="hidden" name="cutoff_end" value="<?= htmlspecialchars($cutoffEnd) ?>"><input type="hidden" name="cutoff_anchor" value="start"><p class="payroll-person"><?= $employee?htmlspecialchars($employee['full_name']).' · '.htmlspecialchars($employee['employee_id']??''):'Open a staff payroll from the list above.' ?></p><?php endif ?>
                <div class="field"><label>Fixed payroll period</label><div class="period-selector"><a class="btn secondary" href="<?= htmlspecialchars(route($activeRoute).'?employee_id='.$employeeId.'&cutoff_start='.date('Y-m-01',strtotime($cutoffStart))) ?>">1–15</a><a class="btn secondary" href="<?= htmlspecialchars(route($activeRoute).'?employee_id='.$employeeId.'&cutoff_start='.date('Y-m-16',strtotime($cutoffStart))) ?>">16–30</a><strong><?= date('M Y',strtotime($cutoffStart)) ?> · <?= date('M d',strtotime($cutoffStart)) ?>–<?= date('d, Y',strtotime($cutoffEnd)) ?></strong><a class="btn secondary" aria-label="Previous month" href="<?= htmlspecialchars(route($activeRoute).'?employee_id='.$employeeId.'&cutoff_start='.date('Y-m-01',strtotime($cutoffStart.' -1 month'))) ?>">‹</a><a class="btn secondary" aria-label="Next month" href="<?= htmlspecialchars(route($activeRoute).'?employee_id='.$employeeId.'&cutoff_start='.date('Y-m-01',strtotime($cutoffStart.' +1 month'))) ?>">›</a></div></div>
                <?php if ($canGenerate): ?>
                    <div class="field">
                        <label for="allowance">Allowances</label>
                        <input id="allowance" value="<?= htmlspecialchars(money($allowance)); ?>" readonly><small>Automatically applied from HR allowance settings for this cutoff.</small>
                    </div>
                    <div class="field">
                        <label for="deduction">Other Deductions</label>
                        <input id="deduction" type="number" name="deduction" min="0" step="0.01" value="<?= htmlspecialchars((string) $deduction); ?>">
                    </div>
                    <div class="field">
                        <label for="tax">Withholding Tax</label>
                        <input id="tax" type="number" name="tax" min="0" step="0.01" value="<?= htmlspecialchars((string) $tax); ?>">
                    </div>
                    <div class="field">
                        <label>Standard Deductions</label>
                        <div style="color:var(--payroll-muted); font-size:12px; line-height:1.7; background:var(--payroll-panel-soft); border:1px solid var(--payroll-line); border-radius:8px; padding:10px 12px;">
                            Pag-IBIG 2.0% · PhilHealth 1.5% · SSS 4.5%<br>
                            Total: <?= number_format($standardDeductionPercent * 100, 1); ?>%
                        </div>
                    </div>
                <?php endif; ?>
                <div class="actions">
                    <button class="btn" type="submit" <?= $canGenerate&&(!$employee||!$periodClosed)?'disabled':'' ?>><i class="fa-solid <?= $canGenerate ? 'fa-wand-magic-sparkles' : 'fa-magnifying-glass'; ?>"></i> <?= $canGenerate?($periodClosed?'Generate & Save':'Available after cut-off'):'View'; ?></button>
                    <a class="btn secondary" href="<?= htmlspecialchars(route($activeRoute)); ?>">Reset</a>
                </div>
            </form>
        </section>

        <section class="payslip">
            <?php if (!$employee): ?>
                <div class="empty-note">Select an active user to generate a payslip.</div>
            <?php elseif (!$canGenerate && !$savedPayslip): ?>
                <div class="empty-note">No payroll slip has been generated by HR for this cut-off.</div>
            <?php else: ?>
                <div class="payslip-header">
                    <div class="payslip-standard-title">Payslip</div>
                    <div class="payslip-meta">
                        <div>
                            <div class="eyebrow">
                                <span>Payslip</span>
                                <span class="paid-badge"><i class="fa-solid fa-circle-check"></i> <?= $savedPayslip?'Generated':'Preview' ?></span>
                            </div>
                            <h2 class="employee-name"><?= htmlspecialchars($employee['full_name']); ?></h2>
                            <h3 class="payslip-month"><?= date('F Y', strtotime($cutoffEnd)); ?></h3>
                            <p class="subtitle">
                                Payroll Period: <?= date('M d, Y', strtotime($cutoffStart)); ?> - <?= date('M d, Y', strtotime($cutoffEnd)); ?>
                            </p>
                        </div>
                        <div class="month-pill">
                            <?= date('M', strtotime($cutoffEnd)); ?>
                            <span><?= date('Y', strtotime($cutoffEnd)); ?></span>
                        </div>
                    </div>

                    <div class="summary-grid">
                        <div class="metric">
                            <label>Gross Earnings</label>
                            <strong><?= money($grossPay); ?></strong>
                            <small><?= $paidDays; ?> paid day<?= $paidDays === 1 ? '' : 's'; ?> at <?= money($dailyRate); ?> + Overtime</small>
                        </div>
                        <div class="metric warning">
                            <label>Standard Deductions</label>
                            <strong>- <?= money($requiredDeductions); ?></strong>
                            <small>Pag-IBIG 2% • PhilHealth 1.5% • SSS 4.5%</small>
                        </div>
                        <div class="metric danger">
                            <label>Withholding Tax</label>
                            <strong>- <?= money($tax); ?></strong>
                            <small>Entered tax amount</small>
                        </div>
                        <div class="metric">
                            <label>Net Take-home Pay</label>
                            <strong><?= money($netPay); ?></strong>
                            <small><?= $totalDeductions > 0 ? number_format(($netPay / max($grossPay, 1)) * 100, 1) . '% after deductions' : 'No deductions applied'; ?></small>
                        </div>
                    </div>
                </div>

                <div class="payslip-body">
                    <div class="breakdown">
                        <div class="breakdown-head">
                            <span>Earnings</span>
                            <span>Qty / Details</span>
                            <span>Amount</span>
                        </div>
                        <div class="line-item">
                            <div>
                                Basic Daily Salary
                                <small><?= $paidDays; ?> paid day<?= $paidDays === 1 ? '' : 's'; ?> x <?= money($dailyRate); ?></small>
                            </div>
                            <strong><?= money($paidDays * $dailyRate); ?></strong>
                        </div>
                        <div class="line-item">
                            <div>
                                Overtime Pay
                                <small><?= ($overtimeHours / 2); ?> overtime cycle(s) at PHP 100 per 2 hours</small>
                            </div>
                            <strong><?= money($overtimePay); ?></strong>
                        </div>
                        <div class="line-item">
                            <div>
                                Allowances
                                <small>Additional cut-off allowance</small>
                            </div>
                            <strong><?= money($allowance); ?></strong>
                        </div>
                        <div class="line-item">
                            <div>
                                Regular Present Days
                                <small><?= $attendance['present']; ?> present record<?= $attendance['present'] === 1 ? '' : 's'; ?></small>
                            </div>
                            <strong><?= $attendance['present']; ?></strong>
                        </div>
                        <div class="line-item">
                            <div>
                                Late But Paid Days
                                <small><?= $attendance['late']; ?> late record<?= $attendance['late'] === 1 ? '' : 's'; ?></small>
                            </div>
                            <strong><?= $attendance['late']; ?></strong>
                        </div>
                    </div>

                    <div class="breakdown">
                        <div class="breakdown-head">
                            <span>Deductions</span>
                            <span>Qty / Details</span>
                            <span>Amount</span>
                        </div>
                        <div class="line-item danger">
                            <div>
                                Absences
                                <small><?= $attendance['absent']; ?> absent record<?= $attendance['absent'] === 1 ? '' : 's'; ?></small>
                            </div>
                            <strong><?= money($attendance['absent'] * $dailyRate); ?></strong>
                        </div>
                        <div class="line-item danger">
                            <div>
                                Unrecorded Workdays
                                <small><?= $unrecordedDays; ?> workday<?= $unrecordedDays === 1 ? '' : 's'; ?> without attendance record</small>
                            </div>
                            <strong><?= money($unrecordedDays * $dailyRate); ?></strong>
                        </div>
                        <div class="line-item danger">
                            <div>
                                Pag-IBIG (2%)
                                <small>Standard statutory contribution</small>
                            </div>
                            <strong><?= money(($paidDays * $dailyRate) * 0.02); ?></strong>
                        </div>
                        <div class="line-item danger">
                            <div>
                                PhilHealth (1.5%)
                                <small>Standard statutory contribution</small>
                            </div>
                            <strong><?= money(($paidDays * $dailyRate) * 0.015); ?></strong>
                        </div>
                        <div class="line-item danger">
                            <div>
                                SSS (4.5%)
                                <small>Standard statutory contribution</small>
                            </div>
                            <strong><?= money(($paidDays * $dailyRate) * 0.045); ?></strong>
                        </div>
                        <div class="line-item">
                            <div>
                                Other Deductions
                                <small>Loans, cash advances, or adjustments</small>
                            </div>
                            <strong><?= money($deduction); ?></strong>
                        </div>
                        <div class="line-item danger">
                            <div>
                                Withholding Tax
                                <small>Manual tax entry for this cut-off</small>
                            </div>
                            <strong><?= money($tax); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="payslip-totals">
                    <div class="line-item"><span>Total Earnings</span><strong><?= money($grossPay); ?></strong></div>
                    <div class="line-item"><span>Total Deductions</span><strong><?= money($totalDeductions); ?></strong></div>
                    <div class="line-item"><span>Gross Pay</span><strong><?= money($grossPay); ?></strong></div>
                    <div class="line-item"><span>Taxable Income</span><strong><?= money(max(0, $grossPay - $requiredDeductions)); ?></strong></div>
                    <div class="line-item net-total"><span>Net Pay</span><strong><?= money($netPay); ?></strong></div>
                </div>
                <div class="payslip-footer">
                    <span><i class="fa-solid fa-shield-halved"></i> Pagecom HRIS generated payroll summary</span>
                    <span>Payee ID: <?= htmlspecialchars($employee['employee_id'] ?? 'N/A'); ?></span>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <?php endif ?>
    <?php if($canGenerate && $employee): ?></div></dialog><?php endif ?>
</main>
<?php if ($canGenerate): ?><script src="<?= htmlspecialchars(app_url('hr/payroll_cutoff.js') . '?v=' . filemtime(__DIR__ . '/../hr/payroll_cutoff.js'), ENT_QUOTES, 'UTF-8'); ?>" defer></script><?php endif; ?>
<?php if($canGenerate && $employee): ?><script src="<?= htmlspecialchars(app_url('hr/payroll_modal.js').'?v='.filemtime(__DIR__.'/../hr/payroll_modal.js'),ENT_QUOTES,'UTF-8') ?>" defer></script><?php endif ?>
</body>
</html>
<?php
}
?>
