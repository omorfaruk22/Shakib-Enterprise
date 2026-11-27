<?php
namespace App\Models;

use App\Core\Database;

class Service
{
    public static function active(): array
    {
        return Database::getConnection()->query('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    }

    public static function all(): array
    {
        return Database::getConnection()->query('SELECT * FROM services ORDER BY sort_order, id')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM services WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM services WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->prepare('INSERT INTO services (slug, name, short_description, description, icon, image, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$data['slug'], $data['name'], $data['short_description'] ?? null, $data['description'] ?? null, $data['icon'] ?? 'fa-solid fa-plane', $data['image'] ?? null, $data['is_active'] ?? 1, $data['sort_order'] ?? 0]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::getConnection()->prepare('UPDATE services SET slug = ?, name = ?, short_description = ?, description = ?, icon = ?, image = ?, is_active = ?, sort_order = ? WHERE id = ?')
            ->execute([$data['slug'], $data['name'], $data['short_description'] ?? null, $data['description'] ?? null, $data['icon'] ?? 'fa-solid fa-plane', $data['image'] ?? null, $data['is_active'] ?? 1, $data['sort_order'] ?? 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM services WHERE id = ?')->execute([$id]);
    }

    public static function count(): int
    {
        return (int)Database::getConnection()->query('SELECT COUNT(*) FROM services')->fetchColumn();
    }
}
