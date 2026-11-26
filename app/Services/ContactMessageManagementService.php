<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Repositories\AdminActivityRepository;
use App\Repositories\ContactMessageRepository;
use App\Security\AdminAccess;
use PDO;

final class ContactMessageManagementService
{
    private readonly PDO $database;
    private readonly ContactMessageRepository $messages;

    public function __construct(?PDO $database = null)
    {
        $this->database = $database ?? Connection::fromConfig(require BASE_PATH . '/config/database.php');
        $this->messages = new ContactMessageRepository($this->database);
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->messages->list($filters, $page, $perPage);
    }

    public function find(int $id): ?array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->messages->find($id);
    }

    public function update(int $id, string $status, ?string $adminNote = null): void
    {
        $admin = AdminAccess::requireAdmin($this->database);
        $this->messages->updateStatus($id, $status, $adminNote, $admin['id']);
    }

    public function delete(int $id): void
    {
        $admin = AdminAccess::requireAdmin($this->database);
        $this->database->beginTransaction();
        try {
            $this->messages->delete($id);
            (new AdminActivityRepository())->record($this->database, $admin['id'], 'contact.deleted', 'contact_message', $id);
            $this->database->commit();
        } catch (\Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }

    public function exportCsv(array $filters = []): string
    {
        AdminAccess::requireAdmin($this->database);
        $stream = fopen('php://temp', 'w+');
        if ($stream === false) {
            throw new \RuntimeException('CSV export could not be initialized.');
        }
        try {
            CsvExporter::write($this->messages->exportStatement($filters), [
                'name' => 'Name',
                'phone' => 'Phone',
                'email' => 'Email',
                'subject' => 'Subject',
                'message' => 'Message',
                'status' => 'Status',
                'created_at' => 'Received at',
            ], $stream);
            rewind($stream);
            return (string) stream_get_contents($stream);
        } finally {
            fclose($stream);
        }
    }
}
