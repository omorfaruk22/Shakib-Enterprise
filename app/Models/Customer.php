<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Validator;
use PDO;

class Customer
{
    public static function findOrCreate(string $name, string $phone, ?string $email = null, ?string $address = null): int
    {
        $normalized = Validator::normalizePhone($phone);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM customers WHERE phone_normalized = ? LIMIT 1');
        $stmt->execute([$normalized]);
        $id = $stmt->fetchColumn();
        if ($id) {
            // Update name/email if provided
            $pdo->prepare('UPDATE customers SET name = ?, email = COALESCE(?, email), address = COALESCE(?, address) WHERE id = ?')
                ->execute([$name, $email, $address, $id]);
            return (int)$id;
        }
        $pdo->prepare('INSERT INTO customers (name, phone, phone_normalized, email, address) VALUES (?, ?, ?, ?, ?)')
            ->execute([$name, $phone, $normalized, $email, $address]);
        return (int)$pdo->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM customers WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(string $search = '', int $limit = 20, int $offset = 0): array
    {
        $where = '';
        $params = [];
        if ($search !== '') {
            $where = 'WHERE name LIKE ? OR phone LIKE ? OR email LIKE ?';
            $params = ["%$search%", "%$search%", "%$search%"];
        }
        $stmt = Database::getConnection()->prepare("SELECT * FROM customers $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        // Bind search params first
        $i = 1;
        // Rebuild with proper binding: use execute with params + limit/offset via query string
        $sql = "SELECT * FROM customers $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $stmt2 = Database::getConnection()->prepare($sql);
        $stmt2->execute($params);
        return $stmt2->fetchAll();
    }

    public static function count(string $search = ''): int
    {
        if ($search !== '') {
            $stmt = Database::getConnection()->prepare('SELECT COUNT(*) FROM customers WHERE name LIKE ? OR phone LIKE ? OR email LIKE ?');
            $stmt->execute(["%$search%", "%$search%", "%$search%"]);
            return (int)$stmt->fetchColumn();
        }
        return (int)Database::getConnection()->query('SELECT COUNT(*) FROM customers')->fetchColumn();
    }

    public static function bookings(int $customerId): array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM bookings WHERE customer_id = ? ORDER BY created_at DESC');
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM customers WHERE email = ? LIMIT 1');
        $stmt->execute([strtolower(trim($email))]);
        return $stmt->fetch() ?: null;
    }

    public static function findByPhone(string $phone): ?array
    {
        $normalized = self::normalizePhone($phone);
        $stmt = Database::getConnection()->prepare('SELECT * FROM customers WHERE phone_normalized = ? LIMIT 1');
        $stmt->execute([$normalized]);
        return $stmt->fetch() ?: null;
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '880') && strlen($digits) === 13) return $digits;
        if (str_starts_with($digits, '01') && strlen($digits) === 11) return '880' . substr($digits, 1);
        return $digits;
    }

    public static function register(string $name, string $phone, ?string $email, string $password): int
    {
        $normalized = self::normalizePhone($phone);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO customers (name, phone, phone_normalized, email, password_hash, status) VALUES (?, ?, ?, ?, ?, "active")');
        $stmt->execute([$name, $phone, $normalized, $email ? strtolower(trim($email)) : null, $hash]);
        return (int)$pdo->lastInsertId();
    }

    public static function setPassword(int $id, string $password): void
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        Database::getConnection()->prepare('UPDATE customers SET password_hash = ? WHERE id = ?')->execute([$hash, $id]);
    }
}
