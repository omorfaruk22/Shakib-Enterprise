<?php
// SAKIB ENTERPRISE — Security Configuration
return [
    'csrf_token_name' => '_token',
    'session_timeout' => (int)(getenv('SESSION_LIFETIME') ?: 1800),
    'login_max_attempts' => 5,
    'login_window_minutes' => 15,
    'upload' => [
        'max_size' => 5 * 1024 * 1024, // 5 MB
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowed_exts' => ['jpg', 'jpeg', 'png', 'webp'],
        'path' => __DIR__ . '/../public/assets/uploads',
        'url_prefix' => '/assets/uploads',
    ],
    'booking_throttle_seconds' => 10,
];
