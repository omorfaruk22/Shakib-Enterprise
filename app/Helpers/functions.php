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
