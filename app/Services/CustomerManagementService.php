<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Repositories\CustomerRepository;
use App\Security\AdminAccess;
use PDO;

final class CustomerManagementService
{
    private readonly PDO $database;
    private readonly CustomerRepository $customers;

    public function __construct(?PDO $database = null)
    {
        $this->database = $database ?? Connection::fromConfig(require BASE_PATH . '/config/database.php');
        $this->customers = new CustomerRepository($this->database);
    }

    public function list(string $search = '', int $page = 1, int $perPage = 25): array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->customers->list($search, $page, $perPage);
    }

    public function find(int $id): ?array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->customers->find($id);
    }

    public function bookings(int $id): array
    {
        AdminAccess::requireAdmin($this->database);
        if ($this->customers->find($id) === null) {
            return [];
        }
        return $this->customers->bookings($id);
    }

    public function bookingCount(int $id): int
    {
        AdminAccess::requireAdmin($this->database);
        return $this->customers->find($id) === null ? 0 : $this->customers->bookingCount($id);
    }

    public function update(int $id, array $input): void
    {
        $admin = AdminAccess::requireAdmin($this->database);
        $this->customers->update($id, $input, $admin['id']);
    }

    public function delete(int $id): void
    {
        $admin = AdminAccess::requireAdmin($this->database);
        $this->customers->delete($id, $admin['id']);
    }
}
