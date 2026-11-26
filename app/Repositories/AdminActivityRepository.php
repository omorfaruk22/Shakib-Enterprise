<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

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
}
