<?php
namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $expected = $_SESSION['_csrf_token'] ?? '';
        if (!$token || !$expected) {
            return false;
        }
        return hash_equals($expected, $token);
    }

    public static function checkOrFail(): void
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!self::validate($token)) {
            http_response_code(419);
            die('Session expired. Please refresh the page and try again.');
        }
    }
}
