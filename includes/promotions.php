<?php
declare(strict_types=1);
function ensurePromotionSchema(mysqli $conn): void {
 static $done=false;if($done)return;
 if(!$conn->query("SHOW COLUMNS FROM users LIKE 'job_title'")->num_rows)$conn->query('ALTER TABLE users ADD COLUMN job_title VARCHAR(120) NULL');
 $conn->query("CREATE TABLE IF NOT EXISTS promotion_suggestions(id INT AUTO_INCREMENT PRIMARY KEY,employee_id INT NOT NULL,suggested_by INT NOT NULL,current_position VARCHAR(120) NOT NULL,proposed_position VARCHAR(120) NOT NULL,reason TEXT NOT NULL,status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',decision_notes TEXT NULL,decided_by INT NULL,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,decided_at DATETIME NULL,INDEX(employee_id,status),FOREIGN KEY(employee_id) REFERENCES users(id) ON DELETE CASCADE,FOREIGN KEY(suggested_by) REFERENCES users(id) ON DELETE CASCADE,FOREIGN KEY(decided_by) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");$done=true;
}
function suggestPromotion(mysqli $conn,int $adminId,int $employeeId,string $position,string $reason): int {
 ensurePromotionSchema($conn);ensureActivitySchema($conn);$position=trim($position);$reason=trim($reason);
 if($position===''||mb_strlen($position)>120||$reason===''||mb_strlen($reason)>3000)throw new InvalidArgumentException('Enter a proposed position (up to 120 characters) and a reason (up to 3,000).');
 $admin=$conn->prepare("SELECT id FROM users WHERE id=? AND role='admin' AND status='active'");$admin->bind_param('i',$adminId);$admin->execute();if(!$admin->get_result()->num_rows)throw new InvalidArgumentException('Only active administrators may suggest promotions.');
 $conn->begin_transaction();try{
  $get=$conn->prepare("SELECT full_name,job_title,role FROM users WHERE id=? AND role IN ('employee','admin') AND status='active' FOR UPDATE");$get->bind_param('i',$employeeId);$get->execute();$user=$get->get_result()->fetch_assoc();if(!$user)throw new InvalidArgumentException('Select an active employee or IT administrator.');
  $q=$conn->prepare("SELECT id FROM promotion_suggestions WHERE employee_id=? AND status='pending'");$q->bind_param('i',$employeeId);$q->execute();if($q->get_result()->num_rows)throw new InvalidArgumentException('This employee already has a pending promotion suggestion.');
  $current=$user['job_title']?:($user['role']==='admin'?'IT Administrator':'Employee');if(mb_strtolower($position)===mb_strtolower($current))throw new InvalidArgumentException('The proposed position must differ from the current position.');
  $q=$conn->prepare('INSERT INTO promotion_suggestions(employee_id,suggested_by,current_position,proposed_position,reason) VALUES(?,?,?,?,?)');$q->bind_param('iisss',$employeeId,$adminId,$current,$position,$reason);$q->execute();$id=(int)$conn->insert_id;
  $conn->commit();return $id;
 }catch(Throwable $e){$conn->rollback();throw $e;}
}
function decidePromotion(mysqli $conn,int $hrId,int $id,string $decision,string $notes): bool {
 ensurePromotionSchema($conn);ensureActivitySchema($conn);$notes=trim($notes);
 if(!in_array($decision,['approved','rejected'],true)||$notes===''||mb_strlen($notes)>3000)throw new InvalidArgumentException('Choose a decision and enter HR notes (up to 3,000 characters).');
 $hr=$conn->prepare("SELECT id FROM users WHERE id=? AND role='hr' AND status='active'");$hr->bind_param('i',$hrId);$hr->execute();if(!$hr->get_result()->num_rows)throw new InvalidArgumentException('Only active Human Resource staff can decide promotions.');
 $find=$conn->prepare('SELECT employee_id FROM promotion_suggestions WHERE id=?');$find->bind_param('i',$id);$find->execute();$owner=$find->get_result()->fetch_assoc();if(!$owner)throw new InvalidArgumentException('Promotion suggestion not found.');$employeeId=(int)$owner['employee_id'];
 $conn->begin_transaction();try{
  $lock=$conn->prepare("SELECT id FROM users WHERE id=? AND role IN ('employee','admin') AND status='active' FOR UPDATE");$lock->bind_param('i',$employeeId);$lock->execute();if(!$lock->get_result()->num_rows)throw new InvalidArgumentException('The employee account is no longer active.');
  $get=$conn->prepare('SELECT * FROM promotion_suggestions WHERE id=? FOR UPDATE');$get->bind_param('i',$id);$get->execute();$row=$get->get_result()->fetch_assoc();if($row['status']!=='pending'){$conn->commit();return false;}
  $q=$conn->prepare('UPDATE promotion_suggestions SET status=?,decision_notes=?,decided_by=?,decided_at=NOW() WHERE id=?');$q->bind_param('ssii',$decision,$notes,$hrId,$id);$q->execute();
  if($decision==='approved'){$q=$conn->prepare('UPDATE users SET job_title=? WHERE id=?');$q->bind_param('si',$row['proposed_position'],$employeeId);$q->execute();}
  $notify=$conn->prepare("INSERT INTO notifications(user_id,message,activity_type) VALUES(?,?,'promotion')");
  foreach([$employeeId,(int)$row['suggested_by']] as $recipient){$message='Promotion suggestion to '.$row['proposed_position'].' was '.$decision.'. HR note: '.$notes;$notify->bind_param('is',$recipient,$message);$notify->execute();}
  $conn->commit();return true;
 }catch(Throwable $e){$conn->rollback();throw $e;}
}
