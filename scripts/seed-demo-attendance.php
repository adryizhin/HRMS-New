<?php
/** CLI-only attendance fixtures for the 30 named demo accounts. Existing rows are preserved. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/attendance.php';
ensureAttendanceTable($conn);
$apply = in_array('--apply', $argv, true);
$groups = require dirname(__DIR__) . '/config/demo_accounts.php';
$now = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
$end = $now->setTime(0,0);
if ($now < $end->setTime(17,0)) $end=$end->modify('-1 day');
// Read the configured calendar without importing holidays or altering other accounts' attendance.
$holidays=[];
foreach($conn->query('SELECT holiday_date FROM holidays') as $holiday)$holidays[$holiday['holiday_date']]=true;
$dates=[];
for($day=$end;count($dates)<30;$day=$day->modify('-1 day')){
 if((int)$day->format('N')<=5&&!isset($holidays[$day->format('Y-m-d')]))$dates[]=$day->format('Y-m-d');
}
sort($dates);
$find=$conn->prepare("SELECT id,full_name,email,employee_id,role,work_shift FROM users WHERE email=? AND role=? AND status='active'");
$accounts=[];
foreach($groups as $role=>[$prefix,$names])foreach($names as $i=>$slug){
 $email=$prefix.'.'.$slug.'@pagecom.com';$find->bind_param('ss',$email,$role);$find->execute();$user=$find->get_result()->fetch_assoc();
 if(!$user||$user['work_shift']!=='morning')throw new RuntimeException('Expected active morning-shift demo account unavailable: '.$email.'. Nothing inserted.');
 $user['demo_group']=$i<3?'Excellent':($i<7?'Neutral':'Low');$user['offset']=$i;$accounts[]=$user;
}
echo ($apply?'Applying':'Preview only'),': 30 demo accounts; ',reset($dates),' to ',end($dates),' (30 working days).',PHP_EOL;
if(!$apply){echo 'Run with --apply to add missing demo attendance. Existing records will never be overwritten.',PHP_EOL;exit;}
$insert=$conn->prepare('INSERT INTO attendance (user_id,attendance_date,shift,login_time,status,late_minutes,created_at) VALUES (?, ?, \'morning\', ?, ?, ?, ?)');
$existing=$conn->prepare("SELECT id FROM attendance WHERE user_id=? AND attendance_date=? AND shift='morning'");
$summary=$conn->prepare("SELECT COUNT(*) AS total,SUM(status='present') AS present,SUM(status='late') AS late,SUM(status='absent') AS absent FROM attendance WHERE user_id=? AND shift='morning' AND attendance_date BETWEEN ? AND ?");
$first=reset($dates);$last=end($dates);$inserted=0;$preserved=0;$report=[];
$conn->begin_transaction();
try{
 foreach($accounts as $account){
  $id=(int)$account['id'];
  foreach($dates as $n=>$date){
   $existing->bind_param('is',$id,$date);$existing->execute();if($existing->get_result()->num_rows){$preserved++;continue;}
   $cycle=($n+$account['offset'])%10;
   $status=$account['demo_group']==='Excellent'?'present':($account['demo_group']==='Neutral'?($cycle<6?'present':($cycle<8?'late':'absent')):($cycle<3?'present':($cycle<5?'late':'absent')));
   $minutes=0;
   if($status==='present')$login=$date.' 08:'.str_pad((string)(($n+$account['offset'])%25),2,'0',STR_PAD_LEFT).':00';
   elseif($status==='late'){$minutes=15+(($n+$account['offset'])%31);$login=(new DateTimeImmutable($date.' 08:30:00',new DateTimeZone('Asia/Manila')))->modify('+'.$minutes.' minutes')->format('Y-m-d H:i:s');}
   else $login=$date.' 17:00:00';
   $insert->bind_param('isssis',$id,$date,$login,$status,$minutes,$login);
   try{$insert->execute();$inserted++;}catch(mysqli_sql_exception $e){if($e->getCode()!==1062)throw $e;$preserved++;}
  }
  $summary->bind_param('iss',$id,$first,$last);$summary->execute();$totals=$summary->get_result()->fetch_assoc();
  if((int)$totals['total']!==30)throw new RuntimeException('Incomplete demo attendance for '.$account['email']);
  $score=round(((int)$totals['present']+0.75*(int)$totals['late'])/30*100,1);
  $report[]=[$account['full_name'],$account['email'],$account['role'],$account['demo_group'],$first,$last,(int)$totals['present'],(int)$totals['late'],(int)$totals['absent'],$score.'%'];
 }
 $conn->commit();
}catch(Throwable $e){$conn->rollback();throw $e;}
echo 'Inserted ',$inserted,' attendance records; preserved ',$preserved,' existing records.',PHP_EOL;
foreach(['Excellent','Neutral','Low'] as $group){$scores=[];foreach($report as $row)if($row[3]===$group)$scores[]=$row[9];echo $group,': ',count($scores),' accounts; attendance score(s): ',implode(', ',array_unique($scores)),PHP_EOL;}
$reportPath=getenv('HRMS_DEMO_ATTENDANCE_REPORT');
if($reportPath){$file=fopen($reportPath,'w');if(!$file)throw new RuntimeException('Attendance saved; could not write summary CSV.');fputcsv($file,['Name','Email','Role','Demo group','Start date','End date','Present','Late','Absent','Attendance score']);foreach($report as $row)fputcsv($file,$row);fclose($file);echo 'Summary saved.',PHP_EOL;}
