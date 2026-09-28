<?php
require '../includes/auth_check.php';
checkRole(['admin']);
require '../config.php';
require_once __DIR__ . '/../includes/attendance_page.php';

<<<<<<< HEAD
renderAttendancePage($conn, 'admin');
=======
renderAttendancePage($conn, 'admin', true);
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
?>
