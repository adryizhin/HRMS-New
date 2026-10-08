<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['employee']);
require_once __DIR__ . '/../includes/reports_page.php';
renderReportsPage($conn, 'employee');
