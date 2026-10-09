<?php
require '../includes/auth_check.php';
checkRole(['admin']);
require '../config.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/chat_contacts.php';

$admin_id = (int) $_SESSION['user_id'];

date_default_timezone_set('Asia/Manila');

/* =========================
   DASHBOARD COUNTS
========================= */

/* TOTAL EMPLOYEES */
$result = $conn->query("
    SELECT COUNT(*) AS total 
    FROM users 
    WHERE role='employee'
");

$totalEmployees = $result ? $result->fetch_assoc()['total'] : 0;

/* TOTAL HR */
$result = $conn->query("
    SELECT COUNT(*) AS total 
    FROM users 
    WHERE role='hr'
");

$totalHR = $result ? $result->fetch_assoc()['total'] : 0;

/* PENDING TASKS */
$result = $conn->query("
    SELECT COUNT(*) AS total 
    FROM tasks
    WHERE status='pending'
");

$pendingRequests = $result ? $result->fetch_assoc()['total'] : 0;

/* TOTAL TASK RECORDS */
$result = $conn->query("
    SELECT COUNT(*) AS total 
    FROM tasks
");

$systemLogs = $result ? $result->fetch_assoc()['total'] : 0;

/* =========================
   LOGIN LOGS
========================= */

$query = "
SELECT * 
FROM login_logs 
ORDER BY id DESC
";

$logs = $conn->query($query);

/* =========================
   ACTIVE USERS
========================= */

$activeUsers = getOnlineUsers($conn);

/* =========================
   TIME AGO FUNCTION
========================= */

function timeAgo(?string $datetime): string {

    if (!$datetime) {
        return "0 sec";
    }

    $time = strtotime($datetime);

    if (!$time) {
        return "0 sec";
    }

    $diff = time() - $time;

    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    $seconds = $diff % 60;

    $output = "";

    if ($hours > 0) {
        $output .= $hours . " hr ";
    }

    if ($minutes > 0) {
        $output .= $minutes . " min ";
    }

    if ($hours == 0 && $seconds > 0) {
        $output .= $seconds . " sec";
    }

    return trim($output);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">
<title>Pagecom HRIS</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="chat.css">

<style>

</style>
</head>

<body>

<?php renderSidebar('admin', 'admin.dashboard'); ?>

<!-- MAIN -->
<div class="main">

<div class="topbar">
  <h1>Welcome, <?= $_SESSION['name']; ?></h1>
  <div style="display:flex; justify-content:flex-end;">
  <div style= "display:inline-block;
   padding:8px 18px;
    background:var(--card-soft);
    border:1px solid var(--border);
    border-radius:20px;
    color:var(--accent);
    font-weight:bold;
    font-size:15px;">Admin</div>
</div>
</div>

<!-- CARDS -->
<div class="cards">
    <div class="card">
      <h3>Total Employees</h3>
      <h1><?= $totalEmployees; ?></h1>
      <i class="fa-solid fa-users"></i>
    </div>

    <div class="card">
      <h3>HR Staff</h3>
      <h1><?= $totalHR; ?></h1>
      <i class="fa-solid fa-user-tie"></i>
    </div>

    <div class="card">
      <h3>Pending Requests</h3>
      <h1><?= $pendingRequests; ?></h1>
      <i class="fa-solid fa-clock"></i>
    </div>

    <div class="card">
      <h3>System Logs</h3>
      <h1><?= $systemLogs; ?></h1>
      <i class="fa-solid fa-database"></i>
    </div>
</div>


<!-- CHAT LIST (HR SELECTION) & ADMIN LIST -->
<div class="dashboard-lower">
<section class="chat-contacts" aria-labelledby="chat-contacts-heading">
  <header class="contacts-header">
    <div class="contacts-title"><span class="contacts-logo" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5H5l-3 2v-8.5A8.5 8.5 0 0 1 10.5 5H13a8 8 0 0 1 8 6.5Z"/><path d="M7 12h.01M12 12h.01M17 12h.01" stroke-width="3" stroke-linecap="round"/></svg></span><div><h2 id="chat-contacts-heading">Messages</h2><p>Connect with your HR team and fellow admins.</p></div></div>
  </header>
  <div class="contacts-search"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg><label class="contacts-sr-only" for="contact-search">Search contacts</label><input id="contact-search" type="search" placeholder="Search contacts" autocomplete="off"></div>
  <div class="contact-groups">
    <?php
    $hrs = $conn->query("SELECT id, full_name FROM users WHERE role='hr' AND status='active' ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);
    $otherAdmins = $conn->query("SELECT id, full_name FROM users WHERE role='admin' AND status='active' AND id != " . $admin_id . " ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);
    renderChatContactGroup('Chat with HR', $hrs, 'hr');
    renderChatContactGroup('Chat with other admins', $otherAdmins, 'admin');
    ?>
  </div>
  <p class="contacts-no-results" role="status" hidden>No contacts match your search.</p>
  <p class="contacts-footer"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5H5l-3 2v-8.5A8.5 8.5 0 0 1 10.5 5H13a8 8 0 0 1 8 6.5Z"/><path d="M7 12h.01M12 12h.01M17 12h.01" stroke-width="3" stroke-linecap="round"/></svg>Select a contact to start a conversation.</p>
</section>
<script src="<?= htmlspecialchars(app_url('admin/chat_contacts.js')); ?>" defer></script>
<aside class="online-users" aria-labelledby="online-users-heading">
    <h2 id="online-users-heading">Online Users</h2>
    <div id="online-users-list"><?php renderOnlineUsers($activeUsers); ?></div>
    <script src="<?= htmlspecialchars(app_url('admin/online_users.js')); ?>" defer></script>
</aside>
</div>
</div>
<!-- MESSAGING POPUP -->
<button id="chatAvatar" type="button" onclick="openChat()" aria-label="Open messages" aria-controls="chatBox" aria-expanded="false">
  <span id="chatNotif" aria-hidden="true"></span>
  <img src="../images/chat-robot-cutout.png" alt="" class="chat-launcher-image">
</button>
<section id="chatBox" data-user-name="<?= htmlspecialchars((string) ($_SESSION['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" role="dialog" aria-modal="false" aria-labelledby="chatContactName" hidden>
  <header class="chat-header">
    <span id="chatContactAvatar" class="chat-contact-avatar" aria-hidden="true">PC</span>
    <div class="chat-contact-heading"><h2 id="chatContactName">Messages</h2><p id="chatContactRole">Choose a contact to get started</p></div>
    <button type="button" class="chat-icon-button" onclick="closeChat()" aria-label="Minimize chat"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14"/></svg></button>
    <button type="button" class="chat-icon-button" onclick="closeChat()" aria-label="Close chat"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
  </header>
  <div class="chat-body" id="chatBody" role="log" aria-label="Conversation" aria-live="polite" aria-relevant="additions"></div>
  <div id="chatError" class="chat-error" role="alert" hidden></div>
  <form id="chatComposer" class="chat-footer">
    <label class="contacts-sr-only" for="chatInput">Message</label>
    <textarea id="chatInput" rows="1" placeholder="Write a message…" aria-describedby="chatComposerHint" disabled></textarea>
    <button id="chatSend" type="submit" aria-label="Send message" disabled><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="m3 3 19 9-19 9 3-8 9-1-9-1-3-8Z"/></svg></button>
  </form>
  <p id="chatComposerHint" class="chat-composer-hint">Enter to send · Shift + Enter for a new line</p>
</section>
<script src="<?= htmlspecialchars(app_url('admin/chat_box.js')); ?>" defer></script>
</body>
</html>
