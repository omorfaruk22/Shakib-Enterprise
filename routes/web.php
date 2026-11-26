<?php
declare(strict_types=1);

use App\Database\Connection;
use App\Services\HomepageService;
use App\Services\PublicPagesService;

$pageService = new PublicPagesService();
$renderPage = static function (string $view, string $title, string $description, string $canonical, array $data = []) use ($pageService): void {
    header('Content-Type: text/html; charset=utf-8');
    $siteSettings = $pageService->settings();
    $siteName = trim((string) ($siteSettings['site_name'] ?? 'SAKIB ENTERPRISE')) ?: 'SAKIB ENTERPRISE';
    $pageTitle = str_contains($title, $siteName) ? $title : $title . ' | ' . $siteName;
    $pageDescription = $description;
    $pageCanonical = app_url($canonical);
    $contentView = BASE_PATH . '/resources/views/pages/' . $view . '.php';
    $pageServiceForView = $pageService;
    extract($data, EXTR_SKIP);
    $pageService = $pageServiceForView;
    require BASE_PATH . '/resources/views/layouts/public.php';
};
$renderNotFound = static function () use ($renderPage): void {
    http_response_code(404);
    $renderPage('not-found', 'Page not found', 'The requested page could not be found.', '/404');
};

$router->get('/', static function () use ($renderPage): void {
    $homepage = (new HomepageService())->load();
    $settings = $homepage['settings'];
    $siteName = trim((string) ($settings['site_name'] ?? 'SAKIB ENTERPRISE')) ?: 'SAKIB ENTERPRISE';
    $title = trim((string) ($settings['homepage_meta_title'] ?? '')) ?: $siteName . " | Travel Services in Cox's Bazar";
    $description = trim((string) ($settings['homepage_meta_description'] ?? ''))
        ?: 'Explore travel services, destinations and package inquiries with ' . $siteName . ' in ' . ($settings['office_address'] ?? "Cox's Bazar Sadar, Bangladesh") . '.';
    $renderPage('home', $title, $description, '/', ['homepage' => $homepage]);
});

$router->get('/services', static function () use ($pageService, $renderPage): void {
    $renderPage('services', 'Travel Services', 'Explore air, ship and bus ticket inquiries, tours, and holiday packages.', '/services', [
        'services' => $pageService->services(),
    ]);
});

$serviceAliases = [
    '/air-tickets' => 'air-ticket',
    '/ship-tickets' => 'ship-ticket',
    '/bus-tickets' => 'bus-ticket',
    '/tour-travel' => 'tour-travel',
    '/holiday-packages' => 'holiday-package',
];
foreach ($serviceAliases as $path => $slug) {
    $router->get($path, static function () use ($pageService, $renderPage, $renderNotFound, $slug, $path): void {
        $service = $pageService->service($slug);
        if ($service === null) {
            $renderNotFound();
            return;
        }
        $renderPage('service', (string) $service['name'], (string) $service['description'], $path, [
            'service' => $service,
            'shipRoutes' => $slug === 'ship-ticket' ? $pageService->shipRoutes() : [],
        ]);
    });
}

$router->get('/services/{slug}', static function (array $parameters) use ($pageService, $renderPage, $renderNotFound): void {
    $slug = (string) ($parameters['slug'] ?? '');
    $service = $pageService->service($slug);
    if ($service === null) {
        $renderNotFound();
        return;
    }
    $renderPage('service', (string) $service['name'], (string) $service['description'], '/services/' . rawurlencode($slug), [
        'service' => $service,
        'shipRoutes' => $slug === 'ship-ticket' ? $pageService->shipRoutes() : [],
    ]);
});

$router->get('/destinations', static function () use ($pageService, $renderPage): void {
    $renderPage('destinations', 'Destinations', 'Browse confirmed destinations published by SAKIB ENTERPRISE.', '/destinations', [
        'destinations' => $pageService->destinations(),
    ]);
});

$router->get('/destinations/{slug}', static function (array $parameters) use ($pageService, $renderPage, $renderNotFound): void {
    $slug = (string) ($parameters['slug'] ?? '');
    $destination = $pageService->destination($slug);
    if ($destination === null) {
        $renderNotFound();
        return;
    }
    $renderPage('destination', (string) $destination['name'], (string) ($destination['description'] ?? 'Travel information for this destination.'), '/destinations/' . rawurlencode($slug), [
        'destination' => $destination,
    ]);
});

$router->get('/packages', static function () use ($pageService, $renderPage): void {
    $requestedType = $_GET['type'] ?? null;
    $packageType = is_string($requestedType) && in_array($requestedType, ['tour', 'holiday'], true) ? $requestedType : null;
    $renderPage('packages', $packageType === 'tour' ? 'Tour Packages' : ($packageType === 'holiday' ? 'Holiday Packages' : 'Travel Packages'), 'Browse active tour and holiday packages published by the business.', '/packages' . ($packageType !== null ? '?type=' . $packageType : ''), [
        'packages' => $pageService->packages($packageType),
        'packageType' => $packageType,
    ]);
});

$router->get('/packages/{slug}', static function (array $parameters) use ($pageService, $renderPage, $renderNotFound): void {
    $slug = (string) ($parameters['slug'] ?? '');
    $package = $pageService->package($slug);
    if ($package === null) {
        $renderNotFound();
        return;
    }
    $description = trim((string) ($package['summary'] ?? '')) ?: 'Package details and travel inquiry information.';
    $renderPage('package', (string) $package['title'], $description, '/packages/' . rawurlencode($slug), ['package' => $package]);
});

$router->get('/about-us', static function () use ($pageService, $renderPage): void {
    $aboutPage = $pageService->aboutPage();
    $title = trim((string) ($aboutPage['meta_title'] ?? '')) ?: (string) $aboutPage['title'];
    $description = trim((string) ($aboutPage['meta_description'] ?? '')) ?: 'Learn about SAKIB ENTERPRISE and its travel inquiry support in Cox\'s Bazar.';
    $renderPage('about', $title, $description, '/about-us', ['aboutPage' => $aboutPage]);
});

$router->get('/contact-us', static function () use ($pageService, $renderPage): void {
    $requestedService = '';
    $requestedSlug = $_GET['service'] ?? '';
    if (is_string($requestedSlug) && $requestedSlug !== '') {
        $service = $pageService->service($requestedSlug);
        $requestedService = (string) ($service['name'] ?? '');
    }
    $requestedPackage = '';
    $packageSlug = $_GET['package'] ?? '';
    if (is_string($packageSlug) && $packageSlug !== '') {
        $package = $pageService->package($packageSlug);
        $requestedPackage = (string) ($package['title'] ?? '');
    }
    $requestedDestination = '';
    $destinationSlug = $_GET['destination'] ?? '';
    if (is_string($destinationSlug) && $destinationSlug !== '') {
        $destination = $pageService->destination($destinationSlug);
        $requestedDestination = (string) ($destination['name'] ?? '');
    }
    $renderPage('contact', 'Contact Us', 'Contact the business about travel services, destinations and packages.', '/contact-us', [
        'requestedService' => $requestedService,
        'requestedPackage' => $requestedPackage,
        'requestedDestination' => $requestedDestination,
    ]);
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
