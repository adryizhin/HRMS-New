<?php
// Populate regularization suggestions for the existing named sample accounts.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/config.php';
require_once dirname(__DIR__).'/includes/notifications.php';
ensurePromotionSchema($conn);
ensureActivitySchema($conn);
$apply = in_array('--apply', $argv, true);
$groups = require dirname(__DIR__).'/config/demo_accounts.php';
$lookup = $conn->prepare("SELECT id,full_name,job_title FROM users WHERE email=? AND role=? AND status='active'");
$accounts = [];
foreach (['employee','admin'] as $role) {
    [$prefix,$slugs] = $groups[$role];
    foreach ($slugs as $slug) {
        $email = $prefix.'.'.$slug.'@pagecom.com';
        $lookup->bind_param('ss',$email,$role);
        $lookup->execute();
        $person = $lookup->get_result()->fetch_assoc();
        if (!$person) throw new RuntimeException('Required active account not found: '.$email);
        $accounts[$role][] = $person;
    }
}
$existing = $conn->prepare("SELECT id FROM promotion_suggestions WHERE employee_id=? AND (status='pending' OR proposed_position='Regular Employee') LIMIT 1");
$plan = [];
foreach ($accounts['employee'] as $index => $employee) {
    $id = (int)$employee['id'];
    $existing->bind_param('i',$id);
    $existing->execute();
    if ($existing->get_result()->num_rows || strcasecmp(trim($employee['job_title']??''),'Regular Employee')===0) {
        echo 'Preserved existing suggestion or regular position: '.$employee['full_name'].PHP_EOL;
        continue;
    }
    $admin = $accounts['admin'][$index % count($accounts['admin'])];
    $plan[] = [$employee,$admin];
    echo $admin['full_name'].' -> '.$employee['full_name'].' -> Regular Employee (pending HR review)'.PHP_EOL;
}
echo count($plan).' suggestion(s) '.($apply?'to add.':'planned. Use --apply to insert.').PHP_EOL;
if (!$apply) exit;
$before = $conn->query('SELECT id,job_title,role,status FROM users ORDER BY id')->fetch_all(MYSQLI_ASSOC);
$verify = $conn->prepare('SELECT status,decided_by,decided_at FROM promotion_suggestions WHERE id=?');
$created = 0;
foreach ($plan as [$employee,$admin]) {
    $reason = 'Recommend '.$employee['full_name'].' for regular employee status, subject to Human Resource review of the performance appraisal, attendance record, completed requirements, and applicable regularization criteria. Please record the final decision and supporting notes.';
    $id = suggestPromotion($conn,(int)$admin['id'],(int)$employee['id'],'Regular Employee',$reason);
    $verify->bind_param('i',$id);
    $verify->execute();
    $row = $verify->get_result()->fetch_assoc();
    if (!$row || $row['status']!=='pending' || $row['decided_by']!==null || $row['decided_at']!==null) throw new RuntimeException('Suggestion was not saved as pending.');
    $created++;
}
$after = $conn->query('SELECT id,job_title,role,status FROM users ORDER BY id')->fetch_all(MYSQLI_ASSOC);
if ($before!==$after) throw new RuntimeException('Unexpected account changes detected.');
echo 'Created '.$created.' pending suggestions. Verified account positions, roles and statuses unchanged.'.PHP_EOL;
