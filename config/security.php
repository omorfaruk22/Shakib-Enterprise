<?php
declare(strict_types=1);

function configure_session_security(array $app): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = request_is_https();

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    session_name('sakib_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps || $app['environment'] === 'production',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
