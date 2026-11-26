<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

$required = ['PDO', 'pdo_mysql', 'session', 'fileinfo'];
$loaded = get_loaded_extensions();
$missing = array_values(array_diff($required, $loaded));
$versionSupported = version_compare(PHP_VERSION, '8.2.0', '>=');

echo 'PHP: ' . PHP_VERSION . PHP_EOL;
echo 'PHP version requirement (8.2+): ' . ($versionSupported ? 'available' : 'not met') . PHP_EOL;
echo 'Environment: ' . $appConfig['environment'] . PHP_EOL;
echo 'Required extensions: ' . ($missing === [] ? 'available' : 'missing: ' . implode(', ', $missing)) . PHP_EOL;

if (!$versionSupported || $missing !== []) {
    exit(1);
}
