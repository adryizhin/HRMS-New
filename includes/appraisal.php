<?php
declare(strict_types=1);
function appraisalCriteria(): array {
 return [
 'job_knowledge'=>['title'=>'I. Job Knowledge','weight'=>20,'prompt'=>'Knowledge gained through experience, education and specialized training. Does the employee understand the functions, requirements and responsibilities of the job?','levels'=>[4=>'Thorough knowledge of all aspects of the job; requires no instructions or assistance.',3=>'Thorough knowledge of almost all aspects; performs with a small amount of supervision.',2=>'Sufficient knowledge in general; occasionally needs direction.',1=>'Insufficient knowledge; needs further training.']],
 'performance'=>['title'=>'II. Performance','weight'=>20,'prompt'=>'Accuracy, neatness, dependability, productivity, meeting schedules and handling additional assignments.','levels'=>[4=>'Exceptionally neat and accurate; exceeds accepted output standards and handles additional assignments.',3=>'Work seldom needs checking; output occasionally exceeds job demands.',2=>'Satisfactory work, usually neat; occasional errors and completion within a reasonable time.',1=>'Too many errors; output and reliability need improvement.']],
 'customer_relation'=>['title'=>'III. Customer Relation','weight'=>15,'prompt'=>'Willingness to serve customers and subscribers, with concern for completing the work they require.','levels'=>[4=>'Very high willingness and interest; completing customer work is a primary concern.',3=>'More than normal interest and takes pride in customer work.',2=>'Normal interest and respect for customer requirements.',1=>'Shows no interest in customer requirements.']],
 'loyalty'=>['title'=>'IV. Loyalty','weight'=>15,'prompt'=>'Respect for Pagecom and contribution to the goodwill of the company, fellow employees and superiors.','levels'=>[4=>'Shows strong respect for the company and works for the common good.',3=>'Cooperates willingly and fits easily in a group.',2=>'Normally respectful and cooperative.',1=>'Very uncooperative; conduct undermines company dignity.']],
 'work_attitude'=>['title'=>'V. Work Attitude','weight'=>15,'prompt'=>'Interest in work and concern for successfully completing assigned responsibilities.','levels'=>[4=>'Very high interest and genuine concern for successful completion of tasks.',3=>'More than normal interest and takes pride in the job.',2=>'Normal interest in the work ordinarily expected.',1=>'Shows no interest in the job or company activities.']],
 'attendance'=>['title'=>'VI. Attendance','weight'=>15,'prompt'=>'Regularity and punctuality, time away from work and proper observance of break periods.','levels'=>[4=>'Perfect attendance and no recorded lateness.',3=>'Very seldom absent or late.',2=>'Normally regular and punctual.',1=>'Frequently absent or late.']],
 ];
}
function ensureAppraisalSchema(mysqli $conn): void {
 static $done=false;if($done)return;
 $conn->query("CREATE TABLE IF NOT EXISTS task_appraisals (
 task_id INT NOT NULL PRIMARY KEY,employee_id INT NOT NULL,evaluator_id INT NOT NULL,
 payload MEDIUMTEXT NOT NULL,total_score DECIMAL(5,2) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE,
 FOREIGN KEY(employee_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(evaluator_id) REFERENCES users(id) ON DELETE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
 $column=$conn->query("SHOW COLUMNS FROM tasks LIKE 'hr_score'")->fetch_assoc();
 if(strtolower($column['Type'])!=='decimal(5,2)')$conn->query('ALTER TABLE tasks MODIFY hr_score DECIMAL(5,2) NULL');
 $done=true;
}
function validateAppraisal(array $input): array {
 $data=[];
 $limits=['position'=>120,'department'=>150,'strengths'=>4000,'weaknesses'=>4000,'recommendations'=>4000,'potential'=>4000,'regularization_position'=>120,'promotion_position'=>120,'promotion_readiness'=>500,'evaluator_position'=>120,'reviewer_name'=>160,'reviewer_position'=>120,'approver_name'=>160,'approver_position'=>120];
 foreach($limits as $key=>$limit){$value=$input[$key]??'';if(!is_string($value)||mb_strlen($value)>$limit)throw new InvalidArgumentException('Check the length and format of the appraisal fields.');$data[$key]=trim($value);}
 foreach(['date_hired','period_start','period_end','accomplished_date','regularization_date','reappraisal_date','evaluator_date','reviewer_date','approver_date'] as $key){
  $value=$input[$key]??'';if(!is_string($value))throw new InvalidArgumentException('Enter valid appraisal dates.');
  if($value!==''){$date=DateTimeImmutable::createFromFormat('!Y-m-d',$value);if(!$date||$date->format('Y-m-d')!==$value)throw new InvalidArgumentException('Enter valid appraisal dates.');}
  $data[$key]=$value;
 }
 if(!$data['period_start']||!$data['period_end']||!$data['accomplished_date']||$data['period_start']>$data['period_end']||$data['period_end']>$data['accomplished_date'])throw new InvalidArgumentException('Set an appraisal period in order, ending on or before the date accomplished.');
 if(!$data['evaluator_date'])$data['evaluator_date']=$data['accomplished_date'];
 if($data['date_hired']&&$data['date_hired']>$data['period_end'])throw new InvalidArgumentException('Date hired cannot follow the appraised period.');
 if($data['regularization_date']&&$data['date_hired']&&$data['regularization_date']<$data['date_hired'])throw new InvalidArgumentException('Regularization cannot precede the date hired.');
 if(!$data['position']||!$data['department'])throw new InvalidArgumentException('Enter the employee position and department.');
 $total=0.0;$ratings=$input['ratings']??[];$comments=$input['comments']??[];
 if(!is_array($ratings)||!is_array($comments))throw new InvalidArgumentException('Select a rating for each appraisal section.');
 foreach(appraisalCriteria() as $key=>$criterion){
  $rating=filter_var($ratings[$key]??null,FILTER_VALIDATE_INT);
  if(!in_array($rating,[1,2,3,4],true))throw new InvalidArgumentException('Select a rating for all six appraisal sections.');
  $comment=$comments[$key]??'';if(!is_string($comment)||mb_strlen($comment)>2000)throw new InvalidArgumentException('Section comments must be at most 2,000 characters.');
  $data['ratings'][$key]=$rating;$data['comments'][$key]=trim($comment);$total+=($rating/4)*$criterion['weight'];
 }
 $allowed=['regularization','promotion','experience','meets_requirements','needs_improvement','reappraisal'];
 $development=$input['development']??[];if(!is_array($development)||count(array_filter($development,'is_string'))!==count($development)||array_diff($development,$allowed))throw new InvalidArgumentException('Choose valid employee development recommendations.');
 $data['development']=array_values(array_unique($development));
 if(in_array('reappraisal',$development,true)&&!$data['reappraisal_date'])throw new InvalidArgumentException('Enter the next appraisal date.');
 if(in_array('regularization',$development,true)&&!$data['regularization_position'])throw new InvalidArgumentException('Enter the recommended regularization position.');
 if(in_array('promotion',$development,true)&&!$data['promotion_position'])throw new InvalidArgumentException('Enter the proposed promotion position.');
 return ['data'=>$data,'score'=>round($total,2)];
}
function appraisalFeedback(array $data): string {
 $parts=[];foreach(['strengths'=>'Strengths','weaknesses'=>'Weaknesses','recommendations'=>'Recommendations to improve performance','potential'=>'Comments and estimate of potential'] as $key=>$title)if($data[$key]!=='')$parts[]=$title.":\n".$data[$key];
 return implode("\n\n",$parts);
}
function saveTaskAppraisal(mysqli $conn,int $taskId,int $evaluator,array $input): float {
 ensureAppraisalSchema($conn);$validated=validateAppraisal($input);$data=$validated['data'];$score=$validated['score'];
 $payload=json_encode($data,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);$feedback=appraisalFeedback($data);
 $conn->begin_transaction();
 try{
  $get=$conn->prepare('SELECT employee_id,status,submission_file FROM tasks WHERE id=? AND hr_id=? FOR UPDATE');$get->bind_param('ii',$taskId,$evaluator);$get->execute();$task=$get->get_result()->fetch_assoc();
  if(!$task)throw new InvalidArgumentException('This evaluation is unavailable or assigned to another reviewer.');
  if(!$task['submission_file'])throw new InvalidArgumentException('A submitted evaluation is required before saving the final appraisal.');
  $save=$conn->prepare('INSERT INTO task_appraisals (task_id,employee_id,evaluator_id,payload,total_score) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE employee_id=VALUES(employee_id),evaluator_id=VALUES(evaluator_id),payload=VALUES(payload),total_score=VALUES(total_score)');
  $save->bind_param('iiisd',$taskId,$task['employee_id'],$evaluator,$payload,$score);$save->execute();
  $update=$conn->prepare("UPDATE tasks SET hr_score=?,hr_feedback=?,status='reviewed' WHERE id=? AND hr_id=?");$update->bind_param('dsii',$score,$feedback,$taskId,$evaluator);$update->execute();
  if($task['status']!=='reviewed'){$message='Your evaluation has been reviewed using the Employee Performance Appraisal form.';$notify=$conn->prepare("INSERT INTO notifications (user_id,message,activity_type) VALUES (?,?,'evaluation')");$notify->bind_param('is',$task['employee_id'],$message);$notify->execute();}
  $conn->commit();return $score;
 }catch(Throwable $e){$conn->rollback();throw $e;}
}
