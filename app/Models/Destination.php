<?php
namespace App\Models;

use App\Core\Database;

class Destination
{
    public static function active(): array
    {
        return Database::getConnection()->query('SELECT * FROM destinations WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    }

    public static function featured(int $limit = 8): array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM destinations WHERE is_active = 1 AND is_featured = 1 ORDER BY sort_order LIMIT ?');
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function all(): array
    {
        return Database::getConnection()->query('SELECT * FROM destinations ORDER BY sort_order, id')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM destinations WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->prepare('INSERT INTO destinations (name, slug, description, image, is_featured, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([$data['name'], $data['slug'], $data['description'] ?? null, $data['image'] ?? null, $data['is_featured'] ?? 0, $data['is_active'] ?? 1, $data['sort_order'] ?? 0]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::getConnection()->prepare('UPDATE destinations SET name = ?, slug = ?, description = ?, image = ?, is_featured = ?, is_active = ?, sort_order = ? WHERE id = ?')
            ->execute([$data['name'], $data['slug'], $data['description'] ?? null, $data['image'] ?? null, $data['is_featured'] ?? 0, $data['is_active'] ?? 1, $data['sort_order'] ?? 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM destinations WHERE id = ?')->execute([$id]);
    }
}
