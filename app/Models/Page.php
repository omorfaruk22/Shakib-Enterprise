<?php
namespace App\Models;

use App\Core\Database;

class Page
{
    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM website_pages WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function update(string $slug, array $data): void
    {
        Database::getConnection()->prepare('UPDATE website_pages SET title = ?, content = ?, meta_title = ?, meta_description = ? WHERE slug = ?')
            ->execute([$data['title'], $data['content'] ?? null, $data['meta_title'] ?? null, $data['meta_description'] ?? null, $slug]);
    }
}
