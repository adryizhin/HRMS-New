<?php
require '../includes/auth_check.php';
checkRole(['hr']);
require '../config.php';
require_once __DIR__ . '/../includes/attendance_page.php';
<<<<<<< HEAD
<<<<<<< HEAD
renderAttendancePage($conn, 'hr');
=======

renderAttendancePage($conn, 'hr', true);
>>>>>>> ec6e45aa1a7825e015d77a9dd001ecf071781d8b
=======
renderAttendancePage($conn, 'hr');
>>>>>>> ea5c816 (partial done system)
?>
