<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $requestedFile = is_string($requestPath)
        ? realpath(__DIR__ . rawurldecode($requestPath))
        : false;
    $publicRoot = realpath(__DIR__);
    $staticExtensions = ['avif', 'css', 'gif', 'ico', 'jpeg', 'jpg', 'js', 'png', 'svg', 'webp', 'woff', 'woff2'];

    if (
        $requestedFile !== false
        && $publicRoot !== false
        && is_file($requestedFile)
        && str_starts_with(strtolower($requestedFile), strtolower($publicRoot . DIRECTORY_SEPARATOR))
        && in_array(strtolower(pathinfo($requestedFile, PATHINFO_EXTENSION)), $staticExtensions, true)
    ) {
        return false;
    }
}

require dirname(__DIR__) . '/bootstrap.php';

use App\Helpers\Router;

$router = new Router();
require BASE_PATH . '/routes/web.php';

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
