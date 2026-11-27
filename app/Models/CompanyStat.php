<?php
namespace App\Models;

use App\Core\Database;

class CompanyStat
{
    public static function active(): array
    {
        return Database::getConnection()->query('SELECT * FROM company_stats WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    }

    public static function all(): array
    {
        return Database::getConnection()->query('SELECT * FROM company_stats ORDER BY sort_order, id')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM company_stats WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->prepare('INSERT INTO company_stats (icon, number, label, is_active, sort_order) VALUES (?, ?, ?, ?, ?)')
            ->execute([$data['icon'] ?? 'fa-solid fa-users', $data['number'], $data['label'], $data['is_active'] ?? 1, $data['sort_order'] ?? 0]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::getConnection()->prepare('UPDATE company_stats SET icon = ?, number = ?, label = ?, is_active = ?, sort_order = ? WHERE id = ?')
            ->execute([$data['icon'] ?? 'fa-solid fa-users', $data['number'], $data['label'], $data['is_active'] ?? 1, $data['sort_order'] ?? 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM company_stats WHERE id = ?')->execute([$id]);
    }
}
