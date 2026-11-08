<?php
// Included by features.php while its isolated database and HTTP server are active.
require_once dirname(__DIR__).'/includes/appraisal.php';
ensureAppraisalSchema($conn);
$base=['position'=>'Staff Specialist','department'=>'Operations','date_hired'=>'2025-01-01','period_start'=>'2026-01-01','period_end'=>'2026-01-31','accomplished_date'=>'2026-02-01','strengths'=>'Careful record keeping.','weaknesses'=>'Needs more supporting detail.','recommendations'=>'Use a weekly checklist.','potential'=>'Ready for additional responsibility.'];
$keys=array_keys(appraisalCriteria());
foreach([4=>100.0,3=>75.0,2=>50.0,1=>25.0] as $rating=>$expected){$valid=validateAppraisal($base+['ratings'=>array_fill_keys($keys,$rating)]);verify($valid['score']===$expected,'Appraisal rating '.$rating.' calculates '.$expected.' percent');}
$form=$base+['ratings'=>array_fill_keys($keys,4)];$form['ratings']['loyalty']=1;
verify(validateAppraisal($form)['score']===88.75,'Appraisal applies 20/20/15/15/15/15 weights with decimal precision');
foreach([
 ['ratings'=>array_fill_keys($keys,0)],
 ['ratings'=>array_fill_keys($keys,5)],
 ['ratings'=>[]],
 ['period_start'=>'2026-03-01'],
 ['accomplished_date'=>'2026-01-01'],
 ['date_hired'=>'2026-02-30'],
 ['development'=>[['invalid']]],
 ['development'=>['reappraisal']],
 ['development'=>['promotion']],
 ['comments'=>['loyalty'=>str_repeat('x',2001)]],
] as $invalid){$blocked=false;try{validateAppraisal(array_replace($form,$invalid));}catch(InvalidArgumentException $e){$blocked=true;}verify($blocked,'Invalid appraisal inputs are rejected: '.implode(',',array_keys($invalid)));}
$fixture='appraisal_test_'.bin2hex(random_bytes(8)).'.txt';$path=dirname(__DIR__).'/uploads/submissions/'.$fixture;file_put_contents($path,'Appraisal review evidence fixture.');
try{
 $insert=$conn->prepare("INSERT INTO tasks(employee_id,hr_id,title,deadline,submission_file,submitted_at,status,created_at) VALUES(3,2,'Appraisal feature fixture','2026-01-31',?,'2026-01-30 12:00:00','submitted','2026-01-01')");$insert->bind_param('s',$fixture);$insert->execute();$taskId=(int)$conn->insert_id;
 [$code,$body]=requestFeature('hr','hr/score_submission.php?id='.$taskId);
 preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$body,$token);
 verify($code===200 && isset($token[1]) && str_contains($body,'VI. Attendance') && str_contains($body,'Employee’s Future Development'),'HR opens all sections of the supplied appraisal form');
 $form['csrf_token']=$token[1];$form['hr_score']=100;$form['strengths']='<script>unsafe</script>';
 [$code,$body]=requestFeature('hr','hr/score_submission.php?id='.$taskId,array_replace($form,['csrf_token'=>'bad']));
 verify($code===400 && $conn->query('SELECT hr_score FROM tasks WHERE id='.$taskId)->fetch_row()[0]===null,'Appraisal save requires CSRF and preserves existing score on failure');
 [$code,$body]=requestFeature('hr','hr/score_submission.php?id='.$taskId,array_replace($form,['ratings'=>[]]));
 verify($code===200 && str_contains($body,'all six appraisal sections') && $conn->query('SELECT hr_score FROM tasks WHERE id='.$taskId)->fetch_row()[0]===null,'Incomplete rubric cannot create a zero or arbitrary score');
 $notificationsBefore=(int)$conn->query("SELECT COUNT(*) FROM notifications WHERE user_id=3 AND activity_type='evaluation'")->fetch_row()[0];
 [$code,$body]=requestFeature('hr','hr/score_submission.php?id='.$taskId,$form);
 $stored=$conn->query('SELECT * FROM task_appraisals WHERE task_id='.$taskId)->fetch_assoc();$taskScore=$conn->query('SELECT hr_score,status,hr_feedback FROM tasks WHERE id='.$taskId)->fetch_assoc();
 verify($code===200 && str_contains($body,'Score saved successfully') && (float)$stored['total_score']===88.75 && (float)$taskScore['hr_score']===88.75 && $taskScore['status']==='reviewed','Server saves the calculated decimal total and ignores injected overall score');
 $payload=json_decode($stored['payload'],true);
 verify($payload['ratings']['loyalty']===1 && $payload['recommendations']===$base['recommendations'] && (int)$stored['evaluator_id']===2,'Appraisal stores individual ratings, narrative fields and actual evaluator');
 verify((int)$conn->query("SELECT COUNT(*) FROM notifications WHERE user_id=3 AND activity_type='evaluation'")->fetch_row()[0]===$notificationsBefore+1,'First completed appraisal creates one employee notification');
 $form['ratings']=array_fill_keys($keys,3);requestFeature('hr','hr/score_submission.php?id='.$taskId,$form);
 verify((float)$conn->query('SELECT hr_score FROM tasks WHERE id='.$taskId)->fetch_row()[0]===75.0 && (int)$conn->query("SELECT COUNT(*) FROM notifications WHERE user_id=3 AND activity_type='evaluation'")->fetch_row()[0]===$notificationsBefore+1,'Editing an appraisal recalculates the score without duplicate notification');
 [$code,$body]=requestFeature('employee','employee/view_appraisal.php?id='.$taskId);
 verify($code===200 && str_contains($body,'&lt;script&gt;unsafe&lt;/script&gt;') && str_contains($body,'disabled') && !str_contains($body,'> Save appraisal'),'Employee can read own full appraisal with escaped comments and no edit controls');
 [$code,$body]=requestFeature('hr','employee/view_appraisal.php?id='.$taskId);verify($code===404,'Personal appraisal viewer cannot expose another employee appraisal');
 [$code,$body]=requestFeature('employee','employee/dashboard.php');verify(str_contains($body,'View Appraisal') && str_contains($body,'employee/view_appraisal.php?id='.$taskId),'My Evaluations links to the completed appraisal');
 $hash=password_hash('TemporaryFeaturePassword!',PASSWORD_DEFAULT);$other=$conn->prepare("INSERT INTO users(id,full_name,email,role,status,password,created_at) VALUES(1004,'Other HR','appraiser-other@feature.example','hr','active',?,'2026-01-01'),(1005,'Other Admin','appraiser-admin@feature.example','admin','active',?,'2026-01-01')");$other->bind_param('ss',$hash,$hash);$other->execute();
 requestFeature('otherhr','login/login_process.php',['email'=>'appraiser-other@feature.example','password'=>'TemporaryFeaturePassword!']);
 [$code]=requestFeature('otherhr','hr/score_submission.php?id='.$taskId,$form);verify($code===404,'Another HR cannot read or change an unassigned appraisal');
 requestFeature('otheradmin','login/login_process.php',['email'=>'appraiser-admin@feature.example','password'=>'TemporaryFeaturePassword!']);
 [$code]=requestFeature('otheradmin','hr/score_submission.php?id='.$taskId);verify($code===403,'Ordinary administrators cannot use System Administrator appraisal privileges');
 $conn->query("INSERT INTO tasks(employee_id,hr_id,title,deadline,submission_file,submitted_at,status,created_at) VALUES(2,1,'System HR appraisal fixture','2026-01-31','".$conn->real_escape_string($fixture)."','2026-01-30 12:00:00','submitted','2026-01-01')");$adminTask=(int)$conn->insert_id;
 [$code,$body]=requestFeature('admin','hr/score_submission.php?id='.$adminTask);preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$body,$adminToken);$adminForm=$form;$adminForm['csrf_token']=$adminToken[1];
 [$code,$body]=requestFeature('admin','hr/score_submission.php?id='.$adminTask,$adminForm);
 verify($code===200 && (float)$conn->query('SELECT hr_score FROM tasks WHERE id='.$adminTask)->fetch_row()[0]===75.0,'System Administrator uses the appraisal form for assigned Human Resource staff');
 if(getenv('HRMS_APPRAISAL_BROWSER')==='1'){
  $browserEnvironment=$environment;$browserEnvironment['HRMS_APPRAISAL_TASK_ID']=(string)$taskId;
  $process=proc_open(['node',__DIR__.'/appraisal-browser.cjs'],[0=>['pipe','r'],1=>STDOUT,2=>STDERR],$bp,dirname(__DIR__),$browserEnvironment);
  if(!is_resource($process)||proc_close($process)!==0)throw new RuntimeException('Appraisal browser checks failed');
 }
}finally{if(is_file($path))unlink($path);}
