<?php
declare(strict_types=1);
require_once __DIR__ . '/holidays.php';

function leaveWorkingDates(mysqli $conn, string $start, string $end): array
{
    $first = DateTimeImmutable::createFromFormat('!Y-m-d', $start);
    $last = DateTimeImmutable::createFromFormat('!Y-m-d', $end);
    if (!$first || !$last || $first->format('Y-m-d') !== $start || $last->format('Y-m-d') !== $end || $first > $last || $first->diff($last)->days > 366) {
        throw new InvalidArgumentException('Select valid dates in order, covering no more than 366 days.');
    }
    $holidays = holidayDatesBetween($conn, $start, $end);
    $dates = [];
    for ($day = $first; $day <= $last; $day = $day->modify('+1 day')) {
        $date = $day->format('Y-m-d');
        if ((int) $day->format('N') <= 5 && !isset($holidays[$date])) {
            $dates[] = $date;
        }
    }
    return $dates;
}

function ensureLeaveSchema(mysqli $conn): void
{
    static $ready = false;
    if ($ready) return;
    ensureHolidayTable($conn);
    $column = $conn->query("SHOW COLUMNS FROM leave_requests LIKE 'request_key'");
    if ($column->num_rows === 0) {
        $conn->query('ALTER TABLE leave_requests ADD COLUMN request_key CHAR(64) NULL, ADD UNIQUE KEY unique_leave_request_key (request_key)');
    }
    $conn->query('CREATE TABLE IF NOT EXISTS leave_request_days (request_id INT NOT NULL, leave_date DATE NOT NULL, PRIMARY KEY (request_id, leave_date), FOREIGN KEY (request_id) REFERENCES leave_requests(id) ON DELETE CASCADE) ENGINE=InnoDB');
    $column = $conn->query("SHOW COLUMNS FROM leave_requests LIKE 'days_initialized'");
    if ($column->num_rows === 0) $conn->query('ALTER TABLE leave_requests ADD COLUMN days_initialized TINYINT NOT NULL DEFAULT 0');
    // Preserve historical requests and initialize their working-day charges once.
    $legacy = $conn->query('SELECT id, start_date, end_date FROM leave_requests WHERE days_initialized = 0');
    $insert = $conn->prepare('INSERT IGNORE INTO leave_request_days (request_id, leave_date) VALUES (?, ?)');
    while ($row = $legacy->fetch_assoc()) {
        $id = (int) $row['id'];
        foreach (leaveWorkingDates($conn, $row['start_date'], $row['end_date']) as $date) {
            $insert->bind_param('is', $id, $date);
            $insert->execute();
        }
        $conn->query("UPDATE leave_requests SET days_initialized = 1 WHERE id = $id");
    }
    $ready = true;
}

function remainingLeaveDays(mysqli $conn, int $userId, int $year): int
{
    ensureLeaveSchema($conn);
    $stmt = $conn->prepare("SELECT COUNT(*) AS used FROM leave_request_days d JOIN leave_requests r ON d.request_id = r.id WHERE r.employee_id = ? AND r.status IN ('pending','approved') AND YEAR(d.leave_date) = ?");
    $stmt->bind_param('ii', $userId, $year);
    $stmt->execute();
    return max(0, 15 - (int) $stmt->get_result()->fetch_assoc()['used']);
}

function submitLeaveRequest(mysqli $conn, int $userId, int $hrId, string $type, string $start, string $end, string $reason, string $key): void
{
    ensureLeaveSchema($conn);
    if (!in_array($type, ['Sick Leave', 'Vacation Leave'], true) || trim($reason) === '' || !preg_match('/\A[a-f0-9]{64}\z/', $key)) {
        throw new InvalidArgumentException('Complete the leave form and reload if the request has expired.');
    }
    $dates = leaveWorkingDates($conn, $start, $end);
    if (!$dates) throw new InvalidArgumentException('The request must include at least one working day.');
    $conn->begin_transaction();
    try {
        $lock = $conn->prepare('SELECT id FROM users WHERE id = ? FOR UPDATE');
        $lock->bind_param('i', $userId);
        $lock->execute();
        if (!$lock->get_result()->fetch_assoc()) throw new InvalidArgumentException('Unable to find your employee account.');
        $existing = $conn->prepare('SELECT id FROM leave_requests WHERE request_key = ? AND employee_id = ?');
        $existing->bind_param('si', $key, $userId);
        $existing->execute();
        if ($existing->get_result()->num_rows) { $conn->commit(); return; }
        $hr = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'hr' AND status = 'active'");
        $hr->bind_param('i', $hrId);
        $hr->execute();
        if (!$hr->get_result()->num_rows) throw new InvalidArgumentException('Select an active HR reviewer.');
        $years = [];
        foreach ($dates as $date) { $year = (int) substr($date, 0, 4); $years[$year] = ($years[$year] ?? 0) + 1; }
        foreach ($years as $year => $count) {
            if ($count > remainingLeaveDays($conn, $userId, $year)) throw new InvalidArgumentException("Insufficient leave balance for $year.");
        }
        $overlap = $conn->prepare("SELECT id FROM leave_requests WHERE employee_id = ? AND status IN ('pending','approved') AND start_date <= ? AND end_date >= ?");
        $overlap->bind_param('iss', $userId, $end, $start);
        $overlap->execute();
        if ($overlap->get_result()->num_rows) throw new InvalidArgumentException('These dates overlap an existing leave request.');
        $stmt = $conn->prepare('INSERT INTO leave_requests (employee_id, hr_id, leave_type, start_date, end_date, reason, request_key, days_initialized) VALUES (?, ?, ?, ?, ?, ?, ?, 1)');
        $stmt->bind_param('iisssss', $userId, $hrId, $type, $start, $end, $reason, $key);
        $stmt->execute();
        $id = $conn->insert_id;
        $insert = $conn->prepare('INSERT INTO leave_request_days (request_id, leave_date) VALUES (?, ?)');
        foreach ($dates as $date) { $insert->bind_param('is', $id, $date); $insert->execute(); }
        $conn->commit();
    } catch (Throwable $error) { $conn->rollback(); throw $error; }
}
