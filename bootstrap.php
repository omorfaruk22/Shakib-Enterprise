<?php
// SAKIB ENTERPRISE — Bootstrap
// Load .env (simple parser, no external dependency)
$envFile = __DIR__ . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        if (str_starts_with($v, '"') && str_ends_with($v, '"')) {
            $v = substr($v, 1, -1);
        } elseif (str_starts_with($v, "'") && str_ends_with($v, "'")) {
            $v = substr($v, 1, -1);
        }
        if (getenv($k) === false) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
}

date_default_timezone_set('Asia/Dhaka');
error_reporting(E_ALL);
ini_set('display_errors', getenv('APP_DEBUG') === 'true' ? '1' : '0');
ini_set('log_errors', '1');

// Secure session defaults
if (session_status() === PHP_SESSION_NONE) {
    $secure = (getenv('SESSION_SECURE') === 'true') || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 1800),
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Autoloader (PSR-4-ish for App\)
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'App\\')) {
        $path = __DIR__ . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($path)) {
            require $path;
        }
    }
});

require __DIR__ . '/app/Core/helpers.php';

// Global error handler — log, don't expose details in production
set_exception_handler(function ($e) {
    \App\Core\Logger::error('Uncaught: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (getenv('APP_DEBUG') === 'true') {
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    } else {
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><title>Error</title></head><body style="font-family:sans-serif;text-align:center;padding:60px 20px"><h1>Something went wrong</h1><p>Please try again later or contact us.</p><p><a href="/">Go Home</a></p></body></html>';
    }
});
