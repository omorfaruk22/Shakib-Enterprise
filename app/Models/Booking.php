<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Booking
{
    public const STATUSES = ['pending','contacted','processing','confirmed','completed','cancelled','rejected'];
    public const TYPES = ['air','ship','bus','tour','holiday'];

    public static function generateReference(): string
    {
        $year = date('Y');
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return "SE-{$year}-{$code}";
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->prepare('INSERT INTO bookings (booking_reference, customer_id, service_type, service_id, package_type, package_id, ship_route_id, departure_location, destination, journey_date, return_date, passenger_count, passenger_type, travel_class, customer_name, phone, email, address, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([
                $data['booking_reference'], $data['customer_id'], $data['service_type'], $data['service_id'] ?? null,
                $data['package_type'] ?? null, $data['package_id'] ?? null, $data['ship_route_id'] ?? null,
                $data['departure_location'] ?? null, $data['destination'] ?? null,
                $data['journey_date'] ?? null, $data['return_date'] ?? null,
                $data['passenger_count'] ?? 1, $data['passenger_type'] ?? null, $data['travel_class'] ?? null,
                $data['customer_name'], $data['phone'], $data['email'] ?? null, $data['address'] ?? null,
                $data['notes'] ?? null, $data['status'] ?? 'pending',
            ]);
        return (int)$pdo->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT b.*, c.name AS customer_db_name FROM bookings b LEFT JOIN customers c ON c.id = b.customer_id WHERE b.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByReference(string $ref): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM bookings WHERE booking_reference = ? LIMIT 1');
        $stmt->execute([$ref]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        [$where, $params] = self::buildWhere($filters);
        $sql = "SELECT b.*, c.name AS customer_name_db FROM bookings b LEFT JOIN customers c ON c.id = b.customer_id $where ORDER BY b.created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function count(array $filters = []): int
    {
        [$where, $params] = self::buildWhere($filters);
        $stmt = Database::getConnection()->prepare("SELECT COUNT(*) FROM bookings b LEFT JOIN customers c ON c.id = b.customer_id $where");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    private static function buildWhere(array $filters): array
    {
        $conds = [];
        $params = [];
        if (!empty($filters['status'])) {
            $conds[] = 'b.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $conds[] = 'b.service_type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['date_from'])) {
            $conds[] = 'DATE(b.created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conds[] = 'DATE(b.created_at) <= ?';
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $conds[] = '(b.booking_reference LIKE ? OR b.customer_name LIKE ? OR b.phone LIKE ? OR b.destination LIKE ?)';
            $s = '%' . $filters['search'] . '%';
            $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
        }
        $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';
        return [$where, $params];
    }

    public static function updateStatus(int $id, string $status, ?int $adminId = null, ?string $note = null): void
    {
        $pdo = Database::getConnection();
        $old = $pdo->prepare('SELECT status FROM bookings WHERE id = ?');
        $old->execute([$id]);
        $oldStatus = $old->fetchColumn();
        $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?')->execute([$status, $id]);
        $pdo->prepare('INSERT INTO booking_status_history (booking_id, old_status, new_status, changed_by, note) VALUES (?, ?, ?, ?, ?)')
            ->execute([$id, $oldStatus, $status, $adminId, $note]);
    }

    public static function addNote(int $bookingId, ?int $adminId, string $note): void
    {
        Database::getConnection()->prepare('INSERT INTO booking_notes (booking_id, admin_id, note) VALUES (?, ?, ?)')
            ->execute([$bookingId, $adminId, $note]);
    }

    public static function notes(int $bookingId): array
    {
        $stmt = Database::getConnection()->prepare('SELECT n.*, a.name AS admin_name FROM booking_notes n LEFT JOIN admins a ON a.id = n.admin_id WHERE n.booking_id = ? ORDER BY n.created_at DESC');
        $stmt->execute([$bookingId]);
        return $stmt->fetchAll();
    }

    public static function history(int $bookingId): array
    {
        $stmt = Database::getConnection()->prepare('SELECT h.*, a.name AS admin_name FROM booking_status_history h LEFT JOIN admins a ON a.id = h.changed_by WHERE h.booking_id = ? ORDER BY h.created_at DESC');
        $stmt->execute([$bookingId]);
        return $stmt->fetchAll();
    }

    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM bookings WHERE id = ?')->execute([$id]);
    }

    public static function stats(): array
    {
        $pdo = Database::getConnection();
        $total = (int)$pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
        $byStatus = [];
        foreach (self::STATUSES as $s) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE status = ?');
            $stmt->execute([$s]);
            $byStatus[$s] = (int)$stmt->fetchColumn();
        }
        $byType = [];
        foreach (self::TYPES as $t) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE service_type = ?');
            $stmt->execute([$t]);
            $byType[$t] = (int)$stmt->fetchColumn();
        }
        $recent = $pdo->query('SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5')->fetchAll();
        // Last 7 days summary
        $daily = $pdo->query("SELECT DATE(created_at) AS d, COUNT(*) AS c FROM bookings WHERE created_at >= (NOW() - INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY d")->fetchAll();
        return ['total' => $total, 'by_status' => $byStatus, 'by_type' => $byType, 'recent' => $recent, 'daily' => $daily];
    }

    public static function exportCsv(array $filters = []): string
    {
        $rows = self::all($filters, 10000, 0);
        $out = "Reference,Type,Status,Customer,Phone,Email,From,To,Journey Date,Passengers,Created\n";
        foreach ($rows as $r) {
            $vals = [$r['booking_reference'], $r['service_type'], $r['status'], $r['customer_name'], $r['phone'], $r['email'] ?? '', $r['departure_location'] ?? '', $r['destination'] ?? '', $r['journey_date'] ?? '', $r['passenger_count'], $r['created_at']];
            $out .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', (string)$v) . '"', $vals)) . "\n";
        }
        return $out;
    }
}
