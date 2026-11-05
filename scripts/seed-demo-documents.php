<?php
// CLI-only fixture documents for all active staff. Existing documents/reviews are preserved.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/config.php';
require_once dirname(__DIR__).'/includes/employee_documents.php';
require_once dirname(__DIR__).'/includes/kpi.php';
ensureEmployeeDocumentsTable($conn);
$manifestPath=$argv[1]??'';
if(!$manifestPath)throw new RuntimeException('Supply a manifest path, optionally followed by --apply.');
$apply=in_array('--apply',$argv,true);
if(!$apply){
 $users=$conn->query("SELECT u.id,u.full_name,u.email,u.employee_id,u.role,u.work_shift,d.name AS department FROM users u LEFT JOIN departments d ON d.id=u.department_id WHERE u.status='active' AND u.role IN ('employee','hr','admin') ORDER BY u.id")->fetch_all(MYSQLI_ASSOC);
 $hrs=$conn->query("SELECT id,full_name FROM users WHERE role='hr' AND status='active' ORDER BY id")->fetch_all(MYSQLI_ASSOC);if(!$hrs)throw new RuntimeException('An active HR reviewer is required.');
 $now=new DateTimeImmutable('now',new DateTimeZone('Asia/Manila'));$records=[];
 $types=[['profile','Resume / CV','Staff Profile','verified','Profile details and work information.','Profile fields checked against the staff record.'],['responsibilities','Other','Role Responsibilities','verified','Responsibilities, reporting expectations and work standards.','Responsibilities and reporting expectations reviewed.'],['performance','Performance Record','Performance Summary','verified','Current performance measures, weights and development priorities.','Figures checked against the recorded performance indicators.'],['training','Other','Training and Development Plan','submitted','Proposed learning activities for the next review cycle.',null],['onboarding','Other','Onboarding Checklist','rejected','Checklist of onboarding requirements for completion.','Please add emergency contact details and the completed orientation acknowledgement before resubmitting.']];
 foreach($users as $i=>$user){
  $reviewer=$hrs[$i%count($hrs)];if((int)$reviewer['id']===(int)$user['id']&&count($hrs)>1)$reviewer=$hrs[($i+1)%count($hrs)];
  $kpi=calculateEmployeeKpi($conn,(int)$user['id']);
  foreach($types as $n=>[$slug,$type,$title,$status,$notes,$hrNotes]){
   $name='personnel_'.$user['id'].'_'.$slug.'.pdf';
   $records[]=['employee_id'=>(int)$user['id'],'person'=>$user,'slug'=>$slug,'document_type'=>$type,'title'=>mb_substr($title.' - '.$user['full_name'],0,160),'file_name'=>$name,'original_name'=>$title.'.pdf','file_path'=>'uploads/201_files/'.$name,'notes'=>$notes,'status'=>$status,'hr_notes'=>$hrNotes,'reviewed_by'=>$status==='submitted'?null:(int)$reviewer['id'],'reviewer_name'=>$status==='submitted'?null:$reviewer['full_name'],'reviewed_at'=>$status==='submitted'?null:$now->format('Y-m-d H:i:s'),'uploaded_at'=>$now->modify('-'.($n+1).' days')->format('Y-m-d H:i:s'),'performance'=>$kpi];
  }
 }
 file_put_contents($manifestPath,json_encode(['database'=>DB_NAME,'generated_at'=>$now->format('Y-m-d H:i:s'),'accounts'=>count($users),'records'=>$records],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
 echo 'Planned ',count($records),' documents for ',count($users),' active accounts.',PHP_EOL;exit;
}
$manifest=json_decode(file_get_contents($manifestPath),true,512,JSON_THROW_ON_ERROR);if($manifest['database']!==DB_NAME)throw new RuntimeException('Manifest belongs to a different database.');
$root=dirname(__DIR__);$inserted=0;$preserved=0;
$insert=$conn->prepare('INSERT INTO employee_documents (employee_id,document_type,title,file_name,original_name,file_path,file_type,file_size,notes,status,hr_notes,reviewed_by,reviewed_at,uploaded_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
$find=$conn->prepare('SELECT id FROM employee_documents WHERE employee_id=? AND file_name=?');
$owner=$conn->prepare("SELECT email FROM users WHERE id=? AND status='active' FOR UPDATE");
$conn->begin_transaction();
try{
 foreach($manifest['records'] as $r){
  $id=(int)$r['employee_id'];$owner->bind_param('i',$id);$owner->execute();$user=$owner->get_result()->fetch_assoc();if(!$user||$user['email']!==$r['person']['email'])throw new RuntimeException('Staff identity changed; regenerate manifest.');
  if(!preg_match('/\Apersonnel_'.$id.'_(profile|responsibilities|performance|training|onboarding)\.pdf\z/',$r['file_name'])||$r['file_path']!=='uploads/201_files/'.$r['file_name'])throw new RuntimeException('Invalid document path.');
  $find->bind_param('is',$id,$r['file_name']);$find->execute();if($find->get_result()->num_rows){$preserved++;continue;}
  $path=$root.'/'.$r['file_path'];if(!is_file($path)||(new finfo(FILEINFO_MIME_TYPE))->file($path)!=='application/pdf')throw new RuntimeException('Missing or invalid PDF: '.$r['file_name']);
  $size=filesize($path);$mime='application/pdf';
  $types='i'.str_repeat('s',6).'i'.str_repeat('s',3).'i'.'ss';
  $insert->bind_param($types,$id,$r['document_type'],$r['title'],$r['file_name'],$r['original_name'],$r['file_path'],$mime,$size,$r['notes'],$r['status'],$r['hr_notes'],$r['reviewed_by'],$r['reviewed_at'],$r['uploaded_at']);$insert->execute();$inserted++;
 }
 $conn->commit();
}catch(Throwable $e){$conn->rollback();throw $e;}
echo 'Inserted ',$inserted,' document records; preserved ',$preserved,' existing records.',PHP_EOL;
