<?php
// SAKIB ENTERPRISE — Global Helpers

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim(getenv('APP_URL') ?: '', '/');
        if ($base === '') {
            $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
                || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
            $protocol = $https ? 'https' : 'http';
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base = $protocol . '://' . $host;
        }
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        // If path already starts with assets/, don't double prefix
        if (str_starts_with($path, 'assets/') || str_starts_with($path, '/assets/')) {
            return url(ltrim($path, '/'));
        }
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('setting')) {
    function setting(string $key, string $default = ''): string
    {
        static $cache = null;
        if ($cache === null) {
            try {
                $cache = \App\Models\Setting::all();
            } catch (\Throwable $e) {
                $cache = [];
            }
        }
        return $cache[$key] ?? $default;
    }
}

if (!function_exists('whatsapp_url')) {
    function whatsapp_url(string $message = 'Hello SAKIB ENTERPRISE, I want to know about your travel services.'): string
    {
        $number = preg_replace('/\D+/', '', setting('whatsapp', ''));
        if ($number === '') {
            return '#';
        }
        return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
    }
}

if (!function_exists('flash')) {
    function flash(string $key): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('clear_old')) {
    function clear_old(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['old']);
    }
}

if (!function_exists('format_price')) {
    function format_price($price): string
    {
        return '৳' . number_format((float)$price, 0);
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date): string
    {
        if (!$date) return '—';
        $ts = strtotime($date);
        return $ts ? date('d M Y', $ts) : e($date);
    }
}

if (!function_exists('status_badge')) {
    function status_badge(string $status): string
    {
        $map = [
            'pending' => 'bg-amber-100 text-amber-800',
            'contacted' => 'bg-blue-100 text-blue-800',
            'processing' => 'bg-indigo-100 text-indigo-800',
            'confirmed' => 'bg-emerald-100 text-emerald-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'rejected' => 'bg-rose-100 text-rose-800',
        ];
        $cls = $map[$status] ?? 'bg-gray-100 text-gray-800';
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ' . $cls . '">' . e(ucfirst($status)) . '</span>';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return \App\Core\Csrf::field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Core\Csrf::token();
    }
}

if (!function_exists('partial')) {
    function partial(string $name, array $data = []): void
    {
        \App\Core\View::partial($name, $data);
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return \App\Core\Auth::user();
    }
}
