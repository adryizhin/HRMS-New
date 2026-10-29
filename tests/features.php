<?php
// Clone schema only. No production rows are copied or modified.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__) . '/config.php';
$source = $conn;
$database = 'hrms_feature_test_' . bin2hex(random_bytes(5));
$source->query("CREATE DATABASE `$database` CHARACTER SET utf8mb4");
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, $database);
$conn->set_charset('utf8mb4');
$server = null;
$cookies = [];
$checks = 0;
function verify(bool $condition, string $message): void {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
    echo "PASS $message\n";
}
function requestFeature(string $role, string $path, ?array $form = null): array {
    global $cookies;
    if (!isset($cookies[$role])) $cookies[$role] = tempnam(sys_get_temp_dir(), 'hrms-feature-cookie-');
    $curl = curl_init('http://127.0.0.1:8091/HRMS/' . $path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_COOKIEJAR => $cookies[$role], CURLOPT_COOKIEFILE => $cookies[$role], CURLOPT_TIMEOUT => 20]);
    if ($form !== null) { curl_setopt($curl, CURLOPT_POST, true); curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($form)); }
    $body = curl_exec($curl);
    if ($body === false) throw new RuntimeException(curl_error($curl));
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if (preg_match('/Fatal error|Parse error|Warning:|Deprecated:/', $body)) throw new RuntimeException('PHP error at ' . $path . ': ' . strip_tags($body));
    return [$code, $body];
}
try {
    $conn->query('SET FOREIGN_KEY_CHECKS = 0');
    $tables = $source->query('SHOW TABLES');
    while ($table = $tables->fetch_row()) {
        $name = $table[0];
        $sql = $source->query("SHOW CREATE TABLE `$name`")->fetch_row()[1];
        $conn->query($sql);
    }
    $conn->query('SET FOREIGN_KEY_CHECKS = 1');
    $password = password_hash('TemporaryFeaturePassword!', PASSWORD_DEFAULT);
    $insert = $conn->prepare("INSERT INTO users (id, full_name, employee_id, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'active', '2026-01-01')");
    $ids = ['admin' => 1, 'hr' => 2, 'employee' => 3];
    foreach ($ids as $role => $id) {
        $name = ucfirst($role) . ' Feature Person'; $employee = 'TEST-' . $id; $email = $role . '@feature.example';
        $insert->bind_param('isssss', $id, $name, $employee, $email, $password, $role); $insert->execute();
    }
    require dirname(__DIR__) . '/includes/leave.php';
    require dirname(__DIR__) . '/includes/reports_page.php';
    ensureAttendanceTable($conn); ensureTaskSchema($conn); ensureLeaveSchema($conn);
    verify((int) $conn->query('SELECT COUNT(*) FROM holidays WHERE YEAR(holiday_date) = 2026')->fetch_row()[0] === 20, '20 Philippine non-working holidays imported');
    verify((int) $conn->query("SELECT COUNT(*) FROM holidays WHERE holiday_date = '2026-02-25'")->fetch_row()[0] === 0, 'Special working day excluded');
    verify(leaveWorkingDates($conn, '2026-04-01', '2026-04-06') === ['2026-04-01','2026-04-06'], 'Leave excludes holidays and weekends');
    verify(reportPeriod('quarterly', 2026, 1, 4) === ['2026-10-01','2026-12-31'], 'Quarter boundaries');
    verify(reportPeriod('monthly', 2028, 2, 1) === ['2028-02-01','2028-02-29'], 'Leap-year month boundaries');
    verify(reportPeriod('yearly', 2026, 6, 2) === ['2026-01-01','2026-12-31'], 'Year boundaries');
    $key = str_repeat('a', 64);
    submitLeaveRequest($conn, 3, 2, 'Vacation Leave', '2026-10-12', '2026-10-14', 'Test leave', $key);
    verify(remainingLeaveDays($conn, 3, 2026) === 12, 'Successful request deducts working days');
    submitLeaveRequest($conn, 3, 2, 'Vacation Leave', '2026-10-12', '2026-10-14', 'Test leave', $key);
    verify(remainingLeaveDays($conn, 3, 2026) === 12, 'Duplicate request does not deduct twice');
    foreach ([['2026-10-13','2026-10-15'],['2026-10-01','2026-10-31'],['2026-10-15','2026-10-12'],['2026-02-30','2026-03-02']] as [$start, $end]) {
        $failed = false;
        try { submitLeaveRequest($conn, 3, 2, 'Sick Leave', $start, $end, 'Invalid test', bin2hex(random_bytes(32))); }
        catch (InvalidArgumentException $error) { $failed = true; }
        verify($failed && remainingLeaveDays($conn, 3, 2026) === 12, "Rejected invalid/overdrawn/overlapping dates $start to $end without deduction");
    }
    submitLeaveRequest($conn, 3, 2, 'Vacation Leave', '2026-12-29', '2027-01-04', 'Cross-year test', str_repeat('b',64));
    verify(remainingLeaveDays($conn, 3, 2026) === 11 && remainingLeaveDays($conn, 3, 2027) === 14, 'Cross-year requests charge each annual balance');
    $conn->query("INSERT INTO attendance (user_id, attendance_date, shift, login_time, status) VALUES (1,'2026-10-01','morning','2026-10-01 08:00:00','present'), (2,'2026-10-01','morning','2026-10-01 08:45:00','late'), (3,'2026-10-01','morning','2026-10-01 08:00:00','present'), (3,'2026-09-01','morning','2026-09-01 17:00:00','absent')");
    $conn->query("INSERT INTO tasks (employee_id, hr_id, title, description, deadline, submission_file, submitted_at, status, hr_score, created_at) VALUES (1,2,'Admin accomplishment','Test','2026-10-31','test.pdf','2026-10-02','reviewed',90,'2026-10-01'), (3,2,'Employee accomplishment','Test','2026-10-31','test.pdf','2026-10-02','reviewed',80,'2026-09-01')");
    $adminKpi = calculateEmployeeKpi($conn,1,'2026-10-01','2026-10-31');
    verify($adminKpi['stats']['assigned'] === 1 && $adminKpi['stats']['present'] === 1 && $adminKpi['overall'] > 0, 'Admin KPI includes attendance and evaluation');
    $employeeKpi = calculateEmployeeKpi($conn,3,'2026-10-01','2026-10-31');
    verify($employeeKpi['stats']['assigned'] === 0 && $employeeKpi['stats']['absent'] === 0, 'KPI excludes out-of-period assignments and attendance');
    $environment = getenv();
    $environment['HRMS_DB_NAME'] = $database; $environment['HRMS_BASE_URL'] = '/HRMS';
    $server = proc_open([PHP_BINARY,'-S','127.0.0.1:8091',dirname(__DIR__) . '/tests/features-router.php'], [0=>['pipe','r'],1=>['file',__DIR__.'/features-server.log','w'],2=>['file',__DIR__.'/features-server.log','a']], $pipes, dirname(__DIR__), $environment);
    if (!is_resource($server)) throw new RuntimeException('Unable to start local test server');
    usleep(500000);
    foreach ($ids as $role => $id) {
        [$code, $body] = requestFeature($role, 'login/login_process.php', ['email'=>$role.'@feature.example','password'=>'TemporaryFeaturePassword!']);
        verify($code === 200 && str_contains($body, 'sidebar'), "$role login");
        foreach (['monthly','quarterly','yearly'] as $period) {
            [$code, $body] = requestFeature($role, "$role/reports.php?period=$period&year=2026&month=10&quarter=4&chart=pie");
            verify($code === 200 && str_contains($body, 'Employee accomplishment'), "$role $period report renders accomplishments");
            if ($role === 'employee') verify(!str_contains($body,'Admin accomplishment'), 'Employee report does not expose admin data');
        }
        [$code, $body] = requestFeature($role, "$role/reports.php?year=2026&month=10&export=csv");
        verify($code === 200 && str_contains($body, 'Period start') && !str_contains($body, '<html'), "$role CSV export");
        verify(str_contains($body, 'Employee accomplishment') && str_contains($body, '12:00 AM'), "$role CSV includes accomplishments with AM/PM times");
        [$code, $body] = requestFeature($role, "$role/reports.php?year=2026&month=10&export=print");
        verify($code === 200 && !str_contains($body, 'class="sidebar"') && str_contains($body, '12:00 AM'), "$role print view renders report without navigation");
        [$code, $body] = requestFeature($role, "$role/attendance.php?search=Admin%20Feature");
        verify($code === 200, "$role attendance search renders");
        if ($role === 'employee') verify(str_contains($body,'No attendance records match'), 'Employee cannot search another staff member attendance');
        else verify(str_contains($body, '<td>Admin Feature Person</td>') && !str_contains($body, '<td>Employee Feature Person</td>'), "$role attendance filters by staff name");
    }
    $conn->query("INSERT INTO departments (name) VALUES ('Research 100% Team')");
    $searchDepartment=(int)$conn->insert_id;
    $conn->query('UPDATE users SET department_id='.$searchDepartment.' WHERE id=1');
    foreach (['Admin Feature'=>'Admin Feature Person','TEST-1'=>'Admin Feature Person','admin@feature.example'=>'Admin Feature Person','Human Resource'=>'Hr Feature Person','Research'=>'Admin Feature Person','100%'=>'Admin Feature Person'] as $searchTerm=>$expected) {
        [$code,$body]=requestFeature('hr','hr/201_files.php?q='.urlencode($searchTerm));
        preg_match('/class="employee-list">([\s\S]*?)<\/aside>/', $body, $list);
        verify($code===200 && str_contains($body,'1 staff record found') && str_contains($list[1]??'','<strong>'.$expected.'</strong>') && !str_contains($list[1]??'','<strong>Employee Feature Person</strong>'), '201 Files searches staff by '.$searchTerm);
    }
    [$code,$body]=requestFeature('hr','hr/201_files.php?employee_id=1&q=Admin+Feature');
    verify($code===200 && str_contains($body,'employee_id=1&amp;q=Admin+Feature') && str_contains($body,'<h1>Admin Feature Person</h1>'), '201 Files preserves search and selected staff record');
    [$code,$body]=requestFeature('hr','hr/201_files.php?q='.urlencode("' OR 1=1 -- <script>"));
    verify($code===200 && str_contains($body,'No staff records match your search.') && str_contains($body,'&lt;script&gt;'), '201 Files safely escapes search and shows no-match state');
    [$code,$body]=requestFeature('employee','hr/201_files.php?q=Admin');
    verify(!str_contains($body,'class="employee-list"'), 'Employee cannot use HR staff search');
    foreach (['admin/kpi.php','admin/evaluations.php','admin/documents.php','hr/documents.php','hr/evaluations.php','hr/kpi.php','hr/201_files.php?employee_id=1','hr/201_files.php?employee_id=2'] as $path) {
        $role = explode('/', $path)[0]; [$code, $body] = requestFeature($role, $path);
        verify($code === 200 && str_contains($body, 'sidebar'), "$path accessible");
    }
    foreach (['admin/kpi.php','admin/evaluations.php','admin/documents.php','hr/documents.php','hr/evaluations.php','hr/reports.php'] as $path) {
        [$code, $body] = requestFeature('employee', $path);
        verify(!str_contains($body, 'class="sidebar"'), "Employee blocked from $path");
    }
    [$code, $body] = requestFeature('hr', 'hr/hr_leave_requests.php');
    preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $body, $token);
    $id = (int) $conn->query("SELECT id FROM leave_requests WHERE request_key = '$key'")->fetch_row()[0];
    [$code] = requestFeature('hr','hr/hr_leave_requests.php',['csrf_token'=>$token[1],'id'=>$id,'status'=>'rejected','review_notes'=>'Please reschedule <safely>.','return_status'=>'rejected']);
    verify($code === 200 && remainingLeaveDays($conn,3,2026) === 14, 'HR rejection restores reserved days');
    [$code] = requestFeature('hr','hr/hr_leave_requests.php',['csrf_token'=>$token[1],'id'=>$id,'status'=>'approved']);
    verify($code === 200 && remainingLeaveDays($conn,3,2026) === 14 && (int) $conn->query('SELECT COUNT(*) FROM notifications')->fetch_row()[0] === 1, 'Repeated review cannot change a finalized request or duplicate notification');
    [$code, $body] = requestFeature('employee','employee/request_leave.php');
    preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $body, $token);
    preg_match('/name="request_key" value="([a-f0-9]+)"/', $body, $requestKey);
    [$code,$body] = requestFeature('employee','employee/request_leave.php',['csrf_token'=>$token[1],'request_key'=>$requestKey[1],'hr_id'=>2,'leave_type'=>'Sick Leave','start_date'=>'2026-10-20','end_date'=>'2026-10-21','reason'=>'HTTP leave test']);
    verify($code === 200 && str_contains($body,'Leave request sent successfully') && remainingLeaveDays($conn,3,2026) === 12, 'Leave form commits and displays updated balance');
    [$code] = requestFeature('employee','employee/request_leave.php',['csrf_token'=>'bad']);
    verify($code === 200 && remainingLeaveDays($conn,3,2026) === 12, 'Invalid form token does not deduct leave');
    $reviewed=$conn->query('SELECT reviewed_at,reviewed_by,review_notes FROM leave_requests WHERE id='.$id)->fetch_assoc();
    verify($reviewed['reviewed_at']!==null && (int)$reviewed['reviewed_by']===2 && $reviewed['review_notes']==='Please reschedule <safely>.','Leave decision stores reviewer, timestamp and notes');
    [$code,$body]=requestFeature('employee','employee/request_leave.php');
    verify(str_contains($body,'My leave requests') && str_contains($body,'Please reschedule &lt;safely&gt;.') && !str_contains($body,'<h3>My Payroll</h3>'),'Employee history shows safe review details without placeholder dashboard totals');
    preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$body,$csrf);
    preg_match('/name="request_key" value="([a-f0-9]+)"/',$body,$requestKey);
    $balanceBefore=remainingLeaveDays($conn,3,2027);
    $form=['csrf_token'=>$csrf[1],'request_key'=>$requestKey[1],'hr_id'=>2,'leave_type'=>'Vacation Leave','start_date'=>'2027-02-01','end_date'=>'2027-02-03','reason'=>'Detailed request <example>'];
    [$code,$body]=requestFeature('employee','employee/request_leave.php',$form+['action'=>'preview']);
    verify($code===200 && str_contains($body,'3 requested working day(s)') && remainingLeaveDays($conn,3,2027)===$balanceBefore,'Working-day preview does not submit or reserve leave');
    [$code,$body]=requestFeature('employee','employee/request_leave.php',array_replace($form,['end_date'=>'2027-01-30']));
    verify(str_contains($body,'Select valid dates in order') && str_contains($body,'Detailed request &lt;example&gt;') && str_contains($body,'value="2027-02-01"'),'Date validation preserves safely escaped form entries');
    [$code,$body]=requestFeature('employee','employee/request_leave.php',array_replace($form,['reason'=>str_repeat('x',2001)]));
    verify(str_contains($body,'2,000 characters') && remainingLeaveDays($conn,3,2027)===$balanceBefore,'Overlong reason is rejected without balance change');
    [$code,$body]=requestFeature('employee','employee/request_leave.php',array_replace($form,['hr_id'=>99999]));
    verify(str_contains($body,'Select an active HR reviewer.') && remainingLeaveDays($conn,3,2027)===$balanceBefore,'Unavailable reviewer cannot receive a leave request');
    [$code,$body]=requestFeature('employee','employee/request_leave.php',$form);
    verify(str_contains($body,'Leave request sent successfully') && str_contains($body,'Detailed request &lt;example&gt;') && remainingLeaveDays($conn,3,2027)===$balanceBefore-3,'Submitted request appears in history and reserves correct working days');
    $latest=(int)$conn->query("SELECT id FROM leave_requests WHERE request_key='".$requestKey[1]."'")->fetch_row()[0];
    $blocked=false;try{reviewLeaveRequest($conn,9999,$latest,'approved');}catch(InvalidArgumentException $e){$blocked=true;}
    verify($blocked && $conn->query('SELECT status FROM leave_requests WHERE id='.$latest)->fetch_row()[0]==='pending','Another HR reviewer cannot finalize the request');
    verify(reviewLeaveRequest($conn,2,$latest,'approved','Enjoy your leave.') && !reviewLeaveRequest($conn,2,$latest,'rejected','Overwrite attempt'),'Final decision cannot be changed or notify twice');
    verify(remainingLeaveDays($conn,3,2027)===$balanceBefore-3,'Approval keeps previously reserved days without double deduction');
    [$code,$body]=requestFeature('hr','hr/hr_leave_requests.php?status=approved');
    verify(str_contains($body,'3 working day(s)') && str_contains($body,'Enjoy your leave.') && str_contains($body,'TEST-3'),'HR sees working days, staff identity and saved review notes');
    submitLeaveRequest($conn,1,2,'Vacation Leave','2027-03-01','2027-03-02','Private admin leave fixture',str_repeat('c',64));
    [$code,$body]=requestFeature('employee','employee/request_leave.php');
    verify(!str_contains($body,'Private admin leave fixture'),'Employee request history excludes other staff');
    if(getenv('HRMS_LEAVE_BROWSER')==='1') {
        $browser=proc_open(['node',__DIR__.'/leave-browser.cjs'],[0=>['pipe','r'],1=>STDOUT,2=>STDERR],$bp,dirname(__DIR__),$environment);
        if(!is_resource($browser)||proc_close($browser)!==0)throw new RuntimeException('Leave browser checks failed');
    }
    if (getenv('HRMS_FEATURE_BROWSER') === '1') {
        $node = getenv('HRMS_TEST_NODE') ?: 'node';
        $browserCheck = proc_open([$node, __DIR__ . '/features-browser.cjs'], [0=>['pipe','r'],1=>STDOUT,2=>STDERR], $browserPipes, dirname(__DIR__), $environment);
        if (!is_resource($browserCheck) || proc_close($browserCheck) !== 0) throw new RuntimeException('Browser checks failed');
    }
    echo "Completed $checks feature checks against disposable database.\n";
} finally {
    if (is_resource($server)) { proc_terminate($server); proc_close($server); }
    foreach ($cookies as $file) if (is_file($file)) unlink($file);
    $conn->close();
    if (preg_match('/\Ahrms_feature_test_[a-f0-9]{10}\z/', $database)) $source->query("DROP DATABASE `$database`");
}
