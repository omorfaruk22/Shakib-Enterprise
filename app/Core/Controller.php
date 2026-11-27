<?php
namespace App\Core;

class Controller
{
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function back(string $fallback = '/'): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? $fallback;
        // Only allow same-host referers
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host && strpos($ref, $host) === false && strpos($ref, 'http') === 0) {
            $ref = $fallback;
        }
        $this->redirect($ref);
    }

    protected function flash(string $key, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'][$key] = $message;
    }

    protected function old(array $input): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($input['_token'], $input['password']);
        $_SESSION['old'] = $input;
    }
}
