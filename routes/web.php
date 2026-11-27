<?php
declare(strict_types=1);

use App\Database\Connection;
use App\Helpers\Logger;
use App\Services\BookingService;
use App\Services\BookingSubmissionException;
use App\Services\ContactMessageService;
use App\Services\CustomerAuthService;
use App\Services\CustomerPortalService;
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
    $customerAccount = (new CustomerAuthService())->currentCustomer();
    if ($customerAccount === null) {
        $nextParameters = [];
        foreach (['package', 'destination'] as $parameterName) {
            $parameterValue = $_GET[$parameterName] ?? null;
            if (is_string($parameterValue) && preg_match('/^[a-z0-9-]{1,180}$/D', $parameterValue) === 1) {
                $nextParameters[$parameterName] = $parameterValue;
            }
        }
        $nextPath = '/booking/' . $slug . ($nextParameters === [] ? '' : '?' . http_build_query($nextParameters, '', '&', PHP_QUERY_RFC3986));
        $_SESSION['_customer_after_login'] = $nextPath;
        $_SESSION['_login_notice'] = 'Sign in or create a customer account to continue with your booking inquiry.';
        redirect('/login?next=' . rawurlencode($nextPath), 303);
    }
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
        'authenticatedCustomer' => $customerAccount,
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

    $customerAccount = (new CustomerAuthService())->currentCustomer();
    if ($customerAccount === null) {
        $nextPath = '/booking/' . $slug;
        $_SESSION['_customer_after_login'] = $nextPath;
        $_SESSION['_login_notice'] = 'Sign in or create a customer account to submit a booking inquiry.';
        redirect('/login?next=' . rawurlencode($nextPath), 303);
    }
    $bookingInput = $_POST;
    $bookingInput['full_name'] = (string) $customerAccount['full_name'];
    $bookingInput['phone'] = (string) $customerAccount['phone'];
    $bookingInput['email'] = (string) $customerAccount['account_email'];

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
            'bookingType' => $bookingType,
            'authenticatedCustomer' => $customerAccount, 'service' => $service, 'packages' => $packages, 'shipRoutes' => $shipRoutes,
            'requestedPackage' => '', 'requestedDestination' => '', 'submissionToken' => issue_booking_submission_token(),
            'errors' => $errors, 'old' => $bookingInput,
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
        $validation = $bookingService->validate($bookingInput, $bookingType);
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
            'authenticatedCustomer' => $customerAccount,
        'service' => $service,
        'packages' => $packages,
        'shipRoutes' => $shipRoutes,
        'requestedPackage' => $requestedPackage,
        'requestedDestination' => $requestedDestination,
        'submissionToken' => $submissionToken,
        'errors' => $errors,
        'old' => $bookingInput,
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

$router->get('/pages/{slug}', static function (array $parameters) use ($pageService, $renderPage, $renderNotFound): void {
    $slug = (string) ($parameters['slug'] ?? '');
    $page = $pageService->page($slug);
    if ($page === null) {
        $renderNotFound();
        return;
    }
    $title = trim((string) ($page['meta_title'] ?? '')) ?: (string) $page['title'];
    $description = trim((string) ($page['meta_description'] ?? '')) ?: 'Learn more about SAKIB ENTERPRISE.';
    $renderPage('content-page', $title, $description, '/pages/' . rawurlencode($slug), ['page' => $page]);
});

$contactContext = static function () use ($pageService): array {
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
    return [
        'requestedService' => $requestedService,
        'requestedPackage' => $requestedPackage,
        'requestedDestination' => $requestedDestination,
    ];
};

$renderContact = static function (array $state = []) use ($contactContext, $renderPage): void {
    $context = $contactContext();
    $renderPage('contact', 'Contact Us', 'Contact SAKIB ENTERPRISE about travel services, destinations and packages.', '/contact-us', [
        ...$context,
        'csrfToken' => csrf_token(),
        'errors' => $state['errors'] ?? [],
        'old' => $state['old'] ?? [],
        'messageSent' => (bool) ($state['messageSent'] ?? false),
    ]);
};

$router->get('/contact-us', static function () use ($renderContact): void {
    start_secure_session();
    $state = is_array($_SESSION['_contact_form'] ?? null) ? $_SESSION['_contact_form'] : [];
    unset($_SESSION['_contact_form']);
    $state['messageSent'] = ($_SESSION['_contact_sent'] ?? false) === true;
    unset($_SESSION['_contact_sent']);
    $renderContact($state);
});

