<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Security\AdminAccess;
use PDO;

final class AdminDashboardService
{
    public function __construct(private readonly ?PDO $database = null)
    {
    }

    public function load(): array
    {
        $database = $this->database ?? Connection::fromConfig(require BASE_PATH . '/config/database.php');
        $admin = AdminAccess::requireAdmin($database);
        $counts = [
            'bookings' => (int) $database->query('SELECT COUNT(*) FROM bookings')->fetchColumn(),
            'pending_bookings' => (int) $database->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
            'processing_bookings' => (int) $database->query("SELECT COUNT(*) FROM bookings WHERE status = 'processing'")->fetchColumn(),
            'confirmed_bookings' => (int) $database->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn(),
            'customers' => (int) $database->query('SELECT COUNT(*) FROM customers')->fetchColumn(),
            'unread_messages' => (int) $database->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn(),
            'active_packages' => (int) $database->query('SELECT COUNT(*) FROM packages WHERE is_active = 1')->fetchColumn(),
            'active_services' => (int) $database->query('SELECT COUNT(*) FROM services WHERE is_active = 1')->fetchColumn(),
        ];
        $recentBookings = $database->query(
            'SELECT b.id, b.booking_reference, b.service_type, b.status, b.created_at,
                    c.full_name AS customer_name
             FROM bookings AS b INNER JOIN customers AS c ON c.id = b.customer_id
             ORDER BY b.created_at DESC, b.id DESC LIMIT 8'
        )->fetchAll();
        $recentMessages = $database->query(
            'SELECT id, name, subject, status, created_at
             FROM contact_messages ORDER BY created_at DESC, id DESC LIMIT 6'
        )->fetchAll();
        $recentActivity = $database->query(
            'SELECT l.action, l.entity_type, l.entity_id, l.created_at, a.name AS admin_name
             FROM admin_activity_logs AS l LEFT JOIN admins AS a ON a.id = l.admin_id
             ORDER BY l.created_at DESC, l.id DESC LIMIT 8'
        )->fetchAll();

        return compact('admin', 'counts', 'recentBookings', 'recentMessages', 'recentActivity');
    }
}
