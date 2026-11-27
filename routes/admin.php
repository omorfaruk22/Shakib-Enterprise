<?php
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\BookingController;
use App\Controllers\Admin\CustomerController;
use App\Controllers\Admin\ServiceController;
use App\Controllers\Admin\PackageController;
use App\Controllers\Admin\DestinationController;
use App\Controllers\Admin\ShipRouteController;
use App\Controllers\Admin\ContactController;
use App\Controllers\Admin\TestimonialController;
use App\Controllers\Admin\SettingController;
use App\Controllers\Admin\ContentItemController;
use App\Middleware\AuthMiddleware;

// Public admin
$router->get('/admin/login', [AuthController::class, 'showLogin']);
$router->post('/admin/login', [AuthController::class, 'login']);
$router->post('/admin/logout', [AuthController::class, 'logout']);

// Protected admin routes — check auth before dispatch via wrapper
// $requireFull = true blocks Managers from the route (settings, profile-level admin only)
$protected = function (callable $fn, bool $requireFull = false) {
    return function (...$args) use ($fn, $requireFull) {
        AuthMiddleware::handle();
        if ($requireFull) AuthMiddleware::requireAdmin();
        $fn(...$args);
    };
};

$router->get('/admin/dashboard', $protected(function () { (new DashboardController())->index(); }));
$router->get('/admin/bookings', $protected(function () { (new BookingController())->index(); }));
$router->get('/admin/bookings/export', $protected(function () { (new BookingController())->exportCsv(); }));
$router->get('/admin/bookings/{id}', $protected(function ($id) { (new BookingController())->show($id); }));
$router->post('/admin/bookings/{id}/status', $protected(function ($id) { (new BookingController())->updateStatus($id); }));
$router->post('/admin/bookings/{id}/note', $protected(function ($id) { (new BookingController())->addNote($id); }));
$router->post('/admin/bookings/{id}/delete', $protected(function ($id) { (new BookingController())->destroy($id); }));

$router->get('/admin/customers', $protected(function () { (new CustomerController())->index(); }));
$router->get('/admin/customers/{id}', $protected(function ($id) { (new CustomerController())->show($id); }));

$router->get('/admin/services', $protected(function () { (new ServiceController())->index(); }));
$router->get('/admin/services/create', $protected(function () { (new ServiceController())->create(); }));
$router->post('/admin/services', $protected(function () { (new ServiceController())->store(); }));
$router->get('/admin/services/{id}/edit', $protected(function ($id) { (new ServiceController())->edit($id); }));
$router->post('/admin/services/{id}', $protected(function ($id) { (new ServiceController())->update($id); }));
$router->post('/admin/services/{id}/delete', $protected(function ($id) { (new ServiceController())->destroy($id); }));

$router->get('/admin/tour-packages', $protected(function () { (new PackageController())->index('tour'); }));
$router->get('/admin/tour-packages/create', $protected(function () { (new PackageController())->create('tour'); }));
$router->post('/admin/tour-packages', $protected(function () { (new PackageController())->store('tour'); }));
$router->get('/admin/tour-packages/{id}/edit', $protected(function ($id) { (new PackageController())->edit('tour', $id); }));
$router->post('/admin/tour-packages/{id}', $protected(function ($id) { (new PackageController())->update('tour', $id); }));
$router->post('/admin/tour-packages/{id}/delete', $protected(function ($id) { (new PackageController())->destroy('tour', $id); }));

$router->get('/admin/holiday-packages', $protected(function () { (new PackageController())->index('holiday'); }));
$router->get('/admin/holiday-packages/create', $protected(function () { (new PackageController())->create('holiday'); }));
$router->post('/admin/holiday-packages', $protected(function () { (new PackageController())->store('holiday'); }));
$router->get('/admin/holiday-packages/{id}/edit', $protected(function ($id) { (new PackageController())->edit('holiday', $id); }));
$router->post('/admin/holiday-packages/{id}', $protected(function ($id) { (new PackageController())->update('holiday', $id); }));
$router->post('/admin/holiday-packages/{id}/delete', $protected(function ($id) { (new PackageController())->destroy('holiday', $id); }));

$router->get('/admin/destinations', $protected(function () { (new DestinationController())->index(); }));
$router->get('/admin/destinations/create', $protected(function () { (new DestinationController())->create(); }));
$router->post('/admin/destinations', $protected(function () { (new DestinationController())->store(); }));
$router->get('/admin/destinations/{id}/edit', $protected(function ($id) { (new DestinationController())->edit($id); }));
$router->post('/admin/destinations/{id}', $protected(function ($id) { (new DestinationController())->update($id); }));
$router->post('/admin/destinations/{id}/delete', $protected(function ($id) { (new DestinationController())->destroy($id); }));

