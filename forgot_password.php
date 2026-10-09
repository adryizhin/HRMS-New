<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/routes.php';
require_once __DIR__ . '/includes/password_tickets.php';
header('Cache-Control: no-store');
$_SESSION['password_ticket_csrf'] ??= bin2hex(random_bytes(32));
$error = ''; $email = ''; $employeeId = ''; $message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $employeeId = trim((string) ($_POST['employee_id'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    try {
        if (!hash_equals($_SESSION['password_ticket_csrf'], (string) ($_POST['csrf_token'] ?? ''))) {
            http_response_code(400); throw new InvalidArgumentException('Reload the page and try again.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 191 || strlen($employeeId) > 50 || strlen($message) > 2000) {
            throw new InvalidArgumentException('Enter a valid email and keep your message under 2,000 characters.');
        }
        require_once __DIR__ . '/config/database.php';
        ensurePasswordTicketSchema($conn);
        if (!allowPasswordTicketRequest($conn, (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'))) {
            http_response_code(429); throw new InvalidArgumentException('Too many requests. Please try again in 15 minutes.');
        }
        submitPasswordTicket($conn, $email, $employeeId, $message);
        $_SESSION['password_ticket_sent'] = true;
        header('Location: ' . app_url('forgot_password.php')); exit;
    } catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); }
    catch (Throwable $exception) { error_log((string) $exception); $error = 'Unable to submit your request. Please try again later.'; }
}
$sent = !empty($_SESSION['password_ticket_sent']); unset($_SESSION['password_ticket_sent']);
$h = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot password | Pagecom HRIS</title>
  <link rel="icon" href="images/logo.png">
  <link rel="stylesheet" href="login/login.css">
  <link rel="stylesheet" href="login/password_reset.css">
</head>
<body class="reset-page">
<div class="reset-layout">
  <aside class="reset-aside" aria-label="Account recovery guidance">
    <div class="reset-brand">
      <img src="images/logo.png" alt="" width="88" height="42">
      <div><strong>Pagecom INC.</strong><span>Human Resource Information System</span></div>
    </div>
    <div class="reset-guide">
      <span class="reset-eyebrow">We're here to help</span>
      <h2>Get back to<br> <span>your workday.</span></h2>
      <p>Locked out? Request help from your admin to securely restore access to your account.</p>
      <ol class="reset-steps">
        <li><span class="reset-step-number" aria-hidden="true">01</span><div><strong>Submit a request</strong><p>Tell us which account you need help with.</p></div></li>
        <li><span class="reset-step-number" aria-hidden="true">02</span><div><strong>Verify your identity</strong><p>Contact your admin to confirm your details.</p></div></li>
        <li><span class="reset-step-number" aria-hidden="true">03</span><div><strong>Sign in again</strong><p>Your admin will help you with your new password.</p></div></li>
      </ol>
    </div>
    <p class="reset-aside-footer">Account recovery is handled by your administrators.</p>
  </aside>
  <div class="reset-form-panel">
  <main class="reset-card" aria-labelledby="reset-title">
  <header class="reset-header">
    <span class="reset-eyebrow">Account support</span>
    <h1 id="reset-title">Forgot password?</h1>
    <p>Submit a ticket and an admin will help you regain access to your account.</p>
  </header>

  <?php if ($sent): ?>
  <div class="reset-notice reset-notice-success" role="status">
    <strong>Request received</strong>
    <p>If the email matches an active account, your request is with the admins. If you already have an open ticket, it will be kept. Contact your admin to verify your identity and get help signing in.</p>
  </div>
  <?php endif ?>
  <?php if ($error): ?>
  <div class="reset-notice reset-notice-error" role="alert"><strong>Unable to submit request</strong><p><?= $h($error) ?></p></div>
  <?php endif ?>

  <form method="post" class="reset-form">
    <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['password_ticket_csrf']) ?>">
    <div class="field">
      <label for="email">Account email <span class="reset-required">Required</span></label>
      <input id="email" name="email" type="email" maxlength="191" autocomplete="email" placeholder="email@pagecom.com" aria-describedby="email-help" required value="<?= $h($email) ?>">
      <small id="email-help" class="reset-hint">Use the email you normally sign in with.</small>
    </div>
    <div class="field">
      <label for="employee_id">Employee ID <span class="reset-optional">Optional</span></label>
      <input id="employee_id" name="employee_id" maxlength="50" placeholder="Enter your employee ID" value="<?= $h($employeeId) ?>">
    </div>
    <div class="field">
      <label for="message">Message to admin <span class="reset-optional">Optional</span></label>
      <textarea id="message" name="message" rows="4" maxlength="2000" aria-describedby="message-help" placeholder="Tell us about the issue you are having."><?= $h($message) ?></textarea>
      <small id="message-help" class="reset-hint">Up to 2,000 characters. Do not include passwords.</small>
    </div>
    <div class="reset-info">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6l-8-3Z"/><path d="m8 12 3 3 5-6"/></svg>
      <p>An admin will verify your identity before resetting your password.</p>
    </div>
    <button class="btn-signin" type="submit">Submit reset request <span aria-hidden="true">&rarr;</span></button>
  </form>
  <footer class="reset-footer"><a class="forgot-password" href="<?= $h(route('login')) ?>"><span aria-hidden="true">&larr;</span> Back to sign in</a></footer>
</main>
  </div>
</div>
</body>
</html>