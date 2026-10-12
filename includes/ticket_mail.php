<?php
declare(strict_types=1);
function ticketMailSettings(): array {
    $file=__DIR__.'/../config/mail.local.php';$settings=is_file($file)?require $file:[];
    $defaults=['enabled'=>false,'host'=>'smtp.gmail.com','port'=>587,'encryption'=>'tls','username'=>'','password'=>'','from_email'=>'','from_name'=>'Pagecom HRIS'];
    foreach($defaults as $key=>$default){$env=getenv('HRMS_MAIL_'.strtoupper($key));$settings[$key]=$env!==false?$env:($settings[$key]??$default);}
    return $settings;
}
function ticketMailConfigured(): bool {
    $s=ticketMailSettings();if(!filter_var($s['enabled'],FILTER_VALIDATE_BOOL))return false;$loopback=in_array($s['host'],['127.0.0.1','localhost','::1'],true);
    return filter_var($s['from_email'],FILTER_VALIDATE_EMAIL)!==false && ($loopback||($s['username']!==''&&$s['password']!=='')) && (in_array($s['encryption'],['tls','ssl'],true)||($loopback&&$s['encryption']==='none'));
}
function ensureTicketMailSchema(mysqli $conn): void {
    $conn->query("CREATE TABLE IF NOT EXISTS password_ticket_emails (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,ticket_id INT NOT NULL,recipient VARCHAR(191) NOT NULL,
        ticket_status VARCHAR(20) NOT NULL,subject VARCHAR(255) NOT NULL,body TEXT NOT NULL,
        delivery_status ENUM('queued','sending','sent','failed') NOT NULL DEFAULT 'queued',attempts INT NOT NULL DEFAULT 0,
        last_error VARCHAR(255) NULL,created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,sent_at DATETIME NULL,claimed_at DATETIME NULL,
        UNIQUE KEY unique_ticket_status_email(ticket_id,ticket_status),INDEX(delivery_status),
        FOREIGN KEY(ticket_id) REFERENCES password_reset_tickets(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
function queueTicketStatusEmail(mysqli $conn,int $ticketId,string $recipient,string $status): void {
    if($recipient==='')return;
    if(!filter_var($recipient,FILTER_VALIDATE_EMAIL)||strlen($recipient)>191)throw new InvalidArgumentException('Enter a valid active email for ticket updates.');
    $labels=['pending'=>'Open / Pending','in_progress'=>'Open / In progress','resolved'=>'Resolved','rejected'=>'Rejected'];
    if(!isset($labels[$status]))throw new InvalidArgumentException('Invalid ticket email status.');
    $label=$labels[$status];$subject='Pagecom HRIS - Password reset ticket #'.$ticketId.' - '.$label;
    $instructions=match($status){'pending'=>'Your request has been received and is waiting for an administrator.','in_progress'=>'An administrator is reviewing your request.','resolved'=>'Your request has been resolved. Contact your administrator through your usual channel for help signing in.','rejected'=>'Your request has been closed without a password reset. Contact your administrator for further help.'};
    $body="Password reset ticket #".$ticketId."\nStatus: ".$label."\n\n".$instructions."\n\nAn administrator must verify your identity before resetting an account. This email never contains a password. If you did not request this, please ignore it.\n\nPagecom HRIS";
    $stmt=$conn->prepare('INSERT IGNORE INTO password_ticket_emails (ticket_id,recipient,ticket_status,subject,body) VALUES (?,?,?,?,?)');$stmt->bind_param('issss',$ticketId,$recipient,$status,$subject,$body);$stmt->execute();
}
function deliverTicketEmails(mysqli $conn,?int $ticketId=null,int $limit=3): array {
    $result=['sent'=>0,'failed'=>0];if(!ticketMailConfigured())return $result;
    require_once __DIR__.'/../vendor/autoload.php';$settings=ticketMailSettings();
    $where="(delivery_status IN ('queued','failed') OR (delivery_status='sending' AND claimed_at<DATE_SUB(NOW(),INTERVAL 5 MINUTE)))";
    if($ticketId!==null)$where.=' AND ticket_id='.(int)$ticketId;
    $rows=$conn->query('SELECT id FROM password_ticket_emails WHERE '.$where.' ORDER BY id LIMIT '.max(1,min(3,$limit)))->fetch_all(MYSQLI_ASSOC);
    foreach($rows as $row){
        $id=(int)$row['id'];$claim=$conn->prepare("UPDATE password_ticket_emails SET delivery_status='sending',claimed_at=NOW(),attempts=attempts+1 WHERE id=? AND ".$where);$claim->bind_param('i',$id);$claim->execute();if($claim->affected_rows!==1)continue;
        $mailRow=$conn->query('SELECT * FROM password_ticket_emails WHERE id='.$id)->fetch_assoc();
        try{
            $mail=new PHPMailer\PHPMailer\PHPMailer(true);$mail->isSMTP();$mail->Host=$settings['host'];$mail->Port=(int)$settings['port'];
            $mail->SMTPAuth=$settings['username']!==''&&$settings['password']!=='';$mail->Username=$settings['username'];$mail->Password=$settings['password'];
            $mail->SMTPSecure=$settings['encryption']==='none'?'':$settings['encryption'];$mail->SMTPAutoTLS=$settings['encryption']!=='none';
            $mail->Timeout=8;$mail->Timelimit=8;$mail->CharSet='UTF-8';$mail->setFrom($settings['from_email'],$settings['from_name']);$mail->addAddress($mailRow['recipient']);
            $mail->Subject=$mailRow['subject'];$mail->Body=$mailRow['body'];$mail->send();
            $conn->query("UPDATE password_ticket_emails SET delivery_status='sent',sent_at=NOW(),last_error=NULL WHERE id=".$id);$result['sent']++;
        }catch(Throwable $error){$conn->query("UPDATE password_ticket_emails SET delivery_status='failed',last_error='Email delivery failed. Check sender settings and retry.' WHERE id=".$id);$result['failed']++;}
    }
    return $result;
}
