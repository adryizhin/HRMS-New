<?php
require '../includes/auth_check.php';
checkRole(['employee']);
require '../config.php';
require_once __DIR__ . '/../includes/attendance_page.php';

<<<<<<< HEAD
renderAttendancePage($conn, 'employee');
=======
renderAttendancePage($conn, 'employee', false);
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
?>
