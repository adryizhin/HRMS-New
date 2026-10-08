<?php
// Used only by the disposable feature test server.
if (PHP_SAPI !== 'cli-server' || !str_starts_with((string) getenv('HRMS_DB_NAME'), 'hrms_feature_test_')) {
    http_response_code(403); exit;
}
$root = dirname(__DIR__);
$path = preg_replace('#^/HRMS#', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = realpath($root . $path);
if (!$file || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || str_contains($file, DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR)) {
    http_response_code(404); exit;
}
if (pathinfo($file, PATHINFO_EXTENSION) === 'php') { chdir(dirname($file)); require $file; }
else { $type = ['css' => 'text/css', 'js' => 'text/javascript'][pathinfo($file, PATHINFO_EXTENSION)] ?? 'application/octet-stream'; header('Content-Type: ' . $type); readfile($file); }
