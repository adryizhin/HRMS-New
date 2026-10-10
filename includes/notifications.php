<?php
declare(strict_types=1);
require_once __DIR__ . '/routes.php';
require_once __DIR__ . '/task_schema.php';
require_once __DIR__ . '/password_tickets.php';
require_once __DIR__ . '/evaluation_access.php';
function ensureActivitySchema(mysqli $conn): void {
    static $done=false;if($done)return;
    $conn->query("CREATE TABLE IF NOT EXISTS notifications (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,message TEXT NOT NULL,is_read TINYINT DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,activity_type VARCHAR(20) NOT NULL DEFAULT 'leave') ENGINE=InnoDB");
    if(!$conn->query("SHOW COLUMNS FROM notifications LIKE 'activity_type'")->num_rows)$conn->query("ALTER TABLE notifications ADD COLUMN activity_type VARCHAR(20) NOT NULL DEFAULT 'leave'");
    $done=true;
}
function activitySummary(mysqli $conn,int $userId,string $role): array {
    ensureActivitySchema($conn);ensureTaskSchema($conn);
    $groups=[];$badges=[];
    $add=static function(string $key,string $label,string $routeName,string $icon,int $count) use (&$groups,&$badges): void {
        if($count<1)return;
        $groups[]=['key'=>$key,'label'=>$label,'url'=>route($routeName),'icon'=>$icon,'count'=>$count];
        $badges[$routeName]=($badges[$routeName]??0)+$count;
    };
    $count=static function(string $sql) use ($conn,$userId): int {$stmt=$conn->prepare($sql);$stmt->bind_param('i',$userId);$stmt->execute();return (int)$stmt->get_result()->fetch_row()[0];};
    $evaluationRoute=$role==='employee'?'employee.tasks':$role.'.evaluations';
    $add('evaluations','Evaluations to complete',$evaluationRoute,'fa-clipboard-list',$count("SELECT COUNT(*) FROM tasks WHERE employee_id=? AND status='pending'"));
    if($role==='hr'||($role==='admin'&&isSystemAdministrator($conn,$userId)))$add('reviews','Evaluations to review',$role==='hr'?'hr.task_submissions':'admin.review_hr_evaluations','fa-clipboard-check',$count("SELECT COUNT(*) FROM tasks WHERE hr_id=? AND status='submitted'"));
    if($role==='hr')$add('leave','Leave requests to review','hr.leave_requests','fa-umbrella',$count("SELECT COUNT(*) FROM leave_requests WHERE hr_id=? AND status='pending'"));
    if($role==='admin') {
        ensurePasswordTicketSchema($conn);
        $open=(int)$conn->query("SELECT COUNT(*) FROM password_reset_tickets WHERE status IN ('pending','in_progress')")->fetch_row()[0];
        $add('tickets','Open password-reset tickets','admin.password_tickets','fa-ticket',$open);
    }
    $unread=$count('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0');
    $stmt=$conn->prepare('SELECT id,message,created_at,activity_type FROM notifications WHERE user_id=? AND is_read=0 ORDER BY id DESC LIMIT 10');$stmt->bind_param('i',$userId);$stmt->execute();
    $updates=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach($updates as &$update)$update['url']=route($update['activity_type']==='evaluation'?$evaluationRoute:($role==='employee'?'employee.leave_request':$role.'.dashboard'));unset($update);
    $stmt=$conn->prepare('SELECT activity_type,COUNT(*) AS total FROM notifications WHERE user_id=? AND is_read=0 GROUP BY activity_type');$stmt->bind_param('i',$userId);$stmt->execute();
    foreach($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row){$routeName=$row['activity_type']==='evaluation'?$evaluationRoute:($role==='employee'?'employee.leave_request':$role.'.dashboard');$badges[$routeName]=($badges[$routeName]??0)+(int)$row['total'];}
    return ['groups'=>$groups,'updates'=>$updates,'unreadUpdates'=>$unread,'badges'=>$badges,'total'=>array_sum(array_column($groups,'count'))+$unread];
}
