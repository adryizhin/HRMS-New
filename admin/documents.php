<?php
require_once __DIR__ . '/../includes/auth_check.php';
checkRole(['admin']);
require __DIR__ . '/../employee/documents.php';
