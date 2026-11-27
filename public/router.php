<?php
// PHP's built-in server router: serve existing public assets, route app URLs.
$requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$publicRoot = realpath(__DIR__);
$requestedFile = realpath(__DIR__ . $requestPath);

if ($requestPath !== '/' && $publicRoot && $requestedFile
    && str_starts_with($requestedFile, $publicRoot . DIRECTORY_SEPARATOR)
    && is_file($requestedFile)) {
    return false;
}

require __DIR__ . '/index.php';
