<?php
declare(strict_types=1);
// The existing System Administrator account is user 1. Override when migrating installations.
function isSystemAdministrator(mysqli $conn, int $id): bool
{
    $configuredId = (int) (getenv('HRMS_SYSTEM_ADMIN_ID') ?: 1);
    if ($id !== $configuredId) return false;
    $stmt = $conn->prepare("SELECT id FROM users WHERE id=? AND role='admin' AND status='active'");
    $stmt->bind_param('i',$id);$stmt->execute();
    return (bool) $stmt->get_result()->fetch_assoc();
}
function validateEvaluationRecipients(mysqli $conn, int $assigner, array $ids): bool
{
    $system = isSystemAdministrator($conn,$assigner);
    $stmt = $conn->prepare('SELECT id,role,status FROM users WHERE id IN (' . implode(',',array_fill(0,count($ids),'?')) . ')');
    $stmt->bind_param(str_repeat('i',count($ids)), ...$ids);$stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if (count($users) !== count($ids)) return false;
    foreach ($users as $user) {
        if ($user['status'] !== 'active' || ($user['role'] === 'hr' && !$system) || ($system && $user['role'] !== 'hr')) return false;
    }
    return true;
}
