<?php
declare(strict_types=1);

return [
    'name' => env_value('APP_NAME', 'SAKIB ENTERPRISE'),
    'environment' => env_value('APP_ENV', 'local'),
    'debug' => filter_var(env_value('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL),
    'url' => rtrim((string) env_value('APP_URL', 'http://127.0.0.1:8000'), '/'),
    'timezone' => env_value('APP_TIMEZONE', 'Asia/Dhaka'),
    'admin_setup_token' => env_value('ADMIN_SETUP_TOKEN', ''),
];
