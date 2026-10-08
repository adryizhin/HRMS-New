<?php
declare(strict_types=1);

function ensureTaskSchema(mysqli $conn): void
{
    static $done = false;
    if ($done) {
        return;
    }

    $columns = [
        'employee_id' => "ALTER TABLE tasks ADD COLUMN employee_id INT(11) DEFAULT NULL AFTER id",
        'hr_id' => "ALTER TABLE tasks ADD COLUMN hr_id INT(11) DEFAULT NULL AFTER employee_id",
        'assignment_batch' => "ALTER TABLE tasks ADD COLUMN assignment_batch VARCHAR(80) DEFAULT NULL AFTER hr_id",
        'assignment_key' => "ALTER TABLE tasks ADD COLUMN assignment_key CHAR(64) DEFAULT NULL AFTER assignment_batch",
        'description' => "ALTER TABLE tasks ADD COLUMN description TEXT DEFAULT NULL AFTER title",
        'deadline' => "ALTER TABLE tasks ADD COLUMN deadline DATE DEFAULT NULL AFTER description",
        'file_attachment' => "ALTER TABLE tasks ADD COLUMN file_attachment VARCHAR(255) DEFAULT NULL AFTER deadline",
        'file_name' => "ALTER TABLE tasks ADD COLUMN file_name VARCHAR(255) DEFAULT NULL AFTER file_attachment",
        'file_path' => "ALTER TABLE tasks ADD COLUMN file_path VARCHAR(255) DEFAULT NULL AFTER file_name",
        'file_type' => "ALTER TABLE tasks ADD COLUMN file_type VARCHAR(120) DEFAULT NULL AFTER file_path",
        'submission_file' => "ALTER TABLE tasks ADD COLUMN submission_file VARCHAR(255) DEFAULT NULL AFTER file_attachment",
        'uploaded_by' => "ALTER TABLE tasks ADD COLUMN uploaded_by INT(11) DEFAULT NULL AFTER file_type",
        'created_at' => "ALTER TABLE tasks ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER uploaded_by",
        'submitted_at' => "ALTER TABLE tasks ADD COLUMN submitted_at DATETIME DEFAULT NULL AFTER submission_file",
        'submission_request_key' => "ALTER TABLE tasks ADD COLUMN submission_request_key CHAR(64) DEFAULT NULL AFTER submitted_at",
        'status' => "ALTER TABLE tasks ADD COLUMN status ENUM('pending','submitted','reviewed') NOT NULL DEFAULT 'pending' AFTER submitted_at",
        'ai_score' => "ALTER TABLE tasks ADD COLUMN ai_score INT(11) DEFAULT NULL AFTER status",
        'grammar_score' => "ALTER TABLE tasks ADD COLUMN grammar_score INT(11) DEFAULT NULL AFTER ai_score",
        'spelling_score' => "ALTER TABLE tasks ADD COLUMN spelling_score INT(11) DEFAULT NULL AFTER grammar_score",
        'clarity_score' => "ALTER TABLE tasks ADD COLUMN clarity_score INT(11) DEFAULT NULL AFTER spelling_score",
        'relevance_score' => "ALTER TABLE tasks ADD COLUMN relevance_score INT(11) DEFAULT NULL AFTER clarity_score",
        'ai_feedback' => "ALTER TABLE tasks ADD COLUMN ai_feedback TEXT DEFAULT NULL AFTER relevance_score",
        'hr_score' => "ALTER TABLE tasks ADD COLUMN hr_score INT(11) DEFAULT NULL AFTER ai_feedback",
        'hr_feedback' => "ALTER TABLE tasks ADD COLUMN hr_feedback TEXT DEFAULT NULL AFTER hr_score",
    ];

    $existing = [];
    $result = $conn->query("SHOW COLUMNS FROM tasks");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $existing[$row['Field']] = true;
        }
    }

    foreach ($columns as $column => $sql) {
        if (!isset($existing[$column])) {
            if (!$conn->query($sql)) {
                throw new RuntimeException("Unable to add tasks.$column: " . $conn->error);
            }
        }
    }

    $indexes = $conn->query("SHOW INDEX FROM tasks WHERE Key_name = 'unique_task_assignment_key'");
    if (!$indexes) {
        throw new RuntimeException("Unable to inspect task assignment indexes: " . $conn->error);
    }

    if ($indexes->num_rows === 0 && !$conn->query("ALTER TABLE tasks ADD UNIQUE KEY unique_task_assignment_key (assignment_key)")) {
        throw new RuntimeException("Unable to enforce unique task assignments: " . $conn->error);
    }

    $submissionIndexes = $conn->query("SHOW INDEX FROM tasks WHERE Key_name = 'unique_submission_request_key'");
    if (!$submissionIndexes) {
        throw new RuntimeException("Unable to inspect submission request indexes: " . $conn->error);
    }

    if ($submissionIndexes->num_rows === 0 && !$conn->query("ALTER TABLE tasks ADD UNIQUE KEY unique_submission_request_key (submission_request_key)")) {
        throw new RuntimeException("Unable to enforce idempotent submissions: " . $conn->error);
    }

    $done = true;
}
?>
