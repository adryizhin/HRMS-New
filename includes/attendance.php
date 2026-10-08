<?php
declare(strict_types=1);

<<<<<<< HEAD
<<<<<<< HEAD
require_once __DIR__ . '/holidays.php';

=======
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
require_once __DIR__ . '/holidays.php';

>>>>>>> ea5c816 (partial done system)
function ensureAttendanceTable(mysqli $conn): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $conn->query("
        CREATE TABLE IF NOT EXISTS attendance (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            attendance_date DATE NOT NULL,
            shift ENUM('morning','night') NOT NULL,
            login_time DATETIME NOT NULL,
            status ENUM('present','late','absent') NOT NULL,
<<<<<<< HEAD
<<<<<<< HEAD
            late_minutes INT NOT NULL DEFAULT 0,
=======
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
            late_minutes INT NOT NULL DEFAULT 0,
>>>>>>> ea5c816 (partial done system)
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_user_shift_date (user_id, attendance_date, shift),
            KEY user_id (user_id),
            CONSTRAINT attendance_user_fk
                FOREIGN KEY (user_id) REFERENCES users (id)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
    $cols = $conn->query("SHOW COLUMNS FROM attendance LIKE 'late_minutes'");
    if ($cols && $cols->num_rows === 0) {
        $conn->query("ALTER TABLE attendance ADD COLUMN late_minutes INT NOT NULL DEFAULT 0 AFTER status");
    }

<<<<<<< HEAD
=======
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
    $checked = true;
}

function ensureUserShiftColumn(mysqli $conn): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'work_shift'");

    if ($result && $result->num_rows === 0) {
        $conn->query("
            ALTER TABLE users
            ADD COLUMN work_shift ENUM('morning','night') NOT NULL DEFAULT 'morning' AFTER department_id
        ");
    }

    $checked = true;
}

function getAttendanceMark(DateTimeImmutable $loginTime): array
{
    $date = $loginTime->format('Y-m-d');
    $timezone = $loginTime->getTimezone();
    $shift = 'morning';
    $shiftDate = $date;
    $lateCutoff = new DateTimeImmutable($shiftDate . ' 08:30:00', $timezone);
    $absentCutoff = new DateTimeImmutable($shiftDate . ' 17:00:00', $timezone);

    $status = 'present';

    if ($loginTime >= $absentCutoff) {
        $status = 'absent';
    } elseif ($loginTime >= $lateCutoff) {
        $status = 'late';
    }

    return [
        'date' => $shiftDate,
        'shift' => $shift,
        'status' => $status,
    ];
}

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
function lateMinutesForLogin(string $loginTime): int
{
    $login = new DateTimeImmutable($loginTime, new DateTimeZone('Asia/Manila'));
    $cutoff = new DateTimeImmutable($login->format('Y-m-d') . ' 08:30:00', new DateTimeZone('Asia/Manila'));

    if ($login <= $cutoff) {
        return 0;
    }

    return max(0, (int) floor(($login->getTimestamp() - $cutoff->getTimestamp()) / 60));
}

function formatLateDuration(int $lateMinutes): string
{
    if ($lateMinutes <= 0) {
        return '0h 0m';
    }

    $hours = intdiv($lateMinutes, 60);
    $minutes = $lateMinutes % 60;

    return $hours . 'h ' . $minutes . 'm';
}

function markMissingAttendanceAbsent(mysqli $conn): void
{
    ensureAttendanceTable($conn);
    ensureHolidayTable($conn);

    $timezone = new DateTimeZone('Asia/Manila');
    $now = new DateTimeImmutable('now', $timezone);
    $today = $now->setTime(0, 0);
    $endDate = $now >= $today->setTime(17, 0)
        ? $today
        : $today->modify('-1 day');

    $createdAtColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
    if (!$createdAtColumn) {
        throw new RuntimeException("Unable to inspect user creation dates: " . $conn->error);
    }

    if ($createdAtColumn->num_rows > 0) {
        $users = $conn->prepare("
            SELECT id, DATE(created_at) AS start_date
            FROM users
            WHERE status = 'active'
              AND created_at IS NOT NULL
              AND DATE(created_at) <= ?
        ");
        if (!$users) {
            throw new RuntimeException("Unable to find active users for attendance: " . $conn->error);
        }
        $endDateString = $endDate->format('Y-m-d');
        $users->bind_param("s", $endDateString);
    } else {
        $users = $conn->prepare("
            SELECT u.id, COALESCE(MIN(a.attendance_date), ?) AS start_date
            FROM users u
            LEFT JOIN attendance a ON a.user_id = u.id
            WHERE u.status = 'active'
            GROUP BY u.id
        ");
        if (!$users) {
            throw new RuntimeException("Unable to find active users for attendance: " . $conn->error);
        }
        $endDateString = $endDate->format('Y-m-d');
        $users->bind_param("s", $endDateString);
    }

    if (!$users->execute()) {
        throw new RuntimeException("Unable to load active users for attendance: " . $users->error);
    }
    $userRows = $users->get_result();
    $holidayDates = holidayDatesBetween(
        $conn,
        '1900-01-01',
        $endDate->format('Y-m-d')
    );
    $batch = [];
    $flushBatch = static function () use ($conn, &$batch): void {
        if ($batch === []) {
            return;
        }

        $values = [];
        foreach ($batch as [$userId, $attendanceDate]) {
            $values[] = sprintf(
                "(%d, '%s', 'morning', '%s 17:00:00', 'absent', 0)",
                $userId,
                $attendanceDate,
                $attendanceDate
            );
        }

        if (!$conn->query("
            INSERT IGNORE INTO attendance (user_id, attendance_date, shift, login_time, status, late_minutes)
            VALUES " . implode(', ', $values))) {
            throw new RuntimeException("Unable to mark missing attendance as absent: " . $conn->error);
        }
        $batch = [];
    };

    while ($user = $userRows->fetch_assoc()) {
        $startDate = new DateTimeImmutable((string) $user['start_date'], $timezone);
        for ($date = $startDate; $date <= $endDate; $date = $date->modify('+1 day')) {
            $attendanceDate = $date->format('Y-m-d');
            if ((int) $date->format('N') > 5 || isset($holidayDates[$attendanceDate])) {
                continue;
            }

            $userId = (int) $user['id'];
            $batch[] = [$userId, $attendanceDate];
            if (count($batch) >= 500) {
                $flushBatch();
            }
        }
    }
    $flushBatch();
}

<<<<<<< HEAD
=======
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
function recordAttendanceOnLogin(mysqli $conn, int $userId): void
{
    ensureAttendanceTable($conn);
    ensureUserShiftColumn($conn);

    $loginTime = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
    $mark = getAttendanceMark($loginTime);
    $loginAt = $loginTime->format('Y-m-d H:i:s');
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ea5c816 (partial done system)
    $lateMinutes = 0;

    if ($mark['status'] === 'late') {
        $cutoff = new DateTimeImmutable($loginTime->format('Y-m-d') . ' 08:30:00', new DateTimeZone('Asia/Manila'));
        $lateMinutes = max(0, (int) (($loginTime->getTimestamp() - $cutoff->getTimestamp()) / 60));
    }

    $stmt = $conn->prepare("
        INSERT INTO attendance (user_id, attendance_date, shift, login_time, status, late_minutes)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            late_minutes = IF(login_time > VALUES(login_time), VALUES(late_minutes), late_minutes),
<<<<<<< HEAD
=======

    $stmt = $conn->prepare("
        INSERT INTO attendance (user_id, attendance_date, shift, login_time, status)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
>>>>>>> ea5c816 (partial done system)
            status = IF(login_time > VALUES(login_time), VALUES(status), status),
            login_time = IF(login_time > VALUES(login_time), VALUES(login_time), login_time)
    ");
    $stmt->bind_param(
<<<<<<< HEAD
<<<<<<< HEAD
        "issssi",
=======
        "issss",
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
        "issssi",
>>>>>>> ea5c816 (partial done system)
        $userId,
        $mark['date'],
        $mark['shift'],
        $loginAt,
<<<<<<< HEAD
<<<<<<< HEAD
        $mark['status'],
        $lateMinutes
=======
        $mark['status']
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
        $mark['status'],
        $lateMinutes
>>>>>>> ea5c816 (partial done system)
    );
    $stmt->execute();
}

function attendanceSummary(mysqli $conn, ?int $userId = null): array
{
    ensureAttendanceTable($conn);

    if ($userId) {
        $stmt = $conn->prepare("
            SELECT
                SUM(status = 'present') AS present_count,
                SUM(status = 'late') AS late_count,
                SUM(status = 'absent') AS absent_count,
                COUNT(*) AS total_count
            FROM attendance
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
    } else {
        $row = $conn->query("
            SELECT
                SUM(status = 'present') AS present_count,
                SUM(status = 'late') AS late_count,
                SUM(status = 'absent') AS absent_count,
                COUNT(*) AS total_count
            FROM attendance
        ")->fetch_assoc();
    }

    return [
        'present' => (int) ($row['present_count'] ?? 0),
        'late' => (int) ($row['late_count'] ?? 0),
        'absent' => (int) ($row['absent_count'] ?? 0),
        'total' => (int) ($row['total_count'] ?? 0),
    ];
}
?>
