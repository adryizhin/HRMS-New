<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['hr','admin']);
require_once __DIR__ . '/../includes/evaluation_access.php';
if ($_SESSION['role']==='admin' && !isSystemAdministrator($conn,(int)$_SESSION['user_id'])) {http_response_code(403);exit('Access denied.');}
if ($_SESSION['role']==='admin') { header('Location: '.route('admin.review_hr_evaluations')); exit; }
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/task_schema.php';

ensureTaskSchema($conn);

$hr_id = (int) $_SESSION['user_id'];

// Get all submitted tasks for this HR
$stmt = $conn->prepare("
    SELECT 
        t.id,
        t.title,
        t.description,
        t.employee_id,
        t.hr_id,
        t.deadline,
        t.submission_file,
        t.submitted_at,
        t.status,
        t.hr_score,
        t.hr_feedback,
        u.full_name AS employee_name,
        u.employee_id AS emp_id
    FROM tasks t
    JOIN users u ON t.employee_id = u.id
    WHERE t.hr_id = ?
    AND t.submission_file IS NOT NULL
    AND t.submission_file != ''
    ORDER BY t.submitted_at DESC
");
$stmt->bind_param("i", $hr_id);
$stmt->execute();
$submissions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Task Submissions Review</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="submissions.css?v=<?= filemtime(__DIR__ . '/submissions.css') ?>">

</head>

<body>

<?php renderSidebar($_SESSION['role'], $_SESSION['role'] === 'admin' ? 'admin.review_hr_evaluations' : 'hr.task_submissions'); ?>

<div class="main">

<div class="topbar">
    <h1><i class="fa-solid fa-clipboard-check"></i> Task Submissions</h1>
</div>

<?php require __DIR__ . '/../includes/evaluation_submissions.php'; ?>

</div>

</body>
</html>
