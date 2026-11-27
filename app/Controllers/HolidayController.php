<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\HolidayPackage;

class HolidayController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 9;
        $search = trim($_GET['search'] ?? '');
        $total = HolidayPackage::countActive($search);
        $packages = HolidayPackage::active($perPage, ($page - 1) * $perPage, $search);
        $pages = (int)ceil($total / $perPage);
        View::render('holiday-packages', [
            'title' => 'Holiday Packages | SAKIB ENTERPRISE',
            'packages' => $packages, 'page' => $page, 'pages' => $pages, 'search' => $search, 'total' => $total,
        ], 'main');
    }

    public function show(string $slug): void
    {
        $pkg = HolidayPackage::findBySlug($slug);
        if (!$pkg) {
            http_response_code(404);
            View::render('404', ['title' => 'Package Not Found'], 'main');
            return;
        }
        View::render('holiday-details', [
            'title' => $pkg['title'] . ' | SAKIB ENTERPRISE',
            'pkg' => $pkg,
            'related' => HolidayPackage::related((int)$pkg['id'], 3),
        ], 'main');
    }
}
