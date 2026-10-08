<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

function app_url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . ($path !== '' ? '/' . $path : '');
}

function route(string $name): string
{
    static $routes = [
        'login' => 'login.php',
        'logout' => 'logout.php',
        'timeout_logout' => 'timeout_logout.php',
        'keepalive' => 'keepalive.php',
        'admin.dashboard' => 'admin/dashboard.php',
        'admin.users' => 'admin/create_user_account.php',
        'admin.departments' => 'admin/departments.php',
        'admin.attendance' => 'admin/attendance.php',
<<<<<<< HEAD
<<<<<<< HEAD
        'admin.holidays' => 'admin/holidays.php',
=======
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
        'admin.kpi' => 'admin/kpi.php',
        'admin.evaluations' => 'admin/evaluations.php',
        'admin.documents' => 'admin/documents.php',
        'admin.reports' => 'admin/reports.php',
        'admin.holidays' => 'admin/holidays.php',
>>>>>>> ea5c816 (partial done system)
        'admin.payroll' => 'admin/payroll.php',
        'admin.login_logs' => 'admin/login_logs.php',
        'hr.dashboard' => 'hr/dashboard.php',
        'hr.task_submissions' => 'hr/task_submissions.php',
        'hr.score_submission' => 'hr/score_submission.php',
        'hr.201_files' => 'hr/201_files.php',
        'hr.leave_requests' => 'hr/hr_leave_requests.php',
        'hr.attendance' => 'hr/attendance.php',
        'hr.kpi' => 'hr/kpi.php',
<<<<<<< HEAD
=======
        'hr.reports' => 'hr/reports.php',
        'hr.documents' => 'hr/documents.php',
        'hr.evaluations' => 'hr/evaluations.php',
>>>>>>> ea5c816 (partial done system)
        'hr.payroll' => 'hr/payroll.php',
        'hr.post_task' => 'hr/post_task.php',
        'employee.dashboard' => 'employee/dashboard.php',
        'employee.tasks' => 'employee/dashboard.php',
        'employee.documents' => 'employee/documents.php',
        'employee.leave_request' => 'employee/request_leave.php',
        'employee.attendance' => 'employee/attendance.php',
        'employee.kpi' => 'employee/kpi.php',
<<<<<<< HEAD
=======
        'employee.reports' => 'employee/reports.php',
>>>>>>> ea5c816 (partial done system)
        'employee.payroll' => 'employee/payroll.php',
    ];

    return app_url($routes[$name] ?? $name);
}
?>
