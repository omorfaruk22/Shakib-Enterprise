<?php
namespace App\Models;

use App\Core\Database;

class ShipRoute
{
    public static function active(): array
    {
        return Database::getConnection()->query('SELECT * FROM ship_routes WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    }

    public static function all(): array
    {
        return Database::getConnection()->query('SELECT * FROM ship_routes ORDER BY sort_order, id')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM ship_routes WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->prepare('INSERT INTO ship_routes (route_name, from_location, to_location, duration, schedule, price_note, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$data['route_name'], $data['from_location'], $data['to_location'], $data['duration'] ?? null, $data['schedule'] ?? null, $data['price_note'] ?? null, $data['is_active'] ?? 1, $data['sort_order'] ?? 0]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::getConnection()->prepare('UPDATE ship_routes SET route_name = ?, from_location = ?, to_location = ?, duration = ?, schedule = ?, price_note = ?, is_active = ?, sort_order = ? WHERE id = ?')
            ->execute([$data['route_name'], $data['from_location'], $data['to_location'], $data['duration'] ?? null, $data['schedule'] ?? null, $data['price_note'] ?? null, $data['is_active'] ?? 1, $data['sort_order'] ?? 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM ship_routes WHERE id = ?')->execute([$id]);
    }
}
