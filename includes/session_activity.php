<?php
declare(strict_types=1);
require_once __DIR__ . '/login_logger.php';

function expireLoginSessions(mysqli $conn): void
{
    ensureLoginLogReasonColumn($conn);
    $conn->query("UPDATE login_logs
        SET logout_time = DATE_ADD(COALESCE(last_ping, login_time), INTERVAL 15 MINUTE), logout_reason = 'timeout'
        WHERE logout_time IS NULL
        AND COALESCE(last_ping, login_time) <= DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
}

// Presence heartbeats must not extend the 15-minute inactivity timeout.
function refreshLoginSession(mysqli $conn, int $userId, string $token, bool $activity = true): bool
{
    ensureLoginPresenceColumn($conn);
    expireLoginSessions($conn);
    if ($token === '') return false;
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("SELECT l.id FROM login_logs l JOIN users u ON u.id = l.user_id
            WHERE l.user_id = ? AND l.session_token = ? AND l.logout_time IS NULL
            AND l.last_ping > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND u.status = 'active'
            ORDER BY l.id DESC LIMIT 1 FOR UPDATE");
        $stmt->bind_param('is', $userId, $token); $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) { $conn->commit(); return false; }
        $id = (int) $row['id'];
        $sql = $activity ? 'UPDATE login_logs SET last_ping = NOW(), last_seen = NOW() WHERE id = ?' : 'UPDATE login_logs SET last_seen = NOW() WHERE id = ?';
        $stmt = $conn->prepare($sql); $stmt->bind_param('i', $id); $stmt->execute();
        $conn->commit(); return true;
    } catch (Throwable $error) { $conn->rollback(); throw $error; }
}

function getOnlineUsers(mysqli $conn): array
{
    ensureLoginPresenceColumn($conn);
    expireLoginSessions($conn);
    // One user may have several valid browser sessions, but appears only once.
    return $conn->query("SELECT u.id, u.full_name AS username, u.role, MAX(l.last_seen) AS last_seen
        FROM users u JOIN login_logs l ON l.user_id = u.id
        WHERE u.status = 'active' AND l.logout_time IS NULL
        AND l.session_token IS NOT NULL AND l.session_token <> ''
        AND l.last_seen > DATE_SUB(NOW(), INTERVAL 90 SECOND)
        AND l.last_ping > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        GROUP BY u.id, u.full_name, u.role ORDER BY last_seen DESC, u.id")->fetch_all(MYSQLI_ASSOC);
}

function renderOnlineUsers(array $users): void
{
    if (!$users) { echo '<p class="online-users-empty">No users online</p>'; return; }
    foreach ($users as $user) {
        $name = htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8');
        $role = htmlspecialchars((string) $user['role'], ENT_QUOTES, 'UTF-8');
        echo '<div class="online-user-row"><div class="online-user-name"><span class="dot"></span><strong>' . $name . '</strong></div><span class="badge ' . $role . '">' . strtoupper($role) . '</span></div>';
    }
}
