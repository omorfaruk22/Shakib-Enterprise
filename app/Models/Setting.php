<?php
namespace App\Models;

use App\Core\Database;

class Setting
{
    private static ?array $cache = null;

    public static function clearCache(): void { self::$cache = null; }

    public static function all(): array
    {
        if (self::$cache !== null) return self::$cache;
        $rows = Database::getConnection()->query('SELECT setting_key, setting_value FROM website_settings')->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['setting_key']] = $r['setting_value'] ?? '';
        }
        self::$cache = $out;
        return $out;
    }

    public static function get(string $key, string $default = ''): string
    {
        $stmt = Database::getConnection()->prepare('SELECT setting_value FROM website_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val === false ? $default : (string)$val;
    }

    public static function set(string $key, ?string $value): void
    {
        Database::getConnection()->prepare(
            'INSERT INTO website_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        )->execute([$key, $value]);
    }

    public static function updateMany(array $pairs): void
    {
        foreach ($pairs as $k => $v) {
            self::set($k, $v);
        }
    }
}
