<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['hr']);
require __DIR__ . '/../employee/dashboard.php';
