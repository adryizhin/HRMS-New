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
    $curl = curl_init('http://127.0.0.1:8093/HRMS/' . $path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_COOKIEJAR => $cookies[$role], CURLOPT_COOKIEFILE => $cookies[$role], CURLOPT_TIMEOUT => 20]);
    if ($form !== null) { curl_setopt($curl, CURLOPT_POST, true); curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($form)); }
    $body = curl_exec($curl);
    if ($body === false) throw new RuntimeException(curl_error($curl));
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
    curl_close($curl);
    if (preg_match('/Fatal error|Parse error|Warning:|Deprecated:/', $body)) throw new RuntimeException('PHP error at ' . $path . ': ' . strip_tags($body));
    return [$code, $body, $contentType];
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
    require dirname(__DIR__).'/includes/account_profile.php';ensureAccountSchema($conn);
    require dirname(__DIR__).'/includes/chat_attachments.php';ensureChatAttachments($conn);
    $uploadFixture=tempnam(sys_get_temp_dir(),'hrms-upload-');file_put_contents($uploadFixture,'Attachment fixture text.');
    function uploadFeature(string $role,string $path,array $form):array{
        global $cookies;if(!isset($cookies[$role]))$cookies[$role]=tempnam(sys_get_temp_dir(),'hrms-cookie-');
        $curl=curl_init('http://127.0.0.1:8093/HRMS/'.$path);curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_COOKIEJAR=>$cookies[$role],CURLOPT_COOKIEFILE=>$cookies[$role],CURLOPT_TIMEOUT=>20,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$form]);$body=curl_exec($curl);$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);curl_close($curl);if(preg_match('/Fatal error|Parse error|Warning:|Deprecated:/',$body))throw new RuntimeException($body);return [$code,$body];
    }
    $environment=getenv();$environment['HRMS_DB_NAME']=$database;$environment['HRMS_BASE_URL']='/HRMS';
    $server=proc_open([PHP_BINARY,'-S','127.0.0.1:8093',dirname(__DIR__).'/tests/features-router.php'],[0=>['pipe','r'],1=>['file',__DIR__.'/account-chat-server.log','w'],2=>['file',__DIR__.'/account-chat-server.log','a']],$pipes,dirname(__DIR__),$environment);if(!is_resource($server))throw new RuntimeException('No server');usleep(2000000);
    foreach(['admin','hr','employee'] as $role){requestFeature($role,'login/login_process.php',['email'=>$role.'@feature.example','password'=>'TemporaryFeaturePassword!']);[$code,$body]=requestFeature($role,'account/settings.php');verify($code===200&&str_contains($body,'Account settings')&&str_contains($body,'Account Settings'),"$role account settings available");}
    [$code,$body]=requestFeature('guest','account/settings.php');verify(!str_contains($body,'settings-form'),'Guest cannot access settings');
    [$code,$body]=requestFeature('admin','admin/dashboard.php');preg_match('/data-upload-token="([a-f0-9]+)"/',$body,$token);$chatToken=$token[1];
    [$code,$body]=uploadFeature('admin','chat/send_message.php',['receiver_id'=>'2','message'=>'','csrf_token'=>$chatToken,'attachment'=>new CURLFile($uploadFixture,'text/plain','report <fixture>.txt')]);verify($code===200&&$body==='success','Admin can send attachment without text');
    $message=$conn->query('SELECT * FROM chat_messages ORDER BY id DESC LIMIT 1')->fetch_assoc();$messageId=(int)$message['id'];
    [$code,$body]=requestFeature('hr','chat/fetch_messages.php?receiver_id=1&read=1');verify(str_contains($body,'chat-file-link')&&str_contains($body,'report &lt;fixture&gt;.txt'),'HR sees safe attachment link');
    foreach(['admin','hr'] as $role){[$code,$body]=requestFeature($role,'chat/download_attachment.php?id='.$messageId);verify($code===200&&$body==='Attachment fixture text.',"$role participant downloads attachment");}
    [$code]=requestFeature('employee','chat/download_attachment.php?id='.$messageId);verify($code===404,'Nonparticipant cannot download attachment');
    [$code,$body]=requestFeature('hr','hr/dashboard.php');preg_match('/data-upload-token="([a-f0-9]+)"/',$body,$token);
    [$code,$body]=uploadFeature('hr','chat/send_message.php',['receiver_id'=>'1','message'=>'HR attachment','csrf_token'=>$token[1],'attachment'=>new CURLFile($uploadFixture,'text/plain','hr-report.txt')]);verify($code===200&&$body==='success','HR can send attachments');
    [$code]=requestFeature('admin','chat/download_attachment.php?id='.$messageId.'&preview=1');verify($code===415,'Documents cannot be served as inline images');
    foreach (['admin','hr'] as $imageSender) {
        $receiver = $imageSender === 'admin' ? '2' : '1';
        $uploadToken = $imageSender === 'admin' ? $chatToken : $token[1];
        [$code,$body]=uploadFeature($imageSender,'chat/send_message.php',['receiver_id'=>$receiver,'message'=>'Picture caption','csrf_token'=>$uploadToken,'attachment'=>new CURLFile(dirname(__DIR__).'/images/logo.png','image/png','picture <fixture>.png')]);
        verify($code===200&&$body==='success',"$imageSender can send a picture");
        $imageId=(int)$conn->query('SELECT id FROM chat_messages ORDER BY id DESC LIMIT 1')->fetch_row()[0];
        foreach (['admin','hr'] as $viewer) {
            [$code,$body]=requestFeature($viewer,'chat/fetch_messages.php?receiver_id='.($viewer==='admin'?'2':'1'));
            verify($code===200&&str_contains($body,'class="chat-message-image"')&&str_contains($body,'id='.$imageId.'&amp;preview=1')&&str_contains($body,'picture &lt;fixture&gt;.png')&&str_contains($body,'Picture caption'),"$viewer sees $imageSender picture as an image with safe name and caption");
            [$code,$body,$mime]=requestFeature($viewer,'chat/download_attachment.php?id='.$imageId.'&preview=1');
            verify($code===200&&$mime==='image/png'&&str_starts_with($body,"\x89PNG"),"$viewer can load $imageSender inline picture with correct MIME type");
        }
        [$code]=requestFeature('employee','chat/download_attachment.php?id='.$imageId.'&preview=1');verify($code===404,'Nonparticipant cannot view inline picture');
    }
    [$code]=uploadFeature('admin','chat/send_message.php',['receiver_id'=>'2','csrf_token'=>$chatToken,'attachment'=>new CURLFile($uploadFixture,'image/png','fake.png')]);verify($code===400,'Non-image content disguised as a picture is rejected');
    [$code]=uploadFeature('admin','chat/send_message.php',['receiver_id'=>'2','csrf_token'=>'bad','attachment'=>new CURLFile($uploadFixture,'text/plain','blocked.txt')]);verify($code===400,'Invalid upload token is blocked');
    [$code]=uploadFeature('admin','chat/send_message.php',['receiver_id'=>'2','csrf_token'=>$chatToken,'attachment'=>new CURLFile($uploadFixture,'text/plain','blocked.php')]);verify($code===400,'Executable file extension is blocked');
    [$code]=uploadFeature('admin','chat/send_message.php',['receiver_id'=>'9999','csrf_token'=>$chatToken,'attachment'=>new CURLFile($uploadFixture,'text/plain','invalid.txt')]);verify($code===400,'Unknown attachment recipient blocked');
    [$code,$body]=requestFeature('admin','account/settings.php');preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$body,$token);$profileToken=$token[1];
    $profile=['csrf_token'=>$profileToken,'full_name'=>'Admin Updated','email'=>'admin@feature.example','phone_number'=>'+63 912 345 6789','role'=>'employee','user_id'=>'3'];
    [$code,$body]=uploadFeature('admin','account/settings.php',$profile+['profile_picture'=>new CURLFile(dirname(__DIR__).'/images/logo.png','image/png','photo.png')]);verify($code===200&&str_contains($body,'Account settings saved'),'Profile details and photo update');
    $user=$conn->query('SELECT * FROM users WHERE id=1')->fetch_assoc();verify($user['full_name']==='Admin Updated'&&$user['role']==='admin'&&$user['phone_number']==='+63 912 345 6789'&&$user['profile_picture']!==null,'Profile save changes only permitted fields');
    verify($conn->query('SELECT full_name FROM users WHERE id=3')->fetch_row()[0]==='Employee Feature Person','Posted user ID cannot change another account');
    [$code,$body]=requestFeature('hr','account/photo.php?id=1');verify($code===200&&str_starts_with($body,"\x89PNG"),'Uploaded photo renders for signed-in colleague');
    [$code,$body]=requestFeature('hr','hr/dashboard.php');verify(str_contains($body,'data-contact-photo="/HRMS/account/photo.php?id=1"'),'Contact list uses saved photo');
    $changed=$profile;$changed['email']='new-admin@feature.example';$changed['current_password']='wrong';requestFeature('admin','account/settings.php',$changed);verify($conn->query('SELECT email FROM users WHERE id=1')->fetch_row()[0]==='admin@feature.example','Wrong current password cannot change email');
    $changed=$profile;$changed['email']='hr@feature.example';$changed['current_password']='TemporaryFeaturePassword!';requestFeature('admin','account/settings.php',$changed);verify($conn->query('SELECT email FROM users WHERE id=1')->fetch_row()[0]==='admin@feature.example','Duplicate email leaves account unchanged');
    $bad=$profile;$bad['csrf_token']='bad';$bad['full_name']='Forged name';requestFeature('admin','account/settings.php',$bad);verify($conn->query('SELECT full_name FROM users WHERE id=1')->fetch_row()[0]==='Admin Updated','Invalid account token cannot change profile');
    [$code,$body]=uploadFeature('admin','account/settings.php',$profile+['profile_picture'=>new CURLFile($uploadFixture,'text/plain','fake.png')]);verify(str_contains($body,'valid JPG')&&$conn->query('SELECT profile_picture FROM users WHERE id=1')->fetch_row()[0]===$user['profile_picture'],'Invalid photo type does not replace existing photo');
    $changed=$profile;$changed['current_password']='TemporaryFeaturePassword!';$changed['new_password']=$changed['confirm_password']='UpdatedAccountPassword!';requestFeature('admin','account/settings.php',$changed);verify(password_verify('UpdatedAccountPassword!',$conn->query('SELECT password FROM users WHERE id=1')->fetch_row()[0]),'Account password is stored hashed');
    [$code,$body]=requestFeature('guest','login.php');verify(str_contains($body,'data-password-target="password"'),'Login page has password visibility button');
    [$code,$body]=requestFeature('hr','hr/hr_leave_requests.php');verify(str_contains($body,'font-awesome')&&str_contains($body,'fa-calendar-check'),'HR leave page restores Font Awesome');
    if(getenv('HRMS_ACCOUNT_BROWSER')==='1'){$process=proc_open(['node',__DIR__.'/account-chat-browser.cjs'],[0=>['pipe','r'],1=>STDOUT,2=>STDERR],$bp,dirname(__DIR__),$environment);if(!is_resource($process)||proc_close($process)!==0)throw new RuntimeException('Browser tests failed');}
    echo "Completed $checks account/attachment checks.\n";
}finally{
    if(is_resource($server)){proc_terminate($server);proc_close($server);}
    foreach($conn->query('SELECT attachment_path FROM chat_messages WHERE attachment_path IS NOT NULL')->fetch_all(MYSQLI_ASSOC) as $row){if(preg_match('/\A[a-f0-9]{32}\.[a-z0-9]+\z/',$row['attachment_path'])){$file=dirname(__DIR__).'/uploads/private_chat/'.$row['attachment_path'];if(is_file($file))unlink($file);}}
    foreach($conn->query('SELECT profile_picture FROM users WHERE profile_picture IS NOT NULL')->fetch_all(MYSQLI_ASSOC) as $row){if(preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/',$row['profile_picture'])){$file=dirname(__DIR__).'/uploads/profile_photos/'.$row['profile_picture'];if(is_file($file))unlink($file);}}
    if(isset($uploadFixture)&&is_file($uploadFixture))unlink($uploadFixture);foreach($cookies as $file)if(is_file($file))unlink($file);$conn->close();if(preg_match('/\Ahrms_feature_test_[a-f0-9]{10}\z/',$database))$source->query("DROP DATABASE `$database`");
}