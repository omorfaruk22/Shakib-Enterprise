<?php
declare(strict_types=1);

namespace App\Security;

use PDO;
use RuntimeException;

final class AdminAccess
{
    /** @return array{id:int, name:string, email:string, role:string} */
    public static function requireAdmin(PDO $database): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            \start_secure_session();
        }

        $sessionId = $_SESSION['admin_id'] ?? null;
        if (!is_int($sessionId) && !(is_string($sessionId) && ctype_digit($sessionId))) {
            throw new RuntimeException('Administrator authentication is required.');
        }
        $adminId = (int) $sessionId;
        if ($adminId < 1) {
            throw new RuntimeException('Administrator authentication is required.');
        }

        $now = time();
        $authenticatedAt = $_SESSION['admin_authenticated_at'] ?? null;
        $lastActivityAt = $_SESSION['admin_last_activity'] ?? null;
        if (
            !is_int($authenticatedAt)
            || !is_int($lastActivityAt)
            || $authenticatedAt > $now
            || $lastActivityAt > $now
            || $now - $lastActivityAt > 1800
            || $now - $authenticatedAt > 43200
        ) {
            self::expireSession();
            throw new RuntimeException('Administrator session expired. Sign in again.');
        }

        $statement = $database->prepare(
            'SELECT id, name, email, role FROM admins WHERE id = ? AND is_active = 1 LIMIT 1'
        );
        $statement->execute([$adminId]);
        $admin = $statement->fetch();
        if (!is_array($admin)) {
            unset($_SESSION['admin_id']);
            throw new RuntimeException('Administrator access is no longer active.');
        }

        $role = strtolower((string) ($admin['role'] ?? ''));
        if (!in_array($role, ['admin', 'owner', 'super_admin'], true)) {
            throw new RuntimeException('This account does not have permission to manage site data.');
        }

        $_SESSION['admin_last_activity'] = $now;

        return [
            'id' => (int) $admin['id'],
            'name' => (string) $admin['name'],
            'email' => (string) $admin['email'],
            'role' => $role,
        ];
    }

    private static function expireSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $_SESSION = [];
        $parameters = session_get_cookie_params();
        if (ini_get('session.use_cookies')) {
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $parameters['path'],
                'domain' => $parameters['domain'],
                'secure' => $parameters['secure'],
                'httponly' => $parameters['httponly'],
                'samesite' => $parameters['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }
}
