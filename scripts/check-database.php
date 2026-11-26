<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

try {
    $databaseConfig = require BASE_PATH . '/config/database.php';
    \App\Database\Connection::fromConfig($databaseConfig)->query('SELECT 1');
    fwrite(STDOUT, "MySQL connection successful.\n");
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
