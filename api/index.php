<?php
// Vercel entry for SAKIB ENTERPRISE
// Change cwd to project root so all includes resolve
chdir(__DIR__ . '/..');
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../public';
$_SERVER['PHP_SELF'] = '/index.php';

// In Vercel, APP_URL must come from VERCEL_URL
if (empty(getenv('APP_URL')) && !empty($_SERVER['HTTP_HOST'])) {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'https';
    putenv("APP_URL=$proto://" . $_SERVER['HTTP_HOST']);
    $_ENV['APP_URL'] = "$proto://" . $_SERVER['HTTP_HOST'];
}

require __DIR__ . '/../public/index.php';
