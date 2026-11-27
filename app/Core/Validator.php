<?php
namespace App\Core;

class Validator
{
    public static function required(array $data, array $fields): array
    {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }
        return $errors;
    }

    public static function email(?string $email): bool
    {
        if ($email === null || trim($email) === '') {
            return true; // optional
        }
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function phone(string $phone): bool
    {
        $digits = preg_replace('/\D+/', '', $phone);
        return strlen($digits) >= 10 && strlen($digits) <= 15;
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '01') && strlen($digits) === 11) {
            return '880' . substr($digits, 1);
        }
        if (str_starts_with($digits, '8801') && strlen($digits) === 13) {
            return $digits;
        }
        return $digits;
    }

    public static function date(string $date): bool
    {
        if (trim($date) === '') return false;
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    public static function slug(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }
}
