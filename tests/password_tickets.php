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
    require dirname(__DIR__) . '/includes/password_tickets.php';
    ensurePasswordTicketSchema($conn);
    $environment = getenv();
    $environment['HRMS_DB_NAME'] = $database; $environment['HRMS_BASE_URL'] = '/HRMS';
    $server = proc_open([PHP_BINARY,'-S','127.0.0.1:8091',dirname(__DIR__) . '/tests/features-router.php'], [0=>['pipe','r'],1=>['file',__DIR__.'/password-tickets-server.log','w'],2=>['file',__DIR__.'/password-tickets-server.log','a']], $pipes, dirname(__DIR__), $environment);
    if (!is_resource($server)) throw new RuntimeException('Unable to start test server');
    usleep(500000);
    [$code,$body] = requestFeature('guest','login.php');
    verify($code === 200 && str_contains($body,'Forgot password?'), 'Login exposes forgot password link');
    [$code,$body] = requestFeature('guest','forgot_password.php');
    preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $body, $token); $guestToken = $token[1];
    [$code] = requestFeature('guest','forgot_password.php',['csrf_token'=>'bad','email'=>'employee@feature.example']);
    verify($code === 400 && (int)$conn->query('SELECT COUNT(*) FROM password_reset_tickets')->fetch_row()[0] === 0, 'Invalid CSRF cannot create ticket');
    $form = ['csrf_token'=>$guestToken,'email'=>'employee@feature.example','employee_id'=>'TEST-3','message'=>'<script>alert(1)</script>'];
    [$code,$body] = requestFeature('guest','forgot_password.php',$form);
    verify($code === 200 && str_contains($body,'If the email matches an active account'), 'Public request receives generic success');
    verify((int)$conn->query('SELECT COUNT(*) FROM password_reset_tickets')->fetch_row()[0] === 1, 'Known account request creates ticket');
    [$code] = requestFeature('guest','forgot_password.php',$form);
    verify((int)$conn->query('SELECT COUNT(*) FROM password_reset_tickets')->fetch_row()[0] === 1, 'Repeat requests do not duplicate open tickets');
    $form['email'] = 'unknown@feature.example';
    [$code,$body] = requestFeature('guest','forgot_password.php',$form);
    verify($code === 200 && str_contains($body,'If the email matches an active account') && (int)$conn->query('SELECT COUNT(*) FROM password_reset_tickets')->fetch_row()[0] === 1, 'Unknown email receives same response without ticket');
    foreach (['hr','employee','admin'] as $role) {
        [$code,$body] = requestFeature($role,'login/login_process.php',['email'=>$role.'@feature.example','password'=>'TemporaryFeaturePassword!']);
        verify($code === 200 && str_contains($body,'sidebar'), "$role test login");
    }
    foreach (['guest','hr','employee'] as $role) {
        [$code,$body] = requestFeature($role,'admin/password_tickets.php');
        verify(!str_contains($body,'Ticket #'), "$role cannot view tickets");
        requestFeature($role,'admin/password_tickets.php',['ticket_id'=>1,'action'=>'resolved','password'=>'UnauthorizedPassword!','confirmation'=>'UnauthorizedPassword!','verified'=>'1']);
        verify(password_verify('TemporaryFeaturePassword!', $conn->query('SELECT password FROM users WHERE id=3')->fetch_row()[0]), "$role cannot reset passwords");
    }
    [$code,$body] = requestFeature('admin','admin/password_tickets.php');
    preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $body, $token);
    verify(str_contains($body,'&lt;script&gt;') && !str_contains($body,'<script>alert(1)</script>'), 'Request messages are escaped');
    $id = (int)$conn->query('SELECT id FROM password_reset_tickets LIMIT 1')->fetch_row()[0];
    $reset = ['csrf_token'=>$token[1],'ticket_id'=>$id,'action'=>'resolved','notes'=>'Verified staff identity','password'=>'NewVerifiedPassword!','confirmation'=>'NewVerifiedPassword!','verified'=>'1'];
    $bad = $reset; $bad['csrf_token'] = 'bad';
    [$code] = requestFeature('admin','admin/password_tickets.php',$bad);
    verify($code === 400 && password_verify('TemporaryFeaturePassword!', $conn->query('SELECT password FROM users WHERE id=3')->fetch_row()[0]), 'Invalid admin CSRF cannot reset password');
    foreach (['verification','short','mismatch'] as $invalid) {
        $bad = $reset;
        if ($invalid === 'verification') unset($bad['verified']);
        if ($invalid === 'short') $bad['password'] = $bad['confirmation'] = 'short';
        if ($invalid === 'mismatch') $bad['confirmation'] = 'AnotherPassword!';
        requestFeature('admin','admin/password_tickets.php',$bad);
        verify(password_verify('TemporaryFeaturePassword!', $conn->query('SELECT password FROM users WHERE id=3')->fetch_row()[0]), "Reject reset with $invalid failure");
    }
    $progress = $reset; $progress['action'] = 'in_progress';
    requestFeature('admin','admin/password_tickets.php',$progress);
    verify($conn->query("SELECT status FROM password_reset_tickets WHERE id=$id")->fetch_row()[0] === 'in_progress', 'Admin marks ticket in progress');
    requestFeature('admin','admin/password_tickets.php',$reset);
    verify(password_verify('NewVerifiedPassword!', $conn->query('SELECT password FROM users WHERE id=3')->fetch_row()[0]), 'Verified reset saves hashed new password');
    $ticket = $conn->query("SELECT * FROM password_reset_tickets WHERE id=$id")->fetch_assoc();
    verify($ticket['status'] === 'resolved' && (int)$ticket['handled_by'] === 1 && $ticket['admin_notes'] === 'Verified staff identity', 'Resolution records admin and notes');
    verify((int)$conn->query('SELECT COUNT(*) FROM login_logs WHERE user_id=3 AND logout_time IS NULL')->fetch_row()[0] === 0, 'Reset invalidates existing sessions');
    [$code,$body] = requestFeature('employee','employee/dashboard.php');
    verify(!str_contains($body,'class="sidebar"'), 'Previous employee session is signed out');
    $reset['password'] = $reset['confirmation'] = 'RepeatedResetPassword!';
    requestFeature('admin','admin/password_tickets.php',$reset);
    verify(password_verify('NewVerifiedPassword!', $conn->query('SELECT password FROM users WHERE id=3')->fetch_row()[0]), 'Closed ticket cannot reset password again');
    [$code,$body] = requestFeature('employee','login/login_process.php',['email'=>'employee@feature.example','password'=>'TemporaryFeaturePassword!']);
    verify(!str_contains($body,'class="sidebar"'), 'Old password no longer works');
    [$code,$body] = requestFeature('employee','login/login_process.php',['email'=>'employee@feature.example','password'=>'NewVerifiedPassword!']);
    verify(str_contains($body,'class="sidebar"'), 'New password logs in');
    submitPasswordTicket($conn,'hr@feature.example','TEST-2','Reject fixture');
    $id = (int)$conn->query('SELECT MAX(id) FROM password_reset_tickets')->fetch_row()[0];
    requestFeature('admin','admin/password_tickets.php',['csrf_token'=>$token[1],'ticket_id'=>$id,'action'=>'rejected','notes'=>'Unable to verify requester']);
    verify($conn->query("SELECT status FROM password_reset_tickets WHERE id=$id")->fetch_row()[0] === 'rejected' && password_verify('TemporaryFeaturePassword!', $conn->query('SELECT password FROM users WHERE id=2')->fetch_row()[0]), 'Rejection closes ticket without changing password');
    for ($i=0; $i<5; $i++) verify(allowPasswordTicketRequest($conn,'test-limit'), 'Rate limit permits attempt '.($i+1));
    verify(!allowPasswordTicketRequest($conn,'test-limit'), 'Rate limit blocks sixth attempt');
    echo "Completed $checks password ticket checks.\n";
} finally {
    if (is_resource($server)) { proc_terminate($server); proc_close($server); }
    foreach ($cookies as $file) if (is_file($file)) unlink($file);
    $conn->close();
    if (preg_match('/\Ahrms_feature_test_[a-f0-9]{10}\z/', $database)) $source->query("DROP DATABASE `$database`");
}