<?php
declare(strict_types=1);

define('BASE_PATH', __DIR__);

require BASE_PATH . '/config/env.php';
load_environment(BASE_PATH . '/.env');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require BASE_PATH . '/app/Helpers/functions.php';
require BASE_PATH . '/config/security.php';

$appConfig = require BASE_PATH . '/config/app.php';
date_default_timezone_set($appConfig['timezone']);

$errorLog = BASE_PATH . '/storage/logs/app.log';
set_error_handler(static function (int $severity, string $message, string $file, int $line) use ($appConfig, $errorLog): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    \App\Helpers\Logger::error(sprintf('%s in %s:%d', $message, $file, $line), $errorLog);
    return true;
});

set_exception_handler(static function (Throwable $exception) use ($appConfig, $errorLog): void {
    \App\Helpers\Logger::error($exception::class . ': ' . $exception->getMessage(), $errorLog);
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo $appConfig['debug'] ? 'Application error. See protected local logs.' : 'Something went wrong. Please try again later.';
});
