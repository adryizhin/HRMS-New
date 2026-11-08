<?php
declare(strict_types=1);
function ensurePayrollProfiles(mysqli $conn): void {
 static $done=false;if($done)return;
 $conn->query("CREATE TABLE IF NOT EXISTS payroll_profiles(user_id INT PRIMARY KEY,is_tenured TINYINT NOT NULL DEFAULT 0,allowance DECIMAL(10,2) NOT NULL DEFAULT 0,effective_from DATE NULL,notes VARCHAR(1000) NOT NULL DEFAULT '',updated_by INT NULL,updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");$done=true;
}
function payrollDate(string $value): string {
 $d=preg_match('/\A[1-9][0-9]{3}-[0-9]{2}-[0-9]{2}\z/',$value)?DateTimeImmutable::createFromFormat('!Y-m-d',$value):false;
 if(!$d||$d->format('Y-m-d')!==$value)throw new InvalidArgumentException('Select a valid payroll date.');return $value;
}
function payrollCutoff(string $selected,string $anchor='start'): array {
 $date=new DateTimeImmutable(payrollDate($selected));$start=$anchor==='end'?$date->modify('-14 days'):$date;$end=$start->modify('+14 days');
 return [payrollDate($start->format('Y-m-d')),payrollDate($end->format('Y-m-d'))];
}
function payrollProfile(mysqli $conn,int $userId): array {
 ensurePayrollProfiles($conn);$q=$conn->prepare('SELECT * FROM payroll_profiles WHERE user_id=?');$q->bind_param('i',$userId);$q->execute();return $q->get_result()->fetch_assoc()?:['is_tenured'=>0,'allowance'=>0,'effective_from'=>null,'notes'=>''];
}
function payrollAllowance(array $profile,string $cutoffEnd): float {
 return !empty($profile['is_tenured'])&&(!$profile['effective_from']||$profile['effective_from']<=$cutoffEnd)?(float)$profile['allowance']:0.0;
}
function savePayrollProfile(mysqli $conn,int $hrId,int $userId,array $input): void {
 ensurePayrollProfiles($conn);$hr=$conn->prepare("SELECT id FROM users WHERE id=? AND role='hr' AND status='active'");$hr->bind_param('i',$hrId);$hr->execute();if(!$hr->get_result()->num_rows)throw new InvalidArgumentException('Only active Human Resource staff can configure allowances.');
 $eligible=!empty($input['is_tenured'])?1:0;$amount=$input['allowance_amount']??'';
 if(!is_string($amount)||!preg_match('/\A\d{1,8}(?:\.\d{1,2})?\z/',$amount))throw new InvalidArgumentException('Enter a non-negative allowance amount with up to two decimal places.');
 $amount=(float)$amount;$effective=$input['effective_from']??'';if(!is_string($effective))throw new InvalidArgumentException('Select a valid effective date.');$effective=$effective!==''?payrollDate($effective):null;
 $notes=$input['allowance_notes']??'';if(!is_string($notes)||mb_strlen($notes)>1000)throw new InvalidArgumentException('Allowance notes must be within 1,000 characters.');$notes=trim($notes);
 $person=$conn->prepare("SELECT id FROM users WHERE id=? AND status='active' AND role IN ('employee','hr','admin')");$person->bind_param('i',$userId);$person->execute();if(!$person->get_result()->num_rows)throw new InvalidArgumentException('Open an active staff payroll first.');
 $q=$conn->prepare('INSERT INTO payroll_profiles(user_id,is_tenured,allowance,effective_from,notes,updated_by) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE is_tenured=VALUES(is_tenured),allowance=VALUES(allowance),effective_from=VALUES(effective_from),notes=VALUES(notes),updated_by=VALUES(updated_by)');$q->bind_param('iidssi',$userId,$eligible,$amount,$effective,$notes,$hrId);$q->execute();
}
