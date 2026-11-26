<?php
declare(strict_types=1);

use App\Helpers\Logger;
use App\Services\AdminActivityService;
use App\Services\AdminAuthService;
use App\Services\AdminCatalogService;
use App\Services\AdminContentService;
use App\Services\AdminDashboardService;
use App\Services\BookingManagementService;
use App\Services\ContactMessageManagementService;
use App\Services\CustomerManagementService;
use App\Services\MediaManagementService;

$renderAdmin = static function (string $view, string $title, array $data = []): void {
    start_secure_session();
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: private, no-store, max-age=0');
    $csrfToken = csrf_token();
    $admin = is_array($data['admin'] ?? null) ? $data['admin'] : null;
    $adminFlash = is_array($_SESSION['_admin_flash'] ?? null) ? $_SESSION['_admin_flash'] : null;
    unset($_SESSION['_admin_flash']);
    $adminCurrentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH);
    $adminCurrentPath = is_string($adminCurrentPath) ? $adminCurrentPath : '/admin';
    $adminPageTitle = $title;
    $adminContentView = BASE_PATH . '/resources/views/pages/' . $view . '.php';
    extract($data, EXTR_SKIP);
    require BASE_PATH . '/resources/views/layouts/admin.php';
};

$setAdminFlash = static function (string $type, string $message): void {
    start_secure_session();
    $_SESSION['_admin_flash'] = ['type' => $type, 'message' => $message];
};

$requireAdmin = static function (): array {
    $admin = (new AdminAuthService())->currentAdmin();
    if ($admin !== null) {
        return $admin;
    }

    start_secure_session();
    $uri = $_SERVER['REQUEST_URI'] ?? '/admin';
    $path = parse_url($uri, PHP_URL_PATH);
    $query = parse_url($uri, PHP_URL_QUERY);
    if (is_string($path) && ($path === '/admin' || str_starts_with($path, '/admin/'))) {
        $next = $path . (is_string($query) && $query !== '' ? '?' . $query : '');
        if (strlen($next) <= 1024 && !str_contains($next, '\\') && !str_starts_with($next, '//')) {
            $_SESSION['_admin_return_to'] = $next;
        }
    }
    redirect('/admin/login', 303);
};

$requireAdminCsrf = static function (array $admin) use ($renderAdmin): bool {
    if (is_valid_csrf_token($_POST['_csrf'] ?? null)) {
        return true;
    }
    http_response_code(403);
    $renderAdmin('admin/error', 'Security check failed', [
        'admin' => $admin,
        'errorMessage' => 'The security token is invalid or expired. Reload the page and try again.',
    ]);
    return false;
};

$flashAndRedirect = static function (string $type, string $message, string $path) use ($setAdminFlash): never {
    $setAdminFlash($type, $message);
    redirect($path, 303);
};

$adminErrorMessage = static function (\Throwable $exception, string $context): string {
    if ($exception instanceof \PDOException) {
        Logger::error('Admin operation failed (' . $context . '): ' . $exception->getMessage(), BASE_PATH . '/storage/logs/app.log');
        return 'The operation could not be completed. Check the entered values and try again.';
    }
    return $exception->getMessage();
};

$positiveId = static function (mixed $value): ?int {
    if (!is_string($value) && !is_int($value)) {
        return null;
    }
    $id = filter_var($value, FILTER_VALIDATE_INT);
    return $id !== false && $id > 0 ? $id : null;
};

$adminNotFound = static function (array $admin) use ($renderAdmin): void {
    http_response_code(404);
    $renderAdmin('admin/error', 'Record not found', [
        'admin' => $admin,
        'errorMessage' => 'The requested admin page or record could not be found.',
    ]);
};

$router->get('/admin/login', static function () use ($renderAdmin, $positiveId): void {
    start_secure_session();
    $auth = new AdminAuthService();
    $admin = $auth->currentAdmin();
    if ($admin !== null) {
        redirect('/admin', 303);
    }
    $renderAdmin('admin/login', 'Admin sign in', [
        'admin' => null,
        'errors' => [],
        'old' => [],
        'setupAvailable' => $auth->setupAvailable(),
    ]);
});

