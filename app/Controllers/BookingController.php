<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\View;
use App\Models\Booking;
use App\Models\TourPackage;
use App\Models\HolidayPackage;
use App\Services\BookingService;

class BookingController extends Controller
{
    private function handleStore(array $input, string $serviceType, string $serviceSlug, ?string $packageType = null, ?int $packageId = null, string $redirectBack = '/'): void
    {
        Csrf::checkOrFail();
        // Honeypot
        if (!empty($input['website'] ?? '')) {
            $this->redirect($redirectBack);
        }
        // Simple throttle: 1 submission per 10 seconds per session
        if (session_status() === PHP_SESSION_NONE) session_start();
        $last = $_SESSION['last_booking_at'] ?? 0;
        if (time() - $last < 10) {
            $this->flash('error', 'Please wait a few seconds before submitting again.');
            $this->old($input);
            $this->back($redirectBack);
        }
        $input['service_type'] = $serviceType;
        $input['service_slug'] = $serviceSlug;
        $input['package_type'] = $packageType;
        $input['package_id'] = $packageId;

        $errors = BookingService::validateCommon($input);
        // Service-specific required fields
        if ($serviceType === 'air' || $serviceType === 'bus') {
            $errors = array_merge($errors, \App\Core\Validator::required($input, ['departure_location', 'destination']));
        }
        if ($serviceType === 'ship') {
            if (empty($input['ship_route_id'])) {
                $errors['ship_route_id'] = 'Please select a ship route.';
            }
        }
        if ($serviceType === 'tour' || $serviceType === 'holiday') {
            // Journey date optional for packages? Keep required per spec — but allow empty with default today+7
            if (empty($input['journey_date'])) {
                $input['journey_date'] = date('Y-m-d', strtotime('+7 days'));
            }
            if (empty($input['passenger_count'])) {
                $input['passenger_count'] = 1;
            }
        }
        if ($errors) {
            $this->flash('error', 'Please fix the highlighted fields and try again.');
            $this->old($input);
            // Store errors for view
            $_SESSION['errors'] = $errors;
            $this->back($redirectBack);
        }
        try {
            $ref = BookingService::createInquiry($input);
            $_SESSION['last_booking_at'] = time();
            clear_old();
            $this->redirect(url('booking-success') . '?ref=' . urlencode($ref));
        } catch (\Throwable $e) {
            \App\Core\Logger::error('Booking create failed: ' . $e->getMessage());
            $this->flash('error', 'Something went wrong. Please try again or contact us on WhatsApp.');
            $this->old($input);
            $this->back($redirectBack);
        }
    }

    public function storeAir(): void
    {
        $this->handleStore($_POST, 'air', 'air-ticket', null, null, '/air-ticket');
    }

    public function storeShip(): void
    {
        $this->handleStore($_POST, 'ship', 'ship-ticket', null, null, '/ship-ticket');
    }

    public function storeBus(): void
    {
        $this->handleStore($_POST, 'bus', 'bus-ticket', null, null, '/bus-ticket');
    }

    public function storeTour(string $slug): void
    {
        $tour = TourPackage::findBySlug($slug);
        if (!$tour) {
            http_response_code(404);
            die('Tour not found.');
        }
        $this->handleStore($_POST, 'tour', 'tour-travel', 'tour', (int)$tour['id'], '/tours/' . $slug);
    }

    public function storeHoliday(string $slug): void
    {
        $pkg = HolidayPackage::findBySlug($slug);
        if (!$pkg) {
            http_response_code(404);
            die('Package not found.');
        }
        $this->handleStore($_POST, 'holiday', 'holiday-packages', 'holiday', (int)$pkg['id'], '/holiday-packages/' . $slug);
    }

    public function success(): void
    {
        $ref = trim($_GET['ref'] ?? '');
        $booking = $ref ? Booking::findByReference($ref) : null;
        View::render('booking-success', [
            'title' => 'Booking Inquiry Received | SAKIB ENTERPRISE',
            'booking' => $booking,
            'ref' => $ref,
        ], 'main');
    }
}
