<?php
namespace App\Models;

use App\Core\Database;

class Testimonial
{
    public static function active(): array
    {
        return Database::getConnection()->query('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    }

    public static function all(): array
    {
        return Database::getConnection()->query('SELECT * FROM testimonials ORDER BY sort_order, id')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM testimonials WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->prepare('INSERT INTO testimonials (customer_name, location, rating, message, image, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([$data['customer_name'], $data['location'] ?? null, $data['rating'] ?? 5, $data['message'], $data['image'] ?? null, $data['is_active'] ?? 1, $data['sort_order'] ?? 0]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::getConnection()->prepare('UPDATE testimonials SET customer_name = ?, location = ?, rating = ?, message = ?, image = ?, is_active = ?, sort_order = ? WHERE id = ?')
            ->execute([$data['customer_name'], $data['location'] ?? null, $data['rating'] ?? 5, $data['message'], $data['image'] ?? null, $data['is_active'] ?? 1, $data['sort_order'] ?? 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);
    }
}
