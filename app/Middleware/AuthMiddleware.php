<?php
namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            header('Location: ' . url('admin/login'));
            exit;
        }
    }

    /** Require super_admin or admin (full access). Managers blocked. */
    public static function requireAdmin(): void
    {
        self::handle();
        $user = Auth::user();
        if (!in_array($user['role'] ?? '', ['super_admin', 'admin'])) {
            http_response_code(403);
            echo '<!DOCTYPE html><html><head><title>403 Forbidden</title><link rel="stylesheet" href="https://cdn.tailwindcss.com"></head><body class="bg-gray-50 flex items-center justify-center min-h-screen"><div class="text-center p-8 bg-white rounded-xl shadow max-w-md"><div class="text-5xl mb-3">🔒</div><h1 class="font-bold text-2xl mb-2">Access Denied</h1><p class="text-gray-600 mb-4">You do not have permission to access this page.</p><a href="' . url('/admin/dashboard') . '" class="btn-primary px-4 py-2 rounded bg-[#102B70] text-white inline-block">Back to Dashboard</a></div></body></html>';
            exit;
        }
    }
}
