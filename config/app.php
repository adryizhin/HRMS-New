<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Manila');

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

if (!defined('BASE_URL')) {
<<<<<<< HEAD
    define('BASE_URL', '/hrms');
=======
    $configuredUrl = getenv('HRMS_BASE_URL');
    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $projectRoot = str_replace('\\', '/', BASE_PATH);
    $webRoot = $documentRoot ? rtrim(str_replace('\\', '/', $documentRoot), '/') : '';
    $detectedUrl = $webRoot !== '' && stripos($projectRoot, $webRoot . '/') === 0
        ? substr($projectRoot, strlen($webRoot))
        : '/HRMS';
    define('BASE_URL', $configuredUrl !== false ? rtrim($configuredUrl, '/') : $detectedUrl);
>>>>>>> ea5c816 (partial done system)
}
?>
