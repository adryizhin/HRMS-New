<?php
declare(strict_types=1);

require_once __DIR__ . '/routes.php';
require_once __DIR__ . '/evaluation_access.php';
require_once __DIR__ . '/account_profile.php';
require_once __DIR__ . '/notifications.php';

function renderSidebar(string $role, string $active = ''): void
{
    if(in_array($active,['admin.assign_evaluations','admin.review_hr_evaluations'],true))$active='admin.hr_evaluations';
    renderSidebarStyles();

    $menus = [
        'admin' => [
            ['admin.dashboard', 'fa-chart-line', 'Dashboard'],
            ['admin.users', 'fa-user', 'Create Employees'],
            ['admin.departments', 'fa-building', 'Departments'],
            ['admin.attendance', 'fa-clock', 'Attendance'],
            ['admin.holidays', 'fa-calendar-days', 'Holiday Calendar'],
            ['admin.documents', 'fa-folder-open', 'My Documents'],
            ['admin.evaluations', 'fa-tasks', 'My Evaluations'],
            ['admin.kpi', 'fa-gauge-high', 'My KPI'],
            ['admin.payroll', 'fa-money-bill', 'Payroll'],
            ['admin.login_logs', 'fa-clock-rotate-left', 'Login Logs'],
            ['admin.password_tickets', 'fa-ticket', 'Password Reset Tickets'],
            ['admin.reports', 'fa-chart-pie', 'Reports'],
            ['logout', 'fa-right-from-bracket', 'Logout'],
        ],
        'hr' => [
            ['hr.dashboard', 'fa-chart-line', 'Dashboard'],
            ['hr.task_submissions', 'fa-clipboard-check', 'Review Evaluations'],
            ['hr.201_files', 'fa-folder-open', '201 Files'],
            ['hr.documents', 'fa-folder-open', 'My Documents'],
            ['hr.evaluations', 'fa-tasks', 'My Evaluations'],
            ['hr.leave_requests', 'fa-umbrella', 'Leave Requests'],
            ['hr.attendance', 'fa-clock', 'Attendance'],
            ['hr.kpi', 'fa-gauge-high', 'Employee KPIs'],
            ['hr.payroll', 'fa-money-bill', 'Payroll'],
            ['hr.reports', 'fa-chart-pie', 'Reports'],
            ['logout', 'fa-right-from-bracket', 'Logout'],
        ],
        'employee' => [
            ['employee.dashboard', 'fa-chart-line', 'Dashboard'],
            ['employee.documents', 'fa-folder-open', 'My Documents'],
            ['employee.tasks', 'fa-tasks', 'Evaluations'],
            ['employee.leave_request', 'fa-umbrella', 'Request Leave'],
            ['employee.attendance', 'fa-clock', 'Attendance'],
            ['employee.kpi', 'fa-gauge-high', 'My KPI'],
            ['employee.payroll', 'fa-money-bill', 'Payroll'],
            ['employee.reports', 'fa-chart-pie', 'Reports'],
            ['logout', 'fa-right-from-bracket', 'Logout'],
        ],
    ];

    global $conn;
    ensureAccountSchema($conn);
    $_SESSION['activity_csrf'] ??= bin2hex(random_bytes(32));
    $activity = activitySummary($conn,(int)$_SESSION['user_id'],$role);
    $activityCount = $activity['total'];
    $bell = '<button type="button" class="activity-toggle" aria-label="Notifications" aria-controls="activity-panel" aria-expanded="false"><i class="fa-solid fa-bell" aria-hidden="true"></i><span class="activity-toggle-label">Notifications</span><span class="activity-total"' . ($activityCount ? '' : ' hidden') . '>' . ($activityCount>99?'99+':$activityCount) . '</span></button>';
    $themeToggle = '<button type="button" class="theme-toggle-btn" aria-label="Switch to dark mode" title="Switch to dark mode"><i class="fa-solid fa-moon" aria-hidden="true"></i></button>';
    $menus[$role] = array_merge(array_slice($menus[$role],0,-1),[[$role.'.settings','fa-gear','Account Settings']],array_slice($menus[$role],-1));
    if ($role === 'admin' && isSystemAdministrator($conn,(int)$_SESSION['user_id'])) {
        array_splice($menus['admin'], 2, 0, [['admin.hr_evaluations','fa-clipboard-check','HR Evaluations']]);
    }
    echo '<header class="mobile-header"><span>Pagecom HRIS</span>' . $bell . $themeToggle . '<button id="sidebar-toggle" type="button" aria-label="Open menu" aria-controls="app-sidebar" aria-expanded="false"><span aria-hidden="true">☰</span></button></header>';
    echo '<div id="sidebar-backdrop" class="sidebar-backdrop" hidden aria-hidden="true"></div>';
    echo '<div class="sidebar" id="app-sidebar" aria-label="Main navigation">';
    echo '<div class="sidebar-heading"><h2>Pagecom HRIS</h2><button id="sidebar-close" type="button" aria-label="Close menu"><span aria-hidden="true">×</span></button></div>';


    foreach ($menus[$role] ?? [] as [$routeName, $icon, $label]) {
        $href = $routeName === '#' ? '#' : route($routeName);
        $class = $active === $routeName ? ' class="active"' : '';
        echo '<a href="' . htmlspecialchars($href) . '"' . $class . '>';
        echo '<i class="fa-solid ' . htmlspecialchars($icon) . '"></i> ';
        echo '<span class="sidebar-label">' . htmlspecialchars($label) . '</span>';
        echo '</a>';
    }

    echo '</div>';
    
    require __DIR__ . '/activity_widget.php';
    if(in_array($role,['admin','hr'],true))require __DIR__ . '/chat_widget.php';
    renderIdleTimeoutScript();
    echo '<script src="' . htmlspecialchars(app_url('includes/sidebar.js')) . '" defer></script>';
}

