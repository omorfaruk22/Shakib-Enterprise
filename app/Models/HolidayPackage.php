<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class HolidayPackage
{
    public static function table(): string { return 'holiday_packages'; }

    public static function active(int $limit = 20, int $offset = 0, string $search = ''): array
    {
        $where = 'WHERE is_active = 1';
        $params = [];
        if ($search !== '') {
            $where .= ' AND (title LIKE ? OR short_description LIKE ?)';
            $params = ["%$search%", "%$search%"];
        }
        $sql = "SELECT * FROM holiday_packages $where ORDER BY sort_order, created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function featured(int $limit = 6): array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM holiday_packages WHERE is_active = 1 AND is_featured = 1 ORDER BY sort_order LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countActive(string $search = ''): int
    {
        if ($search !== '') {
            $stmt = Database::getConnection()->prepare('SELECT COUNT(*) FROM holiday_packages WHERE is_active = 1 AND (title LIKE ? OR short_description LIKE ?)');
            $stmt->execute(["%$search%", "%$search%"]);
            return (int)$stmt->fetchColumn();
        }
        return (int)Database::getConnection()->query('SELECT COUNT(*) FROM holiday_packages WHERE is_active = 1')->fetchColumn();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT hp.*, d.name AS destination_name FROM holiday_packages hp LEFT JOIN destinations d ON d.id = hp.destination_id WHERE hp.slug = ? AND hp.is_active = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM holiday_packages WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(): array
    {
        return Database::getConnection()->query('SELECT * FROM holiday_packages ORDER BY sort_order, created_at DESC')->fetchAll();
    }

    public static function related(int $excludeId, int $limit = 3): array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM holiday_packages WHERE is_active = 1 AND id != ? ORDER BY is_featured DESC, sort_order LIMIT ?');
        $stmt->bindValue(1, $excludeId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->prepare('INSERT INTO holiday_packages (destination_id, title, slug, short_description, description, duration_days, duration_nights, price, currency, image, itinerary, inclusions, exclusions, is_featured, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$data['destination_id'] ?: null, $data['title'], $data['slug'], $data['short_description'] ?? null, $data['description'] ?? null, $data['duration_days'] ?? 3, $data['duration_nights'] ?? 2, $data['price'] ?? 0, $data['currency'] ?? 'BDT', $data['image'] ?? null, $data['itinerary'] ?? null, $data['inclusions'] ?? null, $data['exclusions'] ?? null, $data['is_featured'] ?? 0, $data['is_active'] ?? 1, $data['sort_order'] ?? 0]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::getConnection()->prepare('UPDATE holiday_packages SET destination_id = ?, title = ?, slug = ?, short_description = ?, description = ?, duration_days = ?, duration_nights = ?, price = ?, currency = ?, image = ?, itinerary = ?, inclusions = ?, exclusions = ?, is_featured = ?, is_active = ?, sort_order = ? WHERE id = ?')
            ->execute([$data['destination_id'] ?: null, $data['title'], $data['slug'], $data['short_description'] ?? null, $data['description'] ?? null, $data['duration_days'] ?? 3, $data['duration_nights'] ?? 2, $data['price'] ?? 0, $data['currency'] ?? 'BDT', $data['image'] ?? null, $data['itinerary'] ?? null, $data['inclusions'] ?? null, $data['exclusions'] ?? null, $data['is_featured'] ?? 0, $data['is_active'] ?? 1, $data['sort_order'] ?? 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM holiday_packages WHERE id = ?')->execute([$id]);
    }

    public static function count(): int
    {
        return (int)Database::getConnection()->query('SELECT COUNT(*) FROM holiday_packages')->fetchColumn();
    }
}