$router->post('/admin/login', static function () use ($renderAdmin): void {
    start_secure_session();
    if (!is_valid_csrf_token($_POST['_csrf'] ?? null)) {
        http_response_code(403);
        $renderAdmin('admin/login', 'Admin sign in', [
            'admin' => null,
            'errors' => ['_form' => 'The security check expired. Reload the page and try again.'],
            'old' => [],
            'setupAvailable' => (new AdminAuthService())->setupAvailable(),
        ]);
        return;
    }
    $email = is_string($_POST['email'] ?? null) ? $_POST['email'] : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $returnTo = $_SESSION['_admin_return_to'] ?? '/admin';
    $result = (new AdminAuthService())->login($email, $password, (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    if ($result['ok']) {
        $validReturnPath = is_string($returnTo)
            && ($returnTo === '/admin' || str_starts_with($returnTo, '/admin/'))
            && strlen($returnTo) <= 1024
            && !str_contains($returnTo, '\\')
            && !str_starts_with($returnTo, '//');
        redirect($validReturnPath ? $returnTo : '/admin', 303);
    }
    http_response_code($result['rate_limited'] ? 429 : 401);
    $renderAdmin('admin/login', 'Admin sign in', [
        'admin' => null,
        'errors' => ['_form' => $result['rate_limited']
            ? 'Too many failed attempts. Wait 15 minutes before trying again.'
            : 'Email address or password was not recognized.'],
        'old' => ['email' => $email],
        'setupAvailable' => (new AdminAuthService())->setupAvailable(),
    ]);
});

$router->get('/admin/setup', static function () use ($renderAdmin, $appConfig): void {
    start_secure_session();
    $auth = new AdminAuthService();
    $secureRequired = ($appConfig['environment'] ?? 'production') === 'production' && !request_is_https();
    if ($secureRequired) {
        http_response_code(403);
    }
    $renderAdmin('admin/setup', 'One-time administrator setup', [
        'admin' => null,
        'errors' => $secureRequired ? ['_form' => 'First-admin setup requires HTTPS in production.'] : [],
        'old' => [],
        'setupConfigured' => $auth->setupConfigured(),
        'setupAvailable' => $auth->setupAvailable(),
    ]);
});

$router->post('/admin/setup', static function () use ($renderAdmin, $flashAndRedirect, $appConfig): void {
    start_secure_session();
    $auth = new AdminAuthService();
    $secureRequired = ($appConfig['environment'] ?? 'production') === 'production' && !request_is_https();
    if ($secureRequired) {
        http_response_code(403);
        $renderAdmin('admin/setup', 'One-time administrator setup', [
            'admin' => null,
            'errors' => ['_form' => 'First-admin setup requires HTTPS in production.'],
            'old' => [],
            'setupConfigured' => $auth->setupConfigured(),
            'setupAvailable' => $auth->setupAvailable(),
        ]);
        return;
    }
    if (!is_valid_csrf_token($_POST['_csrf'] ?? null)) {
        http_response_code(403);
        $renderAdmin('admin/setup', 'One-time administrator setup', [
            'admin' => null,
            'errors' => ['_form' => 'The security check expired. Reload the page and try again.'],
            'old' => [],
            'setupConfigured' => $auth->setupConfigured(),
            'setupAvailable' => $auth->setupAvailable(),
        ]);
        return;
    }
    $result = $auth->createFirstAdmin($_POST, (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    if ($result['ok']) {
        $flashAndRedirect('success', 'Administrator account created. Sign in with the credentials you just set.', '/admin/login');
    }
    http_response_code(422);
    $renderAdmin('admin/setup', 'One-time administrator setup', [
        'admin' => null,
        'errors' => $result['errors'],
        'old' => ['name' => $_POST['name'] ?? '', 'email' => $_POST['email'] ?? ''],
        'setupConfigured' => $auth->setupConfigured(),
        'setupAvailable' => $auth->setupAvailable(),
    ]);
});

$router->post('/admin/logout', static function () use ($requireAdmin, $requireAdminCsrf): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) {
        return;
    }
    (new AdminAuthService())->logout();
    redirect('/admin/login', 303);
});

$router->get('/admin', static function () use ($requireAdmin, $renderAdmin): void {
    $admin = $requireAdmin();
    $dashboard = (new AdminDashboardService())->load();
    $renderAdmin('admin/dashboard', 'Dashboard', [...$dashboard, 'admin' => $admin]);
});

$router->get('/admin/bookings/export.csv', static function () use ($requireAdmin): void {
    $requireAdmin();
    $filters = [
        'q' => is_string($_GET['q'] ?? null) ? $_GET['q'] : '',
        'status' => is_string($_GET['status'] ?? null) ? $_GET['status'] : '',
        'service_type' => is_string($_GET['service_type'] ?? null) ? $_GET['service_type'] : '',
        'date_from' => is_string($_GET['date_from'] ?? null) ? $_GET['date_from'] : '',
        'date_to' => is_string($_GET['date_to'] ?? null) ? $_GET['date_to'] : '',
    ];
    $csv = (new BookingManagementService())->exportCsv($filters);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="sakib-bookings-' . date('Y-m-d') . '.csv"');
    header('Cache-Control: private, no-store, max-age=0');
    echo "\xEF\xBB\xBF" . $csv;
});

$router->get('/admin/bookings', static function () use ($requireAdmin, $renderAdmin): void {
    $admin = $requireAdmin();
    $filters = [
        'q' => is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '',
        'status' => is_string($_GET['status'] ?? null) ? $_GET['status'] : '',
        'service_type' => is_string($_GET['service_type'] ?? null) ? $_GET['service_type'] : '',
        'date_from' => is_string($_GET['date_from'] ?? null) ? $_GET['date_from'] : '',
        'date_to' => is_string($_GET['date_to'] ?? null) ? $_GET['date_to'] : '',
    ];
    $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
    $page = $page === false ? 1 : max(1, $page);
    $service = new BookingManagementService();
    try {
        $result = $service->list($filters, $page, 25);
    } catch (\InvalidArgumentException $exception) {
        http_response_code(422);
        $renderAdmin('admin/error', 'Invalid filters', ['admin' => $admin, 'errorMessage' => $exception->getMessage()]);
        return;
    }
    $exportUrl = app_url('/admin/bookings/export.csv?' . http_build_query($filters));
    $pageUrl = static fn (int $number): string => app_url('/admin/bookings?' . http_build_query([...$filters, 'page' => $number]));
    $renderAdmin('admin/bookings', 'Booking inquiries', [
        'admin' => $admin,
        'result' => $result,
        'filters' => $filters,
        'statuses' => \App\Repositories\BookingRepository::STATUSES,
        'exportUrl' => $exportUrl,
        'pageUrl' => $pageUrl,
    ]);
});

$router->get('/admin/bookings/{id}', static function (array $parameters) use ($requireAdmin, $renderAdmin, $positiveId, $adminNotFound): void {
    $admin = $requireAdmin();
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) {
        $adminNotFound($admin);
        return;
    }
    $service = new BookingManagementService();
    $booking = $service->find($id);
    if ($booking === null) {
        $adminNotFound($admin);
        return;
    }
    $renderAdmin('admin/booking-detail', 'Booking inquiry', [
        'admin' => $admin,
        'booking' => $booking,
        'history' => $service->history($id),
        'notes' => $service->notes($id),
        'statuses' => \App\Repositories\BookingRepository::STATUSES,
    ]);
});

$router->post('/admin/bookings/{id}/details', static function (array $parameters) use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { redirect('/admin/bookings', 303); }
    try {
        (new BookingManagementService())->updateDetails($id, $_POST);
        $flashAndRedirect('success', 'Travel details saved.', '/admin/bookings/' . $id);
    } catch (\InvalidArgumentException | \RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'booking details'), '/admin/bookings/' . $id);
    }
});

