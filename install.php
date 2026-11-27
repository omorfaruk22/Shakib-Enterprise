<?php
// SAKIB ENTERPRISE — One-time installer
// Creates tables (if not already present) and sets up the first admin account.
// DELETE THIS FILE after installation.
session_start();

$installedLock = __DIR__ . '/storage/installed.lock';
$dsn = 'mysql:host=localhost;port=3306;dbname=sakib_enterprise;charset=utf8mb4';
$dbUser = 'sakib_user';
$dbPass = 'Sakib@2026!';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Load .env if exists
    $envFile = __DIR__ . '/.env';
    if (is_file($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$k, $v] = explode('=', $line, 2);
            putenv(trim($k) . '=' . trim($v));
        }
    }
    try {
        $pdo = new PDO(
            'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';port=' . (getenv('DB_PORT') ?: '3306')
                . ';dbname=' . (getenv('DB_DATABASE') ?: 'sakib_enterprise') . ';charset=utf8mb4',
            getenv('DB_USERNAME') ?: 'sakib_user',
            getenv('DB_PASSWORD') ?: 'Sakib@2026!',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
        );
    } catch (Throwable $e) {
        $error = 'Database connection failed: ' . htmlspecialchars($e->getMessage());
        $pdo = null;
    }

    if ($pdo && !$error) {
        // 1) Run SQL installation script
        $sqlFile = __DIR__ . '/database/sakib_enterprise.sql';
        if (is_file($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            // Run each statement
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                if ($stmt !== '' && !str_starts_with($stmt, '--')) {
                    try { $pdo->exec($stmt); } catch (Throwable $e) {
                        // Ignore "already exists" errors to allow re-runs
                        if (!str_contains($e->getMessage(), 'already exists') && !str_contains($e->getMessage(), 'Duplicate')) {
                            throw $e;
                        }
                    }
                }
            }
        }

        // 2) Create admin user
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if ($name === '' || $email === '' || $password === '') {
            $error = 'Please fill in name, email and password.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            // Check if admin exists
            $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $exists = $stmt->fetchColumn();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            if ($exists) {
                $pdo->prepare('UPDATE admins SET name = ?, password = ? WHERE email = ?')->execute([$name, $hash, $email]);
                $adminId = $exists;
            } else {
                $pdo->prepare('INSERT INTO admins (name, email, password, is_active, created_at) VALUES (?, ?, ?, 1, NOW())')
                    ->execute([$name, $email, $hash]);
                $adminId = $pdo->lastInsertId();
            }
            // Write installed lock
            @file_put_contents($installedLock, date('Y-m-d H:i:s') . ' | admin: ' . $email . ' | id: ' . $adminId);
            $success = 'Installation complete! First admin account created. Please DELETE install.php and visit /admin/login.';
        }
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Install — SAKIB ENTERPRISE</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config = {theme:{extend:{colors:{'brand-navy':'#102B70','brand-red':'#D52D35','brand-light':'#EAF2FC'}}}}</script>
</head>
<body class="bg-brand-light min-h-screen flex items-center justify-center p-4">
<div class="max-w-lg w-full bg-white rounded-xl shadow-xl p-8">
<div class="flex items-center gap-3 mb-6"><div class="w-12 h-12 bg-brand-navy text-white rounded-lg flex items-center justify-center font-bold text-xl">SE</div><div><h1 class="text-2xl font-bold text-brand-navy">SAKIB ENTERPRISE</h1><p class="text-sm text-gray-500">Installation & Admin Setup</p></div></div>
<?php if ($error): ?><div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded"><?= htmlspecialchars($success) ?><br><a class="text-brand-navy font-semibold underline" href="/admin/login">Go to Admin Login</a></div><?php else: ?>
<?php if (is_file($installedLock)): ?><div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded">The system appears to be installed already. Running install.php again will update the database and reset the admin password. <strong>Remember to DELETE this file after setup.</strong></div><?php endif; ?>
<form method="POST" class="space-y-4">
<div><label class="block text-sm font-medium text-gray-700 mb-1">Admin Full Name</label><input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-navy focus:outline-none" placeholder="Administrator" value="<?= htmlspecialchars($_POST['name'] ?? 'Super Admin') ?>"></div>
<div><label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label><input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-navy focus:outline-none" placeholder="admin@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
<div><label class="block text-sm font-medium text-gray-700 mb-1">Password (min 8 chars)</label><input type="password" name="password" required minlength="8" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-navy focus:outline-none"></div>
<div><label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label><input type="password" name="confirm_password" required minlength="8" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-navy focus:outline-none"></div>
<button type="submit" class="w-full bg-brand-navy text-white py-3 rounded-lg font-semibold hover:bg-opacity-90">Install & Create Admin</button>
<p class="text-xs text-gray-500 mt-3">This will create database tables (using <code>database/sakib_enterprise.sql</code>) and create your admin account. Make sure your MySQL database exists and .env is configured.</p>
</form>
<?php endif; ?>
</div>
</body>
</html>
