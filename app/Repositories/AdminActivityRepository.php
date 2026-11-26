<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use InvalidArgumentException;

final class AdminActivityRepository
{
    public function record(PDO $database, int $adminId, string $action, string $entityType, ?int $entityId): void
    {
        $statement = $database->prepare(
            'INSERT INTO admin_activity_logs (admin_id, action, entity_type, entity_id) VALUES (?, ?, ?, ?)'
        );
        $statement->execute([
            $adminId,
            substr($action, 0, 120),
            substr($entityType, 0, 100),
            $entityId,
        ]);
    }

    public function list(PDO $database, string $search = '', int $page = 1, int $perPage = 25): array
    {
        $search = trim($search);
        if (function_exists('mb_strlen') ? mb_strlen($search, 'UTF-8') > 120 : strlen($search) > 480) {
            throw new InvalidArgumentException('Search must be 120 characters or fewer.');
        }
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $where = '';
        $parameters = [];
        if ($search !== '') {
            $where = ' WHERE l.action LIKE ? OR l.entity_type LIKE ? OR a.name LIKE ?';
            $parameters = array_fill(0, 3, '%' . $search . '%');
        }
        $count = $database->prepare(
            'SELECT COUNT(*) FROM admin_activity_logs AS l LEFT JOIN admins AS a ON a.id = l.admin_id' . $where
        );
        $count->execute($parameters);
        $total = (int) $count->fetchColumn();
        $statement = $database->prepare(
            'SELECT l.id, l.action, l.entity_type, l.entity_id, l.created_at, a.name AS admin_name
             FROM admin_activity_logs AS l LEFT JOIN admins AS a ON a.id = l.admin_id' . $where . '
             ORDER BY l.created_at DESC, l.id DESC LIMIT ? OFFSET ?'
        );
        foreach ($parameters as $index => $parameter) {
            $statement->bindValue($index + 1, $parameter, PDO::PARAM_STR);
        }
        $statement->bindValue(count($parameters) + 1, $perPage, PDO::PARAM_INT);
        $statement->bindValue(count($parameters) + 2, ($page - 1) * $perPage, PDO::PARAM_INT);
        $statement->execute();
        return [
            'items' => $statement->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }
}
