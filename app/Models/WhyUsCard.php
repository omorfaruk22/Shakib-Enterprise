<?php
namespace App\Models;

use App\Core\Database;

class WhyUsCard
{
    public static function active(): array
    {
        return Database::getConnection()->query('SELECT * FROM why_us_cards WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    }

    public static function all(): array
    {
        return Database::getConnection()->query('SELECT * FROM why_us_cards ORDER BY sort_order, id')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM why_us_cards WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->prepare('INSERT INTO why_us_cards (icon, title, description, is_active, sort_order) VALUES (?, ?, ?, ?, ?)')
            ->execute([$data['icon'] ?? 'fa-solid fa-check-circle', $data['title'], $data['description'] ?? '', $data['is_active'] ?? 1, $data['sort_order'] ?? 0]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::getConnection()->prepare('UPDATE why_us_cards SET icon = ?, title = ?, description = ?, is_active = ?, sort_order = ? WHERE id = ?')
            ->execute([$data['icon'] ?? 'fa-solid fa-check-circle', $data['title'], $data['description'] ?? '', $data['is_active'] ?? 1, $data['sort_order'] ?? 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM why_us_cards WHERE id = ?')->execute([$id]);
    }
}
