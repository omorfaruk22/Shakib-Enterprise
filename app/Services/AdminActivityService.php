<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Repositories\AdminActivityRepository;
use App\Security\AdminAccess;
use PDO;

final class AdminActivityService
{
    private readonly PDO $database;
    private readonly AdminActivityRepository $activity;

    public function __construct(?PDO $database = null)
    {
        $this->database = $database ?? Connection::fromConfig(require BASE_PATH . '/config/database.php');
        $this->activity = new AdminActivityRepository();
    }

    public function list(string $search = '', int $page = 1, int $perPage = 25): array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->activity->list($this->database, $search, $page, $perPage);
    }
}
