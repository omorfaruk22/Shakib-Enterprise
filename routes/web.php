<?php
declare(strict_types=1);

use App\Database\Connection;

$router->get('/', static function (): void {
    header('Content-Type: text/html; charset=utf-8');
    echo '<main><h1>SAKIB ENTERPRISE</h1><p>Project foundation is ready.</p></main>';
});

$router->get('/health', static function (): void {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'ok';
});

if (($appConfig['environment'] ?? 'production') !== 'production') {
    $router->get('/health/database', static function (): void {
        $databaseConfig = require BASE_PATH . '/config/database.php';
        Connection::fromConfig($databaseConfig)->query('SELECT 1');
        header('Content-Type: text/plain; charset=utf-8');
        echo 'database ok';
    });
}