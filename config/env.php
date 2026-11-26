<?php
declare(strict_types=1);

/**
 * Load simple KEY=value settings from the project-root .env file.
 * Environment variables already provided by the host take precedence.
 */
function load_environment(string $file): void
{
    if (!is_file($file) || !is_readable($file)) {
        return;
    }

    $values = parse_ini_file($file, false, INI_SCANNER_RAW);
    if (!is_array($values)) {
        throw new RuntimeException('The .env file could not be parsed.');
    }

    foreach ($values as $key => $value) {
        if (getenv((string) $key) === false) {
            putenv($key . '=' . (string) $value);
            $_ENV[$key] = (string) $value;
        }
    }
}

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}
