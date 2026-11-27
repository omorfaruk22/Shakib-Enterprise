<?php
namespace App\Core;

class CustomerAuth
{
    private const SESSION_KEY = 'customer_id';
    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;

    public static function user(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION[self::SESSION_KEY])) return null;
        return \App\Models\Customer::find((int)$_SESSION[self::SESSION_KEY]);
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function attempt(string $phoneOrEmail, string $password, string $ip = ''): ?array
    {
        $pdo = Database::getConnection();
        $field = filter_var($phoneOrEmail, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_normalized';
        $val = $field === 'phone_normalized' ? \App\Models\Customer::normalizePhone($phoneOrEmail) : strtolower(trim($phoneOrEmail));

        $stmt = $pdo->prepare("SELECT * FROM customers WHERE $field = ? LIMIT 1");
        $stmt->execute([$val]);
        $customer = $stmt->fetch();

        if (!$customer) { self::fakeDelay(); return null; }
        if ($customer['status'] === 'banned' || $customer['status'] === 'inactive') return null;
        if ($customer['login_locked_until'] && strtotime($customer['login_locked_until']) > time()) return null;
        if (empty($customer['password_hash'])) return null;
        if (!password_verify($password, $customer['password_hash'])) {
            self::registerFailedAttempt((int)$customer['id']);
            return null;
        }

        // Success
        $pdo->prepare("UPDATE customers SET last_login_at = NOW(), login_attempts = 0, login_locked_until = NULL WHERE id = ?")->execute([$customer['id']]);
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = (int)$customer['id'];
        return $customer;
    }

    public static function login(array $customer): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = (int)$customer['id'];
    }

    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
    }

    public static function registerFailedAttempt(int $id): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare("UPDATE customers SET login_attempts = login_attempts + 1 WHERE id = ?")->execute([$id]);
        $stmt = $pdo->prepare("SELECT login_attempts FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        $attempts = (int)$stmt->fetchColumn();
        if ($attempts >= self::MAX_ATTEMPTS) {
            $pdo->prepare("UPDATE customers SET login_locked_until = DATE_ADD(NOW(), INTERVAL " . self::LOCK_MINUTES . " MINUTE) WHERE id = ?")->execute([$id]);
        }
    }

    private static function fakeDelay(): void
    {
        usleep(300000); // mitigate timing attacks
    }
}
