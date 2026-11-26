<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use PDO;

final class CustomerPortalService
{
    private const REFERENCE_WINDOW_MINUTES = 15;
    private const MAX_REFERENCE_FAILURES = 5;
    private const MAX_IP_FAILURES = 12;
    private const SESSION_IDLE_SECONDS = 1800;
    private const SESSION_MAX_SECONDS = 43200;

    private readonly PDO $database;

    public function __construct(?PDO $database = null)
    {
        $this->database = $database ?? Connection::fromConfig(require BASE_PATH . '/config/database.php');
    }

    /** @return array{ok:bool, rate_limited:bool} */
    public function authenticate(mixed $referenceInput, mixed $phoneInput, string $ipAddress): array
    {
        start_secure_session();
        $reference = is_string($referenceInput) ? strtoupper(trim($referenceInput)) : '';
        $phone = is_string($phoneInput) ? trim($phoneInput) : '';
        $referenceHash = hash('sha256', $reference);
        $ipHash = hash('sha256', $ipAddress !== '' ? $ipAddress : 'unknown');

        $this->database->exec('DELETE FROM customer_portal_login_attempts WHERE attempted_at < UTC_TIMESTAMP() - INTERVAL 1 DAY');
        if ($this->isRateLimited($referenceHash, $ipHash)) {
            return ['ok' => false, 'rate_limited' => true];
        }

        $phoneDigits = preg_replace('/\D+/', '', $phone) ?? '';
        $phoneLooksValid = preg_match('/^\+?[0-9() .-]+$/D', $phone) === 1
            && strlen($phoneDigits) >= 7
            && strlen($phoneDigits) <= 15;
        $referenceLooksValid = preg_match('/^SE-[0-9]{6}-[A-F0-9]{16}$/D', $reference) === 1;

        $customerId = null;
        if ($referenceLooksValid && $phoneLooksValid) {
            $query = $this->database->prepare(
                'SELECT b.customer_id, c.phone, c.phone_normalized
                 FROM bookings AS b
                 INNER JOIN customers AS c ON c.id = b.customer_id
                 WHERE b.booking_reference = ?
                 LIMIT 1'
            );
            $query->execute([$reference]);
            $row = $query->fetch();
            if (is_array($row)) {
                $enteredPhone = $this->normalizePhone($phone);
                $storedPhone = $this->normalizePhone((string) $row['phone']);
                $storedNormalized = $this->normalizePhone((string) $row['phone_normalized']);
                if (
                    $enteredPhone !== ''
                    && (hash_equals($storedPhone, $enteredPhone) || hash_equals($storedNormalized, $enteredPhone))
                ) {
                    $customerId = (int) $row['customer_id'];
                }
            }
        }

        $this->recordAttempt($referenceHash, $ipHash, $customerId !== null);
        if ($customerId === null) {
            return ['ok' => false, 'rate_limited' => false];
        }

        session_regenerate_id(true);
        $_SESSION['customer_portal'] = [
            'customer_id' => $customerId,
            'authenticated_at' => time(),
            'last_activity' => time(),
        ];
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));

        return ['ok' => true, 'rate_limited' => false];
    }

    public function currentCustomerId(): ?int
    {
        start_secure_session();
        $portal = $_SESSION['customer_portal'] ?? null;
        if (!is_array($portal)) {
            return null;
        }

        $customerId = filter_var($portal['customer_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $authenticatedAt = filter_var($portal['authenticated_at'] ?? null, FILTER_VALIDATE_INT);
        $lastActivity = filter_var($portal['last_activity'] ?? null, FILTER_VALIDATE_INT);
        $now = time();
        if (
            $customerId === false
            || $authenticatedAt === false
            || $lastActivity === false
            || $authenticatedAt > $now
            || $lastActivity > $now
            || $now - $lastActivity > self::SESSION_IDLE_SECONDS
            || $now - $authenticatedAt > self::SESSION_MAX_SECONDS
        ) {
            $this->clearPortalSession();
            return null;
        }

        $exists = $this->database->prepare('SELECT 1 FROM customers WHERE id = ? LIMIT 1');
        $exists->execute([$customerId]);
        if ($exists->fetchColumn() === false) {
            $this->clearPortalSession();
            return null;
        }

        $_SESSION['customer_portal']['last_activity'] = $now;
        return (int) $customerId;
    }

    /** @return array{customer:array, bookings:list<array>, total:int} */
    public function dashboard(int $customerId): array
    {
        $customerQuery = $this->database->prepare('SELECT id, full_name FROM customers WHERE id = ? LIMIT 1');
        $customerQuery->execute([$customerId]);
        $customer = $customerQuery->fetch();
        if (!is_array($customer)) {
            return ['customer' => [], 'bookings' => [], 'total' => 0];
        }

        $countQuery = $this->database->prepare('SELECT COUNT(*) FROM bookings WHERE customer_id = ?');
        $countQuery->execute([$customerId]);
        $total = (int) $countQuery->fetchColumn();

        $bookingsQuery = $this->database->prepare(
            'SELECT booking_reference, service_type, status, destination, journey_date, created_at, updated_at
             FROM bookings
             WHERE customer_id = ?
             ORDER BY created_at DESC, id DESC
             LIMIT 50'
        );
        $bookingsQuery->execute([$customerId]);

        return ['customer' => $customer, 'bookings' => $bookingsQuery->fetchAll(), 'total' => $total];
    }

    public function logout(): void
    {
        start_secure_session();
        $this->clearPortalSession();
    }

    private function isRateLimited(string $referenceHash, string $ipHash): bool
    {
        $byReference = $this->database->prepare(
            'SELECT COUNT(*) FROM customer_portal_login_attempts
             WHERE reference_hash = ? AND was_successful = 0
               AND attempted_at >= UTC_TIMESTAMP() - INTERVAL 15 MINUTE'
        );
        $byReference->execute([$referenceHash]);
        if ((int) $byReference->fetchColumn() >= self::MAX_REFERENCE_FAILURES) {
            return true;
        }

        $byIp = $this->database->prepare(
            'SELECT COUNT(*) FROM customer_portal_login_attempts
             WHERE ip_hash = ? AND was_successful = 0
               AND attempted_at >= UTC_TIMESTAMP() - INTERVAL 15 MINUTE'
        );
        $byIp->execute([$ipHash]);
        return (int) $byIp->fetchColumn() >= self::MAX_IP_FAILURES;
    }

    private function recordAttempt(string $referenceHash, string $ipHash, bool $successful): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO customer_portal_login_attempts (reference_hash, ip_hash, was_successful)
             VALUES (?, ?, ?)'
        );
        $statement->execute([$referenceHash, $ipHash, $successful ? 1 : 0]);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return '880' . substr($digits, 1);
        }
        return $digits;
    }

    private function clearPortalSession(): void
    {
        unset($_SESSION['customer_portal']);
        session_regenerate_id(true);
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
}
