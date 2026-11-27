<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class ContactMessage
{
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->prepare('INSERT INTO contact_messages (name, phone, email, subject, message) VALUES (?, ?, ?, ?, ?)')
            ->execute([$data['name'], $data['phone'] ?? null, $data['email'] ?? null, $data['subject'] ?? null, $data['message']]);
        return (int)$pdo->lastInsertId();
    }

    public static function all(string $search = '', int $limit = 20, int $offset = 0): array
    {
        $where = '';
        $params = [];
        if ($search !== '') {
            $where = 'WHERE name LIKE ? OR phone LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?';
            $params = array_fill(0, 5, "%$search%");
        }
        $sql = "SELECT * FROM contact_messages $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function count(string $search = ''): int
    {
        if ($search !== '') {
            $stmt = Database::getConnection()->prepare('SELECT COUNT(*) FROM contact_messages WHERE name LIKE ? OR phone LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?');
            $stmt->execute(array_fill(0, 5, "%$search%"));
            return (int)$stmt->fetchColumn();
        }
        return (int)Database::getConnection()->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM contact_messages WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function markRead(int $id, int $read = 1): void
    {
        Database::getConnection()->prepare('UPDATE contact_messages SET is_read = ? WHERE id = ?')->execute([$read, $id]);
    }

    public static function markReplied(int $id, int $replied = 1): void
    {
        Database::getConnection()->prepare('UPDATE contact_messages SET is_replied = ? WHERE id = ?')->execute([$replied, $id]);
    }

    public static function addNote(int $id, string $note): void
    {
        Database::getConnection()->prepare('UPDATE contact_messages SET admin_notes = ? WHERE id = ?')->execute([$note, $id]);
    }

    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$id]);
    }

    public static function unreadCount(): int
    {
        return (int)Database::getConnection()->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
    }

    public static function recent(int $limit = 5): array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
