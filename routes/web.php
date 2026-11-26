<?php
declare(strict_types=1);

use App\Database\Connection;
use App\Services\HomepageService;

$router->get('/', static function (): void {
    $homepage = (new HomepageService())->load();
    $siteSettings = $homepage['settings'];
    $siteName = trim((string) ($siteSettings['site_name'] ?? 'SAKIB ENTERPRISE')) ?: 'SAKIB ENTERPRISE';
    $pageTitle = trim((string) ($siteSettings['homepage_meta_title'] ?? '')) ?: $siteName . " | Travel Services in Cox's Bazar";
    $pageDescription = trim((string) ($siteSettings['homepage_meta_description'] ?? ''))
        ?: 'Explore travel services, destinations and package inquiries with ' . $siteName . '.';
    $pageCanonical = app_url('/');
    $contentView = BASE_PATH . '/resources/views/pages/home.php';
    header('Content-Type: text/html; charset=utf-8');
    require BASE_PATH . '/resources/views/layouts/public.php';
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