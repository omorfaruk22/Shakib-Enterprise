<?php
declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;

final class Connection
{
    public static function fromConfig(array $config): PDO
    {
        foreach (['host', 'port', 'database', 'username', 'charset'] as $key) {
            if (!array_key_exists($key, $config) || $config[$key] === '') {
                throw new RuntimeException('Database configuration is incomplete. Check the project .env file.');
            }
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            $database = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]);
            $database->exec("SET time_zone = '+00:00'");
            return $database;
        } catch (PDOException $exception) {
            throw new RuntimeException('Database connection failed. Verify MySQL is running and the .env settings are correct.', 0, $exception);
        }
    }
}
