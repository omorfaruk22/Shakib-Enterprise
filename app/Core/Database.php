<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $cfg = require __DIR__ . '/../../config/database.php';
            $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset={$cfg['charset']}";
            try {
                self::$instance = new PDO($dsn, $cfg['username'], $cfg['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                Logger::error('DB connection failed: ' . $e->getMessage());
                throw new \RuntimeException('Database connection failed. Please contact the administrator.');
            }
        }
        return self::$instance;
    }

    public static function setConnection(PDO $pdo): void
    {
        self::$instance = $pdo;
    }
}
