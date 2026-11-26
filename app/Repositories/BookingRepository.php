<?php
declare(strict_types=1);

namespace App\Repositories;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use PDO;
use RuntimeException;

final class BookingRepository
{
    public const STATUSES = ['pending', 'contacted', 'processing', 'confirmed', 'completed', 'cancelled', 'rejected'];
    public const SERVICE_TYPES = ['air_ticket', 'ship_ticket', 'bus_ticket', 'tour_package', 'holiday_package'];

    public function __construct(private readonly PDO $database)
    {
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        [$where, $parameters] = $this->where($filters);
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $count = $this->database->prepare(
            'SELECT COUNT(*) FROM bookings AS b LEFT JOIN customers AS c ON c.id = b.customer_id' . $where
        );
        $count->execute($parameters);
        $total = (int) $count->fetchColumn();

        $statement = $this->database->prepare(
            'SELECT b.id, b.booking_reference, b.service_type, b.status, b.departure_location, b.destination,
                    b.journey_date, b.return_date, b.passenger_count, b.created_at, b.updated_at,
                    c.full_name AS customer_name, c.phone AS customer_phone, c.email AS customer_email,
                    s.name AS service_name, p.title AS package_title, r.origin AS ship_origin, r.destination AS ship_destination
             FROM bookings AS b
             LEFT JOIN customers AS c ON c.id = b.customer_id
             LEFT JOIN services AS s ON s.id = b.service_id
             LEFT JOIN packages AS p ON p.id = b.package_id
             LEFT JOIN ship_routes AS r ON r.id = b.ship_route_id' . $where . '
             ORDER BY b.created_at DESC, b.id DESC LIMIT ? OFFSET ?'
        );
        foreach ($parameters as $index => $parameter) {
            $statement->bindValue($index + 1, $parameter, \PDO::PARAM_STR);
        }
        $statement->bindValue(count($parameters) + 1, $perPage, \PDO::PARAM_INT);
        $statement->bindValue(count($parameters) + 2, ($page - 1) * $perPage, \PDO::PARAM_INT);
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
        $statement = $this->database->prepare(
            'SELECT b.*, c.full_name AS customer_name, c.phone AS customer_phone, c.email AS customer_email,
                    s.name AS service_name, p.title AS package_title, r.origin AS ship_origin, r.destination AS ship_destination
             FROM bookings AS b
             LEFT JOIN customers AS c ON c.id = b.customer_id
             LEFT JOIN services AS s ON s.id = b.service_id
             LEFT JOIN packages AS p ON p.id = b.package_id
             LEFT JOIN ship_routes AS r ON r.id = b.ship_route_id
             WHERE b.id = ? LIMIT 1'
        );
        $statement->execute([$id]);
        $row = $statement->fetch();
        return is_array($row) ? $row : null;
    }

    public function history(int $bookingId): array
    {
        $statement = $this->database->prepare(
            'SELECT h.id, h.old_status, h.new_status, h.note, h.created_at, a.name AS admin_name
             FROM booking_status_history AS h
             LEFT JOIN admins AS a ON a.id = h.admin_id
             WHERE h.booking_id = ? ORDER BY h.created_at DESC, h.id DESC'
        );
        $statement->execute([$bookingId]);
        return $statement->fetchAll();
    }

    public function notes(int $bookingId): array
    {
        $statement = $this->database->prepare(
            'SELECT n.id, n.note, n.created_at, a.name AS admin_name
             FROM booking_notes AS n
             INNER JOIN admins AS a ON a.id = n.admin_id
             WHERE n.booking_id = ? ORDER BY n.created_at DESC, n.id DESC'
        );
        $statement->execute([$bookingId]);
        return $statement->fetchAll();
    }

