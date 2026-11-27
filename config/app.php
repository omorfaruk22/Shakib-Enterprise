<?php
// SAKIB ENTERPRISE — App Configuration
return [
    'name' => getenv('APP_NAME') ?: 'SAKIB ENTERPRISE',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => (getenv('APP_DEBUG') === 'true'),
    'url' => rtrim(getenv('APP_URL') ?: 'http://localhost', '/'),
    'timezone' => 'Asia/Dhaka',
    'session_lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 1800),
    'session_secure' => (getenv('SESSION_SECURE') === 'true'),
    'currency' => 'BDT',
    'currency_symbol' => '৳',
];
