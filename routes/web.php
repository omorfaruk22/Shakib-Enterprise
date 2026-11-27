<?php
use App\Controllers\HomeController;
use App\Controllers\ServiceController;
use App\Controllers\TourController;
use App\Controllers\HolidayController;
use App\Controllers\PageController;
use App\Controllers\BookingController;
use App\Controllers\ContactController;
use App\Controllers\SeoController;
use App\Controllers\AuthController;
use App\Controllers\CustomerController;
use App\Middleware\CustomerMiddleware;

$router->get('/', [HomeController::class, 'index']);
$router->get('/air-ticket', [ServiceController::class, 'airTicket']);
$router->post('/air-ticket/inquiry', [BookingController::class, 'storeAir']);
$router->get('/ship-ticket', [ServiceController::class, 'shipTicket']);
$router->post('/ship-ticket/inquiry', [BookingController::class, 'storeShip']);
$router->get('/bus-ticket', [ServiceController::class, 'busTicket']);
$router->post('/bus-ticket/inquiry', [BookingController::class, 'storeBus']);
$router->get('/tours', [TourController::class, 'index']);
$router->get('/tours/{slug}', [TourController::class, 'show']);
$router->post('/tours/{slug}/inquiry', [BookingController::class, 'storeTour']);
$router->get('/holiday-packages', [HolidayController::class, 'index']);
$router->get('/holiday-packages/{slug}', [HolidayController::class, 'show']);
$router->post('/holiday-packages/{slug}/inquiry', [BookingController::class, 'storeHoliday']);
$router->get('/about', [PageController::class, 'about']);
$router->get('/contact', [PageController::class, 'contact']);
$router->post('/contact', [ContactController::class, 'store']);
$router->get('/booking-success', [BookingController::class, 'success']);
$router->get('/sitemap.xml', [SeoController::class, 'sitemap']);

// Customer Auth
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

// Customer Account (protected)
$router->get('/customer', function () { CustomerMiddleware::handle(); (new CustomerController())->dashboard(); });
$router->get('/customer/bookings/{id}', function ($id) { CustomerMiddleware::handle(); (new CustomerController())->booking($id); });