    public function updateDetails(int $bookingId, array $input, int $adminId): void
    {
        $allowed = ['departure_location', 'destination', 'journey_date', 'return_date', 'passenger_count', 'passenger_type', 'travel_class', 'customer_message'];
        $fields = [];
        foreach ($allowed as $field) {
            if (!array_key_exists($field, $input)) {
                continue;
            }
            $value = $input[$field];
            if ($field === 'passenger_count') {
                if ($value === '' || $value === null) {
                    $fields[$field] = null;
                    continue;
                }
                $count = filter_var($value, FILTER_VALIDATE_INT);
                if ($count === false || $count < 1 || $count > 50) {
                    throw new \InvalidArgumentException('Traveler count must be between 1 and 50.');
                }
                $fields[$field] = $count;
                continue;
            }
            if ($value !== null && !is_string($value)) {
                throw new \InvalidArgumentException('Booking details must be plain text.');
            }
            $text = trim((string) ($value ?? ''));
            $maximum = match ($field) {
                'departure_location', 'destination' => 180,
                'journey_date', 'return_date' => 10,
                'passenger_type', 'travel_class' => 80,
                'customer_message' => 4000,
            };
            if ($this->length($text) > $maximum) {
                throw new \InvalidArgumentException('A booking detail exceeds its maximum allowed length.');
            }
            if (in_array($field, ['journey_date', 'return_date'], true) && $text !== '') {
                $date = DateTimeImmutable::createFromFormat('!Y-m-d', $text);
                $errors = DateTimeImmutable::getLastErrors();
                if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d') !== $text) {
                    throw new \InvalidArgumentException('Booking dates must use YYYY-MM-DD format.');
                }
                $fields[$field] = $text;
            } else {
                $fields[$field] = $text === '' ? null : $text;
            }
        }
        if ($fields === []) {
            throw new \InvalidArgumentException('No booking details were provided for updating.');
        }

        $this->database->beginTransaction();
        try {
            $check = $this->database->prepare('SELECT id FROM bookings WHERE id = ? FOR UPDATE');
            $check->execute([$bookingId]);
            if ($check->fetchColumn() === false) {
                throw new RuntimeException('The requested booking does not exist.');
            }
            $statement = $this->database->prepare(
                'UPDATE bookings SET ' . implode(', ', array_map(static fn (string $field): string => $field . ' = ?', array_keys($fields))) . ' WHERE id = ?'
            );
            $statement->execute([...array_values($fields), $bookingId]);
            (new AdminActivityRepository())->record($this->database, $adminId, 'booking.details_updated', 'booking', $bookingId);
            $this->database->commit();
        } catch (\Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }

