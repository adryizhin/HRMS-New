<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['admin']);
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/password_tickets.php';
header('Cache-Control: no-store');
ensurePasswordTicketSchema($conn);
$_SESSION['admin_password_ticket_csrf'] ??= bin2hex(random_bytes(32));
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['admin_password_ticket_csrf'], (string) ($_POST['csrf_token'] ?? ''))) {
            http_response_code(400); throw new InvalidArgumentException('Reload the page and try again.');
        }
        handlePasswordTicket($conn, (int) $_SESSION['user_id'], (int) ($_POST['ticket_id'] ?? 0), (string) ($_POST['action'] ?? ''), trim((string) ($_POST['notes'] ?? '')), (string) ($_POST['password'] ?? ''), (string) ($_POST['confirmation'] ?? ''), ($_POST['verified'] ?? '') === '1');
        $_SESSION['password_ticket_notice'] = 'Ticket updated. For a resolved ticket, provide the new password to the verified requester through your approved communication channel.';
        header('Location: ' . route('admin.password_tickets')); exit;
    } catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); }
    catch (Throwable $exception) { error_log((string) $exception); $error = 'Unable to update the ticket. Please try again later.'; }
}
$filter = (string) ($_GET['status'] ?? 'open');
if (!in_array($filter, ['open','pending','in_progress','resolved','rejected','all'], true)) $filter = 'open';
$where = $filter === 'open' ? "WHERE t.status IN ('pending','in_progress')" : ($filter === 'all' ? '' : 'WHERE t.status = ?');
$stmt = $conn->prepare("SELECT t.*, u.full_name, u.email, u.employee_id AS account_employee_id, u.role, u.status AS account_status, a.full_name AS admin_name FROM password_reset_tickets t JOIN users u ON u.id = t.user_id LEFT JOIN users a ON a.id = t.handled_by $where ORDER BY t.created_at DESC, t.id DESC LIMIT 100");
if (!in_array($filter, ['open','all'], true)) $stmt->bind_param('s', $filter);
$stmt->execute(); $tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$notice = (string) ($_SESSION['password_ticket_notice'] ?? ''); unset($_SESSION['password_ticket_notice']);
$h = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Password Reset Tickets | Pagecom HRIS</title><link rel="stylesheet" href="dashboard.css"><link rel="stylesheet" href="password_tickets.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></head>
<body><?php renderSidebar('admin','admin.password_tickets'); ?><main class="main tickets-main">
<h1>Password Reset Tickets</h1><p>Verify each requester's identity using your usual staff verification process before changing their password. Submitted IDs and messages are unverified.</p>
<?php if ($error): ?><div class="ticket-notice" role="alert"><?= $h($error) ?></div><?php endif ?>
<?php if ($notice): ?><div class="ticket-notice" role="status"><?= $h($notice) ?></div><?php endif ?>
<form method="get" class="ticket-filter"><label for="status">Show tickets</label><select id="status" name="status"><?php foreach (['open'=>'Open','pending'=>'Pending','in_progress'=>'In progress','resolved'=>'Resolved','rejected'=>'Rejected','all'=>'All'] as $key=>$label): ?><option value="<?= $key ?>" <?= $filter === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach ?></select><button type="submit">Filter</button></form>
<p>Showing the latest 100 matching tickets.</p>
<?php if (!$tickets): ?><section class="ticket-card">No tickets match this filter.</section><?php endif ?>
<?php foreach ($tickets as $ticket): $id = (int) $ticket['id']; ?>
<section class="ticket-card"><h2>Ticket #<?= $id ?> · <?= $h(ucwords(str_replace('_',' ',$ticket['status']))) ?></h2>
<p><strong><?= $h($ticket['full_name']) ?></strong> · <?= $h(strtoupper($ticket['role'])) ?><br><?= $h($ticket['email']) ?></p>
<dl><dt>Account employee ID</dt><dd><?= $h($ticket['account_employee_id'] ?: 'Not set') ?></dd><dt>Submitted employee ID</dt><dd><?= $h($ticket['employee_id'] ?: 'Not provided') ?></dd><dt>Account status</dt><dd><?= $h($ticket['account_status']) ?></dd><dt>Created</dt><dd><?= $h($ticket['created_at']) ?></dd><dt>Last handled by</dt><dd><?= $h($ticket['admin_name'] ?: 'Unassigned') ?><?= $ticket['updated_at'] ? ' · ' . $h($ticket['updated_at']) : '' ?></dd></dl>
<p class="ticket-message"><?= nl2br($h($ticket['request_message'] ?: 'No message provided.')) ?></p>
<?php if (in_array($ticket['status'], ['pending','in_progress'], true)): ?>
<form method="post" class="ticket-form">
<input type="hidden" name="csrf_token" value="<?= $h($_SESSION['admin_password_ticket_csrf']) ?>"><input type="hidden" name="ticket_id" value="<?= $id ?>">
<label for="notes-<?= $id ?>">Admin notes / rejection reason</label><textarea id="notes-<?= $id ?>" name="notes" rows="3" maxlength="2000"><?= $h($ticket['admin_notes']) ?></textarea>
<details><summary>Reset password after verification</summary><p>Use 12–72 bytes. Provide the password directly to the verified requester. Resolving this ticket signs the account out of all existing sessions.</p>
<label class="verify"><input type="checkbox" name="verified" value="1"> I verified this requester's identity.</label>
<label for="password-<?= $id ?>">New password</label><input id="password-<?= $id ?>" type="password" name="password" autocomplete="new-password" minlength="12" maxlength="72">
<label for="confirmation-<?= $id ?>">Confirm new password</label><input id="confirmation-<?= $id ?>" type="password" name="confirmation" autocomplete="new-password" minlength="12" maxlength="72">
<button name="action" value="resolved" type="submit">Reset password &amp; resolve</button></details>
<div class="ticket-actions"><button name="action" value="in_progress" type="submit">Mark in progress / save notes</button><button name="action" value="rejected" type="submit">Reject ticket</button></div>
</form>
<?php else: ?><p class="ticket-message"><strong>Admin notes:</strong><br><?= nl2br($h($ticket['admin_notes'] ?: 'No notes.')) ?></p><?php endif ?>
</section><?php endforeach ?>
</main></body></html>
