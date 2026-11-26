<?php
declare(strict_types=1);

namespace App\Repositories;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use PDO;
use RuntimeException;

final class ContactMessageRepository
{
    public const STATUSES = ['unread', 'read', 'replied'];

    public function __construct(private readonly PDO $database)
    {
    }

    public function create(array $message): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO contact_messages (name, phone, email, subject, message) VALUES (?, ?, ?, ?, ?)'
        );
        $statement->execute([
            $message['name'],
            $message['phone'],
            $message['email'],
            $message['subject'],
            $message['message'],
        ]);
        return (int) $this->database->lastInsertId();
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        [$where, $parameters] = $this->where($filters);
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $count = $this->database->prepare('SELECT COUNT(*) FROM contact_messages' . $where);
        $count->execute($parameters);
        $total = (int) $count->fetchColumn();
        $statement = $this->database->prepare(
            'SELECT id, name, phone, email, subject, message, status, admin_note, created_at, updated_at
             FROM contact_messages' . $where . ' ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?'
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
            'SELECT id, name, phone, email, subject, message, status, admin_note, created_at, updated_at
             FROM contact_messages WHERE id = ? LIMIT 1'
        );
        $statement->execute([$id]);
        $row = $statement->fetch();
        return is_array($row) ? $row : null;
    }

    public function updateStatus(int $id, string $status, ?string $adminNote, int $adminId): void
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Choose a valid contact message status.');
        }
        $note = trim((string) ($adminNote ?? ''));
        if ($this->length($note) > 10000) {
            throw new InvalidArgumentException('Admin note must be 10000 characters or fewer.');
        }

        $this->database->beginTransaction();
        try {
            $check = $this->database->prepare('SELECT id FROM contact_messages WHERE id = ? FOR UPDATE');
            $check->execute([$id]);
            if ($check->fetchColumn() === false) {
                throw new RuntimeException('The requested contact message does not exist.');
            }
            $statement = $this->database->prepare('UPDATE contact_messages SET status = ?, admin_note = ? WHERE id = ?');
            $statement->execute([$status, $note === '' ? null : $note, $id]);
            (new AdminActivityRepository())->record($this->database, $adminId, 'contact.status_changed', 'contact_message', $id);
            $this->database->commit();
        } catch (\Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }

    public function delete(int $id): void
    {
        $check = $this->database->prepare('SELECT id FROM contact_messages WHERE id = ? LIMIT 1');
        $check->execute([$id]);
        if ($check->fetchColumn() === false) {
            throw new RuntimeException('The requested contact message does not exist.');
        }
        $delete = $this->database->prepare('DELETE FROM contact_messages WHERE id = ?');
        $delete->execute([$id]);
    }

    public function exportStatement(array $filters = []): \PDOStatement
    {
        [$where, $parameters] = $this->where($filters);
        $statement = $this->database->prepare(
            'SELECT name, phone, email, subject, message, status, created_at FROM contact_messages' . $where .
            ' ORDER BY created_at DESC, id DESC LIMIT 10000'
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
            $clauses[] = '(name LIKE ? OR phone LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)';
            array_push($parameters, ...array_fill(0, 5, '%' . $search . '%'));
        }
        $status = $filters['status'] ?? '';
        if ($status !== '' && $status !== null) {
            if (!is_string($status) || !in_array($status, self::STATUSES, true)) {
                throw new InvalidArgumentException('Choose a valid contact message status filter.');
            }
            $clauses[] = 'status = ?';
            $parameters[] = $status;
        }

        $fromDate = $this->dateFilter($filters['date_from'] ?? '', 'Start date');
        $toDate = $this->dateFilter($filters['date_to'] ?? '', 'End date');
        if ($fromDate !== null && $toDate !== null && $fromDate > $toDate) {
            throw new InvalidArgumentException('Start date must be on or before end date.');
        }
        if ($fromDate !== null) {
            $clauses[] = 'created_at >= ?';
            $parameters[] = $this->utcBoundary($fromDate, false);
        }
        if ($toDate !== null) {
            $clauses[] = 'created_at < ?';
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
