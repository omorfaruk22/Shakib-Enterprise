<?php
declare(strict_types=1);

namespace App\Services;

use PDOStatement;

final class CsvExporter
{
    /** @param resource $stream */
    public static function write(PDOStatement $rows, array $columns, $stream): void
    {
        fputcsv($stream, array_values($columns), ',', '"', '');
        while ($row = $rows->fetch()) {
            $values = [];
            foreach ($columns as $field => $_label) {
                $value = $row[$field] ?? '';
                $value = is_scalar($value) ? (string) $value : '';
                $values[] = preg_match('/^\s*[=+\-@]/u', $value) === 1 ? "'" . $value : $value;
            }
            fputcsv($stream, $values, ',', '"', '');
        }
    }
}
