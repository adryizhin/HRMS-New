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
    require dirname(__DIR__) . '/includes/session_activity.php';
    ensureLoginPresenceColumn($conn);
    $conn->query("INSERT INTO login_logs (user_id,username,role,session_token,login_time,last_ping,last_seen,logout_time) VALUES
      (1,'Live admin','admin','fresh',NOW(),NOW(),NOW(),NULL),
      (1,'Duplicate admin','admin','fresh-2',NOW(),NOW(),NOW(),NULL),
      (2,'Closed tab','hr','closed-tab',NOW(),NOW(),DATE_SUB(NOW(),INTERVAL 2 MINUTE),NULL),
      (2,'Legacy log','hr','legacy',NOW(),NOW(),NULL,NULL),
      (3,'Expired user','employee','expired',DATE_SUB(NOW(),INTERVAL 1 HOUR),DATE_SUB(NOW(),INTERVAL 16 MINUTE),NOW(),NULL),
      (2,'Logged out','hr','logout',NOW(),NOW(),NOW(),NOW()),
      (2,'Missing token','hr','',NOW(),NOW(),NOW(),NULL)");
    $online = getOnlineUsers($conn);
    verify(count($online) === 1 && (int)$online[0]['id'] === 1, 'Online list excludes stale, legacy, logged-out, tokenless and expired sessions, and deduplicates users');
    $expired = $conn->query("SELECT logout_time,logout_reason FROM login_logs WHERE session_token='expired'")->fetch_assoc();
    verify($expired['logout_time'] !== null && $expired['logout_reason'] === 'timeout', 'Expired sessions are marked timed out');
    verify(!refreshLoginSession($conn,3,'expired',false), 'Presence cannot revive an expired session');
    verify(!refreshLoginSession($conn,2,'logout',true), 'Activity cannot revive a logged-out session');
    verify(!refreshLoginSession($conn,2,'unknown',true) && !refreshLoginSession($conn,2,'',true), 'Unknown and empty tokens are rejected');
    verify(refreshLoginSession($conn,2,'closed-tab',false), 'A valid returning session can resume presence');
    $conn->query("UPDATE users SET status='inactive' WHERE id=2");
    verify(!refreshLoginSession($conn,2,'closed-tab',false) && count(getOnlineUsers($conn)) === 1, 'Inactive accounts cannot heartbeat or appear online');
    $conn->query("UPDATE users SET status='active' WHERE id=2");
    $conn->query('DELETE FROM login_logs');
    $environment = getenv(); $environment['HRMS_DB_NAME'] = $database; $environment['HRMS_BASE_URL'] = '/HRMS';
    $server = proc_open([PHP_BINARY,'-S','127.0.0.1:8091',dirname(__DIR__) . '/tests/features-router.php'], [0=>['pipe','r'],1=>['file',__DIR__.'/online-users-server.log','w'],2=>['file',__DIR__.'/online-users-server.log','a']], $pipes, dirname(__DIR__), $environment);
    if (!is_resource($server)) throw new RuntimeException('Unable to start test server');
    usleep(500000);
    [$code] = requestFeature('guest','keepalive.php', ['presence'=>'1']);
    verify($code === 401, 'Unauthenticated heartbeat rejected');
    foreach (['admin','hr','employee'] as $role) {
        [$code,$body] = requestFeature($role,'login/login_process.php',['email'=>$role.'@feature.example','password'=>'TemporaryFeaturePassword!']);
        verify($code === 200 && str_contains($body,'sidebar'), "$role login still works");
    }
    verify(count(getOnlineUsers($conn)) === 3, 'Each authenticated user appears online');
    [$code,$body] = requestFeature('admin','admin/online_users.php');
    verify($code === 200 && str_contains($body,'Employee Feature Person'), 'Admin online list endpoint returns current users');
    [$code,$body] = requestFeature('employee','admin/online_users.php');
    verify(!str_contains($body,'online-user-row'), 'Employee cannot read online list endpoint');
    [$code,$body] = requestFeature('hr','admin/online_users.php');
    verify(!str_contains($body,'online-user-row'), 'HR cannot read online list endpoint');
    $conn->query("UPDATE login_logs SET last_ping=DATE_SUB(NOW(),INTERVAL 10 MINUTE),last_seen=DATE_SUB(NOW(),INTERVAL 2 MINUTE) WHERE user_id=3");
    $before = $conn->query('SELECT last_ping FROM login_logs WHERE user_id=3')->fetch_row()[0];
    [$code] = requestFeature('employee','keepalive.php',['presence'=>'1']);
    $row = $conn->query('SELECT last_ping,last_seen > DATE_SUB(NOW(),INTERVAL 5 SECOND) AS fresh FROM login_logs WHERE user_id=3')->fetch_assoc();
    verify($code === 204 && $row['last_ping'] === $before && (int)$row['fresh'] === 1, 'Presence refreshes online status without extending idle timeout');
    [$code] = requestFeature('employee','keepalive.php',[]);
    $after = $conn->query('SELECT last_ping FROM login_logs WHERE user_id=3')->fetch_row()[0];
    verify($code === 204 && $after !== $before, 'Real activity renews idle timeout');
    $conn->query("UPDATE login_logs SET last_ping=DATE_SUB(NOW(),INTERVAL 10 MINUTE) WHERE user_id=1");
    $before = $conn->query('SELECT last_ping FROM login_logs WHERE user_id=1')->fetch_row()[0];
    requestFeature('admin','admin/online_users.php');
    verify($conn->query('SELECT last_ping FROM login_logs WHERE user_id=1')->fetch_row()[0] === $before, 'Dashboard background polling does not extend idle timeout');
    $conn->query("UPDATE login_logs SET last_ping=DATE_SUB(NOW(),INTERVAL 16 MINUTE) WHERE user_id=3");
    [$code] = requestFeature('employee','keepalive.php',[]);
    verify($code === 401, 'Expired cookie cannot revive session through activity heartbeat');
    [$code,$body] = requestFeature('employee','employee/dashboard.php');
    verify(!str_contains($body,'class="sidebar"'), 'Expired user is redirected to login');
    requestFeature('hr','logout.php');
    [$code] = requestFeature('hr','keepalive.php',['presence'=>'1']);
    verify($code === 401 && count(getOnlineUsers($conn)) === 1, 'Logout removes presence and rejects subsequent heartbeats');
    [$code,$body] = requestFeature('admin','admin/dashboard.php');
    verify($code === 200 && str_contains($body,'online-users-list') && str_contains($body,'online_users.js') && !str_contains($body,'<strong>Employee Feature Person</strong>'), 'Dashboard uses refreshed list and excludes signed-out user');
    $conn->query("INSERT INTO login_logs (user_id,username,role,session_token,login_time,last_ping,last_seen) VALUES (2,'Disconnected fixture','hr','disconnected',NOW(),NOW(),DATE_SUB(NOW(),INTERVAL 2 MINUTE))");
    [$code,$body] = requestFeature('admin','admin/login_logs.php');
    verify($code === 200 && str_contains($body,'Offline') && str_contains($body,'Session open'), 'Login logs distinguish an offline browser from an active online user');
    [$code,$body] = requestFeature('admin','admin/login_logs.php',['export_excel'=>'1']);
    verify($code === 200 && str_contains($body,'Offline') && !str_contains($body,'<html'), 'Export uses the same online status as login logs');
    $conn->query("INSERT INTO login_logs (user_id,username,role,session_token,login_time,last_ping,last_seen,logout_time,logout_reason) VALUES
      (2,'Timeout search fixture','hr','timeout-search','2026-01-02 09:00:00','2026-01-02 09:00:00','2026-01-02 09:00:00','2026-01-02 09:15:00','timeout'),
      (2,'Logout search fixture','hr','logout-search','2026-01-02 09:00:00','2026-01-02 09:00:00','2026-01-02 09:00:00','2026-01-02 09:10:00','logout'),
      (2,'Literal 100% fixture','hr','literal-search','2026-01-02 09:00:00','2026-01-02 09:00:00','2026-01-02 09:00:00','2026-01-02 09:10:00','logout')");
    foreach (['time out', 'timeout', 'timed out'] as $term) {
        [$code,$body] = requestFeature('admin','admin/login_logs.php?q=' . urlencode($term));
        verify($code === 200 && str_contains($body,'<td>Timeout search fixture</td>') && !str_contains($body,'<td>Disconnected fixture</td>') && !str_contains($body,'<td>Logout search fixture</td>'), "Search finds timed-out sessions using '$term'");
    }
    [$code,$body] = requestFeature('admin','admin/login_logs.php?status=timeout&q=Timeout+search+fixture');
    verify($code === 200 && str_contains($body,'1 session found') && str_contains($body,'<td>Timeout search fixture</td>'), 'Timeout filter combines with name search');
    [$code,$body] = requestFeature('admin','admin/login_logs.php?status=offline');
    verify($code === 200 && str_contains($body,'<td>Disconnected fixture</td>') && !str_contains($body,'<td>Timeout search fixture</td>'), 'Offline filter excludes timed-out sessions');
    [$code,$body] = requestFeature('admin','admin/login_logs.php?status=active');
    verify($code === 200 && str_contains($body,'<td>Admin Feature Person</td>') && !str_contains($body,'<td>Disconnected fixture</td>'), 'Active filter shows online sessions');
    [$code,$body] = requestFeature('admin','admin/login_logs.php?q=100%25');
    verify($code === 200 && str_contains($body,'1 session found') && str_contains($body,'<td>Literal 100% fixture</td>'), 'Search treats SQL wildcard characters literally');
    [$code,$body] = requestFeature('admin','admin/login_logs.php?q=2026-01-02&status=logout');
    verify($code === 200 && str_contains($body,'<td>Logout search fixture</td>') && !str_contains($body,'<td>Timeout search fixture</td>'), 'Date search combines with logged-out status');
    [$code,$body] = requestFeature('admin','admin/login_logs.php?q=' . urlencode("' OR 1=1 -- <script>"));
    verify($code === 200 && str_contains($body,'No login sessions match your search.') && !str_contains($body,'<td>Timeout search fixture</td>') && str_contains($body,'&lt;script&gt;'), 'Search is safely parameterized and escaped, with a clear empty state');
    [$code,$body] = requestFeature('admin','admin/login_logs.php',['export_excel'=>'1','q'=>'Timeout search fixture','status'=>'timeout']);
    $csvLines = preg_split('/\r?\n/', trim($body));
    verify($code === 200 && count($csvLines) === 2 && str_contains($body,'Timeout search fixture') && !str_contains($body,'Logout search fixture'), 'Export contains only matching filtered sessions');
    if (getenv('HRMS_PAYSLIP_PRINT_BROWSER') === '1') {
        $browser = proc_open(['node', __DIR__ . '/payslip-print-browser.cjs'], [0=>['pipe','r'],1=>STDOUT,2=>STDERR], $browserPipes, dirname(__DIR__), $environment);
        if (!is_resource($browser) || proc_close($browser) !== 0) throw new RuntimeException('Payslip print checks failed');
    }
    if (getenv('HRMS_THEME_BROWSER') === '1') {
        $browser = proc_open(['node', __DIR__ . '/theme-browser.cjs'], [0=>['pipe','r'],1=>STDOUT,2=>STDERR], $browserPipes, dirname(__DIR__), $environment);
        if (!is_resource($browser) || proc_close($browser) !== 0) throw new RuntimeException('Theme browser checks failed');
    }
    echo "Completed $checks online-user checks.\n";
} finally {
    if (is_resource($server)) { proc_terminate($server); proc_close($server); }
    foreach ($cookies as $file) if (is_file($file)) unlink($file);
    $conn->close();
    if (preg_match('/\Ahrms_feature_test_[a-f0-9]{10}\z/', $database)) $source->query("DROP DATABASE `$database`");
}
