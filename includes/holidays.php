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
