<?php
declare(strict_types=1);

use App\Database\Connection;
use App\Helpers\Logger;
use App\Services\BookingService;
use App\Services\BookingSubmissionException;
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

$router->get('/booking/{service}', static function (array $parameters) use ($pageService, $renderPage, $renderNotFound): void {
    $slug = (string) ($parameters['service'] ?? '');
    $bookingType = BookingService::definition($slug);
    $service = $pageService->service($slug);
    if ($bookingType === null || $service === null) {
        $renderNotFound();
        return;
    }

    start_secure_session();
    $packageType = $bookingType['type'] === 'tour_package' ? 'tour' : ($bookingType['type'] === 'holiday_package' ? 'holiday' : null);
    $packages = $packageType !== null ? $pageService->packages($packageType) : [];
    $requestedPackage = '';
    $packageSlug = $_GET['package'] ?? '';
    if (is_string($packageSlug) && $packageSlug !== '' && $packageType !== null) {
        foreach ($packages as $package) {
            if (($package['slug'] ?? '') === $packageSlug) {
                $requestedPackage = $packageSlug;
                break;
            }
        }
    }
    $requestedDestination = '';
    $destinationSlug = $_GET['destination'] ?? '';
    if (is_string($destinationSlug) && $destinationSlug !== '') {
        $destination = $pageService->destination($destinationSlug);
        $requestedDestination = (string) ($destination['name'] ?? '');
    }

    $renderPage('booking-form', (string) $bookingType['label'], 'Send a travel inquiry to SAKIB ENTERPRISE.', '/booking/' . $slug, [
        'bookingType' => $bookingType,
        'service' => $service,
        'packages' => $packages,
        'shipRoutes' => $slug === 'ship-ticket' ? $pageService->shipRoutes() : [],
        'requestedPackage' => $requestedPackage,
        'requestedDestination' => $requestedDestination,
        'submissionToken' => issue_booking_submission_token(),
        'errors' => [],
        'old' => [],
    ]);
});

