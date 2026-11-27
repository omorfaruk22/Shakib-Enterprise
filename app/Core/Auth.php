<?php
namespace App\Core;

use App\Models\Admin;

class Auth
{
    public static function check(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['admin_id'])) {
            return false;
        }
        $timeout = (int)(getenv('SESSION_LIFETIME') ?: 1800);
        $last = $_SESSION['last_activity'] ?? time();
        if (time() - $last > $timeout) {
            self::logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return Admin::find((int)$_SESSION['admin_id']);
    }

    public static function login(array $admin): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['last_activity'] = time();
        Admin::updateLastLogin((int)$admin['id']);
    }

    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function attempt(string $email, string $password, string $ip): ?array
    {
        $admin = Admin::findByEmail($email);
        $valid = $admin && $admin['status'] === 'active' && password_verify($password, $admin['password_hash']);
        Admin::logAttempt($email, $ip, $valid ? 1 : 0);
        if ($valid) {
            self::login($admin);
            return $admin;
        }
        return null;
    }

    public static function tooManyAttempts(string $email, string $ip): bool
    {
        return Admin::recentFailedAttempts($email, $ip) >= 5;
    }
}
