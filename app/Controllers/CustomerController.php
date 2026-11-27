<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\CustomerAuth;
use App\Core\View;
use App\Models\Booking;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function dashboard(): void
    {
        $user = CustomerAuth::user();
        if (!$user) { $this->redirect(url('/login')); return; }
        $bookings = Customer::bookings((int)$user['id']);
        $stats = [
            'total' => count($bookings),
            'pending' => count(array_filter($bookings, fn($b) => $b['status'] === 'Pending')),
            'confirmed' => count(array_filter($bookings, fn($b) => in_array($b['status'], ['Confirmed','Completed']))),
        ];
        View::render('customer/dashboard', [
            'title' => 'My Account | SAKIB ENTERPRISE',
            'customer' => $user,
            'bookings' => $bookings,
            'stats' => $stats,
        ]);
    }

    public function booking(string $id): void
    {
        $user = CustomerAuth::user();
        if (!$user) { $this->redirect(url('/login')); return; }
        $booking = Booking::find((int)$id);
        if (!$booking || (int)$booking['customer_id'] !== (int)$user['id']) { http_response_code(404); die('Booking not found.'); }
        View::render('customer/booking', [
            'title' => 'Booking ' . $booking['booking_reference'] . ' | SAKIB ENTERPRISE',
            'booking' => $booking,
            'customer' => $user,
        ]);
    }
}
