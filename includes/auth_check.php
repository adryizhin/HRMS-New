<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/routes.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/session_activity.php';

function checkRole(array $allowedRoles = []): void
{
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles, true)) {
        header('Location: ' . route('login'));
        exit();
    }
}

if (!isset($_SESSION['user_id'], $_SESSION['session_token'])) {
    header('Location: ' . route('login'));
    exit();
}

if (!refreshLoginSession($conn, (int) $_SESSION['user_id'], (string) $_SESSION['session_token'], $refreshSessionActivity ?? true)) {
    session_unset();
    session_destroy();
    header('Location: ' . route('login'));
    exit();
}
