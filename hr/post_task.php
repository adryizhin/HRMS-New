<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['hr']);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/task_schema.php';

ensureTaskSchema($conn);

$hr_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

/* =========================
   VALIDATION
========================= */
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$deadline = trim($_POST['deadline'] ?? '');
$employee_ids = $_POST['employee_ids'] ?? [];

if ($title === '' || $description === '' || $deadline === '' || empty($employee_ids) || !is_array($employee_ids)) {
    header("Location: dashboard.php?task_error=1");
    exit;
}

$deadlineDate = DateTime::createFromFormat('Y-m-d', $deadline);
if (!$deadlineDate || $deadlineDate->format('Y-m-d') !== $deadline) {
    header("Location: dashboard.php?task_error=1");
    exit;
}

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
$today = new DateTimeImmutable('today', new DateTimeZone('Asia/Manila'));
if ($deadlineDate < $today) {
    header("Location: dashboard.php?task_date_error=1");
    exit;
}

$employee_ids = array_values(array_unique(array_filter(
    array_map('intval', $employee_ids),
    static fn (int $employeeId): bool => $employeeId > 0
)));
if ($employee_ids === []) {
    header("Location: dashboard.php?task_error=1");
    exit;
}

<<<<<<< HEAD
=======
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
/* =========================
   FILE UPLOAD (OPTIONAL)
========================= */
$file_name = null;
$file_path = null;
$file_type = null;
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
$attachmentDigest = '';
$uploadPending = !empty($_FILES['file']['name']) && is_uploaded_file($_FILES['file']['tmp_name']);

if ($uploadPending) {
    $attachmentDigest = hash_file('sha256', $_FILES['file']['tmp_name']);
    if ($attachmentDigest === false) {
        header("Location: dashboard.php?task_error=1");
        exit;
    }
}

$eligibleEmployeeIds = [];
$duplicate_count = 0;
$duplicateCheck = $conn->prepare("
    SELECT id
    FROM tasks
    WHERE hr_id = ?
      AND employee_id = ?
      AND title = ?
      AND description = ?
      AND deadline = ?
      AND (assignment_key = ? OR assignment_key IS NULL)
    LIMIT 1
");
if (!$duplicateCheck) {
    die("Unable to check for duplicate evaluations: " . $conn->error);
}

foreach ($employee_ids as $employeeId) {
    $assignmentKey = hash('sha256', json_encode([
        $hr_id,
        $employeeId,
        $title,
        $description,
        $deadline,
        $attachmentDigest,
    ], JSON_THROW_ON_ERROR));
    $duplicateCheck->bind_param(
        "iissss",
        $hr_id,
        $employeeId,
        $title,
        $description,
        $deadline,
        $assignmentKey
    );
    if (!$duplicateCheck->execute()) {
        die("Unable to check for duplicate evaluations: " . $duplicateCheck->error);
    }
    if ($duplicateCheck->get_result()->num_rows > 0) {
        $duplicate_count++;
        continue;
    }
    $eligibleEmployeeIds[] = $employeeId;
}
$duplicateCheck->close();

if ($eligibleEmployeeIds === []) {
    header("Location: dashboard.php?task_sent=0&task_duplicate=" . $duplicate_count);
    exit;
}

if ($uploadPending) {
<<<<<<< HEAD
=======

if (!empty($_FILES['file']['name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)

    $uploadDir = __DIR__ . "/../uploads/tasks/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $original = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['file']['name']));
<<<<<<< HEAD
<<<<<<< HEAD
    $file_name = bin2hex(random_bytes(8)) . "_" . $original;
=======
    $file_name = time() . "_" . $original;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
    $file_name = bin2hex(random_bytes(8)) . "_" . $original;
>>>>>>> ea5c816 (partial done system)
    $target_path = $uploadDir . $file_name;
    $file_path = "uploads/tasks/" . $file_name;
    $file_type = $_FILES['file']['type'] ?: pathinfo($file_name, PATHINFO_EXTENSION);

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
        header("Location: dashboard.php?task_error=1");
        exit;
    }
}

/* =========================
   ASSIGNMENT BATCH ID
========================= */
$batch_id = uniqid("batch_");

/* =========================
   INSERT TASKS PER EMPLOYEE
========================= */
$stmt = $conn->prepare("
    INSERT INTO tasks (
        assignment_batch,
<<<<<<< HEAD
<<<<<<< HEAD
        assignment_key,
=======
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
        assignment_key,
>>>>>>> ea5c816 (partial done system)
        title,
        description,
        deadline,
        employee_id,
        hr_id,
        file_attachment,
        file_name,
        file_path,
        file_type,
        uploaded_by,
        status,
        created_at
<<<<<<< HEAD
<<<<<<< HEAD
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
=======
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
>>>>>>> ea5c816 (partial done system)
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$success_count = 0;

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
foreach ($eligibleEmployeeIds as $emp_id) {
    $assignmentKey = hash('sha256', json_encode([
        $hr_id,
        $emp_id,
        $title,
        $description,
        $deadline,
        $attachmentDigest,
    ], JSON_THROW_ON_ERROR));

    $stmt->bind_param(
        "sssssiissssi",
        $batch_id,
        $assignmentKey,
<<<<<<< HEAD
=======
foreach ($employee_ids as $emp_id) {

    $emp_id = (int) $emp_id;
    if ($emp_id <= 0) {
        continue;
    }

    $stmt->bind_param(
        "ssssiissssi",
        $batch_id,
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
        $title,
        $description,
        $deadline,
        $emp_id,
        $hr_id,
        $file_name,
        $file_name,
        $file_path,
        $file_type,
        $hr_id
    );

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
    try {
        if ($stmt->execute()) {
            $success_count++;
        } elseif ($stmt->errno === 1062) {
            $duplicate_count++;
        } else {
            if ($file_name !== null) {
                $uploadedPath = __DIR__ . "/../uploads/tasks/" . $file_name;
                if (is_file($uploadedPath)) {
                    unlink($uploadedPath);
                }
            }
            die("Unable to create evaluation: " . $stmt->error);
        }
    } catch (mysqli_sql_exception $exception) {
        if ($exception->getCode() !== 1062) {
            throw $exception;
        }
        $duplicate_count++;
<<<<<<< HEAD
=======
    if ($stmt->execute()) {
        $success_count++;
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
    }
}

$stmt->close();

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
if ($success_count === 0 && $file_name !== null) {
    $uploadedPath = __DIR__ . "/../uploads/tasks/" . $file_name;
    if (is_file($uploadedPath)) {
        unlink($uploadedPath);
    }
}

<<<<<<< HEAD
/* =========================
   REDIRECT BACK
========================= */
header("Location: dashboard.php?task_sent=" . $success_count . "&task_duplicate=" . $duplicate_count);
=======
/* =========================
   REDIRECT BACK
========================= */
header("Location: dashboard.php?task_sent=" . $success_count);
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
header('Location: dashboard.php?' . http_build_query([
    'task_sent' => $success_count,
    'task_duplicate' => $duplicate_count,
]));
>>>>>>> ea5c816 (partial done system)
exit;
