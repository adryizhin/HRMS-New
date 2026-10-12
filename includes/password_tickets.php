<?php
declare(strict_types=1);
require_once __DIR__.'/ticket_mail.php';

function ensurePasswordTicketSchema(mysqli $conn): void
{
    $conn->query("CREATE TABLE IF NOT EXISTS password_reset_tickets (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        requested_email VARCHAR(191) NULL,
        system_note VARCHAR(255) NULL,
        employee_id VARCHAR(50) NOT NULL,
        request_message TEXT NOT NULL,
        status ENUM('pending','in_progress','resolved','rejected') NOT NULL DEFAULT 'pending',
        admin_notes TEXT NULL,
        handled_by INT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL,
        INDEX (user_id, status), INDEX (status, created_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (handled_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $columns = [];
    $result = $conn->query('SHOW COLUMNS FROM password_reset_tickets');
    while ($column = $result->fetch_assoc()) $columns[$column['Field']] = $column;
    if (!isset($columns['requested_email'])) $conn->query('ALTER TABLE password_reset_tickets ADD COLUMN requested_email VARCHAR(191) NULL');
    if (!isset($columns['notification_email'])) $conn->query('ALTER TABLE password_reset_tickets ADD COLUMN notification_email VARCHAR(191) NULL');
    if (!isset($columns['system_note'])) $conn->query('ALTER TABLE password_reset_tickets ADD COLUMN system_note VARCHAR(255) NULL');
    if ($columns['user_id']['Null'] === 'NO') $conn->query('ALTER TABLE password_reset_tickets MODIFY user_id INT NULL');
    ensureTicketMailSchema($conn);
    $conn->query("CREATE TABLE IF NOT EXISTS password_reset_throttle (
        source_hash CHAR(64) NOT NULL PRIMARY KEY,
        window_start DATETIME NOT NULL,
        attempts INT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// Database-backed limit applies across browser sessions; do not trust forwarded IP headers.
function allowPasswordTicketRequest(mysqli $conn, string $source): bool
{
    $key = hash('sha256', $source);
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare('INSERT IGNORE INTO password_reset_throttle (source_hash, window_start, attempts) VALUES (?, NOW(), 0)');
        $stmt->bind_param('s', $key); $stmt->execute();
        $stmt = $conn->prepare('SELECT attempts, window_start <= DATE_SUB(NOW(), INTERVAL 15 MINUTE) AS expired FROM password_reset_throttle WHERE source_hash = ? FOR UPDATE');
        $stmt->bind_param('s', $key); $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $allowed = (bool) $row['expired'] || (int) $row['attempts'] < 5;
        if ($allowed) {
            $stmt = $conn->prepare('UPDATE password_reset_throttle SET attempts = IF(window_start <= DATE_SUB(NOW(), INTERVAL 15 MINUTE), 1, attempts + 1), window_start = IF(window_start <= DATE_SUB(NOW(), INTERVAL 15 MINUTE), NOW(), window_start) WHERE source_hash = ?');
            $stmt->bind_param('s', $key); $stmt->execute();
        }
        $conn->commit();
        return $allowed;
    } catch (Throwable $error) { $conn->rollback(); throw $error; }
}

function submitPasswordTicket(mysqli $conn, string $email, string $employeeId, string $message, ?string $notificationEmail=null): ?int
{
    $notificationEmail ??= $email;
    if(!filter_var($notificationEmail,FILTER_VALIDATE_EMAIL)||strlen($notificationEmail)>191)throw new InvalidArgumentException('Enter a valid active email for ticket updates.');
    $createdTicketId=null;
    $conn->begin_transaction();
    try {
        // Lock the account to prevent concurrent duplicate open tickets.
        $stmt = $conn->prepare("SELECT id, status FROM users WHERE email = ? FOR UPDATE");
        $stmt->bind_param('s', $email); $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if ($user) {
            $id = (int) $user['id'];
            $stmt = $conn->prepare("SELECT id FROM password_reset_tickets WHERE user_id = ? AND status IN ('pending','in_progress') LIMIT 1");
            $stmt->bind_param('i', $id); $stmt->execute();
            if (!$stmt->get_result()->fetch_assoc()) {
                $note = $user['status'] === 'active' ? null : 'This account is inactive. Verify its status before handling this request.';
                $stmt = $conn->prepare('INSERT INTO password_reset_tickets (user_id, employee_id, request_message, requested_email, system_note, notification_email) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('isssss', $id, $employeeId, $message, $email, $note, $notificationEmail); $stmt->execute();
                $createdTicketId=(int)$conn->insert_id;queueTicketStatusEmail($conn,$createdTicketId,$notificationEmail,'pending');
            }
        }
        if (!$user) {
            $stmt = $conn->prepare("SELECT id FROM password_reset_tickets WHERE user_id IS NULL AND requested_email = ? AND status IN ('pending','in_progress') LIMIT 1 FOR UPDATE");
            $stmt->bind_param('s', $email); $stmt->execute();
            if (!$stmt->get_result()->fetch_assoc()) {
                $note = 'Email not found in the system. No account can be reset for this request.';
                $stmt = $conn->prepare('INSERT INTO password_reset_tickets (employee_id, request_message, requested_email, system_note, notification_email) VALUES (?, ?, ?, ?, ?)');
                $stmt->bind_param('sssss', $employeeId, $message, $email, $note, $notificationEmail); $stmt->execute();
                $createdTicketId=(int)$conn->insert_id;queueTicketStatusEmail($conn,$createdTicketId,$notificationEmail,'pending');
            }
        }
        $conn->commit();
        return $createdTicketId;
    } catch (Throwable $error) { $conn->rollback(); throw $error; }
}

function handlePasswordTicket(mysqli $conn, int $adminId, int $ticketId, string $action, string $notes, string $password, string $confirmation, bool $verified): void
{
    if (!in_array($action, ['in_progress', 'resolved', 'rejected'], true) || strlen($notes) > 2000) {
        throw new InvalidArgumentException('Choose a valid action and keep notes under 2,000 characters.');
    }
    if ($action === 'resolved' && (!$verified || strlen($password) < 12 || strlen($password) > 72 || $password !== $confirmation)) {
        throw new InvalidArgumentException('Verify the requester, then enter matching passwords of 12 to 72 bytes.');
    }
    if ($action === 'rejected' && trim($notes) === '') throw new InvalidArgumentException('Enter a reason for rejecting this request.');
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'admin' AND status = 'active' FOR UPDATE");
        $stmt->bind_param('i', $adminId); $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) throw new InvalidArgumentException('Only active admins can handle tickets.');
        $stmt = $conn->prepare('SELECT t.*,COALESCE(t.notification_email,t.requested_email,u.email) AS update_email FROM password_reset_tickets t LEFT JOIN users u ON u.id=t.user_id WHERE t.id = ? FOR UPDATE');
        $stmt->bind_param('i', $ticketId); $stmt->execute();
        $ticket = $stmt->get_result()->fetch_assoc();
        if (!$ticket || !in_array($ticket['status'], ['pending','in_progress'], true)) throw new InvalidArgumentException('This ticket is already closed or unavailable.');
        if ($action === 'resolved') {
            if (empty($ticket['user_id'])) throw new InvalidArgumentException('Email not found in the system. Add notes or reject this request; a password cannot be reset.');
            $userId = (int) $ticket['user_id'];
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND status = 'active'");
            $stmt->bind_param('si', $hash, $userId); $stmt->execute();
            if ($stmt->affected_rows !== 1) throw new InvalidArgumentException('This account is unavailable or inactive.');
            $stmt = $conn->prepare("UPDATE login_logs SET logout_time = NOW(), logout_reason = 'password_reset' WHERE user_id = ? AND logout_time IS NULL");
            $stmt->bind_param('i', $userId); $stmt->execute();
        }
        $stmt = $conn->prepare('UPDATE password_reset_tickets SET status = ?, admin_notes = ?, handled_by = ?, updated_at = NOW() WHERE id = ?');
        $stmt->bind_param('ssii', $action, $notes, $adminId, $ticketId); $stmt->execute();
        if($ticket['status']!==$action)queueTicketStatusEmail($conn,$ticketId,(string)($ticket['update_email']??''),$action);
        $conn->commit();
    } catch (Throwable $error) { $conn->rollback(); throw $error; }
}
