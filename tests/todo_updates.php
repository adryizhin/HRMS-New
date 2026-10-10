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
    $conn->query("INSERT INTO users (id,full_name,email,password,role,status) VALUES (4,'Other Administrator','other@feature.example','$password','admin','active')");
    require dirname(__DIR__).'/includes/password_tickets.php'; ensurePasswordTicketSchema($conn);
    require dirname(__DIR__).'/includes/evaluation_access.php';
    verify(isSystemAdministrator($conn,1) && !isSystemAdministrator($conn,4), 'System Administrator privilege belongs only to configured active account');
    verify(validateEvaluationRecipients($conn,1,[2]) && !validateEvaluationRecipients($conn,2,[2]) && !validateEvaluationRecipients($conn,4,[2]), 'Only System Administrator can assign Human Resource evaluations');
    verify(validateEvaluationRecipients($conn,2,[3,4]) && !validateEvaluationRecipients($conn,2,[9999]), 'HR retains employee/admin assignment access and invalid recipients are blocked');
    $environment=getenv();$environment['HRMS_DB_NAME']=$database;$environment['HRMS_BASE_URL']='/HRMS';
    $server=proc_open([PHP_BINARY,'-S','127.0.0.1:8091',dirname(__DIR__).'/tests/features-router.php'],[0=>['pipe','r'],1=>['file',__DIR__.'/todo-server.log','w'],2=>['file',__DIR__.'/todo-server.log','a']],$pipes,dirname(__DIR__),$environment);
    if(!is_resource($server))throw new RuntimeException('Unable to start test server');usleep(500000);
    foreach(['admin','hr','employee','other'] as $role){[$code,$body]=requestFeature($role,'login/login_process.php',['email'=>$role.'@feature.example','password'=>'TemporaryFeaturePassword!']);verify($code===200&&str_contains($body,'sidebar'), "$role login");}
    [$code,$body]=requestFeature('hr','hr/dashboard.php');
    verify(str_contains($body,'chat-robot-cutout.png')&&str_contains($body,'admin/chat_box.js')&&str_contains($body,'id="chatComposer"'),'HR shares robot launcher and messaging UI');
    verify(str_contains($body,'class="hr-dashboard-panels"') && str_contains($body,'id="send-evaluation"') && !str_contains($body,'send_evaluation.php'), 'HR dashboard shows Messages and Send Evaluation together without an extra sidebar link');
    verify(str_contains($body,'<details class="sent-tasks">') && str_contains($body,'id="chatComposer"'), 'Combined dashboard keeps sent history collapsed and includes messaging');
    preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$body,$token);$hrToken=$token[1];
    [$code,$body]=requestFeature('hr','hr/evaluations.php');verify(str_contains($body,'Human Resource'),'My Evaluations displays full Human Resource role');
    $assignment=['title'=>'HR assignment security fixture','description'=>'Test instructions','deadline'=>'2027-12-31','employee_ids'=>[2],'csrf_token'=>$hrToken];
    [$code]=requestFeature('hr','hr/post_task.php',$assignment);verify($code===403&&(int)$conn->query('SELECT COUNT(*) FROM tasks')->fetch_row()[0]===0,'Forged HR recipient submission is blocked');
    $hrAssignment=$assignment;$hrAssignment['employee_ids']=[3];$hrAssignment['title']='HR separate page fixture';
    [$code,$body]=requestFeature('hr','hr/post_task.php',$hrAssignment);
    verify($code===200 && str_contains($body,'class="hr-dashboard-panels"') && str_contains($body,'Evaluation sent to 1 recipient') && str_contains($body,'HR separate page fixture'), 'HR submission returns to the combined dashboard with success and sent history');
    [$code]=requestFeature('other','admin/assign_evaluations.php');verify($code===403,'Other admin cannot open HR assignment form');
    [$code]=requestFeature('other','hr/post_task.php',$assignment);verify($code===403,'Other admin cannot assign Human Resource work');
    [$code,$body]=requestFeature('admin','admin/hr_evaluations.php');
    verify($code===200 && substr_count($body,'>HR Evaluations</span>')===1 && !str_contains($body,'>Assign HR Evaluations</span>') && !str_contains($body,'>Review HR Evaluations</span>'),'System Administrator has one combined HR Evaluations sidebar item');
    [$code]=requestFeature('other','admin/hr_evaluations.php');verify($code===403,'Combined evaluation page remains restricted to System Administrator');
    [$code,$body]=requestFeature('admin','admin/assign_evaluations.php');preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$body,$token);$assignment['csrf_token']=$token[1];
    [$code,$body]=requestFeature('admin','hr/post_task.php',$assignment);verify($code===200&&str_contains($body,'Evaluations assigned: 1'),'System Administrator assigns HR evaluation');
    [$code,$body]=requestFeature('hr','hr/evaluations.php');verify(str_contains($body,'HR assignment security fixture'),'Human Resource sees System Administrator assignment in My Evaluations');
    [$code,$body]=requestFeature('admin','hr/task_submissions.php');verify($code===200&&str_contains($body,'sidebar'),'System Administrator can review assigned HR work');
    [$code]=requestFeature('other','hr/task_submissions.php');verify($code===403,'Other admins cannot review System Administrator HR work');
    requestFeature('hr','chat/send_message.php',['receiver_id'=>1,'message'=>'Unread HR message']);
    requestFeature('other','chat/send_message.php',['receiver_id'=>1,'message'=>'Unread admin message']);
    [$code,$body]=requestFeature('admin','chat/unread_messages.php');$unread=json_decode($body,true);verify($unread['count']===2&&count($unread['senders'])===2,'Admin unread count includes messages from every sender');
    requestFeature('admin','chat/fetch_messages.php?receiver_id=2');
    [$code,$body]=requestFeature('admin','chat/unread_messages.php');verify(json_decode($body,true)['count']===2,'Background message retrieval does not clear unread messages');
    requestFeature('admin','chat/fetch_messages.php?receiver_id=2&read=1');
    [$code,$body]=requestFeature('admin','chat/unread_messages.php');verify(json_decode($body,true)['count']===1,'Reading one conversation leaves other conversations unread');
    requestFeature('admin','chat/fetch_messages.php?receiver_id=4&read=1');
    [$code,$body]=requestFeature('admin','chat/unread_messages.php');verify(json_decode($body,true)['count']===0,'Unread counter clears after all messages are read');
    requestFeature('admin','chat/send_message.php',['receiver_id'=>2,'message'=>'Reply to HR']);
    [$code,$body]=requestFeature('hr','chat/unread_messages.php');verify(json_decode($body,true)['count']===1,'HR receives real unread count');
    submitPasswordTicket($conn,'missing@feature.example','','No account request');
    $ticket=$conn->query("SELECT * FROM password_reset_tickets WHERE requested_email='missing@feature.example'")->fetch_assoc();
    verify($ticket['user_id']===null&&str_contains($ticket['system_note'],'Email not found'),'Unknown email creates an admin note with no password-reset target');
    submitPasswordTicket($conn,'missing@feature.example','','Duplicate');verify((int)$conn->query('SELECT COUNT(*) FROM password_reset_tickets')->fetch_row()[0]===1,'Repeated unmatched request keeps existing ticket');
    $blocked=false;try{handlePasswordTicket($conn,1,(int)$ticket['id'],'resolved','','NewVerifiedPassword!','NewVerifiedPassword!',true);}catch(InvalidArgumentException $e){$blocked=true;}verify($blocked,'Unknown email ticket cannot reset a password');
    [$code,$body]=requestFeature('admin','admin/password_tickets.php');verify(str_contains($body,'Email not found in the system')&&str_contains($body,'missing@feature.example')&&!str_contains($body,'Reset password &amp; resolve'),'Admin sees unknown-email notification without reset control');
    [$code,$body]=requestFeature('hr','hr/hr_leave_requests.php?status=pending');verify($code===200&&str_contains($body,'leave-panel')&&str_contains($body,'No leave requests'),'HR leave layout and filtered empty state render');
    [$code,$body]=requestFeature('admin','notifications/activity.php');$activity=json_decode($body,true);
    verify($code===200 && ($activity['badges']['admin.password_tickets']??0)===1, 'Admin notification counts open password tickets');
    verify(!in_array('messages',array_column($activity['groups'],'key'),true),'Admin bell excludes message activity');
    $conn->query("INSERT INTO chat_messages (sender_id,receiver_id,message,status,is_seen) VALUES (2,1,'Legacy seen fixture','seen',0)");
    [$code,$body]=requestFeature('admin','notifications/activity.php');$legacy=json_decode($body,true);
    verify(!in_array('messages',array_column($legacy['groups'],'key'),true),'Seen messages with a legacy unread flag do not create phantom notifications');
    [$code,$body]=requestFeature('hr','notifications/activity.php');$activity=json_decode($body,true);
    verify(!in_array('messages',array_column($activity['groups'],'key'),true),'HR message notifications are exclusive to chatbot badge');
    verify(!isset($activity['badges']['admin.password_tickets']) && ($activity['badges']['hr.evaluations']??0)===1 && !isset($activity['badges']['hr.dashboard']), 'HR bell shows evaluation activity without duplicate chat counts or admin tickets');
    $before=$conn->query('SELECT last_ping FROM login_logs WHERE user_id=2 AND logout_time IS NULL ORDER BY id DESC LIMIT 1')->fetch_row()[0];
    requestFeature('hr','notifications/activity.php');
    verify($before===$conn->query('SELECT last_ping FROM login_logs WHERE user_id=2 AND logout_time IS NULL ORDER BY id DESC LIMIT 1')->fetch_row()[0], 'Notification polling does not extend idle session');
    $conn->query("INSERT INTO leave_requests (employee_id,hr_id,leave_type,start_date,end_date,reason,status) VALUES (3,2,'Sick Leave','2027-01-04','2027-01-04','Notification fixture','pending')");
    [$code,$body]=requestFeature('hr','notifications/activity.php');verify((json_decode($body,true)['badges']['hr.leave_requests']??0)===1,'Pending leave badge belongs to assigned HR');
    $conn->query("UPDATE tasks SET status='submitted' WHERE employee_id=3 AND hr_id=2");
    [$code,$body]=requestFeature('hr','notifications/activity.php');verify((json_decode($body,true)['badges']['hr.task_submissions']??0)===1,'Submitted evaluation notifies its reviewer');
    $reviewId=(int)$conn->query('SELECT id FROM tasks WHERE employee_id=3 AND hr_id=2 LIMIT 1')->fetch_row()[0];
    requestFeature('hr','hr/score_submission.php?id='.$reviewId,['hr_score'=>90,'hr_feedback'=>'Notification test']);
    verify((int)$conn->query("SELECT COUNT(*) FROM notifications WHERE user_id=3 AND activity_type='evaluation'")->fetch_row()[0]===1,'Review creates a personal evaluation update');
    requestFeature('hr','hr/score_submission.php?id='.$reviewId,['hr_score'=>91,'hr_feedback'=>'Updated review']);
    verify((int)$conn->query("SELECT COUNT(*) FROM notifications WHERE user_id=3 AND activity_type='evaluation'")->fetch_row()[0]===1,'Saving an existing review does not duplicate the notification');
    $conn->query("INSERT INTO notifications (user_id,message) VALUES (3,'Leave decision <script>untrusted</script>'),(2,'Private HR update')");
    $privateId=(int)$conn->insert_id+1;
    [$code,$body]=requestFeature('employee','notifications/activity.php');$activity=json_decode($body,true);
    verify($activity['unreadUpdates']===2 && !str_contains($body,'Private HR update') && !isset($activity['badges']['admin.password_tickets']), 'Employee notifications exclude other users and restricted activity');
    [$code]=requestFeature('employee','notifications/activity.php',['csrf_token'=>'invalid','ids'=>'1']);verify($code===400,'Notification read requires CSRF');
    [$code,$body]=requestFeature('employee','employee/dashboard.php');preg_match('/data-token="([a-f0-9]+)"/',$body,$token);
    requestFeature('employee','notifications/activity.php',['csrf_token'=>$token[1],'ids'=>(string)$privateId]);
    verify((int)$conn->query('SELECT is_read FROM notifications WHERE id='.$privateId)->fetch_row()[0]===0,'Read action cannot acknowledge another user update');
    $ownIds=array_column($activity['updates'],'id');
    [$code,$body]=requestFeature('employee','notifications/activity.php',['csrf_token'=>$token[1],'ids'=>implode(',',$ownIds)]);
    verify(json_decode($body,true)['unreadUpdates']===0,'Explicit acknowledgement clears only displayed personal updates');
    $conn->query("INSERT INTO notifications (user_id,message) VALUES (3,'Browser leave decision')");
    $conn->query("UPDATE password_reset_tickets SET status='resolved'");
    [$code,$body]=requestFeature('admin','notifications/activity.php');verify(!isset(json_decode($body,true)['badges']['admin.password_tickets']),'Resolved tickets clear the admin badge');
    $conn->query("UPDATE tasks SET status='submitted',submission_file='combined-review-fixture.txt',submitted_at=NOW() WHERE hr_id=1 AND employee_id=2");
    [$code,$body]=requestFeature('admin','admin/hr_evaluations.php?tab=review');
    verify($code===200 && str_contains($body,'HR assignment security fixture') && str_contains($body,'hr/score_submission.php?id=') && !str_contains($body,'name="employee_ids[]"'),'Review tab displays only own HR submissions with working score links');
    if(getenv('HRMS_TODO_BROWSER')==='1'){
      $conn->query("INSERT INTO leave_requests (employee_id,hr_id,leave_type,start_date,end_date,reason,status) VALUES (3,2,'Sick Leave','2027-01-04','2027-01-05','Leave reason fixture &lt;script&gt;','pending'),(3,2,'Vacation Leave','2027-02-01','2027-02-02','Family trip','approved')");
      $browser=proc_open(['node',__DIR__.'/todo-browser.cjs'],[0=>['pipe','r'],1=>STDOUT,2=>STDERR],$browserPipes,dirname(__DIR__),$environment);
      if(!is_resource($browser)||proc_close($browser)!==0)throw new RuntimeException('Browser checks failed');
    }
    echo "Completed $checks requested-update checks.\n";
} finally {
    if(is_resource($server)){proc_terminate($server);proc_close($server);}
    foreach($cookies as $file)if(is_file($file))unlink($file);
    $conn->close();if(preg_match('/\Ahrms_feature_test_[a-f0-9]{10}\z/',$database))$source->query("DROP DATABASE `$database`");
}