function renderSidebarStyles(): void
{
    static $rendered = false;

    if ($rendered) {
        return;
    }

    $rendered = true;
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">';
    echo '<style>' . file_get_contents(__DIR__ . '/activity.css') . '</style>';
    echo '<style>' . file_get_contents(__DIR__ . '/buttons.css') . '</style>';
    echo '<style>' . file_get_contents(__DIR__ . '/contrast.css') . '</style>';

    echo <<<HTML
<style>
/* --- Color Palette Tokens --- */
:root {
--bg-color: #f3f4f6;
    --text-color: #0f172a;
    --card-bg: #ffffff;
    --sidebar: #ffffff;
    --sidebar-border: #e2e8f0;
--muted: #475569;
    --accent: #2563eb;
    --active-bg: #2563eb;
    --active-text: #ffffff;
    --hover-bg: #f1f5f9;
    --hover-text: #0f172a;
    --btn-border: #cbd5e1;
}

[data-theme="dark"] {
    --bg-color: #090d16;
    --text-color: #f8fafc;
    --card-bg: #1e293b;
    --sidebar: #0f172a;
    --sidebar-border: #1e293b;
--muted: #cbd5e1;
--accent: #60a5fa;
--active-bg: #60a5fa;
--active-text: #0f172a;
    --hover-bg: #1e293b;
    --hover-text: #ffffff;
    --btn-border: #334155;
}

body {
    min-height: 100vh;
    overflow-x: hidden;
    background-color: var(--bg-color);
    color: var(--text-color);
    transition: background-color 0.2s ease, color 0.2s ease;
}

.sidebar {
    width: 240px !important;
    height: 100vh !important;
    min-height: 100vh !important;
    background: var(--sidebar) !important;
    border-right: 1px solid var(--sidebar-border) !important;
    padding: 20px !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    z-index: 1000 !important;
    overflow-y: auto !important;
    display: flex !important;
    flex-direction: column !important;
    font-family: 'Segoe UI', sans-serif !important;
    transition: background 0.2s ease, border-color 0.2s ease !important;
}

.sidebar h2 {
    color: var(--accent) !important;
    margin-top: 0 !important;
    margin-bottom: 30px !important;
    font-size: 1.25rem !important;
    font-weight: 700 !important;
    line-height: 1.2 !important;
}

.sidebar a {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    box-sizing: border-box !important;
    width: 100% !important;
    height: 44px !important;
    flex: 0 0 44px !important;
    padding: 12px !important;
    font-family: inherit !important;
    font-size: 1rem !important;
    color: var(--muted) !important;
    text-decoration: none !important;
    border-radius: 10px !important;
    margin-bottom: 6px !important;
    line-height: 20px !important;
    font-weight: 500 !important;
    transition: background-color 0.2s ease, color 0.2s ease !important;
}

.sidebar a i {
    flex: 0 0 18px !important;
    width: 18px !important;
    text-align: center !important;
}

.sidebar a:hover {
    background: var(--hover-bg) !important;
    color: var(--hover-text) !important;
}

.sidebar a.active {
    background: var(--active-bg) !important;
    color: var(--active-text) !important;
    font-size: 1rem !important;
    font-weight: 700 !important;
    height: 44px !important;
    flex: 0 0 44px !important;
    line-height: 20px !important;
}

.theme-toggle-btn {
    display: inline-grid !important;
    place-items: center !important;
    width: 44px !important;
    height: 44px !important;
    min-height: 44px !important;
    flex: 0 0 44px !important;
    margin: 0 !important;
    padding: 0 !important;
    background: var(--hover-bg) !important;
    border: 1px solid var(--btn-border) !important;
    color: var(--text-color) !important;
    border-radius: 50% !important;
    cursor: pointer !important;
    font-size: 20px !important;
    transition: background-color 0.2s ease !important;
}
.theme-toggle-btn:hover { background: var(--card-bg) !important; }
.theme-toggle-btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 3px; }
@media (max-width: 400px) {
    .mobile-header { gap: 8px !important; }
    .mobile-header > span:first-child { font-size: 14px; min-width: 0; }
}
@media print { .theme-toggle-btn { display: none !important; } }

