<?php
declare(strict_types=1);

// Environment variables take priority; shared hosts can use hosting.local.php.
function hrmsHostingSetting(string $key, string $default): string
{
    $environment = getenv($key);
    if ($environment !== false) {
        return $environment;
    }

    static $settings = null;
    if ($settings === null) {
        $file = __DIR__ . '/hosting.local.php';
        $settings = is_file($file) ? require $file : [];
        if (!is_array($settings)) {
            throw new RuntimeException('Hosting configuration must return an array.');
        }
    }

    return isset($settings[$key]) ? (string) $settings[$key] : $default;
}