$router->post('/admin/bookings/{id}/status', static function (array $parameters) use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { redirect('/admin/bookings', 303); }
    $status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';
    $note = is_string($_POST['note'] ?? null) ? $_POST['note'] : null;
    try {
        (new BookingManagementService())->changeStatus($id, $status, $note);
        $flashAndRedirect('success', 'Booking status updated.', '/admin/bookings/' . $id);
    } catch (\InvalidArgumentException | \RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'booking status'), '/admin/bookings/' . $id);
    }
});

$router->post('/admin/bookings/{id}/notes', static function (array $parameters) use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { redirect('/admin/bookings', 303); }
    $note = is_string($_POST['note'] ?? null) ? $_POST['note'] : '';
    try {
        (new BookingManagementService())->addNote($id, $note);
        $flashAndRedirect('success', 'Internal note added.', '/admin/bookings/' . $id);
    } catch (\InvalidArgumentException | \RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'booking note'), '/admin/bookings/' . $id);
    }
});

$router->post('/admin/bookings/{id}/delete', static function (array $parameters) use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { redirect('/admin/bookings', 303); }
    if (($_POST['confirm_delete'] ?? '') !== '1') {
        $flashAndRedirect('error', 'Confirm deletion before removing the inquiry.', '/admin/bookings/' . $id);
    }
    try {
        (new BookingManagementService())->delete($id);
        $flashAndRedirect('success', 'Booking inquiry deleted. The customer record was retained.', '/admin/bookings');
    } catch (\RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'booking delete'), '/admin/bookings/' . $id);
    }
});