.main {
    margin-left: 240px !important;
    width: calc(100% - 240px) !important;
    flex: none !important;
}

</style>
HTML;
    echo '<style>' . file_get_contents(__DIR__ . '/responsive.css') . '</style>';
}

function renderIdleTimeoutScript(): void
{
    static $rendered = false;

    if ($rendered) {
        return;
    }

    $rendered = true;
    $timeoutUrl = route('timeout_logout');
    $keepaliveUrl = route('keepalive');

    echo <<<HTML
<script>
(function () {
    const timeoutMs = 15 * 60 * 1000;
    const timeoutUrl = "{$timeoutUrl}";
    const keepaliveUrl = "{$keepaliveUrl}";
    let timer = null;
    let lastKeepalive = 0;

    function forceTimeoutLogout() {
        window.location.href = timeoutUrl;
    }

    function keepSessionAlive() {
        const now = Date.now();
        if (now - lastKeepalive < 60000) {
            return;
        }

        lastKeepalive = now;
        fetch(keepaliveUrl, { method: "POST", credentials: "same-origin" }).then(function (response) {
            if (response.status === 401) window.location.href = timeoutUrl;
        }).catch(function () {});
    }

    function resetIdleTimer() {
        clearTimeout(timer);
        keepSessionAlive();
        timer = setTimeout(forceTimeoutLogout, timeoutMs);
    }

    ["click", "keydown", "mousemove", "scroll", "touchstart"].forEach(function (eventName) {
        window.addEventListener(eventName, resetIdleTimer, { passive: true });
    });

    // Presence signals must not renew the inactivity timer.
    function pingPresence() {
        fetch(keepaliveUrl, {
            method: "POST", credentials: "same-origin",
            headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: "presence=1"
        }).then(function (response) {
            if (response.status === 401) window.location.href = timeoutUrl;
        }).catch(function () {});
    }
    setInterval(pingPresence, 30000);
    window.addEventListener("pageshow", pingPresence);
    resetIdleTimer();
})();

document.addEventListener("DOMContentLoaded", () => {
    const toggles = document.querySelectorAll(".theme-toggle-btn");
    function applyTheme(theme) {
        theme = theme === "dark" ? "dark" : "light";
        document.documentElement.setAttribute("data-theme", theme);
        const label = theme === "dark" ? "Switch to light mode" : "Switch to dark mode";
        toggles.forEach(button => {
            button.setAttribute("aria-label", label);
            button.title = label;
            button.querySelector("i").className = "fa-solid " + (theme === "dark" ? "fa-sun" : "fa-moon");
        });
    }
    applyTheme(localStorage.getItem("theme"));
    toggles.forEach(button => button.addEventListener("click", () => {
        const newTheme = document.documentElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
        applyTheme(newTheme);
        localStorage.setItem("theme", newTheme);
    }));
    window.addEventListener("storage", event => {
        if (event.key === "theme") applyTheme(event.newValue);
    });
});
</script>
HTML;
}  
