<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\ShipRoute;

class ServiceController extends Controller
{
    public function airTicket(): void
    {
        View::render('air-ticket', ['title' => 'Air Ticket Booking Inquiry | SAKIB ENTERPRISE'], 'main');
    }

    public function shipTicket(): void
    {
        View::render('ship-ticket', [
            'title' => 'Ship Ticket Booking Inquiry | SAKIB ENTERPRISE',
            'routes' => ShipRoute::active(),
        ], 'main');
    }

    public function busTicket(): void
    {
        View::render('bus-ticket', ['title' => 'Bus Ticket Booking Inquiry | SAKIB ENTERPRISE'], 'main');
    }
}
