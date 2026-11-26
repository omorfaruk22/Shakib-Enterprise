<?php
declare(strict_types=1);

namespace App\Repositories;

use InvalidArgumentException;
use PDO;
use RuntimeException;

final class CustomerRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    public function list(string $search = '', int $page = 1, int $perPage = 25): array
    {
        $search = trim($search);
        if ($this->length($search) > 120) {
            throw new InvalidArgumentException('Search must be 120 characters or fewer.');
        }
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $where = '';
        $parameters = [];
        if ($search !== '') {
            $where = ' WHERE c.full_name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?';
            $parameters = array_fill(0, 3, '%' . $search . '%');
        }
        $count = $this->database->prepare('SELECT COUNT(*) FROM customers AS c' . $where);
        $count->execute($parameters);
        $total = (int) $count->fetchColumn();
        $statement = $this->database->prepare(
            'SELECT c.id, c.full_name, c.phone, c.email, c.created_at, c.updated_at, COUNT(b.id) AS booking_count
             FROM customers AS c LEFT JOIN bookings AS b ON b.customer_id = c.id' . $where . '
             GROUP BY c.id ORDER BY c.updated_at DESC, c.id DESC LIMIT ? OFFSET ?'
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

    public function find(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        $statement = $this->database->prepare('SELECT * FROM customers WHERE id = ? LIMIT 1');
        $statement->execute([$id]);
        $row = $statement->fetch();
        return is_array($row) ? $row : null;
    }

    public function bookings(int $customerId, int $limit = 50): array
    {
        $statement = $this->database->prepare(
            'SELECT id, booking_reference, service_type, status, destination, journey_date, created_at
             FROM bookings WHERE customer_id = ? ORDER BY created_at DESC, id DESC LIMIT ?'
        );
        $statement->bindValue(1, $customerId, PDO::PARAM_INT);
        $statement->bindValue(2, min(100, max(1, $limit)), PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function bookingCount(int $customerId): int
    {
        $statement = $this->database->prepare('SELECT COUNT(*) FROM bookings WHERE customer_id = ?');
        $statement->execute([$customerId]);
        return (int) $statement->fetchColumn();
    }

    public function update(int $id, array $input, int $adminId): void
    {
        $current = $this->find($id);
        if ($current === null) {
            throw new RuntimeException('The requested customer does not exist.');
        }
        $name = trim($this->text($input, 'full_name'));
        $phone = trim($this->text($input, 'phone'));
        $email = strtolower(trim($this->text($input, 'email')));
        if ($this->length($name) < 2 || $this->length($name) > 160) {
            throw new InvalidArgumentException('Customer name must contain 2 to 160 characters.');
        }
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($this->length($phone) > 40 || preg_match('/^\+?[0-9() .-]+$/D', $phone) !== 1 || strlen($digits) < 7 || strlen($digits) > 15) {
            throw new InvalidArgumentException('Enter a valid customer phone number with 7 to 15 digits.');
        }
        if ($email !== '' && (strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false)) {
            throw new InvalidArgumentException('Enter a valid customer email address or leave it blank.');
        }
        $normalizedPhone = substr($digits, 0, 32);
        $duplicate = $this->database->prepare('SELECT id FROM customers WHERE phone_normalized = ? AND id <> ? LIMIT 1');
        $duplicate->execute([$normalizedPhone, $id]);
        if ($duplicate->fetchColumn() !== false) {
            throw new InvalidArgumentException('Another customer already uses this normalized phone number.');
        }

        $this->database->beginTransaction();
        try {
            $statement = $this->database->prepare(
                'UPDATE customers SET full_name = ?, phone = ?, phone_normalized = ?, email = ? WHERE id = ?'
            );
            $statement->execute([$name, $phone, $normalizedPhone, $email === '' ? null : $email, $id]);
            (new AdminActivityRepository())->record($this->database, $adminId, 'customer.updated', 'customer', $id);
            $this->database->commit();
        } catch (\Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }

    public function delete(int $id, int $adminId): void
    {
        $this->database->beginTransaction();
        try {
            $statement = $this->database->prepare(
                'SELECT c.id, (SELECT COUNT(*) FROM bookings WHERE customer_id = c.id) AS booking_count
                 FROM customers AS c WHERE c.id = ? FOR UPDATE'
            );
            $statement->execute([$id]);
            $customer = $statement->fetch();
            if (!is_array($customer)) {
                throw new RuntimeException('The requested customer does not exist.');
            }
            if ((int) $customer['booking_count'] > 0) {
                throw new RuntimeException('A customer with booking history cannot be deleted.');
            }
            (new AdminActivityRepository())->record($this->database, $adminId, 'customer.deleted', 'customer', $id);
            $delete = $this->database->prepare('DELETE FROM customers WHERE id = ?');
            $delete->execute([$id]);
            $this->database->commit();
        } catch (\Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }

    private function text(array $input, string $key): string
    {
        $value = $input[$key] ?? '';
        if (!is_string($value)) {
            throw new InvalidArgumentException('Customer details must be plain text.');
        }
        return $value;
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