$router->get('/admin/customers', static function () use ($requireAdmin, $renderAdmin): void {
    $admin = $requireAdmin();
    $search = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
    $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
    try {
        $result = (new CustomerManagementService())->list($search, $page === false ? 1 : max(1, $page), 25);
    } catch (\InvalidArgumentException $exception) {
        http_response_code(422);
        $renderAdmin('admin/error', 'Invalid search', ['admin' => $admin, 'errorMessage' => $exception->getMessage()]);
        return;
    }
    $renderAdmin('admin/customers', 'Customers', ['admin' => $admin, 'result' => $result, 'search' => $search]);
});

$router->get('/admin/customers/{id}', static function (array $parameters) use ($requireAdmin, $renderAdmin, $positiveId, $adminNotFound): void {
    $admin = $requireAdmin();
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { $adminNotFound($admin); return; }
    $service = new CustomerManagementService();
    $customer = $service->find($id);
    if ($customer === null) { $adminNotFound($admin); return; }
    $renderAdmin('admin/customer-detail', 'Customer details', [
        'admin' => $admin,
        'customer' => $customer,
        'bookings' => $service->bookings($id),
        'bookingCount' => $service->bookingCount($id),
    ]);
});

$router->post('/admin/customers/{id}/update', static function (array $parameters) use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { redirect('/admin/customers', 303); }
    try {
        (new CustomerManagementService())->update($id, $_POST);
        $flashAndRedirect('success', 'Customer details updated.', '/admin/customers/' . $id);
    } catch (\InvalidArgumentException | \RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'customer update'), '/admin/customers/' . $id);
    }
});

$router->post('/admin/customers/{id}/delete', static function (array $parameters) use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { redirect('/admin/customers', 303); }
    if (($_POST['confirm_delete'] ?? '') !== '1') {
        $flashAndRedirect('error', 'Confirm deletion before removing the customer.', '/admin/customers/' . $id);
    }
    try {
        (new CustomerManagementService())->delete($id);
        $flashAndRedirect('success', 'Customer deleted.', '/admin/customers');
    } catch (\RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'customer delete'), '/admin/customers/' . $id);
    }
});

$router->get('/admin/messages/export.csv', static function () use ($requireAdmin): void {
    $requireAdmin();
    $filters = [
        'q' => is_string($_GET['q'] ?? null) ? $_GET['q'] : '',
        'status' => is_string($_GET['status'] ?? null) ? $_GET['status'] : '',
        'date_from' => is_string($_GET['date_from'] ?? null) ? $_GET['date_from'] : '',
        'date_to' => is_string($_GET['date_to'] ?? null) ? $_GET['date_to'] : '',
    ];
    $csv = (new ContactMessageManagementService())->exportCsv($filters);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="sakib-messages-' . date('Y-m-d') . '.csv"');
    header('Cache-Control: private, no-store, max-age=0');
    echo "\xEF\xBB\xBF" . $csv;
});

$router->get('/admin/messages', static function () use ($requireAdmin, $renderAdmin): void {
    $admin = $requireAdmin();
    $filters = [
        'q' => is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '',
        'status' => is_string($_GET['status'] ?? null) ? $_GET['status'] : '',
    ];
    $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
    try {
        $result = (new ContactMessageManagementService())->list($filters, $page === false ? 1 : max(1, $page), 25);
    } catch (\InvalidArgumentException $exception) {
        http_response_code(422);
        $renderAdmin('admin/error', 'Invalid filters', ['admin' => $admin, 'errorMessage' => $exception->getMessage()]);
        return;
    }
    $renderAdmin('admin/messages', 'Contact messages', [
        'admin' => $admin,
        'result' => $result,
        'filters' => $filters,
        'statuses' => \App\Repositories\ContactMessageRepository::STATUSES,
        'exportUrl' => app_url('/admin/messages/export.csv?' . http_build_query($filters)),
    ]);
});