$router->get('/admin/ship-routes', $protected(function () { (new ShipRouteController())->index(); }));
$router->get('/admin/ship-routes/create', $protected(function () { (new ShipRouteController())->create(); }));
$router->post('/admin/ship-routes', $protected(function () { (new ShipRouteController())->store(); }));
$router->get('/admin/ship-routes/{id}/edit', $protected(function ($id) { (new ShipRouteController())->edit($id); }));
$router->post('/admin/ship-routes/{id}', $protected(function ($id) { (new ShipRouteController())->update($id); }));
$router->post('/admin/ship-routes/{id}/delete', $protected(function ($id) { (new ShipRouteController())->destroy($id); }));

$router->get('/admin/contacts', $protected(function () { (new ContactController())->index(); }));
$router->get('/admin/contacts/{id}', $protected(function ($id) { (new ContactController())->show($id); }));
$router->post('/admin/contacts/{id}/read', $protected(function ($id) { (new ContactController())->markRead($id); }));
$router->post('/admin/contacts/{id}/reply', $protected(function ($id) { (new ContactController())->markReplied($id); }));
$router->post('/admin/contacts/{id}/note', $protected(function ($id) { (new ContactController())->addNote($id); }));
$router->post('/admin/contacts/{id}/delete', $protected(function ($id) { (new ContactController())->destroy($id); }));

$router->get('/admin/testimonials', $protected(function () { (new TestimonialController())->index(); }));
$router->get('/admin/testimonials/create', $protected(function () { (new TestimonialController())->create(); }));
$router->post('/admin/testimonials', $protected(function () { (new TestimonialController())->store(); }));
$router->get('/admin/testimonials/{id}/edit', $protected(function ($id) { (new TestimonialController())->edit($id); }));
$router->post('/admin/testimonials/{id}', $protected(function ($id) { (new TestimonialController())->update($id); }));
$router->post('/admin/testimonials/{id}/delete', $protected(function ($id) { (new TestimonialController())->destroy($id); }));

$router->get('/admin/settings', $protected(function () { (new SettingController())->index(); }, true));
$router->post('/admin/settings', $protected(function () { (new SettingController())->update(); }, true));

$router->get('/admin/activity-logs', $protected(function () { (new DashboardController())->logs(); }, true));
$router->get('/admin/profile', $protected(function () { (new AuthController())->profile(); }));
$router->post('/admin/profile', $protected(function () { (new AuthController())->updateProfile(); }));

// Admin management (super_admin only — can add/edit/delete managers)
$router->get('/admin/managers', $protected(function () { (new \App\Controllers\Admin\ManagerController())->index(); }, true));
$router->get('/admin/managers/create', $protected(function () { (new \App\Controllers\Admin\ManagerController())->create(); }, true));
$router->post('/admin/managers', $protected(function () { (new \App\Controllers\Admin\ManagerController())->store(); }, true));
$router->get('/admin/managers/{id}/edit', $protected(function ($id) { (new \App\Controllers\Admin\ManagerController())->edit($id); }, true));
$router->post('/admin/managers/{id}', $protected(function ($id) { (new \App\Controllers\Admin\ManagerController())->update($id); }, true));
$router->post('/admin/managers/{id}/delete', $protected(function ($id) { (new \App\Controllers\Admin\ManagerController())->destroy($id); }, true));

// Homepage CMS — Why Us, Process Steps, Company Stats
foreach (['why-us','process-steps','company-stats'] as $section) {
    $router->get("/admin/$section",          $protected(function () use ($section) { (new ContentItemController())->index($section); }, true));
    $router->get("/admin/$section/create",   $protected(function () use ($section) { (new ContentItemController())->create($section); }, true));
    $router->post("/admin/$section",         $protected(function () use ($section) { (new ContentItemController())->store($section); }, true));
    $router->get("/admin/$section/{id}/edit",$protected(function ($id) use ($section) { (new ContentItemController())->edit($section, $id); }, true));
    $router->post("/admin/$section/{id}",    $protected(function ($id) use ($section) { (new ContentItemController())->update($section, $id); }, true));
    $router->post("/admin/$section/{id}/delete", $protected(function ($id) use ($section) { (new ContentItemController())->destroy($section, $id); }, true));
}