$router->post('/contact-us', static function () use ($renderContact): void {
    start_secure_session();
    if (!is_valid_csrf_token($_POST['_csrf'] ?? null)) {
        http_response_code(403);
        $renderContact([
            'errors' => ['_form' => 'The security check expired. Please review the form and submit it again.'],
            'old' => $_POST,
        ]);
        return;
    }

    if (trim(is_string($_POST['company_website'] ?? null) ? $_POST['company_website'] : '') !== '') {
        $_SESSION['_contact_sent'] = true;
        redirect('/contact-us', 303);
    }

    $contactMessages = new ContactMessageService();
    $validation = $contactMessages->validate($_POST);
    if ($validation['errors'] !== []) {
        $_SESSION['_contact_form'] = ['errors' => $validation['errors'], 'old' => $_POST];
        redirect('/contact-us', 303);
    }

    try {
        $contactMessages->store($validation['data']);
        $_SESSION['_contact_sent'] = true;
        redirect('/contact-us', 303);
    } catch (Throwable $exception) {
        Logger::error('A public contact message could not be saved: ' . $exception->getMessage(), BASE_PATH . '/storage/logs/app.log');
        $_SESSION['_contact_form'] = [
            'errors' => ['_form' => 'We could not send your message right now. Please try again later.'],
            'old' => $_POST,
        ];
        redirect('/contact-us', 303);
    }
});

$renderAuthLogin = static function (
    string $nextPath = '/customer/dashboard',
    ?string $loginError = null,
    array $registerErrors = [],
    array $oldRegistration = [],
    ?string $notice = null
) use ($renderPage): void {
    start_secure_session();
    $renderPage('auth-login', 'Sign in or create account', 'Browse travel options publicly, then sign in to send a booking inquiry.', '/login', [
        'csrfToken' => csrf_token(),
        'nextPath' => CustomerAuthService::safeReturnPath($nextPath) ?? '/customer/dashboard',
        'loginError' => $loginError,
        'registerErrors' => $registerErrors,
        'oldRegistration' => $oldRegistration,
        'notice' => $notice,
    ]);
};

$router->get('/login', static function () use ($renderPage, $renderAuthLogin): void {
    start_secure_session();
    $auth = new CustomerAuthService();
    $portal = new CustomerPortalService();
    $nextCandidate = $_GET['next'] ?? $_SESSION['_customer_after_login'] ?? null;
    $nextPath = CustomerAuthService::safeReturnPath($nextCandidate) ?? '/customer/dashboard';
    if (isset($_GET['next'])) {
        $_SESSION['_customer_after_login'] = $nextPath;
    }
    if ($auth->currentCustomerId() !== null || $portal->currentCustomerId() !== null) {
        redirect($nextPath, 303);
    }
    $notice = is_string($_SESSION['_login_notice'] ?? null) ? $_SESSION['_login_notice'] : null;
    unset($_SESSION['_login_notice']);
    $renderAuthLogin($nextPath, null, [], [], $notice);
});

$router->post('/login', static function () use ($renderAuthLogin): void {
    start_secure_session();
    $nextPath = CustomerAuthService::safeReturnPath($_POST['next'] ?? $_SESSION['_customer_after_login'] ?? null)
        ?? '/customer/dashboard';
    if (!is_valid_csrf_token($_POST['_csrf'] ?? null)) {
        http_response_code(403);
        $renderAuthLogin($nextPath, 'The security check expired. Reload the page and try again.');
        return;
    }
    $result = (new CustomerAuthService())->login(
        $_POST['email'] ?? null,
        $_POST['password'] ?? null,
        (string) ($_SERVER['REMOTE_ADDR'] ?? '')
    );
    if ($result['ok']) {
        unset($_SESSION['_customer_after_login'], $_SESSION['_login_notice']);
        redirect($nextPath, 303);
    }
    http_response_code($result['rate_limited'] ? 429 : 401);
    $renderAuthLogin(
        $nextPath,
        $result['rate_limited'] ? 'Too many attempts. Wait 15 minutes and try again.' : 'Email address or password was not recognized.'
    );
});

