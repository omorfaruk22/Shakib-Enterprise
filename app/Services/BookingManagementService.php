<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Repositories\BookingRepository;
use App\Security\AdminAccess;
use PDO;

final class BookingManagementService
{
    private readonly PDO $database;
    private readonly BookingRepository $bookings;

    public function __construct(?PDO $database = null)
    {
        $this->database = $database ?? Connection::fromConfig(require BASE_PATH . '/config/database.php');
        $this->bookings = new BookingRepository($this->database);
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->bookings->list($filters, $page, $perPage);
    }

    public function find(int $id): ?array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->bookings->find($id);
    }

    public function history(int $id): array
    {
        AdminAccess::requireAdmin($this->database);
        if ($this->bookings->find($id) === null) {
            return [];
        }
        return $this->bookings->history($id);
    }

    public function notes(int $id): array
    {
        AdminAccess::requireAdmin($this->database);
        if ($this->bookings->find($id) === null) {
            return [];
        }
        return $this->bookings->notes($id);
    }

    public function changeStatus(int $id, string $status, ?string $note = null): bool
    {
        $admin = AdminAccess::requireAdmin($this->database);
        return $this->bookings->changeStatus($id, $status, $admin['id'], $note);
    }

    public function updateDetails(int $id, array $input): void
    {
        $admin = AdminAccess::requireAdmin($this->database);
        $this->bookings->updateDetails($id, $input, $admin['id']);
    }

    public function addNote(int $id, string $note): int
    {
        $admin = AdminAccess::requireAdmin($this->database);
        return $this->bookings->addNote($id, $note, $admin['id']);
    }

    public function delete(int $id): void
    {
        $admin = AdminAccess::requireAdmin($this->database);
        $this->bookings->delete($id, $admin['id']);
    }

    public function exportCsv(array $filters = []): string
    {
        AdminAccess::requireAdmin($this->database);
        $stream = fopen('php://temp', 'w+');
        if ($stream === false) {
            throw new \RuntimeException('CSV export could not be initialized.');
        }
        try {
            CsvExporter::write($this->bookings->exportStatement($filters), [
                'booking_reference' => 'Booking reference',
                'customer_name' => 'Customer',
                'customer_phone' => 'Phone',
                'customer_email' => 'Email',
                'service_type' => 'Service',
                'status' => 'Status',
                'departure_location' => 'Departure',
                'destination' => 'Destination',
                'journey_date' => 'Journey date',
                'return_date' => 'Return date',
                'passenger_count' => 'Passengers',
                'created_at' => 'Submitted at',
            ], $stream);
            rewind($stream);
            return (string) stream_get_contents($stream);
        } finally {
            fclose($stream);
        }
    }
}
