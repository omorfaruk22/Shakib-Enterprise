<?php
declare(strict_types=1);

namespace App\Helpers;

final class Logger
{
    public static function error(string $message, string $logFile): void
    {
        $directory = dirname($logFile);
        if (!is_dir($directory)) {
            mkdir($directory, 0750, true);
        }

        $line = sprintf("[%s] ERROR %s%s", gmdate('c'), $message, PHP_EOL);
        error_log($line, 3, $logFile);
    }
}
