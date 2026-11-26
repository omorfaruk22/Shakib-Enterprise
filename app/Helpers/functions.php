<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_url(string $path = ''): string
{
    global $appConfig;
    return rtrim((string) ($appConfig['url'] ?? ''), '/') . '/' . ltrim($path, '/');
}

function redirect(string $path, int $status = 302): never
{
    header('Location: ' . app_url($path), true, $status);
    exit;
}

function start_secure_session(): void
{
    global $appConfig;
    configure_session_security($appConfig);
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function csrf_token(): string
{
    start_secure_session();
    if (!isset($_SESSION['_csrf']) || !is_string($_SESSION['_csrf']) || strlen($_SESSION['_csrf']) !== 64) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function is_valid_csrf_token(mixed $submitted): bool
{
    start_secure_session();
    $sessionToken = $_SESSION['_csrf'] ?? null;
    return is_string($submitted)
        && $submitted !== ''
        && is_string($sessionToken)
        && strlen($sessionToken) === 64
        && hash_equals($sessionToken, $submitted);
}

function issue_booking_submission_token(): string
{
    start_secure_session();
    $token = bin2hex(random_bytes(32));
    $_SESSION['_booking_forms'] ??= [];
    $_SESSION['_booking_forms'][$token] = time();
    foreach ($_SESSION['_booking_forms'] as $issuedToken => $issuedAt) {
        if (!is_int($issuedAt) || $issuedAt < time() - 7200) {
            unset($_SESSION['_booking_forms'][$issuedToken]);
        }
    }
    return $token;
}

function e_value(mixed $value): string
{
    return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
