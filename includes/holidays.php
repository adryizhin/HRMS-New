<?php
declare(strict_types=1);

function ensureHolidayTable(mysqli $conn): void
{
    static $ready = false;

    if ($ready) {
        return;
    }

    if (!$conn->query("
        CREATE TABLE IF NOT EXISTS holidays (
            holiday_date DATE NOT NULL,
            name VARCHAR(150) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (holiday_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ")) {
        throw new RuntimeException("Unable to create holiday calendar: " . $conn->error);
    }

<<<<<<< HEAD
=======
    $conn->query('CREATE TABLE IF NOT EXISTS holiday_imports (calendar_year INT NOT NULL PRIMARY KEY, imported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
    $calendars = require __DIR__ . '/../config/philippine_holidays.php';
    foreach ($calendars as $year => $dates) {
        $imported = $conn->query('SELECT calendar_year FROM holiday_imports WHERE calendar_year = ' . (int) $year);
        if ($imported->num_rows > 0) continue;
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare('INSERT IGNORE INTO holidays (holiday_date, name) VALUES (?, ?)');
            $hasAttendance = $conn->query("SHOW TABLES LIKE 'attendance'")->num_rows > 0;
            foreach ($dates as $monthDay => $name) {
                $date = $year . '-' . $monthDay;
                $stmt->bind_param('ss', $date, $name);
                $stmt->execute();
                if ($hasAttendance) {
                    $cleanup = $conn->prepare("DELETE FROM attendance WHERE attendance_date = ? AND status = 'absent' AND login_time = CONCAT(?, ' 17:00:00')");
                    $cleanup->bind_param('ss', $date, $date);
                    $cleanup->execute();
                }
            }
            $conn->query('INSERT IGNORE INTO holiday_imports (calendar_year) VALUES (' . (int) $year . ')');
            $conn->commit();
        } catch (Throwable $error) { $conn->rollback(); throw $error; }
    }
>>>>>>> ea5c816 (partial done system)
    $ready = true;
}

function holidayDatesBetween(mysqli $conn, string $startDate, string $endDate): array
{
    ensureHolidayTable($conn);

    $stmt = $conn->prepare("
        SELECT holiday_date
        FROM holidays
        WHERE holiday_date BETWEEN ? AND ?
    ");
    if (!$stmt) {
        throw new RuntimeException("Unable to load holiday dates: " . $conn->error);
    }
    $stmt->bind_param("ss", $startDate, $endDate);
    if (!$stmt->execute()) {
        throw new RuntimeException("Unable to load holiday dates: " . $stmt->error);
    }

    $dates = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $dates[$row['holiday_date']] = true;
    }

    return $dates;
}
?>
