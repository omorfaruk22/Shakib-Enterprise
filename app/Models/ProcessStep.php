<?php
namespace App\Models;

use App\Core\Database;

class ProcessStep
{
    public static function active(): array
    {
        return Database::getConnection()->query('SELECT * FROM process_steps WHERE is_active = 1 ORDER BY sort_order, step_number, id')->fetchAll();
    }

    public static function all(): array
    {
        return Database::getConnection()->query('SELECT * FROM process_steps ORDER BY sort_order, step_number, id')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM process_steps WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->prepare('INSERT INTO process_steps (step_number, title, description, is_active, sort_order) VALUES (?, ?, ?, ?, ?)')
            ->execute([(int)($data['step_number'] ?? 1), $data['title'], $data['description'] ?? '', $data['is_active'] ?? 1, $data['sort_order'] ?? 0]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::getConnection()->prepare('UPDATE process_steps SET step_number = ?, title = ?, description = ?, is_active = ?, sort_order = ? WHERE id = ?')
            ->execute([(int)($data['step_number'] ?? 1), $data['title'], $data['description'] ?? '', $data['is_active'] ?? 1, $data['sort_order'] ?? 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM process_steps WHERE id = ?')->execute([$id]);
    }
}