$router->get('/admin/messages/{id}', static function (array $parameters) use ($requireAdmin, $renderAdmin, $positiveId, $adminNotFound): void {
    $admin = $requireAdmin();
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { $adminNotFound($admin); return; }
    $message = (new ContactMessageManagementService())->find($id);
    if ($message === null) { $adminNotFound($admin); return; }
    $renderAdmin('admin/message-detail', 'Contact message', [
        'admin' => $admin,
        'message' => $message,
        'statuses' => \App\Repositories\ContactMessageRepository::STATUSES,
    ]);
});

$router->post('/admin/messages/{id}/update', static function (array $parameters) use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { redirect('/admin/messages', 303); }
    $status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';
    $note = is_string($_POST['admin_note'] ?? null) ? $_POST['admin_note'] : null;
    try {
        (new ContactMessageManagementService())->update($id, $status, $note);
        $flashAndRedirect('success', 'Contact message updated.', '/admin/messages/' . $id);
    } catch (\InvalidArgumentException | \RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'contact update'), '/admin/messages/' . $id);
    }
});

$router->post('/admin/messages/{id}/delete', static function (array $parameters) use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { redirect('/admin/messages', 303); }
    if (($_POST['confirm_delete'] ?? '') !== '1') {
        $flashAndRedirect('error', 'Confirm deletion before removing this message.', '/admin/messages/' . $id);
    }
    try {
        (new ContactMessageManagementService())->delete($id);
        $flashAndRedirect('success', 'Contact message deleted.', '/admin/messages');
    } catch (\RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'contact delete'), '/admin/messages/' . $id);
    }
});

$catalogPathMap = [
    'services' => 'services',
    'destinations' => 'destinations',
    'packages' => 'packages',
    'ship-routes' => 'ship_routes',
];
$catalogTitles = [
    'services' => 'Services',
    'destinations' => 'Destinations',
    'packages' => 'Packages',
    'ship_routes' => 'Ship routes',
];
$catalogResource = static function (string $path, array $admin) use ($catalogPathMap, $renderAdmin): ?string {
    if (!isset($catalogPathMap[$path])) {
        http_response_code(404);
        $renderAdmin('admin/error', 'Catalog not found', ['admin' => $admin, 'errorMessage' => 'The requested catalog section does not exist.']);
        return null;
    }
    return $catalogPathMap[$path];
};

$router->get('/admin/catalog/{resource}', static function (array $parameters) use ($requireAdmin, $renderAdmin, $positiveId, $catalogResource, $catalogTitles): void {
    $admin = $requireAdmin();
    $pathName = (string) ($parameters['resource'] ?? '');
    $resource = $catalogResource($pathName, $admin);
    if ($resource === null) { return; }
    $search = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
    $activeFilter = is_string($_GET['active'] ?? null) ? $_GET['active'] : '';
    $active = $activeFilter === '1' ? true : ($activeFilter === '0' ? false : null);
    $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
    $service = new AdminCatalogService();
    try {
        $result = $service->list($resource, $search, $active, $page === false ? 1 : max(1, $page), 25);
    } catch (\InvalidArgumentException $exception) {
        http_response_code(422);
        $renderAdmin('admin/error', 'Invalid filters', ['admin' => $admin, 'errorMessage' => $exception->getMessage()]);
        return;
    }
    $editId = $positiveId($_GET['edit'] ?? null);
    $record = $editId === null ? null : $service->find($resource, $editId);
    if ($editId !== null && $record === null) { http_response_code(404); }
    $destinationChoices = $resource === 'packages'
        ? $service->list('destinations', '', null, 1, 100)['items']
        : [];
    $renderAdmin('admin/catalog', $catalogTitles[$resource], [
        'admin' => $admin,
        'resource' => $resource,
        'resourcePathName' => $pathName,
        'title' => $catalogTitles[$resource],
        'result' => $result,
        'record' => $record,
        'search' => $search,
        'activeFilter' => $activeFilter,
        'destinationChoices' => $destinationChoices,
    ]);
});

