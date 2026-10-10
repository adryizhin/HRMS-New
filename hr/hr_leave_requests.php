<?php
require '../includes/auth_check.php';
checkRole(['hr']);
require '../config.php';
require_once __DIR__ . '/../includes/sidebar.php';

$hr_id = (int) $_SESSION['user_id'];

require_once __DIR__ . '/../includes/leave.php';
ensureLeaveSchema($conn);
if (empty($_SESSION['leave_review_csrf'])) $_SESSION['leave_review_csrf'] = bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['leave_review_csrf'], (string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(400);
        exit('Reload the leave requests page and try again.');
    }
    $id = (int) ($_POST['id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');
    if (in_array($status, ['approved', 'rejected'], true)) {
        $conn->begin_transaction();
        try {
            $get = $conn->prepare("SELECT employee_id, leave_type, start_date, end_date FROM leave_requests WHERE id = ? AND hr_id = ? AND status = 'pending' FOR UPDATE");
            $get->bind_param('ii', $id, $hr_id);
            $get->execute();
            $data = $get->get_result()->fetch_assoc();
            if ($data) {
                $stmt = $conn->prepare("UPDATE leave_requests SET status = ? WHERE id = ? AND hr_id = ? AND status = 'pending'");
                $stmt->bind_param('sii', $status, $id, $hr_id);
                $stmt->execute();
                $message = 'Your ' . $data['leave_type'] . ' request (' . $data['start_date'] . ' to ' . $data['end_date'] . ') has been ' . strtoupper($status) . '.';
                if ($status === 'rejected') $message .= ' Reserved leave days have been restored.';
                $notif = $conn->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
                $notif->bind_param('is', $data['employee_id'], $message);
                $notif->execute();
            }
            $conn->commit();
        } catch (Throwable $exception) {
            $conn->rollback();
            http_response_code(500);
            exit('Unable to update this request. Please try again.');
        }
    }
    if (!empty($data)) $_SESSION['leave_review_notice'] = 'Leave request ' . $status . '.';
    header('Location: hr_leave_requests.php');
    exit;
}
$filter = (string)($_GET['status'] ?? 'all');
if (!in_array($filter,['all','pending','approved','rejected'],true)) $filter='all';
$filterSql = $filter === 'all' ? '' : " AND lr.status = '" . $filter . "'";
$notice = (string)($_SESSION['leave_review_notice'] ?? ''); unset($_SESSION['leave_review_notice']);
$h=static fn($value)=>htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');
// FETCH REQUESTS FOR THIS HR
$query = "
SELECT lr.*, u.full_name 
FROM leave_requests lr
JOIN users u ON lr.employee_id = u.id
WHERE lr.hr_id = $hr_id $filterSql
ORDER BY lr.id DESC
";

$result = $conn->query($query);

// COUNT CARDS
$total = $conn->query("SELECT COUNT(*) as total FROM leave_requests WHERE hr_id=$hr_id")->fetch_assoc()['total'];
$pending = $conn->query("SELECT COUNT(*) as total FROM leave_requests WHERE hr_id=$hr_id AND status='pending'")->fetch_assoc()['total'];
$approved = $conn->query("SELECT COUNT(*) as total FROM leave_requests WHERE hr_id=$hr_id AND status='approved'")->fetch_assoc()['total'];
$rejected = $conn->query("SELECT COUNT(*) as total FROM leave_requests WHERE hr_id=$hr_id AND status='rejected'")->fetch_assoc()['total'];
?>

<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Leave Requests | Human Resource</title><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"><link rel="stylesheet" href="hr_leave_requests.css"></head><body>
<?php renderSidebar('hr','hr.leave_requests'); ?><main class="main leave-main">
<header class="leave-header"><div><p class="leave-eyebrow">Human Resource</p><h1><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Leave requests</h1><p>Review staff requests and manage leave approvals.</p></div><span class="leave-welcome"><?= $h($_SESSION['name']) ?></span></header>
<?php if($notice): ?><div class="leave-notice" role="status"><?= $h($notice) ?></div><?php endif ?>
<div class="leave-stats">
<?php foreach([['Total requests',$total,'total'],['Pending review',$pending,'pending'],['Approved',$approved,'approved'],['Rejected',$rejected,'rejected']] as [$label,$count,$type]): ?><section class="leave-stat"><span class="leave-stat-label"><?= $label ?></span><strong><?= (int)$count ?></strong><span class="leave-stat-marker <?= $type ?>"><i class="fa-solid <?= ['total'=>'fa-folder-open','pending'=>'fa-clock','approved'=>'fa-circle-check','rejected'=>'fa-circle-xmark'][$type] ?>" aria-hidden="true"></i></span></section><?php endforeach ?>
</div>
<section class="leave-panel" aria-labelledby="leave-list-heading"><div class="leave-panel-header"><div><h2 id="leave-list-heading">Staff leave requests</h2><p>Only requests assigned to you appear here.</p></div><form method="get" class="leave-filter"><label for="leave-status">Status</label><select name="status" id="leave-status"><?php foreach(['all'=>'All requests','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $key=>$label): ?><option value="<?= $key ?>" <?= $key===$filter?'selected':'' ?>><?= $label ?></option><?php endforeach ?></select><button type="submit">Filter</button></form></div>
<div class="leave-table-wrap"><table class="leave-table"><thead><tr><th>Staff member</th><th>Leave type</th><th>Dates</th><th>Reason</th><th>Status</th><th>Review</th></tr></thead><tbody>
<?php if(!$result->num_rows): ?><tr class="leave-empty"><td colspan="6"><strong>No leave requests</strong><p>No requests match this status.</p></td></tr><?php endif ?>
<?php while($row=$result->fetch_assoc()): ?><tr>
<td data-label="Staff member"><strong><?= $h($row['full_name']) ?></strong></td><td data-label="Leave type"><?= $h($row['leave_type']) ?></td>
<td data-label="Dates" class="leave-dates"><span><?= $h(date('M d, Y',strtotime($row['start_date']))) ?></span><span class="leave-date-to">to <?= $h(date('M d, Y',strtotime($row['end_date']))) ?></span></td>
<td data-label="Reason" class="leave-reason"><?= nl2br($h($row['reason'])) ?></td><td data-label="Status"><span class="leave-status <?= $h($row['status']) ?>"><?= $h(ucfirst($row['status'])) ?></span></td>
<td data-label="Review"><?php if($row['status']==='pending'): ?><form method="post" class="leave-review-actions"><input type="hidden" name="csrf_token" value="<?= $h($_SESSION['leave_review_csrf']) ?>"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button type="submit" name="status" value="approved" class="leave-approve"><i class="fa-solid fa-check" aria-hidden="true"></i> Approve</button><button type="submit" name="status" value="rejected" class="leave-reject"><i class="fa-solid fa-xmark" aria-hidden="true"></i> Reject</button></form><?php else: ?><span class="leave-reviewed">Reviewed</span><?php endif ?></td>
</tr><?php endwhile ?></tbody></table></div></section></main></body></html>
