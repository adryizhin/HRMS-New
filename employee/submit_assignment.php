<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['employee']);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/task_schema.php';

ensureTaskSchema($conn);

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $assignment_id = isset($_POST['assignment_id']) ? (int) $_POST['assignment_id'] : 0;
    $requestKey = strtolower(trim($_POST['submission_request_key'] ?? ''));
    if ($assignment_id <= 0) {
        die("Invalid evaluation.");
    }
    if (!preg_match('/\A[a-f0-9]{64}\z/', $requestKey)) {
        die("Invalid submission request. Reload the evaluation page and try again.");
    }

    if (!isset($_FILES['submission_file']) || $_FILES['submission_file']['error'] !== UPLOAD_ERR_OK) {
        die("No file uploaded.");
    }

    $conn->begin_transaction();
    $task = $conn->prepare("
        SELECT id, deadline, submission_file, submission_request_key
        FROM tasks
        WHERE id = ? AND employee_id = ?
        FOR UPDATE
    ");
    if (!$task) {
        $conn->rollback();
        die("Unable to verify evaluation: " . $conn->error);
    }
    $task->bind_param("ii", $assignment_id, $user_id);
    if (!$task->execute()) {
        $conn->rollback();
        die("Unable to verify evaluation: " . $task->error);
    }
    $existing = $task->get_result()->fetch_assoc();

    if (!$existing) {
        $conn->rollback();
        die("Task not found or access denied.");
    }

    if (hash_equals((string) ($existing['submission_request_key'] ?? ''), $requestKey)) {
        $conn->rollback();
        header("Location: dashboard.php?success=1&duplicate=1");
        exit;
    }

    $today = new DateTimeImmutable('today', new DateTimeZone('Asia/Manila'));
    if (!empty($existing['deadline']) && $existing['deadline'] < $today->format('Y-m-d')) {
        $conn->rollback();
        header("Location: dashboard.php?expired=1");
        exit;
    }

    $file = $_FILES['submission_file'];

    $original = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file["name"]));
    $fileName = bin2hex(random_bytes(16)) . "_" . $user_id . "_" . $original;
    $targetDir = __DIR__ . "/../uploads/submissions/";
    $targetFile = $targetDir . $fileName;

    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            $conn->rollback();
            die("Unable to prepare the submission upload directory.");
        }
    }

    if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
        $conn->rollback();
        die("Upload failed.");
    }

    $stmt = $conn->prepare("
        UPDATE tasks
        SET submission_file = ?,
            submitted_at = NOW(),
            submission_request_key = ?,
            status = 'submitted',
            ai_score = NULL,
            grammar_score = NULL,
            spelling_score = NULL,
            clarity_score = NULL,
            relevance_score = NULL,
            ai_feedback = NULL
        WHERE id = ? AND employee_id = ?
    ");
    if (!$stmt) {
        $conn->rollback();
        unlink($targetFile);
        die("Unable to save the submission: " . $conn->error);
    }

    $stmt->bind_param(
        "ssii",
        $fileName,
        $requestKey,
        $assignment_id,
        $user_id
    );

    try {
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $conn->rollback();
            unlink($targetFile);
            die("Database error saving the submission: " . $error);
        }
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        unlink($targetFile);
        if ($exception->getCode() === 1062) {
            header("Location: dashboard.php?success=1&duplicate=1");
            exit;
        }
        throw $exception;
    }
    if (!$conn->commit()) {
        $conn->rollback();
        unlink($targetFile);
        die("Unable to commit the submission. Please try again.");
    }

    if (!empty($existing['submission_file'])) {
        $oldPath = $targetDir . basename($existing['submission_file']);
        if (is_file($oldPath) && $oldPath !== $targetFile) {
            unlink($oldPath);
        }
    }

    header("Location: dashboard.php?success=1");
    exit;
}
