<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use PDO;
use PDOException;

final class CustomerAuthService
{
    private const SESSION_IDLE_SECONDS = 1800;
    private const SESSION_MAX_SECONDS = 43200;
    private const MAX_EMAIL_FAILURES = 5;
    private const MAX_IP_FAILURES = 12;

    private readonly PDO $database;

    public function __construct(?PDO $database = null)
    {
        $this->database = $database ?? Connection::fromConfig(require BASE_PATH . '/config/database.php');
    }

    /** @return array{ok:bool, errors:array<string,string>} */
    public function register(array $input, string $ipAddress): array
    {
        start_secure_session();
        $name = trim($this->text($input, 'full_name'));
        $phone = trim($this->text($input, 'phone'));
        $email = strtolower(trim($this->text($input, 'email')));
        $password = is_string($input['password'] ?? null) ? $input['password'] : '';
        $confirmation = is_string($input['password_confirmation'] ?? null) ? $input['password_confirmation'] : '';
        $bookingReference = strtoupper(trim($this->text($input, 'booking_reference')));
        $errors = [];

        if ($name === '' || $this->length($name) > 160) {
            $errors['full_name'] = 'Enter your name (up to 160 characters).';
        }
        if (strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Enter a valid email address.';
        }
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($phone === '' || preg_match('/^\+?[0-9() .-]+$/D', $phone) !== 1 || strlen($digits) < 7 || strlen($digits) > 15) {
            $errors['phone'] = 'Enter a phone number with 7 to 15 digits.';
        }
        if (strlen($password) < 12 || strlen($password) > 72) {
            $errors['password'] = 'Use a password between 12 and 72 characters.';
        }
        if ($password !== $confirmation) {
            $errors['password_confirmation'] = 'The passwords do not match.';
        }
        if ($bookingReference !== '' && preg_match('/^SE-[0-9]{6}-[A-F0-9]{16}$/D', $bookingReference) !== 1) {
            $errors['booking_reference'] = 'Enter a valid inquiry reference or leave this field blank.';
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $normalizedPhone = $this->normalizePhone($phone);
        $emailHash = hash('sha256', $email);
        $ipHash = hash('sha256', $ipAddress !== '' ? $ipAddress : 'unknown');
        $referenceProvided = $bookingReference !== '';
        if ($referenceProvided && $this->rateLimited($emailHash, $ipHash)) {
            return ['ok' => false, 'errors' => ['_form' => 'Too many attempts. Wait 15 minutes and try again.']];
        }

        $this->database->beginTransaction();
        try {
            $emailCheck = $this->database->prepare('SELECT customer_id FROM customer_accounts WHERE email = ? LIMIT 1');
            $emailCheck->execute([$email]);
            if ($emailCheck->fetchColumn() !== false) {
                $this->database->rollBack();
                return ['ok' => false, 'errors' => ['email' => 'An account already uses this email. Sign in instead.']];
            }

            $customerLookup = $this->database->prepare(
                'SELECT id, full_name, phone, email
                 FROM customers
                 WHERE phone_normalized = ?
                 ORDER BY id ASC
                 LIMIT 1
                 FOR UPDATE'
            );
            $customerLookup->execute([$normalizedPhone]);
            $existingCustomer = $customerLookup->fetch(PDO::FETCH_ASSOC);

            if (is_array($existingCustomer)) {
                $customerId = (int) $existingCustomer['id'];
                $accountLookup = $this->database->prepare('SELECT customer_id FROM customer_accounts WHERE customer_id = ? LIMIT 1');
                $accountLookup->execute([$customerId]);
                if ($accountLookup->fetchColumn() !== false) {
                    $this->database->rollBack();
                    return ['ok' => false, 'errors' => ['_form' => 'This customer already has an account. Sign in or use inquiry tracking.']];
                }
                $bookingLookup = $this->database->prepare(
                    'SELECT 1
                     FROM bookings
                     WHERE customer_id = ? AND booking_reference = ?
                     LIMIT 1'
                );
                $bookingLookup->execute([$customerId, $bookingReference]);
                if (!$referenceProvided || $bookingLookup->fetchColumn() === false) {
                    $this->database->rollBack();
                    if ($referenceProvided) {
                        $this->recordAttempt($emailHash, $ipHash, false);
                    }
                    return ['ok' => false, 'errors' => ['booking_reference' => 'To link an earlier inquiry, enter its reference and the same phone number.']];
                }
                $updateCustomer = $this->database->prepare(
                    'UPDATE customers SET full_name = ?, phone = ?, email = COALESCE(email, ?) WHERE id = ?'
                );
                $updateCustomer->execute([$name, $phone, $email, $customerId]);
            } else {
                if ($referenceProvided) {
                    $this->database->rollBack();
                    $this->recordAttempt($emailHash, $ipHash, false);
                    return ['ok' => false, 'errors' => ['booking_reference' => 'That inquiry could not be matched to this phone number.']];
                }
                $createCustomer = $this->database->prepare(
                    'INSERT INTO customers (full_name, phone, phone_normalized, email) VALUES (?, ?, ?, ?)'
                );
                $createCustomer->execute([$name, $phone, $normalizedPhone, $email]);
                $customerId = (int) $this->database->lastInsertId();
            }

            $createAccount = $this->database->prepare(
                'INSERT INTO customer_accounts (customer_id, email, password_hash) VALUES (?, ?, ?)'
            );
            $createAccount->execute([$customerId, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $this->database->commit();
            $this->recordAttempt($emailHash, $ipHash, true);
            $this->establishSession($customerId);
            return ['ok' => true, 'errors' => []];
        } catch (PDOException $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            if ($exception->getCode() === '23000') {
                return ['ok' => false, 'errors' => ['email' => 'An account already uses these details. Sign in or check your information.']];
            }
            throw $exception;
        }
    }

    /** @return array{ok:bool, rate_limited:bool} */
    public function login(mixed $emailInput, mixed $passwordInput, string $ipAddress): array
    {
        start_secure_session();
        $email = is_string($emailInput) ? strtolower(trim($emailInput)) : '';
        $password = is_string($passwordInput) ? $passwordInput : '';
        $emailHash = hash('sha256', $email);
        $ipHash = hash('sha256', $ipAddress !== '' ? $ipAddress : 'unknown');
        $this->database->exec('DELETE FROM customer_account_login_attempts WHERE attempted_at < UTC_TIMESTAMP() - INTERVAL 1 DAY');

        $dummyHash = password_hash(random_bytes(32), PASSWORD_DEFAULT);
        if ($this->rateLimited($emailHash, $ipHash)) {
            password_verify($password, $dummyHash);
            return ['ok' => false, 'rate_limited' => true];
        }

        $account = null;
        if (strlen($email) <= 190 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            $query = $this->database->prepare(
                'SELECT ca.customer_id, ca.password_hash
                 FROM customer_accounts AS ca
                 WHERE ca.email = ?
                 LIMIT 1'
            );
            $query->execute([$email]);
            $row = $query->fetch(PDO::FETCH_ASSOC);
            $account = is_array($row) ? $row : null;
        }
        $hash = is_array($account) && is_string($account['password_hash'] ?? null)
            ? $account['password_hash']
            : $dummyHash;
        $passwordMatches = strlen($password) <= 4096 && password_verify($password, $hash);
        if ($account === null || !$passwordMatches) {
            $this->recordAttempt($emailHash, $ipHash, false);
            return ['ok' => false, 'rate_limited' => false];
        }

        $this->recordAttempt($emailHash, $ipHash, true);
        $this->establishSession((int) $account['customer_id']);
        return ['ok' => true, 'rate_limited' => false];
    }

    public function currentCustomerId(): ?int
    {
        $customer = $this->currentCustomer();
        return is_array($customer) ? (int) $customer['customer_id'] : null;
    }

    public function currentCustomer(): ?array
    {
        start_secure_session();
        $auth = $_SESSION['customer_auth'] ?? null;
        if (!is_array($auth)) {
            return null;
        }
        $customerId = filter_var($auth['customer_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $authenticatedAt = filter_var($auth['authenticated_at'] ?? null, FILTER_VALIDATE_INT);
        $lastActivity = filter_var($auth['last_activity'] ?? null, FILTER_VALIDATE_INT);
        $now = time();
        if (
            $customerId === false || $authenticatedAt === false || $lastActivity === false
            || $authenticatedAt > $now || $lastActivity > $now
            || $now - $authenticatedAt > self::SESSION_MAX_SECONDS
            || $now - $lastActivity > self::SESSION_IDLE_SECONDS
        ) {
            unset($_SESSION['customer_auth']);
            return null;
        }

        $query = $this->database->prepare(
            'SELECT c.id AS customer_id, c.full_name, c.phone, c.phone_normalized, ca.email AS account_email
             FROM customer_accounts AS ca
             INNER JOIN customers AS c ON c.id = ca.customer_id
             WHERE ca.customer_id = ?
             LIMIT 1'
        );
        $query->execute([(int) $customerId]);
        $customer = $query->fetch(PDO::FETCH_ASSOC);
        if (!is_array($customer)) {
            unset($_SESSION['customer_auth']);
            return null;
        }
        $_SESSION['customer_auth']['last_activity'] = $now;
        return $customer;
    }

    public function logout(): void
    {
        start_secure_session();
        unset($_SESSION['customer_auth']);
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        session_regenerate_id(true);
    }

    public static function safeReturnPath(mixed $value): ?string
    {
        if (!is_string($value) || $value === '' || strlen($value) > 1024 || str_contains($value, '\\') || str_starts_with($value, '//')) {
            return null;
        }
        $parts = parse_url($value);
        if (!is_array($parts) || isset($parts['scheme']) || isset($parts['host']) || isset($parts['fragment'])) {
            return null;
        }
        $path = (string) ($parts['path'] ?? '');
        if ($path === '/customer/dashboard') {
            return $path;
        }
        if (preg_match('#^/booking/(air-ticket|ship-ticket|bus-ticket|tour-travel|holiday-package)$#D', $path) !== 1) {
            return null;
        }
        $query = [];
        if (isset($parts['query'])) {
            parse_str((string) $parts['query'], $parameters);
            foreach ($parameters as $key => $parameter) {
                if (!in_array($key, ['package', 'destination'], true) || !is_string($parameter) || preg_match('/^[a-z0-9-]{1,180}$/D', $parameter) !== 1) {
                    return null;
                }
                $query[$key] = $parameter;
            }
        }
        return $path . ($query === [] ? '' : '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986));
    }

    private function establishSession(int $customerId): void
    {
        start_secure_session();
        session_regenerate_id(true);
        $_SESSION = [];
        $now = time();
        $_SESSION['customer_auth'] = [
            'customer_id' => $customerId,
            'authenticated_at' => $now,
            'last_activity' => $now,
        ];
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    private function rateLimited(string $emailHash, string $ipHash): bool
    {
        $emailAttempts = $this->database->prepare(
            'SELECT COUNT(*) FROM customer_account_login_attempts
             WHERE identifier_hash = ? AND was_successful = 0
               AND attempted_at >= UTC_TIMESTAMP() - INTERVAL 15 MINUTE'
        );
        $emailAttempts->execute([$emailHash]);
        if ((int) $emailAttempts->fetchColumn() >= self::MAX_EMAIL_FAILURES) {
            return true;
        }
        $ipAttempts = $this->database->prepare(
            'SELECT COUNT(*) FROM customer_account_login_attempts
             WHERE ip_hash = ? AND was_successful = 0
               AND attempted_at >= UTC_TIMESTAMP() - INTERVAL 15 MINUTE'
        );
        $ipAttempts->execute([$ipHash]);
        return (int) $ipAttempts->fetchColumn() >= self::MAX_IP_FAILURES;
    }

    private function recordAttempt(string $emailHash, string $ipHash, bool $successful): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO customer_account_login_attempts (identifier_hash, ip_hash, was_successful)
             VALUES (?, ?, ?)'
        );
        $statement->execute([$emailHash, $ipHash, $successful ? 1 : 0]);
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

    private function text(array $input, string $key): string
    {
        return is_string($input[$key] ?? null) ? $input[$key] : '';
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