$router->post('/booking/submit', static function () use ($pageService, $renderPage, $renderNotFound): void {
    start_secure_session();
    $slug = isset($_POST['service_slug']) && is_string($_POST['service_slug']) ? trim($_POST['service_slug']) : '';
    $bookingType = BookingService::definition($slug);
    $service = $bookingType !== null ? $pageService->service($slug) : null;
    if ($bookingType === null || $service === null) {
        $renderNotFound();
        return;
    }

    $packageType = $bookingType['type'] === 'tour_package' ? 'tour' : ($bookingType['type'] === 'holiday_package' ? 'holiday' : null);
    $packages = $packageType !== null ? $pageService->packages($packageType) : [];
    $shipRoutes = $slug === 'ship-ticket' ? $pageService->shipRoutes() : [];
    $submissionToken = isset($_POST['_submission_token']) && is_string($_POST['_submission_token'])
        ? $_POST['_submission_token']
        : '';
    if (preg_match('/^[a-f0-9]{64}$/D', $submissionToken) !== 1) {
        http_response_code(400);
        $errors = ['_form' => 'This form has expired. Please reload it and try again.'];
        $renderPage('booking-form', (string) $bookingType['label'], 'Send a travel inquiry to SAKIB ENTERPRISE.', '/booking/' . $slug, [
            'bookingType' => $bookingType, 'service' => $service, 'packages' => $packages, 'shipRoutes' => $shipRoutes,
            'requestedPackage' => '', 'requestedDestination' => '', 'submissionToken' => issue_booking_submission_token(),
            'errors' => $errors, 'old' => $_POST,
        ]);
        return;
    }

    if (isset($_SESSION['_booking_submitted'][$submissionToken])) {
        redirect('/booking/success/' . rawurlencode((string) $_SESSION['_booking_submitted'][$submissionToken]), 303);
    }

    $issuedAt = $_SESSION['_booking_forms'][$submissionToken] ?? null;
    $csrf = isset($_POST['_csrf']) && is_string($_POST['_csrf']) ? $_POST['_csrf'] : '';
    $sessionCsrf = isset($_SESSION['_csrf']) && is_string($_SESSION['_csrf']) ? $_SESSION['_csrf'] : '';
    $errors = [];
    if (!is_int($issuedAt) || $issuedAt < time() - 7200) {
        $errors['_form'] = 'This form has expired. Please reload it and try again.';
        http_response_code(400);
    } elseif ($csrf === '' || $sessionCsrf === '' || !hash_equals($sessionCsrf, $csrf)) {
        $errors['_form'] = 'The security check expired. Please review the form and submit it again.';
        http_response_code(403);
    } elseif (trim(is_string($_POST['company_website'] ?? null) ? $_POST['company_website'] : '') !== '') {
        $errors['_form'] = 'The submission could not be verified. Please try again.';
        http_response_code(422);
    } else {
        $bookingService = new BookingService();
        $validation = $bookingService->validate($_POST, $bookingType);
        $errors = $validation['errors'];
        if ($errors !== []) {
            http_response_code(422);
        } elseif (!$pageService->databaseAvailable()) {
            $errors['_form'] = 'We could not save your inquiry because the database is unavailable. Please try again later.';
            http_response_code(503);
        } else {
            try {
                $result = $bookingService->store($validation['data'], $bookingType, $submissionToken);
                unset($_SESSION['_booking_forms'][$submissionToken]);
                $_SESSION['_booking_submitted'] ??= [];
                $_SESSION['_booking_submitted'][$submissionToken] = $result['reference'];
                foreach ($_SESSION['_booking_submitted'] as $usedToken => $reference) {
                    if (!is_string($reference) || !preg_match('/^SE-[0-9]{6}-[A-F0-9]{16}$/D', $reference)) {
                        unset($_SESSION['_booking_submitted'][$usedToken]);
                    }
                }
                if (count($_SESSION['_booking_submitted']) > 20) {
                    $_SESSION['_booking_submitted'] = array_slice($_SESSION['_booking_submitted'], -20, null, true);
                }
                session_write_close();
                redirect('/booking/success/' . rawurlencode($result['reference']), 303);
            } catch (BookingSubmissionException $exception) {
                $errors = $exception->errors;
                http_response_code(422);
            } catch (Throwable $exception) {
                Logger::error('A public booking inquiry could not be saved: ' . $exception->getMessage(), BASE_PATH . '/storage/logs/app.log');
                $errors['_form'] = 'We could not save your inquiry right now. Please try again later.';
                http_response_code(503);
            }
        }
    }

    $requestedPackage = isset($_POST['package_slug']) && is_string($_POST['package_slug']) ? $_POST['package_slug'] : '';
    $requestedDestination = isset($_POST['destination']) && is_string($_POST['destination']) ? $_POST['destination'] : '';
    $renderPage('booking-form', (string) $bookingType['label'], 'Send a travel inquiry to SAKIB ENTERPRISE.', '/booking/' . $slug, [
        'bookingType' => $bookingType,
        'service' => $service,
        'packages' => $packages,
        'shipRoutes' => $shipRoutes,
        'requestedPackage' => $requestedPackage,
        'requestedDestination' => $requestedDestination,
        'submissionToken' => $submissionToken,
        'errors' => $errors,
        'old' => $_POST,
    ]);
});

$router->get('/booking/success/{reference}', static function (array $parameters) use ($renderPage, $renderNotFound): void {
    $reference = strtoupper((string) ($parameters['reference'] ?? ''));
    try {
        $confirmation = (new BookingService())->findPublicConfirmation($reference);
    } catch (Throwable $exception) {
        Logger::error('A booking confirmation page could not be loaded: ' . $exception->getMessage(), BASE_PATH . '/storage/logs/app.log');
        http_response_code(503);
        $renderPage('not-found', 'Confirmation temporarily unavailable', 'Your inquiry may have been saved. Please check again later.', '/booking/confirmation-unavailable');
        return;
    }
    if ($confirmation === null) {
        $renderNotFound();
        return;
    }
    header('Cache-Control: private, no-store, max-age=0');
    $renderPage('booking-success', 'Inquiry received', 'Your travel inquiry has been saved. The team will contact you.', '/booking/success/' . rawurlencode($reference), [
        'confirmation' => $confirmation,
    ]);
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
