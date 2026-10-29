<?php
require '../includes/auth_check.php';
checkRole(['hr']);
require '../config.php';
require_once __DIR__ . '/../includes/sidebar.php';

$hr_id = (int) $_SESSION['user_id'];

require_once __DIR__ . '/../includes/leave.php';
ensureLeaveSchema($conn);
if (empty($_SESSION['leave_review_csrf'])) $_SESSION['leave_review_csrf'] = bin2hex(random_bytes(32));
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['leave_review_csrf'], (string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(400);
        exit('Reload the leave requests page and try again.');
    }
    try {
        $changed = reviewLeaveRequest($conn, $hr_id, (int)($_POST['id']??0), (string)($_POST['status']??''), (string)($_POST['review_notes']??''));
        $_SESSION['leave_review_notice'] = $changed ? 'Leave request ' . $_POST['status'] . '. The employee has been notified.' : 'This request was already reviewed. Its decision and balance were not changed.';
        $returnFilter = $_POST['return_status'] ?? 'all';
        if (!in_array($returnFilter, ['all','pending','approved','rejected'],true)) $returnFilter='all';
        header('Location: hr_leave_requests.php?status=' . $returnFilter); exit;
    } catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); }
    catch (Throwable $exception) { $error = 'Unable to update this request. No decision was saved. Please try again.'; }
}
$filter = (string)($_GET['status'] ?? 'all');
if (!in_array($filter,['all','pending','approved','rejected'],true)) $filter='all';
$filterSql = $filter === 'all' ? '' : " AND lr.status = '" . $filter . "'";
$notice = (string)($_SESSION['leave_review_notice'] ?? ''); unset($_SESSION['leave_review_notice']);
$h=static fn($value)=>htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');
// FETCH REQUESTS FOR THIS HR
$query = "
SELECT lr.*, u.full_name, u.employee_id AS staff_number, u.email, d.name AS department_name,
 reviewer.full_name AS reviewer_name,
 (SELECT COUNT(*) FROM leave_request_days days WHERE days.request_id=lr.id) AS working_days
FROM leave_requests lr
JOIN users u ON lr.employee_id = u.id
LEFT JOIN departments d ON d.id=u.department_id
LEFT JOIN users reviewer ON reviewer.id=lr.reviewed_by
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

<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Leave Requests | Human Resource</title><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"><link rel="stylesheet" href="hr_leave_requests.css?v=20261029"></head><body>
<?php renderSidebar('hr','hr.leave_requests'); ?><main class="main leave-main">
<header class="leave-header"><div><p class="leave-eyebrow">Human Resource</p><h1><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Leave requests</h1><p>Review staff requests and manage leave approvals.</p></div><span class="leave-welcome"><?= $h($_SESSION['name']) ?></span></header>
<?php if($error): ?><div class="leave-notice" role="alert"><?= $h($error) ?></div><?php endif ?>
<?php if($notice): ?><div class="leave-notice" role="status"><?= $h($notice) ?></div><?php endif ?>
<div class="leave-stats">
<?php foreach([['Total requests',$total,'total'],['Pending review',$pending,'pending'],['Approved',$approved,'approved'],['Rejected',$rejected,'rejected']] as [$label,$count,$type]): ?><section class="leave-stat"><span class="leave-stat-label"><?= $label ?></span><strong><?= (int)$count ?></strong><span class="leave-stat-marker <?= $type ?>"><i class="fa-solid <?= ['total'=>'fa-folder-open','pending'=>'fa-clock','approved'=>'fa-circle-check','rejected'=>'fa-circle-xmark'][$type] ?>" aria-hidden="true"></i></span></section><?php endforeach ?>
</div>
<section class="leave-panel" aria-labelledby="leave-list-heading"><div class="leave-panel-header"><div><h2 id="leave-list-heading">Staff leave requests</h2><p>Only requests assigned to you appear here.</p></div><form method="get" class="leave-filter"><label for="leave-status">Status</label><select name="status" id="leave-status"><?php foreach(['all'=>'All requests','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $key=>$label): ?><option value="<?= $key ?>" <?= $key===$filter?'selected':'' ?>><?= $label ?></option><?php endforeach ?></select><button type="submit">Filter</button></form></div>
<div class="leave-table-wrap"><table class="leave-table"><thead><tr><th>Staff member</th><th>Leave type</th><th>Dates</th><th>Reason</th><th>Status</th><th>Review</th></tr></thead><tbody>
<?php if(!$result->num_rows): ?><tr class="leave-empty"><td colspan="6"><strong>No leave requests</strong><p>No requests match this status.</p></td></tr><?php endif ?>
<?php while($row=$result->fetch_assoc()): ?><tr>
<td data-label="Staff member"><strong><?= $h($row['full_name']) ?></strong><small class="leave-detail"><?= $h($row['staff_number']?:'No employee ID') ?> · <?= $h($row['department_name']??'Unassigned') ?></small><small class="leave-detail"><?= $h($row['email']) ?></small></td><td data-label="Leave type"><?= $h($row['leave_type']) ?></td>
<td data-label="Dates" class="leave-dates"><span><?= $h(date('M d, Y',strtotime($row['start_date']))) ?></span><span class="leave-date-to">to <?= $h(date('M d, Y',strtotime($row['end_date']))) ?></span><small class="leave-detail"><?= (int)$row['working_days'] ?> working day(s)</small><small class="leave-detail">Submitted <?= $h($row['created_at']) ?></small></td>
<td data-label="Reason" class="leave-reason"><?= nl2br($h($row['reason'])) ?></td><td data-label="Status"><span class="leave-status <?= $h($row['status']) ?>"><?= $h(ucfirst($row['status'])) ?></span></td>
<td data-label="Review"><?php if($row['status']==='pending'): ?><form method="post" class="leave-review-actions"><input type="hidden" name="return_status" value="<?= $h($filter) ?>"><label for="review-notes-<?= (int)$row['id'] ?>">Review note (optional; visible to employee)</label><textarea id="review-notes-<?= (int)$row['id'] ?>" name="review_notes" rows="3" maxlength="2000" placeholder="Explain your decision or provide instructions."><?= (int)($_POST['id']??0)===(int)$row['id'] ? $h($_POST['review_notes']??'') : '' ?></textarea><input type="hidden" name="csrf_token" value="<?= $h($_SESSION['leave_review_csrf']) ?>"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button type="submit" name="status" value="approved" class="leave-approve"><i class="fa-solid fa-check" aria-hidden="true"></i> Approve</button><button type="submit" name="status" value="rejected" class="leave-reject"><i class="fa-solid fa-xmark" aria-hidden="true"></i> Reject</button></form><?php else: ?><div class="leave-reviewed"><?php if($row['reviewed_at']): ?>Reviewed <?= $h($row['reviewed_at']) ?><br>By <?= $h($row['reviewer_name']??'Human Resource') ?><?php else: ?>Reviewed (legacy record)<?php endif ?><?php if($row['review_notes']): ?><p><strong>HR note:</strong> <?= nl2br($h($row['review_notes'])) ?></p><?php endif ?><?php if($row['status']==='rejected'): ?><p>Reserved days restored.</p><?php endif ?></div><?php endif ?></td>
</tr><?php endwhile ?></tbody></table></div></section></main></body></html>
