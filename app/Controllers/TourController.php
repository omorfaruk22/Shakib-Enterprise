<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\TourPackage;

class TourController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 9;
        $search = trim($_GET['search'] ?? '');
        $total = TourPackage::countActive($search);
        $tours = TourPackage::active($perPage, ($page - 1) * $perPage, $search);
        $pages = (int)ceil($total / $perPage);
        View::render('tours', [
            'title' => 'Tour & Travel Packages | SAKIB ENTERPRISE',
            'tours' => $tours, 'page' => $page, 'pages' => $pages, 'search' => $search, 'total' => $total,
        ], 'main');
    }

    public function show(string $slug): void
    {
        $tour = TourPackage::findBySlug($slug);
        if (!$tour) {
            http_response_code(404);
            View::render('404', ['title' => 'Tour Not Found'], 'main');
            return;
        }
        View::render('tour-details', [
            'title' => $tour['title'] . ' | SAKIB ENTERPRISE',
            'tour' => $tour,
            'package' => $tour, // alias for shared partials
            'related' => TourPackage::related((int)$tour['id'], 3),
        ], 'main');
    }
}