    public function addNote(int $bookingId, string $note, int $adminId): int
    {
        $note = trim($note);
        if ($note === '' || $this->length($note) > 10000) {
            throw new \InvalidArgumentException('Internal notes must contain 1 to 10000 characters.');
        }
        $this->database->beginTransaction();
        try {
            $check = $this->database->prepare('SELECT id FROM bookings WHERE id = ? FOR UPDATE');
            $check->execute([$bookingId]);
            if ($check->fetchColumn() === false) {
                throw new RuntimeException('The requested booking does not exist.');
            }
            $statement = $this->database->prepare('INSERT INTO booking_notes (booking_id, admin_id, note) VALUES (?, ?, ?)');
            $statement->execute([$bookingId, $adminId, $note]);
            $noteId = (int) $this->database->lastInsertId();
            (new AdminActivityRepository())->record($this->database, $adminId, 'booking.note_added', 'booking', $bookingId);
            $this->database->commit();
            return $noteId;
        } catch (\Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }

    public function delete(int $bookingId, int $adminId): void
    {
        $this->database->beginTransaction();
        try {
            $check = $this->database->prepare('SELECT id FROM bookings WHERE id = ? FOR UPDATE');
            $check->execute([$bookingId]);
            if ($check->fetchColumn() === false) {
                throw new RuntimeException('The requested booking does not exist.');
            }
            (new AdminActivityRepository())->record($this->database, $adminId, 'booking.deleted', 'booking', $bookingId);
            $statement = $this->database->prepare('DELETE FROM bookings WHERE id = ?');
            $statement->execute([$bookingId]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }

    public function changeStatus(int $bookingId, string $newStatus, int $adminId, ?string $note = null): bool
    {
        if (!in_array($newStatus, self::STATUSES, true)) {
            throw new InvalidArgumentException('Choose a valid booking status.');
        }
        $note = $note === null ? null : trim($note);
        if ($note !== null && $this->length($note) > 500) {
            throw new InvalidArgumentException('Status note must be 500 characters or fewer.');
        }

        $this->database->beginTransaction();
        try {
            $current = $this->database->prepare('SELECT status FROM bookings WHERE id = ? FOR UPDATE');
            $current->execute([$bookingId]);
            $oldStatus = $current->fetchColumn();
            if (!is_string($oldStatus)) {
                throw new RuntimeException('The requested booking does not exist.');
            }
            if ($oldStatus === $newStatus) {
                $this->database->commit();
                return false;
            }

            $update = $this->database->prepare('UPDATE bookings SET status = ? WHERE id = ?');
            $update->execute([$newStatus, $bookingId]);
            $history = $this->database->prepare(
                'INSERT INTO booking_status_history (booking_id, admin_id, old_status, new_status, note) VALUES (?, ?, ?, ?, ?)'
            );
            $history->execute([$bookingId, $adminId, $oldStatus, $newStatus, $note === '' ? null : $note]);
            (new AdminActivityRepository())->record(
                $this->database,
                $adminId,
                'booking.status_changed',
                'booking',
                $bookingId
            );
            $this->database->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }

    public function exportStatement(array $filters = []): \PDOStatement
    {
        [$where, $parameters] = $this->where($filters);
        $statement = $this->database->prepare(
            'SELECT b.booking_reference, c.full_name AS customer_name, c.phone AS customer_phone, c.email AS customer_email,
                    b.service_type, b.status, b.departure_location, b.destination, b.journey_date, b.return_date,
                    b.passenger_count, b.created_at
             FROM bookings AS b
             LEFT JOIN customers AS c ON c.id = b.customer_id' . $where . '
             ORDER BY b.created_at DESC, b.id DESC LIMIT 10000'
        );
        $statement->execute($parameters);
        return $statement;
    }

    private function where(array $filters): array
    {
        $clauses = [];
        $parameters = [];
        $search = trim(is_string($filters['q'] ?? null) ? $filters['q'] : '');
        if ($search !== '') {
            if ($this->length($search) > 120) {
                throw new InvalidArgumentException('Search must be 120 characters or fewer.');
            }
            $clauses[] = '(b.booking_reference LIKE ? OR c.full_name LIKE ? OR c.phone LIKE ? OR c.email LIKE ? OR b.destination LIKE ?)';
            array_push($parameters, ...array_fill(0, 5, '%' . $search . '%'));
        }
        $status = $filters['status'] ?? '';
        if ($status !== '' && $status !== null) {
            if (!is_string($status) || !in_array($status, self::STATUSES, true)) {
                throw new InvalidArgumentException('Choose a valid booking status filter.');
            }
            $clauses[] = 'b.status = ?';
            $parameters[] = $status;
        }
        $serviceType = $filters['service_type'] ?? '';
        if ($serviceType !== '' && $serviceType !== null) {
            if (!is_string($serviceType) || !in_array($serviceType, self::SERVICE_TYPES, true)) {
                throw new InvalidArgumentException('Choose a valid booking service filter.');
            }
            $clauses[] = 'b.service_type = ?';
            $parameters[] = $serviceType;
        }

        $fromDate = $this->dateFilter($filters['date_from'] ?? '', 'Start date');
        $toDate = $this->dateFilter($filters['date_to'] ?? '', 'End date');
        if ($fromDate !== null && $toDate !== null && $fromDate > $toDate) {
            throw new InvalidArgumentException('Start date must be on or before end date.');
        }
        if ($fromDate !== null) {
            $clauses[] = 'b.created_at >= ?';
            $parameters[] = $this->utcBoundary($fromDate, false);
        }
        if ($toDate !== null) {
            $clauses[] = 'b.created_at < ?';
            $parameters[] = $this->utcBoundary($toDate, true);
        }

        return [$clauses === [] ? '' : ' WHERE ' . implode(' AND ', $clauses), $parameters];
    }

    private function dateFilter(mixed $value, string $label): ?string
    {
        if ($value === '' || $value === null) {
            return null;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException($label . ' is invalid.');
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException($label . ' must use YYYY-MM-DD format.');
        }
        return $value;
    }

    private function utcBoundary(string $date, bool $nextDay): string
    {
        $boundary = DateTimeImmutable::createFromFormat('!Y-m-d', $date, new DateTimeZone(date_default_timezone_get()));
        if ($boundary === false) {
            throw new InvalidArgumentException('Date filter could not be converted.');
        }
        if ($nextDay) {
            $boundary = $boundary->modify('+1 day');
        }
        return $boundary->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    private function length(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }
        $length = preg_match_all('/./us', $value, $matches);
        return $length === false ? strlen($value) : $length;
    }
}