$router->post('/admin/catalog/{resource}/save', static function (array $parameters) use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $catalogResource, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $pathName = (string) ($parameters['resource'] ?? '');
    $resource = $catalogResource($pathName, $admin);
    if ($resource === null) { return; }
    $id = $positiveId($_POST['id'] ?? null);
    try {
        (new AdminCatalogService())->save($resource, $id, $_POST);
        $flashAndRedirect('success', 'Catalog record saved.', '/admin/catalog/' . $pathName);
    } catch (\InvalidArgumentException | \RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'catalog save'), '/admin/catalog/' . $pathName . ($id === null ? '' : '?edit=' . $id));
    }
});

$router->post('/admin/catalog/{resource}/{id}/delete', static function (array $parameters) use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $catalogResource, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $pathName = (string) ($parameters['resource'] ?? '');
    $resource = $catalogResource($pathName, $admin);
    if ($resource === null) { return; }
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { redirect('/admin/catalog/' . $pathName, 303); }
    if (($_POST['confirm_delete'] ?? '') !== '1') {
        $flashAndRedirect('error', 'Confirm deletion before removing this record.', '/admin/catalog/' . $pathName . '?edit=' . $id);
    }
    try {
        (new AdminCatalogService())->delete($resource, $id);
        $flashAndRedirect('success', 'Catalog record deleted.', '/admin/catalog/' . $pathName);
    } catch (\InvalidArgumentException | \RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'catalog delete'), '/admin/catalog/' . $pathName . '?edit=' . $id);
    }
});

$router->get('/admin/content', static function () use ($requireAdmin): void {
    $requireAdmin();
    redirect('/admin/content/pages', 303);
});

$router->get('/admin/content/pages', static function () use ($requireAdmin, $renderAdmin, $positiveId): void {
    $admin = $requireAdmin();
    $search = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
    $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
    $service = new AdminContentService();
    try {
        $result = $service->listPages($search, $page === false ? 1 : max(1, $page), 25);
    } catch (\InvalidArgumentException $exception) {
        http_response_code(422);
        $renderAdmin('admin/error', 'Invalid search', ['admin' => $admin, 'errorMessage' => $exception->getMessage()]);
        return;
    }
    $editId = $positiveId($_GET['edit'] ?? null);
    $pageRecord = $editId === null ? null : $service->findPage($editId);
    if ($editId !== null && $pageRecord === null) { http_response_code(404); }
    $renderAdmin('admin/pages', 'Website pages', ['admin' => $admin, 'result' => $result, 'search' => $search, 'pageRecord' => $pageRecord]);
});

$router->post('/admin/content/pages/save', static function () use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $id = $positiveId($_POST['id'] ?? null);
    try {
        (new AdminContentService())->savePage($id, $_POST);
        $flashAndRedirect('success', 'Website page saved.', '/admin/content/pages');
    } catch (\InvalidArgumentException | \RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'page save'), '/admin/content/pages' . ($id === null ? '' : '?edit=' . $id));
    }
});

$router->post('/admin/content/pages/{id}/delete', static function (array $parameters) use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { redirect('/admin/content/pages', 303); }
    if (($_POST['confirm_delete'] ?? '') !== '1') { $flashAndRedirect('error', 'Confirm page deletion first.', '/admin/content/pages?edit=' . $id); }
    try {
        (new AdminContentService())->deletePage($id);
        $flashAndRedirect('success', 'Website page deleted.', '/admin/content/pages');
    } catch (\RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'page delete'), '/admin/content/pages?edit=' . $id);
    }
});

$router->get('/admin/testimonials', static function () use ($requireAdmin, $renderAdmin, $positiveId): void {
    $admin = $requireAdmin();
    $search = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
    $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
    $service = new AdminContentService();
    try {
        $result = $service->listTestimonials($search, $page === false ? 1 : max(1, $page), 25);
    } catch (\InvalidArgumentException $exception) {
        http_response_code(422);
        $renderAdmin('admin/error', 'Invalid search', ['admin' => $admin, 'errorMessage' => $exception->getMessage()]);
        return;
    }
    $editId = $positiveId($_GET['edit'] ?? null);
    $testimonialRecord = $editId === null ? null : $service->findTestimonial($editId);
    if ($editId !== null && $testimonialRecord === null) { http_response_code(404); }
    $renderAdmin('admin/testimonials', 'Testimonials', ['admin' => $admin, 'result' => $result, 'search' => $search, 'testimonialRecord' => $testimonialRecord]);
});

