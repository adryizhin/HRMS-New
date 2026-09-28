<?php
declare(strict_types=1);

require_once __DIR__ . '/routes.php';

function renderSidebar(string $role, string $active = ''): void
{
    renderSidebarStyles();

    $menus = [
        'admin' => [
            ['admin.dashboard', 'fa-chart-line', 'Dashboard'],
            ['admin.users', 'fa-user', 'Create Employees'],
            ['admin.departments', 'fa-building', 'Departments'],
            ['admin.attendance', 'fa-clock', 'Attendance'],
<<<<<<< HEAD
            ['admin.holidays', 'fa-calendar-days', 'Holiday Calendar'],
=======
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
            ['admin.payroll', 'fa-money-bill', 'Payroll'],
            ['admin.login_logs', 'fa-clock-rotate-left', 'Login Logs'],
            ['#', 'fa-chart-pie', 'Reports'],
            ['logout', 'fa-right-from-bracket', 'Logout'],
        ],
        'hr' => [
            ['hr.dashboard', 'fa-chart-line', 'Dashboard'],
<<<<<<< HEAD
            ['hr.task_submissions', 'fa-clipboard-check', 'Review Evaluations'],
=======
            ['hr.task_submissions', 'fa-clipboard-check', 'Review Submissions'],
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
            ['hr.201_files', 'fa-folder-open', '201 Files'],
            ['hr.leave_requests', 'fa-umbrella', 'Leave Requests'],
            ['hr.attendance', 'fa-clock', 'Attendance'],
            ['hr.kpi', 'fa-gauge-high', 'Employee KPIs'],
            ['hr.payroll', 'fa-money-bill', 'Payroll'],
            ['#', 'fa-chart-pie', 'Reports'],
            ['logout', 'fa-right-from-bracket', 'Logout'],
        ],
        'employee' => [
            ['employee.dashboard', 'fa-chart-line', 'Dashboard'],
            ['employee.documents', 'fa-folder-open', 'My Documents'],
<<<<<<< HEAD
            ['employee.tasks', 'fa-tasks', 'Evaluations'],
=======
            ['employee.tasks', 'fa-tasks', 'Tasks'],
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
            ['employee.leave_request', 'fa-umbrella', 'Request Leave'],
            ['employee.attendance', 'fa-clock', 'Attendance'],
            ['employee.kpi', 'fa-gauge-high', 'My KPI'],
            ['employee.payroll', 'fa-money-bill', 'Payroll'],
            ['#', 'fa-chart-pie', 'Reports'],
            ['logout', 'fa-right-from-bracket', 'Logout'],
        ],
    ];

    echo '<div class="sidebar">';
    echo '<h2>Pagecom HRIS</h2>';

    foreach ($menus[$role] ?? [] as [$routeName, $icon, $label]) {
        $href = $routeName === '#' ? '#' : route($routeName);
        $class = $active === $routeName ? ' class="active"' : '';
        echo '<a href="' . htmlspecialchars($href) . '"' . $class . '>';
        echo '<i class="fa-solid ' . htmlspecialchars($icon) . '"></i> ';
        echo htmlspecialchars($label);
        echo '</a>';
    }

<<<<<<< HEAD
    echo '<button id="theme-toggle-btn" class="theme-toggle-btn">🌙 Dark Mode</button>';
    echo '</div>';
    
=======
    echo '</div>';
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
    renderIdleTimeoutScript();
}

function renderSidebarStyles(): void
{
    static $rendered = false;

    if ($rendered) {
        return;
    }

    $rendered = true;

    echo <<<HTML
<style>
<<<<<<< HEAD
/* --- Color Palette Tokens --- */
:root {
--bg-color: #dcdfe4;
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
=======
body {
    min-height: 100vh;
    overflow-x: hidden;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
}

.sidebar {
    width: 240px !important;
    height: 100vh !important;
    min-height: 100vh !important;
<<<<<<< HEAD
    background: var(--sidebar) !important;
    border-right: 1px solid var(--sidebar-border) !important;
=======
    background: var(--sidebar, #020617) !important;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
    padding: 20px !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    z-index: 1000 !important;
    overflow-y: auto !important;
<<<<<<< HEAD
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
=======
}

.sidebar h2 {
    color: var(--accent, #3251ff) !important;
    margin-bottom: 30px !important;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
}

.sidebar a {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
<<<<<<< HEAD
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
    margin-top: auto !important;
    padding: 10px 15px !important;
    background: transparent !important;
    border: 1px solid var(--btn-border) !important;
    color: var(--text-color) !important;
    border-radius: 8px !important;
    cursor: pointer !important;
    text-align: left !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
    font-size: 0.9rem !important;
}

.theme-toggle-btn:hover {
    background: var(--hover-bg) !important;
    color: var(--hover-text) !important;
    border-color: var(--accent) !important;
=======
    padding: 12px !important;
    color: var(--muted, #9ca3af) !important;
    text-decoration: none !important;
    border-radius: 10px !important;
    margin-bottom: 10px !important;
    transition: background 0.2s ease, color 0.2s ease !important;
}

.sidebar a:hover {
    background: var(--accent, #3251ff) !important;
    color: #000 !important;
}

.sidebar a.active {
    background: var(--accent, #3251ff) !important;
    color: #000 !important;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
}

.main {
    margin-left: 240px !important;
    width: calc(100% - 240px) !important;
    flex: none !important;
}

@media (max-width: 780px) {
    .sidebar {
        width: 100% !important;
        height: auto !important;
        min-height: 0 !important;
        position: sticky !important;
        top: 0 !important;
<<<<<<< HEAD
        border-right: none !important;
        border-bottom: 1px solid var(--sidebar-border) !important;
=======
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
    }

    .main {
        margin-left: 0 !important;
        width: 100% !important;
    }
}
</style>
HTML;
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
        fetch(keepaliveUrl, { method: "POST", credentials: "same-origin" }).catch(function () {});
    }

    function resetIdleTimer() {
        clearTimeout(timer);
        keepSessionAlive();
        timer = setTimeout(forceTimeoutLogout, timeoutMs);
    }

    ["click", "keydown", "mousemove", "scroll", "touchstart"].forEach(function (eventName) {
        window.addEventListener(eventName, resetIdleTimer, { passive: true });
    });

    resetIdleTimer();
})();
<<<<<<< HEAD

document.addEventListener("DOMContentLoaded", () => {
    const currentTheme = localStorage.getItem("theme") || "light";
    document.documentElement.setAttribute("data-theme", currentTheme);

    const toggleBtn = document.getElementById("theme-toggle-btn");
    if (toggleBtn) {
        toggleBtn.innerText = currentTheme === "dark" ? "☀️ Light Mode" : "🌙 Dark Mode";
        toggleBtn.addEventListener("click", () => {
            let theme = document.documentElement.getAttribute("data-theme");
            let newTheme = theme === "dark" ? "light" : "dark";
            document.documentElement.setAttribute("data-theme", newTheme);
            localStorage.setItem("theme", newTheme);
            toggleBtn.innerText = newTheme === "dark" ? "☀️ Light Mode" : "🌙 Dark Mode";
        });
    }
});
</script>
HTML;
}  
=======
</script>
HTML;
}
?>
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