$router->post('/register', static function () use ($renderAuthLogin): void {
    start_secure_session();
    $nextPath = CustomerAuthService::safeReturnPath($_POST['next'] ?? $_SESSION['_customer_after_login'] ?? null)
        ?? '/customer/dashboard';
    if (!is_valid_csrf_token($_POST['_csrf'] ?? null)) {
        http_response_code(403);
        $renderAuthLogin($nextPath, null, ['_form' => 'The security check expired. Reload the page and try again.']);
        return;
    }
    $result = (new CustomerAuthService())->register($_POST, (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    if ($result['ok']) {
        unset($_SESSION['_customer_after_login'], $_SESSION['_login_notice']);
        redirect($nextPath, 303);
    }
    http_response_code(422);
    $oldRegistration = [
        'full_name' => $_POST['full_name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'booking_reference' => $_POST['booking_reference'] ?? '',
    ];
    $renderAuthLogin($nextPath, null, $result['errors'], $oldRegistration);
});



$customerLoginTitle = 'Customer booking status';
$renderCustomerLogin = static function (?string $errorMessage = null) use ($renderPage, $customerLoginTitle): void {
    start_secure_session();
    $renderPage('customer-login', $customerLoginTitle, 'Sign in securely to review the latest status of your travel inquiries.', '/customer/login', [
        'csrfToken' => csrf_token(),
        'errorMessage' => $errorMessage,
    ]);
};

$router->get('/customer/login', static function () use ($renderCustomerLogin): void {
    $customerPortal = new CustomerPortalService();
    if ($customerPortal->currentCustomerId() !== null || (new CustomerAuthService())->currentCustomerId() !== null) {
        redirect('/customer/dashboard', 303);
    }
    $renderCustomerLogin();
});

$router->post('/customer/login', static function () use ($renderCustomerLogin): void {
    start_secure_session();
    if (!is_valid_csrf_token($_POST['_csrf'] ?? null)) {
        http_response_code(403);
        $renderCustomerLogin('The security check expired. Reload this page and try again.');
        return;
    }

    $customerPortal = new CustomerPortalService();
    $result = $customerPortal->authenticate(
        $_POST['booking_reference'] ?? null,
        $_POST['phone'] ?? null,
        (string) ($_SERVER['REMOTE_ADDR'] ?? '')
    );
    if ($result['ok']) {
        redirect('/customer/dashboard', 303);
    }

    if ($result['rate_limited']) {
        http_response_code(429);
        $renderCustomerLogin('Too many attempts. Please wait 15 minutes and try again.');
        return;
    }

    http_response_code(401);
    $renderCustomerLogin('The booking reference and phone number did not match. Check them and try again.');
});

$router->get('/customer/dashboard', static function () use ($renderPage): void {
    $customerPortal = new CustomerPortalService();
    $customerAuth = new CustomerAuthService();
    $customerId = $customerAuth->currentCustomerId() ?? $customerPortal->currentCustomerId();
    if ($customerId === null) {
        redirect('/login', 303);
    }

    $data = $customerPortal->dashboard($customerId);
    if ($data['customer'] === []) {
        $customerAuth->logout();
        $customerPortal->logout();
        redirect('/login', 303);
    }

    $renderPage('customer-dashboard', 'My travel inquiries', 'Review your inquiry references and their latest status.', '/customer/dashboard', [
        'csrfToken' => csrf_token(),
        'customer' => $data['customer'],
        'bookings' => $data['bookings'],
        'bookingTotal' => $data['total'],
    ]);
});

$router->post('/customer/logout', static function (): void {
    $customerAuth = new CustomerAuthService();
    $customerPortal = new CustomerPortalService();
    $accountCustomerId = $customerAuth->currentCustomerId();
    $portalCustomerId = $customerPortal->currentCustomerId();
    if ($accountCustomerId === null && $portalCustomerId === null) {
        redirect('/login', 303);
    }
    if (!is_valid_csrf_token($_POST['_csrf'] ?? null)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Security check failed';
        return;
    }
    if ($accountCustomerId !== null) {
        $customerAuth->logout();
    }
    if ($portalCustomerId !== null) {
        $customerPortal->logout();
    }
    redirect($accountCustomerId !== null ? '/login' : '/customer/login', 303);
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

require BASE_PATH . '/routes/admin.php';
