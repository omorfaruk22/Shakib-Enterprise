<?php
namespace App\Middleware;

use App\Core\CustomerAuth;

class CustomerMiddleware
{
    public static function handle(): void
    {
        if (!CustomerAuth::check()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/customer';
            header('Location: /login');
            exit;
        }
    }
}
