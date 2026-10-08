<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['admin']);
require_once __DIR__ . '/../includes/reports_page.php';
renderReportsPage($conn, 'admin');
