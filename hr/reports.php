<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['hr']);
require_once __DIR__ . '/../includes/reports_page.php';
renderReportsPage($conn, 'hr');
