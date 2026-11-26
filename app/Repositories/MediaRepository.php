<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use RuntimeException;

final class MediaRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    public function list(int $page = 1, int $perPage = 24): array
    {
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $total = (int) $this->database->query('SELECT COUNT(*) FROM media')->fetchColumn();
        $statement = $this->database->prepare(
            'SELECT m.id, m.storage_path, m.original_name, m.mime_type, m.file_size, m.alt_text, m.created_at,
                    a.name AS admin_name
             FROM media AS m LEFT JOIN admins AS a ON a.id = m.uploaded_by
             ORDER BY m.created_at DESC, m.id DESC LIMIT ? OFFSET ?'
        );
        $statement->bindValue(1, $perPage, PDO::PARAM_INT);
        $statement->bindValue(2, ($page - 1) * $perPage, PDO::PARAM_INT);
        $statement->execute();
        return [
            'items' => $statement->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function find(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        $statement = $this->database->prepare('SELECT * FROM media WHERE id = ? LIMIT 1');
        $statement->execute([$id]);
        $row = $statement->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $data, int $adminId): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO media (storage_path, original_name, mime_type, file_size, alt_text, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $statement->execute([
            $data['storage_path'],
            $data['original_name'],
            $data['mime_type'],
            $data['file_size'],
            $data['alt_text'],
            $adminId,
        ]);
        return (int) $this->database->lastInsertId();
    }

    public function delete(int $id): array
    {
        $media = $this->find($id);
        if ($media === null) {
            throw new RuntimeException('The requested media record does not exist.');
        }
        $statement = $this->database->prepare('DELETE FROM media WHERE id = ?');
        $statement->execute([$id]);
        return $media;
    }
}
