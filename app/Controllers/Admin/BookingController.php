<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\View;
use App\Models\Booking;
use App\Models\Admin;

class BookingController extends Controller
{
    private function filters(): array
    {
        return [
            'status' => trim($_GET['status'] ?? ''),
            'type' => trim($_GET['type'] ?? ''),
            'search' => trim($_GET['search'] ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to' => trim($_GET['date_to'] ?? ''),
        ];
    }

    public function index(): void
    {
        $filters = $this->filters();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $total = Booking::count($filters);
        $bookings = Booking::all($filters, $perPage, ($page - 1) * $perPage);
        View::render('bookings/index', [
            'title' => 'Bookings | SAKIB ENTERPRISE',
            'bookings' => $bookings, 'filters' => $filters,
            'page' => $page, 'pages' => (int)ceil($total / $perPage), 'total' => $total,
            'admin' => Auth::user(),
        ], 'admin');
    }

    public function show(string $id): void
    {
        $booking = Booking::find((int)$id);
        if (!$booking) {
            http_response_code(404);
            die('Booking not found.');
        }
        View::render('bookings/show', [
            'title' => 'Booking ' . $booking['booking_reference'] . ' | SAKIB ENTERPRISE',
            'booking' => $booking,
            'notes' => Booking::notes((int)$id),
            'history' => Booking::history((int)$id),
            'admin' => Auth::user(),
        ], 'admin');
    }

    public function updateStatus(string $id): void
    {
        Csrf::checkOrFail();
        $status = trim($_POST['status'] ?? '');
        if (!in_array($status, Booking::STATUSES, true)) {
            $this->flash('error', 'Invalid status.');
            $this->back(url('admin/bookings/' . $id));
        }
        $admin = Auth::user();
        Booking::updateStatus((int)$id, $status, (int)$admin['id'], trim($_POST['note'] ?? '') ?: null);
        Admin::logActivity((int)$admin['id'], 'update_booking_status', 'booking', (int)$id, "Status changed to $status", $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'Booking status updated to ' . ucfirst($status) . '.');
        $this->redirect(url('admin/bookings/' . $id));
    }

    public function addNote(string $id): void
    {
        Csrf::checkOrFail();
        $note = trim($_POST['note'] ?? '');
        if ($note === '') {
            $this->flash('error', 'Note cannot be empty.');
            $this->back(url('admin/bookings/' . $id));
        }
        $admin = Auth::user();
        Booking::addNote((int)$id, (int)$admin['id'], $note);
        Admin::logActivity((int)$admin['id'], 'add_booking_note', 'booking', (int)$id, 'Internal note added', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'Note added.');
        $this->redirect(url('admin/bookings/' . $id));
    }

    public function destroy(string $id): void
    {
        Csrf::checkOrFail();
        $admin = Auth::user();
        Booking::delete((int)$id);
        Admin::logActivity((int)$admin['id'], 'delete_booking', 'booking', (int)$id, 'Booking deleted', $_SERVER['REMOTE_ADDR'] ?? null);
        $this->flash('success', 'Booking deleted.');
        $this->redirect(url('admin/bookings'));
    }

    public function exportCsv(): void
    {
        $filters = $this->filters();
        $csv = Booking::exportCsv($filters);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="bookings-' . date('Y-m-d') . '.csv"');
        echo $csv;
    }
}
