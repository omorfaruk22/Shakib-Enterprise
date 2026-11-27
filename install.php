<?php
// One-time installer. Remove/disable the public wrapper after setup.
require __DIR__ . '/bootstrap.php';

$installedLock = __DIR__ . '/storage/installed.lock';
$requiredTables = [
    'admin_activity_logs', 'admin_login_attempts', 'admins', 'booking_notes',
    'booking_status_history', 'bookings', 'contact_messages', 'customers',
    'destinations', 'holiday_packages', 'media', 'services', 'ship_routes',
    'testimonials', 'tour_packages', 'website_pages', 'website_settings',
];
$error = is_file($installedLock) ? 'This application has already been installed. The installer is locked.' : '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {
    \App\Core\Csrf::checkOrFail();

    $name = is_string($_POST['name'] ?? null) ? trim($_POST['name']) : '';
    $email = is_string($_POST['email'] ?? null) ? strtolower(trim($_POST['email'])) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirm = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Please fill in name, email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 12) {
        $error = 'Password must be at least 12 characters.';
    } elseif (!hash_equals($password, $confirm)) {
        $error = 'Passwords do not match.';
    } else {
        $dbUser = getenv('DB_USERNAME');
        $dbPass = getenv('DB_PASSWORD');
        if ($dbUser === false || $dbPass === false) {
            $error = 'Set DB_USERNAME and DB_PASSWORD in .env before installing.';
        } else {
            try {
                $pdo = new PDO(
                    'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';port=' . (getenv('DB_PORT') ?: '3306')
                        . ';dbname=' . (getenv('DB_DATABASE') ?: 'sakib_enterprise') . ';charset=utf8mb4',
                    $dbUser,
                    $dbPass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
                );

                $tables = $pdo->query('SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE()')->fetchAll(PDO::FETCH_COLUMN);
                if (in_array('admins', $tables, true)) {
                    $adminCount = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
                    if ($adminCount > 0) {
                        throw new RuntimeException('An admin already exists. The installer will not change existing accounts.');
                    }
                    $missing = array_values(array_diff($requiredTables, $tables));
                    if ($missing) {
                        throw new RuntimeException('The database schema is incomplete. Restore an empty database, then run the installer again.');
                    }
                } elseif ($tables) {
                    throw new RuntimeException('The selected database is not empty and has no admins table. Use a new empty database to avoid overwriting data.');
                } else {
                    $sqlFile = __DIR__ . '/database/sakib_enterprise.sql';
                    if (!is_file($sqlFile)) {
                        throw new RuntimeException('The database schema file is missing.');
                    }
                    $sql = file_get_contents($sqlFile);
                    foreach (splitSqlStatements($sql) as $statement) {
                        $pdo->exec($statement);
                    }
                }

                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare('INSERT INTO admins (name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$name, $email, $hash, 'super_admin', 'active']);

                if (@file_put_contents($installedLock, date('Y-m-d H:i:s') . "\n", LOCK_EX) === false) {
                    error_log('Could not write the installer lock file. Admin account was created; database guard remains active.');
                }
                $success = 'Installation complete. Remove or disable the installer, then sign in at /admin/login.';
            } catch (PDOException $e) {
                error_log('Installer database failure: ' . $e->getMessage());
                $error = 'Installation failed. Check the database settings and application log.';
            } catch (RuntimeException $e) {
                error_log('Installer failed: ' . $e->getMessage());
                $error = $e->getMessage();
            } catch (Throwable $e) {
                error_log('Installer failed: ' . $e->getMessage());
                $error = 'Installation failed. Check the database settings and application log.';
            }
        }
    }
}

/** Split this schema dump while ignoring SQL comments. */
function splitSqlStatements(string $sql): array
{
    $sql = preg_replace('/^[ \t]*--.*$/m', '', $sql);
    $sql = preg_replace('/^[ \t]*#.*$/m', '', $sql);
    $sql = preg_replace('~/\*.*?\*/~s', '', $sql);
    return array_values(array_filter(array_map('trim', explode(';', $sql)), static fn($part) => $part !== ''));
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
<div class="flex items-center gap-3 mb-6"><div class="w-12 h-12 bg-brand-navy text-white rounded-lg flex items-center justify-center font-bold text-xl">SE</div><div><h1 class="text-2xl font-bold text-brand-navy">SAKIB ENTERPRISE</h1><p class="text-sm text-gray-500">Installation &amp; Admin Setup</p></div></div>
<?php if ($error): ?><div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($success): ?><div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?><br><a class="text-brand-navy font-semibold underline" href="<?= htmlspecialchars(url('admin/login'), ENT_QUOTES, 'UTF-8') ?>">Go to Admin Login</a></div>
<?php elseif (!is_file($installedLock) && !str_contains($error, 'already exists') && !str_contains($error, 'already been installed')): ?>
<form method="POST" class="space-y-4">
<?= csrf_field() ?>
<div><label class="block text-sm font-medium text-gray-700 mb-1">Admin Full Name</label><input type="text" name="name" required maxlength="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-navy focus:outline-none" placeholder="Administrator" value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
<div><label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label><input type="email" name="email" required maxlength="191" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-navy focus:outline-none" placeholder="admin@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
<div><label class="block text-sm font-medium text-gray-700 mb-1">Password (minimum 12 characters)</label><input type="password" name="password" required minlength="12" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-navy focus:outline-none"></div>
<div><label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label><input type="password" name="confirm_password" required minlength="12" autocomplete="new-password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-navy focus:outline-none"></div>
<button type="submit" class="w-full bg-brand-navy text-white py-3 rounded-lg font-semibold hover:bg-opacity-90">Install &amp; Create Admin</button>
<p class="text-xs text-gray-500 mt-3">Use a new, empty database or import the complete schema first. Existing accounts and non-empty databases are never overwritten.</p>
</form>
<?php endif; ?>
</div>
</body>
</html>
