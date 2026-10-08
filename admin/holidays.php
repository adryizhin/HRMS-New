<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['admin']);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/holidays.php';

ensureHolidayTable($conn);

if (empty($_SESSION['holiday_csrf_token'])) {
    $_SESSION['holiday_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = (string) $_SESSION['holiday_csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals($csrfToken, $postedToken)) {
        http_response_code(400);
        exit('Invalid request token. Reload the page and try again.');
    }

    $action = (string) ($_POST['action'] ?? '');
    $holidayDate = trim((string) ($_POST['holiday_date'] ?? ''));
    $name = trim((string) ($_POST['name'] ?? ''));
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $holidayDate);
    $validDate = $date && $date->format('Y-m-d') === $holidayDate;

    try {
        if ($action === 'delete') {
            if (!$validDate) {
                throw new InvalidArgumentException('Select a valid holiday date.');
            }
            $stmt = $conn->prepare("DELETE FROM holidays WHERE holiday_date = ?");
            if (!$stmt) {
                throw new RuntimeException("Unable to prepare holiday deletion: " . $conn->error);
            }
            $stmt->bind_param("s", $holidayDate);
            if (!$stmt->execute()) {
                throw new RuntimeException("Unable to delete holiday: " . $stmt->error);
            }
            header('Location: holidays.php?notice=deleted');
            exit;
        }

        if ($name === '' || strlen($name) > 150 || !$validDate) {
            throw new InvalidArgumentException('Enter a valid holiday name and date.');
        }

        if ($action === 'add') {
            $stmt = $conn->prepare("
                INSERT INTO holidays (holiday_date, name)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE name = VALUES(name)
            ");
            if (!$stmt) {
                throw new RuntimeException("Unable to prepare holiday save: " . $conn->error);
            }
            $stmt->bind_param("ss", $holidayDate, $name);
        } elseif ($action === 'edit') {
            $originalDate = trim((string) ($_POST['original_date'] ?? ''));
            $original = DateTimeImmutable::createFromFormat('!Y-m-d', $originalDate);
            if (!$original || $original->format('Y-m-d') !== $originalDate) {
                throw new InvalidArgumentException('Select a valid existing holiday date.');
            }
            $stmt = $conn->prepare("
                UPDATE holidays
                SET holiday_date = ?, name = ?
                WHERE holiday_date = ?
            ");
            if (!$stmt) {
                throw new RuntimeException("Unable to prepare holiday update: " . $conn->error);
            }
            $stmt->bind_param("sss", $holidayDate, $name, $originalDate);
        } else {
            throw new InvalidArgumentException('Unsupported holiday action.');
        }

        if (!$stmt->execute()) {
            throw new RuntimeException("Unable to save holiday: " . $stmt->error);
        }

        $removeGeneratedAbsences = $conn->prepare("
            DELETE FROM attendance
            WHERE attendance_date = ?
              AND status = 'absent'
              AND login_time = CONCAT(?, ' 17:00:00')
        ");
        if (!$removeGeneratedAbsences) {
            throw new RuntimeException("Holiday saved, but generated absences could not be refreshed: " . $conn->error);
        }
        $removeGeneratedAbsences->bind_param("ss", $holidayDate, $holidayDate);
        if (!$removeGeneratedAbsences->execute()) {
            throw new RuntimeException("Holiday saved, but generated absences could not be refreshed: " . $removeGeneratedAbsences->error);
        }

        header('Location: holidays.php?notice=saved');
        exit;
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    } catch (mysqli_sql_exception $exception) {
        $error = $exception->getCode() === 1062
            ? 'A holiday already exists for that date.'
            : 'Unable to save the holiday: ' . $exception->getMessage();
    } catch (RuntimeException $exception) {
        $error = $exception->getMessage();
    }
}

$holidays = $conn->query("
    SELECT holiday_date, name
    FROM holidays
    ORDER BY holiday_date DESC
");
if (!$holidays) {
    throw new RuntimeException("Unable to load holidays: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Holiday Calendar</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="dashboard.css">
<style>
.holiday-page { max-width: 1100px; margin: 0 auto; }
.holiday-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; margin: 18px 0; padding: 20px; }
.holiday-form, .holiday-row { align-items: center; display: grid; gap: 12px; grid-template-columns: minmax(150px, 220px) minmax(180px, 1fr) auto; }
.holiday-form input, .holiday-row input { background: var(--card-soft); border: 1px solid var(--border); border-radius: 8px; color: var(--text); padding: 10px 12px; }
.holiday-form button, .holiday-row button { background: var(--accent); border: 0; border-radius: 8px; color: var(--on-accent); cursor: pointer; font-weight: 600; padding: 10px 14px; }
.holiday-row { border-top: 1px solid var(--border); padding: 12px 0; }
.holiday-row form { margin: 0; }
.holiday-row .delete-button { background: #b91c1c; color: #ffffff; }
.holiday-notice { background: #dcfce7; border-radius: 8px; color: #166534; margin-top: 14px; padding: 12px; }
.holiday-error { background: #fee2e2; border-radius: 8px; color: #991b1b; margin-top: 14px; padding: 12px; }
[data-theme="dark"] .holiday-notice { background: #14532d; color: #bbf7d0; }
[data-theme="dark"] .holiday-error { background: #7f1d1d; color: #fecaca; }
.holiday-empty { color: var(--muted); padding: 16px 0; }
@media (max-width: 700px) { .holiday-form, .holiday-row { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<?php renderSidebar('admin', 'admin.holidays'); ?>
<main class="main holiday-page">
    <h1><i class="fa-solid fa-calendar-days"></i> Holiday Calendar</h1>
    <p style="color:var(--muted);">Configured holidays are excluded from automatic absence marking and payroll workday counts.</p>
    <p style="color:var(--muted);">Philippine nationwide non-working holidays for 2026 and 2027 are added automatically from official proclamations. The 2027 Eid dates await separate announcements and can be added here when declared. Local holidays can also be added below. Changes and deletions are preserved. Future yearly proclamations must be added to the calendar data before that year's automatic import.</p>

    <?php if (!empty($error)): ?>
        <div class="holiday-error"><?= htmlspecialchars($error); ?></div>
    <?php elseif (($_GET['notice'] ?? '') === 'saved'): ?>
        <div class="holiday-notice">Holiday saved. Any auto-generated absence for that date was removed.</div>
    <?php elseif (($_GET['notice'] ?? '') === 'deleted'): ?>
        <div class="holiday-notice">Holiday removed from the calendar.</div>
    <?php endif; ?>

    <section class="holiday-card">
        <h2>Add Holiday</h2>
        <form class="holiday-form" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="add">
            <input type="date" name="holiday_date" required>
            <input type="text" name="name" maxlength="150" placeholder="Holiday name" required>
            <button type="submit"><i class="fa-solid fa-plus"></i> Add Holiday</button>
        </form>
    </section>

    <section class="holiday-card">
        <h2>Configured Holidays</h2>
        <?php if ($holidays->num_rows === 0): ?>
            <p class="holiday-empty">No holidays configured yet.</p>
        <?php else: ?>
            <?php while ($holiday = $holidays->fetch_assoc()): ?>
                <div class="holiday-row">
                    <form id="holiday-<?= htmlspecialchars($holiday['holiday_date']); ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="original_date" value="<?= htmlspecialchars($holiday['holiday_date']); ?>">
                        <input type="date" name="holiday_date" value="<?= htmlspecialchars($holiday['holiday_date']); ?>" required>
                    </form>
                    <input form="holiday-<?= htmlspecialchars($holiday['holiday_date']); ?>" name="name" type="text" value="<?= htmlspecialchars($holiday['name']); ?>" maxlength="150" required>
                    <div style="display:flex;gap:8px;">
                        <button form="holiday-<?= htmlspecialchars($holiday['holiday_date']); ?>" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="holiday_date" value="<?= htmlspecialchars($holiday['holiday_date']); ?>">
                            <button class="delete-button" type="submit" onclick="return confirm('Remove this holiday?')"><i class="fa-solid fa-trash"></i> Delete</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
