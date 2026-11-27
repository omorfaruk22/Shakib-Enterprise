<?php
// SAKIB ENTERPRISE — Front Controller (public/index.php)
require __DIR__ . '/../bootstrap.php';

use App\Core\Router;

$router = new Router();
require __DIR__ . '/../routes/web.php';
require __DIR__ . '/../routes/admin.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
// Support clean URLs: ?route=/tours/... or REQUEST_URI path
$uri = $_GET['route'] ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
// Strip index.php prefix if present
$uri = preg_replace('#^/index\.php#', '', $uri);
if ($uri === '' || $uri === null) $uri = '/';

$router->dispatch($method, $uri);
