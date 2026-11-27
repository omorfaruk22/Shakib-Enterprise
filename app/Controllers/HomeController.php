<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\Service;
use App\Models\Destination;
use App\Models\TourPackage;
use App\Models\HolidayPackage;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index(): void
    {
        View::render('home', [
            'title' => setting('meta_title', 'SAKIB ENTERPRISE | Your Trusted Travel Partner'),
            'services' => Service::active(),
            'destinations' => Destination::featured(8),
            'tours' => TourPackage::featured(6),
            'holidays' => HolidayPackage::featured(6),
            'testimonials' => Testimonial::active(),
        ], 'main');
    }
}
