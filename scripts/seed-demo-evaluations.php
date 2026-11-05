<?php
// Add sample evaluations; no existing assignments, submissions or scores are changed.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/config.php';
require_once dirname(__DIR__).'/includes/task_schema.php';
require_once dirname(__DIR__).'/includes/evaluation_access.php';
require_once dirname(__DIR__).'/includes/kpi.php';
ensureTaskSchema($conn);
$apply=in_array('--apply',$argv,true);
$groups=require dirname(__DIR__).'/config/demo_accounts.php';
$accounts=[];
$lookup=$conn->prepare("SELECT id,full_name,email,role FROM users WHERE email=? AND role=? AND status='active'");
foreach($groups as $role=>[$prefix,$slugs])foreach($slugs as $i=>$slug){
 $email=$prefix.'.'.$slug.'@pagecom.com';$lookup->bind_param('ss',$email,$role);$lookup->execute();$user=$lookup->get_result()->fetch_assoc();
 if(!$user)throw new RuntimeException('Expected demo account not found: '.$email.'. Nothing inserted.');
 $user['demo_group']=$i<3?'Excellent':($i<7?'Neutral':'Low');$accounts[(int)$user['id']]=$user;
}
if(in_array('--all-active',$argv,true)){
 $i=0;foreach($conn->query("SELECT id,full_name,email,role FROM users WHERE status='active' AND role IN ('employee','hr','admin') ORDER BY id") as $user){if(isset($accounts[(int)$user['id']]))continue;$user['demo_group']=['Excellent','Neutral','Low'][$i++%3];$accounts[(int)$user['id']]=$user;}
}
$system=(int)(getenv('HRMS_SYSTEM_ADMIN_ID')?:1);
if(!isSystemAdministrator($conn,$system))throw new RuntimeException('An active System Administrator is required for HR evaluations.');
$reviewers=$conn->query("SELECT id FROM users WHERE role='hr' AND status='active' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
if(!$reviewers)throw new RuntimeException('An active HR reviewer is required.');
$now=new DateTimeImmutable('now',new DateTimeZone('Asia/Manila'));$end=$now->setTime(0,0)->modify('-1 day');
$holidays=[];foreach($conn->query('SELECT holiday_date FROM holidays') as $row)$holidays[$row['holiday_date']]=true;
$dates=[];for($d=$end;count($dates)<20;$d=$d->modify('-1 day'))if((int)$d->format('N')<=5&&!isset($holidays[$d->format('Y-m-d')]))$dates[]=$d->format('Y-m-d');sort($dates);
$topics=[
 'employee'=>['Operations accomplishment report','Customer request handling','Inventory verification','Policy compliance checklist','Team coordination summary','Next-cycle work plan'],
 'hr'=>['Personnel records audit','Attendance reconciliation','Recruitment progress review','Leave processing review','Payroll validation checklist','Next-cycle HR plan'],
 'admin'=>['User access review','Backup verification report','Support ticket resolution','Network health checklist','Security compliance review','Next-cycle IT plan']
];
echo ($apply?'Applying':'Preview only'),': ',count($accounts),' accounts, ',count($accounts)*6,' sample assignments.',PHP_EOL;
if(!$apply){echo 'Use --apply to insert; --all-active additionally includes original active staff accounts.',PHP_EOL;exit;}
$exists=$conn->prepare('SELECT id FROM tasks WHERE assignment_key=?');
$insert=$conn->prepare('INSERT INTO tasks (employee_id,hr_id,uploaded_by,assignment_batch,assignment_key,title,description,deadline,file_attachment,file_name,file_path,file_type,submission_file,submitted_at,status,hr_score,hr_feedback,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
$createdFiles=[];$created=0;$preserved=0;$report=[];$root=dirname(__DIR__);
$write=static function(string $folder,string $name,string $text)use($root,&$createdFiles):void{
 $path=$root.'/uploads/'.$folder.'/'.$name;
 if(!is_dir(dirname($path)))throw new RuntimeException('Upload folder is missing: '.$folder);
 if(is_file($path))throw new RuntimeException('A sample filename already exists; refusing to overwrite it: '.$name);
 if(file_put_contents($path,$text,LOCK_EX)===false)throw new RuntimeException('Could not create sample file.');
 $createdFiles[]=$path;
};
$conn->begin_transaction();
try{
 $person=0;
 foreach($accounts as $id=>$user){
  $group=$user['demo_group'];
  $reviewer=$user['role']==='hr'?$system:(int)$reviewers[$person++%count($reviewers)]['id'];
  if(!validateEvaluationRecipients($conn,$reviewer,[$id]))throw new RuntimeException('Invalid evaluation reviewer/recipient pair.');
  for($n=0;$n<6;$n++){
   $key=hash('sha256','hrms-demo-evaluation-v1:'.$id.':'.$n);$exists->bind_param('s',$key);$exists->execute();if($exists->get_result()->num_rows){$preserved++;continue;}
   $batch='demo-evaluation-v1-'.$user['role'].'-'.$n;
   $title=$topics[$user['role']][$n];
   $deadline=$n===5?$now->modify('+10 days')->format('Y-m-d'):$dates[3+$n*3];
   $assigned=(new DateTimeImmutable($n===5?$now->format('Y-m-d'):$dates[$n*3],new DateTimeZone('Asia/Manila')))->setTime(9,0)->format('Y-m-d H:i:s');
   if($n===5)$assigned=$now->format('Y-m-d H:i:s');
   $group=$user['demo_group'];$state='pending';$submittedAt=null;$submission=null;$score=null;$feedback=null;
   if($n<5){
    if($group==='Excellent'){$state='reviewed';$score=[92,94,96,98,95][$n];}
    elseif($group==='Neutral'){$state=$n<3?'reviewed':($n===3?'submitted':'pending');$score=$n<3?[75,78,72][$n]:null;}
    else{$state=$n<2?'reviewed':'pending';$score=$n<2?[45,50][$n]:null;}
    if($state!=='pending'){
     $late=$group==='Low'||($group==='Neutral'&&$n===2);
     $submittedAt=(new DateTimeImmutable($deadline.' 15:00:00',new DateTimeZone('Asia/Manila')))->modify($late?'+1 day':'-1 day')->format('Y-m-d H:i:s');
     $submission='evaluation-'.substr($key,0,24).'-submission.txt';
    }
    if($state==='reviewed')$feedback=($group==='Excellent'?'Clear, accurate and complete evidence. Requirements were met with strong attention to detail. Continue the same standard.':($group==='Neutral'?'Core requirements were met. Improve supporting evidence, consistency and deadline planning. Recheck the checklist before submitting.':'Important requirements and supporting evidence are missing. Rework the incomplete sections, verify accuracy and agree a recovery plan with the reviewer.'));
   }
   $description="Objective: ".$topics[$user['role']][$n].".\nDeliverable: a written summary covering work completed, evidence checked, findings, risks and recommended next steps.\nReview criteria: accuracy, completeness, clarity and relevance (25 points each).";
   $attachment='demo-eval-'.substr($key,0,24).'-instructions.txt';$filePath='uploads/tasks/'.$attachment;$fileType='text/plain';
   $write('tasks',$attachment,$title."\n\n".$description."\n\nAssigned to: ".$user['full_name']."\nDeadline: ".$deadline."\n");
   if($submission)$write('submissions',$submission,"Evaluation Submission\n".$title."\nPrepared by: ".$user['full_name']."\n\nSummary: Reviewed the assigned checklist and documented the relevant activities.\nEvidence: Supporting records were checked and findings were documented against the assignment checklist.\nFindings: ".($group==='Excellent'?'All requirements reconciled; no unresolved discrepancies.':($group==='Neutral'?'Most requirements addressed; some evidence needs further detail.':'Several requirements remain incomplete and need follow-up.'))."\nNext steps: ".($group==='Excellent'?'Maintain the checklist and monitor the next cycle.':($group==='Neutral'?'Add supporting details and plan earlier submission.':'Complete missing sections, validate evidence and request reviewer guidance.'))."\n");
   $types='iii'.str_repeat('s',12).'iss';
   $insert->bind_param($types,$id,$reviewer,$reviewer,$batch,$key,$title,$description,$deadline,$attachment,$attachment,$filePath,$fileType,$submission,$submittedAt,$state,$score,$feedback,$assigned);
   $insert->execute();$created++;
  }
  $kpi=calculateEmployeeKpi($conn,$id);
  if($kpi['factors']['hr_rating']['score']===null || $kpi['factors']['completion']['score']===null || $kpi['factors']['deadline']['score']===null)throw new RuntimeException('Sample evaluation did not provide all work performance measures for '.$user['email']);
  $report[]=[$user['full_name'],$user['email'],$user['role'],$group,$kpi['overall'],$kpi['label'],$kpi['factors']['hr_rating']['score'],$kpi['factors']['completion']['score'],$kpi['factors']['deadline']['score']];
 }
 $conn->commit();
}catch(Throwable $e){$conn->rollback();foreach($createdFiles as $path)if(is_file($path))unlink($path);throw $e;}
echo 'Inserted ',$created,' evaluations; preserved ',$preserved,' existing demo evaluations.',PHP_EOL;
$reportPath=getenv('HRMS_DEMO_EVALUATION_REPORT');
if($reportPath){$f=fopen($reportPath,'w');if(!$f)throw new RuntimeException('Evaluations saved; summary could not be written.');fputcsv($f,['Name','Email','Role','Demo group','Overall score','Rating','Work quality','Task completion','On-time delivery']);foreach($report as $row)fputcsv($f,$row);fclose($f);}
echo 'Verified work quality, completion and on-time delivery for ',count($accounts),' accounts. All sample attachments exist.',PHP_EOL;
