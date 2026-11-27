<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Admin
{
    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM admins WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $name, string $email, string $password): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO admins (name, email, password_hash, role, status) VALUES (?, ?, ?, "admin", "active")'
        );
        $stmt->execute([$name, $email, $hash]);
        return (int)Database::getConnection()->lastInsertId();
    }

    public static function updateLastLogin(int $id): void
    {
        Database::getConnection()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?')->execute([$id]);
    }

    public static function logAttempt(string $email, string $ip, int $success): void
    {
        Database::getConnection()->prepare(
            'INSERT INTO admin_login_attempts (email, ip_address, success) VALUES (?, ?, ?)'
        )->execute([$email, $ip, $success]);
    }

    public static function recentFailedAttempts(string $email, string $ip): int
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT COUNT(*) FROM admin_login_attempts WHERE email = ? AND ip_address = ? AND success = 0 AND attempted_at >= (NOW() - INTERVAL 15 MINUTE)'
        );
        $stmt->execute([$email, $ip]);
        return (int)$stmt->fetchColumn();
    }

    public static function updateProfile(int $id, string $name, ?string $password = null): void
    {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            Database::getConnection()->prepare('UPDATE admins SET name = ?, password_hash = ? WHERE id = ?')->execute([$name, $hash, $id]);
        } else {
            Database::getConnection()->prepare('UPDATE admins SET name = ? WHERE id = ?')->execute([$name, $id]);
        }
    }

    public static function logActivity(?int $adminId, string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null, ?string $ip = null): void
    {
        Database::getConnection()->prepare(
            'INSERT INTO admin_activity_logs (admin_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$adminId, $action, $entityType, $entityId, $details, $ip]);
    }

    public static function activityLogs(int $limit = 50, int $offset = 0): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT l.*, a.name AS admin_name FROM admin_activity_logs l LEFT JOIN admins a ON a.id = l.admin_id ORDER BY l.created_at DESC LIMIT ? OFFSET ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count(): int
    {
        return (int)Database::getConnection()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    }
}
