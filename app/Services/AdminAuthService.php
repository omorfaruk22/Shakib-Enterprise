<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Repositories\AdminActivityRepository;
use App\Security\AdminAccess;
use PDO;
use RuntimeException;
use Throwable;

final class AdminAuthService
{
    private const RATE_WINDOW_MINUTES = 15;
    private const MAX_FAILED_ATTEMPTS = 5;
    private const SESSION_IDLE_LIMIT = 1800;
    private const SETUP_LOCK_NAME = 'sakib_enterprise_first_admin_setup';

    private readonly PDO $database;

    public function __construct(?PDO $database = null)
    {
        $this->database = $database ?? Connection::fromConfig(require BASE_PATH . '/config/database.php');
    }

    public function setupAvailable(): bool
    {
        if (!$this->setupConfigured()) {
            return false;
        }
        return (int) $this->database->query('SELECT COUNT(*) FROM admins')->fetchColumn() === 0;
    }

    public function setupConfigured(): bool
    {
        return strlen((string) env_value('ADMIN_SETUP_TOKEN', '')) >= 32;
    }

    /** @return array{ok:bool, errors:array<string,string>} */
    public function createFirstAdmin(array $input, string $ipAddress): array
    {
        $errors = [];
        $email = strtolower(trim($this->text($input, 'email')));
        $name = trim($this->text($input, 'name'));
        $password = is_string($input['password'] ?? null) ? $input['password'] : '';
        $confirmation = is_string($input['password_confirmation'] ?? null) ? $input['password_confirmation'] : '';
        $submittedToken = is_string($input['setup_token'] ?? null) ? $input['setup_token'] : '';
        $setupToken = (string) env_value('ADMIN_SETUP_TOKEN', '');
        $identifier = '__first_admin_setup__';

        if (strlen($setupToken) < 32) {
            return ['ok' => false, 'errors' => ['_form' => 'First-admin setup is disabled. Set a strong ADMIN_SETUP_TOKEN in the protected .env file.']];
        }
        if ($this->rateLimited($identifier, $ipAddress)) {
            return ['ok' => false, 'errors' => ['_form' => 'Too many setup attempts. Wait 15 minutes and try again.']];
        }
        if ($submittedToken === '' || !hash_equals($setupToken, $submittedToken)) {
            $this->recordAttempt($identifier, $ipAddress, false);
            return ['ok' => false, 'errors' => ['_form' => 'The setup key is invalid or the initial setup is closed.']];
        }

        if ($name === '' || $this->length($name) < 2 || $this->length($name) > 160) {
            $errors['name'] = 'Enter a name using 2 to 160 characters.';
        }
        if ($this->length($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Enter a valid email address.';
        }
        if (strlen($password) < 12 || strlen($password) > 72) {
            $errors['password'] = 'Use a password between 12 and 72 bytes.';
        }
        if ($password !== $confirmation) {
            $errors['password_confirmation'] = 'The password confirmation does not match.';
        }
        if ($errors !== []) {
            $this->recordAttempt($identifier, $ipAddress, false);
            return ['ok' => false, 'errors' => $errors];
        }

        $lockAcquired = (int) $this->database->query(
            'SELECT GET_LOCK(' . $this->database->quote(self::SETUP_LOCK_NAME) . ', 5)'
        )->fetchColumn() === 1;
        if (!$lockAcquired) {
            $this->recordAttempt($identifier, $ipAddress, false);
            return ['ok' => false, 'errors' => ['_form' => 'First-admin setup is busy. Wait a moment and try again.']];
        }

        try {
            if ((int) $this->database->query('SELECT COUNT(*) FROM admins')->fetchColumn() !== 0) {
                $this->recordAttempt($identifier, $ipAddress, false);
                return ['ok' => false, 'errors' => ['_form' => 'An administrator already exists. Initial setup is closed.']];
            }

            $this->database->beginTransaction();
            try {
                $statement = $this->database->prepare(
                    'INSERT INTO admins (name, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)'
                );
                $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 'admin']);
                $adminId = (int) $this->database->lastInsertId();
                (new AdminActivityRepository())->record($this->database, $adminId, 'admin.setup_completed', 'admin', $adminId);
                $this->recordAttempt($identifier, $ipAddress, true);
                $this->database->commit();
                return ['ok' => true, 'errors' => []];
            } catch (Throwable $exception) {
                if ($this->database->inTransaction()) {
                    $this->database->rollBack();
                }
                throw $exception;
            }
        } finally {
            $release = $this->database->prepare('SELECT RELEASE_LOCK(?)');
            $release->execute([self::SETUP_LOCK_NAME]);
        }
    }

    /** @return array{ok:bool, rate_limited:bool} */
    public function login(string $emailInput, string $password, string $ipAddress): array
    {
        $email = strtolower(trim($emailInput));
        $identifier = substr($email, 0, 190);
        if ($identifier === '') {
            $identifier = '__empty_login__';
        }

        $dummyHash = password_hash(random_bytes(24), PASSWORD_DEFAULT);
        if ($this->rateLimited($identifier, $ipAddress)) {
            password_verify($password, $dummyHash);
            return ['ok' => false, 'rate_limited' => true];
        }

        $admin = null;
        if (strlen($email) <= 190 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            $statement = $this->database->prepare(
                'SELECT id, password_hash FROM admins WHERE email = ? AND is_active = 1 LIMIT 1'
            );
            $statement->execute([$email]);
            $row = $statement->fetch();
            $admin = is_array($row) ? $row : null;
        }

        $hash = is_array($admin) && is_string($admin['password_hash'] ?? null)
            ? $admin['password_hash']
            : $dummyHash;
        $passwordMatches = password_verify($password, $hash);
        if ($admin === null || !$passwordMatches) {
            $this->recordAttempt($identifier, $ipAddress, false);
            return ['ok' => false, 'rate_limited' => false];
        }

        start_secure_session();
        session_regenerate_id(true);
        $_SESSION = [];
        $now = time();
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_authenticated_at'] = $now;
        $_SESSION['admin_last_activity'] = $now;
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));

        $update = $this->database->prepare('UPDATE admins SET last_login_at = UTC_TIMESTAMP() WHERE id = ?');
        $update->execute([(int) $admin['id']]);
        $this->recordAttempt($identifier, $ipAddress, true);
        (new AdminActivityRepository())->record($this->database, (int) $admin['id'], 'auth.login', 'admin', (int) $admin['id']);

        return ['ok' => true, 'rate_limited' => false];
    }

    public function currentAdmin(): ?array
    {
        try {
            return AdminAccess::requireAdmin($this->database);
        } catch (RuntimeException) {
            return null;
        }
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            start_secure_session();
        }
        $adminId = $_SESSION['admin_id'] ?? null;
        if ((is_int($adminId) || (is_string($adminId) && ctype_digit($adminId))) && (int) $adminId > 0) {
            (new AdminActivityRepository())->record($this->database, (int) $adminId, 'auth.logout', 'admin', (int) $adminId);
        }
        $_SESSION = [];
        $parameters = session_get_cookie_params();
        if (ini_get('session.use_cookies')) {
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $parameters['path'],
                'domain' => $parameters['domain'],
                'secure' => $parameters['secure'],
                'httponly' => $parameters['httponly'],
                'samesite' => $parameters['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }

    private function rateLimited(string $identifier, string $ipAddress): bool
    {
        $ipHash = $this->hashIp($ipAddress);
        $window = self::RATE_WINDOW_MINUTES;
        $byIdentifier = $this->database->prepare(
            'SELECT COUNT(*) FROM admin_login_attempts
             WHERE login_identifier = ? AND was_successful = 0
               AND attempted_at >= UTC_TIMESTAMP() - INTERVAL ' . $window . ' MINUTE'
        );
        $byIdentifier->execute([$identifier]);
        if ((int) $byIdentifier->fetchColumn() >= self::MAX_FAILED_ATTEMPTS) {
            return true;
        }
        $byIp = $this->database->prepare(
            'SELECT COUNT(*) FROM admin_login_attempts
             WHERE ip_hash = ? AND was_successful = 0
               AND attempted_at >= UTC_TIMESTAMP() - INTERVAL ' . $window . ' MINUTE'
        );
        $byIp->execute([$ipHash]);
        return (int) $byIp->fetchColumn() >= self::MAX_FAILED_ATTEMPTS;
    }

    private function recordAttempt(string $identifier, string $ipAddress, bool $successful): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO admin_login_attempts (login_identifier, ip_hash, was_successful) VALUES (?, ?, ?)'
        );
        $statement->execute([
            substr($identifier, 0, 190),
            $this->hashIp($ipAddress),
            $successful ? 1 : 0,
        ]);
    }

    private function hashIp(string $ipAddress): string
    {
        return hash('sha256', $ipAddress !== '' ? $ipAddress : 'unknown');
    }

    private function text(array $input, string $key): string
    {
        $value = $input[$key] ?? '';
        return is_string($value) ? trim($value) : '';
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