$router->post('/admin/testimonials/save', static function () use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $id = $positiveId($_POST['id'] ?? null);
    try {
        (new AdminContentService())->saveTestimonial($id, $_POST);
        $flashAndRedirect('success', 'Testimonial saved.', '/admin/testimonials');
    } catch (\InvalidArgumentException | \RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'testimonial save'), '/admin/testimonials' . ($id === null ? '' : '?edit=' . $id));
    }
});

$router->post('/admin/testimonials/{id}/delete', static function (array $parameters) use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { redirect('/admin/testimonials', 303); }
    if (($_POST['confirm_delete'] ?? '') !== '1') { $flashAndRedirect('error', 'Confirm testimonial deletion first.', '/admin/testimonials?edit=' . $id); }
    try {
        (new AdminContentService())->deleteTestimonial($id);
        $flashAndRedirect('success', 'Testimonial deleted.', '/admin/testimonials');
    } catch (\RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'testimonial delete'), '/admin/testimonials?edit=' . $id);
    }
});

$router->get('/admin/settings', static function () use ($requireAdmin, $renderAdmin): void {
    $admin = $requireAdmin();
    $renderAdmin('admin/settings', 'Website settings', [
        'admin' => $admin,
        'settings' => (new AdminContentService())->settings(),
    ]);
});

$router->post('/admin/settings', static function () use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $keys = [
        'site_name', 'site_tagline', 'office_address', 'office_hours', 'phone', 'whatsapp', 'email',
        'facebook_url', 'instagram_url', 'map_embed_url', 'homepage_hero_eyebrow', 'homepage_hero_title',
        'homepage_hero_highlight', 'homepage_hero_description', 'homepage_meta_title', 'homepage_meta_description',
    ];
    $settings = [];
    foreach ($keys as $key) {
        $settings[$key] = is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
    }
    try {
        (new AdminContentService())->saveSettings($settings);
        $flashAndRedirect('success', 'Website settings saved.', '/admin/settings');
    } catch (\InvalidArgumentException | \RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'website settings'), '/admin/settings');
    }
});

$router->get('/admin/media', static function () use ($requireAdmin, $renderAdmin): void {
    $admin = $requireAdmin();
    $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
    $renderAdmin('admin/media', 'Media library', [
        'admin' => $admin,
        'result' => (new MediaManagementService())->list($page === false ? 1 : max(1, $page), 24),
    ]);
});

$router->post('/admin/media/upload', static function () use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    try {
        $file = is_array($_FILES['image'] ?? null) ? $_FILES['image'] : [];
        $altText = is_string($_POST['alt_text'] ?? null) ? $_POST['alt_text'] : '';
        (new MediaManagementService())->upload($file, $altText);
        $flashAndRedirect('success', 'Image uploaded to the media library.', '/admin/media');
    } catch (\InvalidArgumentException | \RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'media upload'), '/admin/media');
    }
});

$router->post('/admin/media/{id}/delete', static function (array $parameters) use ($requireAdmin, $requireAdminCsrf, $flashAndRedirect, $positiveId, $adminErrorMessage): void {
    $admin = $requireAdmin();
    if (!$requireAdminCsrf($admin)) { return; }
    $id = $positiveId($parameters['id'] ?? null);
    if ($id === null) { redirect('/admin/media', 303); }
    if (($_POST['confirm_delete'] ?? '') !== '1') { $flashAndRedirect('error', 'Confirm image deletion first.', '/admin/media'); }
    try {
        (new MediaManagementService())->delete($id);
        $flashAndRedirect('success', 'Image deleted.', '/admin/media');
    } catch (\RuntimeException $exception) {
        $flashAndRedirect('error', $adminErrorMessage($exception, 'media delete'), '/admin/media');
    }
});

$router->get('/admin/activity', static function () use ($requireAdmin, $renderAdmin): void {
    $admin = $requireAdmin();
    $search = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
    $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
    $renderAdmin('admin/activity', 'Activity log', [
        'admin' => $admin,
        'result' => (new AdminActivityService())->list($search, $page === false ? 1 : max(1, $page), 25),
        'search' => $search,
    ]);
});
