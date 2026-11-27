<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\View;
use App\Models\Booking;
use App\Models\TourPackage;
use App\Models\HolidayPackage;
use App\Models\Service;
use App\Models\ContactMessage;
use App\Models\Admin;

class DashboardController extends Controller
{
    public function index(): void
    {
        $stats = Booking::stats();
        View::render('dashboard', [
            'title' => 'Dashboard | SAKIB ENTERPRISE',
            'stats' => $stats,
            'tourCount' => TourPackage::count(),
            'holidayCount' => HolidayPackage::count(),
            'serviceCount' => Service::count(),
            'contactCount' => ContactMessage::count(),
            'unreadCount' => ContactMessage::unreadCount(),
            'recentContacts' => ContactMessage::recent(5),
            'admin' => Auth::user(),
        ], 'admin');
    }

    public function logs(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 30;
        $logs = Admin::activityLogs($perPage, ($page - 1) * $perPage);
        View::render('activity-logs', [
            'title' => 'Activity Logs | SAKIB ENTERPRISE',
            'logs' => $logs, 'page' => $page, 'admin' => Auth::user(),
        ], 'admin');
    }
}
