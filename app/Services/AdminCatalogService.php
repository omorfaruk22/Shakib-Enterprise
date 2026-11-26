<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Repositories\AdminActivityRepository;
use App\Repositories\CatalogRepository;
use App\Security\AdminAccess;
use PDO;

final class AdminCatalogService
{
    private readonly PDO $database;
    private readonly CatalogRepository $catalog;
    private readonly AdminActivityRepository $activity;

    public function __construct(?PDO $database = null)
    {
        $this->database = $database ?? Connection::fromConfig(require BASE_PATH . '/config/database.php');
        $this->catalog = new CatalogRepository($this->database);
        $this->activity = new AdminActivityRepository();
    }

    public function list(string $resource, string $search = '', ?bool $active = null, int $page = 1, int $perPage = 25): array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->catalog->list($resource, $search, $active, $page, $perPage);
    }

    public function find(string $resource, int $id): ?array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->catalog->find($resource, $id);
    }

    public function save(string $resource, ?int $id, array $input): int
    {
        $admin = AdminAccess::requireAdmin($this->database);
        $this->database->beginTransaction();
        try {
            $recordId = $this->catalog->save($resource, $id, $input);
            $this->activity->record($this->database, $admin['id'], $id === null ? 'catalog.created' : 'catalog.updated', $resource, $recordId);
            $this->database->commit();
            return $recordId;
        } catch (\Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }

    public function delete(string $resource, int $id): void
    {
        $admin = AdminAccess::requireAdmin($this->database);
        $this->database->beginTransaction();
        try {
            $this->catalog->delete($resource, $id);
            $this->activity->record($this->database, $admin['id'], 'catalog.deleted', $resource, $id);
            $this->database->commit();
        } catch (\Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }
}
