<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class TourPackage
{
    public static function table(): string { return 'tour_packages'; }

    public static function active(int $limit = 20, int $offset = 0, string $search = '', int $destinationId = 0): array
    {
        $where = 'WHERE tp.is_active = 1';
        $params = [];
        if ($search !== '') {
            $where .= ' AND (tp.title LIKE ? OR tp.short_description LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($destinationId > 0) {
            $where .= ' AND tp.destination_id = ?';
            $params[] = $destinationId;
        }
        $sql = "SELECT tp.*, d.name AS destination_name FROM tour_packages tp LEFT JOIN destinations d ON d.id = tp.destination_id $where ORDER BY tp.sort_order, tp.created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function featured(int $limit = 6): array
    {
        $stmt = Database::getConnection()->prepare('SELECT tp.*, d.name AS destination_name FROM tour_packages tp LEFT JOIN destinations d ON d.id = tp.destination_id WHERE tp.is_active = 1 AND tp.is_featured = 1 ORDER BY tp.sort_order LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countActive(string $search = '', int $destinationId = 0): int
    {
        $where = 'WHERE is_active = 1';
        $params = [];
        if ($search !== '') {
            $where .= ' AND (title LIKE ? OR short_description LIKE ?)';
            $params = ["%$search%", "%$search%"];
        }
        if ($destinationId > 0) {
            $where .= ' AND destination_id = ?';
            $params[] = $destinationId;
        }
        $stmt = Database::getConnection()->prepare("SELECT COUNT(*) FROM tour_packages $where");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT tp.*, d.name AS destination_name FROM tour_packages tp LEFT JOIN destinations d ON d.id = tp.destination_id WHERE tp.slug = ? AND tp.is_active = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT tp.*, d.name AS destination_name FROM tour_packages tp LEFT JOIN destinations d ON d.id = tp.destination_id WHERE tp.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(): array
    {
        return Database::getConnection()->query('SELECT tp.*, d.name AS destination_name FROM tour_packages tp LEFT JOIN destinations d ON d.id = tp.destination_id ORDER BY tp.sort_order, tp.created_at DESC')->fetchAll();
    }

    public static function related(int $excludeId, int $limit = 3): array
    {
        $stmt = Database::getConnection()->prepare('SELECT tp.*, d.name AS destination_name FROM tour_packages tp LEFT JOIN destinations d ON d.id = tp.destination_id WHERE tp.is_active = 1 AND tp.id != ? ORDER BY tp.is_featured DESC, tp.sort_order LIMIT ?');
        $stmt->bindValue(1, $excludeId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->prepare('INSERT INTO tour_packages (destination_id, title, slug, short_description, description, duration_days, duration_nights, price, currency, image, itinerary, inclusions, exclusions, is_featured, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$data['destination_id'] ?: null, $data['title'], $data['slug'], $data['short_description'] ?? null, $data['description'] ?? null, $data['duration_days'] ?? 3, $data['duration_nights'] ?? 2, $data['price'] ?? 0, $data['currency'] ?? 'BDT', $data['image'] ?? null, $data['itinerary'] ?? null, $data['inclusions'] ?? null, $data['exclusions'] ?? null, $data['is_featured'] ?? 0, $data['is_active'] ?? 1, $data['sort_order'] ?? 0]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::getConnection()->prepare('UPDATE tour_packages SET destination_id = ?, title = ?, slug = ?, short_description = ?, description = ?, duration_days = ?, duration_nights = ?, price = ?, currency = ?, image = ?, itinerary = ?, inclusions = ?, exclusions = ?, is_featured = ?, is_active = ?, sort_order = ? WHERE id = ?')
            ->execute([$data['destination_id'] ?: null, $data['title'], $data['slug'], $data['short_description'] ?? null, $data['description'] ?? null, $data['duration_days'] ?? 3, $data['duration_nights'] ?? 2, $data['price'] ?? 0, $data['currency'] ?? 'BDT', $data['image'] ?? null, $data['itinerary'] ?? null, $data['inclusions'] ?? null, $data['exclusions'] ?? null, $data['is_featured'] ?? 0, $data['is_active'] ?? 1, $data['sort_order'] ?? 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM tour_packages WHERE id = ?')->execute([$id]);
    }

    public static function count(): int
    {
        return (int)Database::getConnection()->query('SELECT COUNT(*) FROM tour_packages')->fetchColumn();
    }
}
