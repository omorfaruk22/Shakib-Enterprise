<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Repositories\ContactMessageRepository;
use InvalidArgumentException;
use PDO;

final class ContactMessageService
{
    private readonly ContactMessageRepository $messages;

    public function __construct(?PDO $database = null)
    {
        $database ??= Connection::fromConfig(require BASE_PATH . '/config/database.php');
        $this->messages = new ContactMessageRepository($database);
    }

    /** @return array{data: array<string, string|null>, errors: array<string, string>} */
    public function validate(array $input): array
    {
        $errors = [];
        $name = $this->text($input, 'name');
        $phone = $this->text($input, 'phone');
        $email = strtolower($this->text($input, 'email'));
        $subject = $this->text($input, 'subject');
        $message = $this->text($input, 'message');

        if ($this->length($name) < 2 || $this->length($name) > 160) {
            $errors['name'] = 'Enter your name using 2 to 160 characters.';
        }
        if ($this->length($phone) > 40 || ($phone !== '' && (
            preg_match('/^\+?[0-9() .-]+$/D', $phone) !== 1
            || strlen(preg_replace('/\D+/', '', $phone) ?? '') < 7
            || strlen(preg_replace('/\D+/', '', $phone) ?? '') > 15
        ))) {
            $errors['phone'] = 'Enter a valid phone number with 7 to 15 digits.';
        }
        if ($email !== '' && (strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false)) {
            $errors['email'] = 'Enter a valid email address or leave this field blank.';
        }
        if ($phone === '' && $email === '') {
            $errors['phone'] = 'Provide a phone number or an email address so the team can reply.';
            $errors['email'] = $errors['phone'];
        }
        if ($this->length($subject) > 200) {
            $errors['subject'] = 'Subject must be 200 characters or fewer.';
        }
        if ($this->length($message) < 10 || $this->length($message) > 5000) {
            $errors['message'] = 'Message must contain 10 to 5000 characters.';
        }

        return [
            'data' => [
                'name' => $name,
                'phone' => $phone === '' ? null : $phone,
                'email' => $email === '' ? null : $email,
                'subject' => $subject === '' ? null : $subject,
                'message' => $message,
            ],
            'errors' => $errors,
        ];
    }

    public function store(array $validatedData): int
    {
        $validation = $this->validate($validatedData);
        if ($validation['errors'] !== []) {
            throw new InvalidArgumentException('Contact message details are incomplete or invalid.');
        }
        return $this->messages->create($validation['data']);
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
