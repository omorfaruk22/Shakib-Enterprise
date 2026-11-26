<?php
declare(strict_types=1);

namespace App\Repositories;

use InvalidArgumentException;
use PDO;
use RuntimeException;

final class ContentRepository
{
    private const SETTINGS = [
        'site_name' => 160,
        'site_tagline' => 200,
        'office_address' => 1000,
        'phone' => 40,
        'whatsapp' => 40,
        'email' => 190,
        'office_hours' => 2000,
        'homepage_hero_eyebrow' => 200,
        'homepage_hero_title' => 200,
        'homepage_hero_highlight' => 200,
        'homepage_hero_description' => 2000,
        'homepage_meta_title' => 200,
        'homepage_meta_description' => 320,
    ];

    public function __construct(private readonly PDO $database)
    {
    }

    public function listPages(string $search = '', int $page = 1, int $perPage = 25): array
    {
        return $this->paginate(
            'website_pages',
            ['slug', 'title', 'body'],
            $search,
            $page,
            $perPage,
            'updated_at DESC, id DESC'
        );
    }

    public function findPage(int $id): ?array
    {
        return $this->findById('website_pages', $id);
    }

    public function settings(): array
    {
        $settings = array_fill_keys(array_keys(self::SETTINGS), '');
        $keys = array_keys(self::SETTINGS);
        $statement = $this->database->prepare(
            'SELECT setting_key, setting_value FROM website_settings WHERE setting_key IN (' .
            implode(', ', array_fill(0, count($keys), '?')) . ')'
        );
        $statement->execute($keys);
        foreach ($statement->fetchAll() as $row) {
            $key = (string) ($row['setting_key'] ?? '');
            if (array_key_exists($key, $settings)) {
                $settings[$key] = (string) ($row['setting_value'] ?? '');
            }
        }
        return $settings;
    }

    public function savePage(?int $id, array $input): int
    {
        $existing = $id === null ? [] : ($this->findPage($id) ?? throw new RuntimeException('The requested page does not exist.'));
        $data = array_replace($existing, $input);
        $slug = $this->plainText($data['slug'] ?? null, 'Page slug');
        $title = $this->plainText($data['title'] ?? null, 'Page title');
        $body = $this->nullableText($data['body'] ?? null, 100000, 'Page body');
        $metaTitle = $this->nullableText($data['meta_title'] ?? null, 200, 'SEO title');
        $metaDescription = $this->nullableText($data['meta_description'] ?? null, 320, 'SEO description');
        $published = $this->boolean($data['is_published'] ?? ($existing['is_published'] ?? 0), 'Published');

        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug) !== 1) {
            throw new InvalidArgumentException('Use lowercase letters, numbers and single hyphens in the page slug.');
        }
        if ($title === '' || $this->length($title) > 200) {
            throw new InvalidArgumentException('Page title is required and must be 200 characters or fewer.');
        }

        return $this->saveRow('website_pages', $id, [
            'slug' => $slug,
            'title' => $title,
            'body' => $body,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'is_published' => $published,
        ]);
    }

    public function deletePage(int $id): void
    {
        $this->deleteById('website_pages', $id);
    }

    public function saveSetting(string $key, mixed $value, int $adminId): void
    {
        if (!isset(self::SETTINGS[$key])) {
            throw new InvalidArgumentException('This website setting is not editable.');
        }
        if (!is_string($value) && !is_numeric($value)) {
            throw new InvalidArgumentException('Website setting value must be plain text.');
        }
        $text = trim((string) $value);
        if ($this->length($text) > self::SETTINGS[$key]) {
            throw new InvalidArgumentException('Website setting exceeds its maximum allowed length.');
        }
        if ($key === 'email' && $text !== '' && filter_var($text, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Enter a valid business email address.');
        }
        if (in_array($key, ['phone', 'whatsapp'], true) && $text !== '') {
            $digits = preg_replace('/\D+/', '', $text) ?? '';
            if (preg_match('/^\+?[0-9() .-]+$/D', $text) !== 1 || strlen($digits) < 7 || strlen($digits) > 15) {
                throw new InvalidArgumentException('Business phone and WhatsApp settings must contain 7 to 15 digits.');
            }
        }
        $statement = $this->database->prepare(
            'INSERT INTO website_settings (setting_key, setting_value, updated_by)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)'
        );
        $statement->execute([$key, $text, $adminId]);
    }

    public function listTestimonials(string $search = '', int $page = 1, int $perPage = 25): array
    {
        return $this->paginate(
            'testimonials',
            ['customer_display_name', 'quote'],
            $search,
            $page,
            $perPage,
            'sort_order ASC, created_at DESC'
        );
    }

    public function findTestimonial(int $id): ?array
    {
        return $this->findById('testimonials', $id);
    }

    public function saveTestimonial(?int $id, array $input): int
    {
        $existing = $id === null ? [] : ($this->findTestimonial($id) ?? throw new RuntimeException('The requested testimonial does not exist.'));
        $data = array_replace($existing, $input);
        $name = $this->plainText($data['customer_display_name'] ?? null, 'Customer display name');
        $quote = $this->plainText($data['quote'] ?? null, 'Testimonial');
        if ($name === '' || $this->length($name) > 160) {
            throw new InvalidArgumentException('Customer display name is required and must be 160 characters or fewer.');
        }
        if ($quote === '' || $this->length($quote) > 10000) {
            throw new InvalidArgumentException('Testimonial text is required and must be 10000 characters or fewer.');
        }

        $ratingValue = $data['rating'] ?? null;
        if ($ratingValue === '' || $ratingValue === null) {
            $rating = null;
        } else {
            if (!is_scalar($ratingValue)) {
                throw new InvalidArgumentException('Rating value is invalid.');
            }
            $rating = filter_var($ratingValue, FILTER_VALIDATE_INT);
            if ($rating === false || $rating < 1 || $rating > 5) {
                throw new InvalidArgumentException('Rating must be from 1 to 5, or left blank.');
            }
        }
        $approved = $this->boolean($data['is_approved'] ?? ($existing['is_approved'] ?? 0), 'Approved');
        $sortOrder = filter_var($data['sort_order'] ?? 0, FILTER_VALIDATE_INT);
        if ($sortOrder === false || $sortOrder < 0 || $sortOrder > 1000000) {
            throw new InvalidArgumentException('Sort order must be between 0 and 1000000.');
        }

        return $this->saveRow('testimonials', $id, [
            'customer_display_name' => $name,
            'quote' => $quote,
            'rating' => $rating,
            'is_approved' => $approved,
            'sort_order' => $sortOrder,
        ]);
    }

    public function deleteTestimonial(int $id): void
    {
        $this->deleteById('testimonials', $id);
    }

    private function paginate(string $table, array $searchable, string $search, int $page, int $perPage, string $order): array
    {
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $search = trim($search);
        if ($this->length($search) > 120) {
            throw new InvalidArgumentException('Search must be 120 characters or fewer.');
        }
        $parameters = [];
        $where = '';
        if ($search !== '') {
            $where = ' WHERE (' . implode(' OR ', array_map(static fn (string $column): string => $column . ' LIKE ?', $searchable)) . ')';
            $parameters = array_fill(0, count($searchable), '%' . $search . '%');
        }
        $count = $this->database->prepare('SELECT COUNT(*) FROM ' . $table . $where);
        $count->execute($parameters);
        $total = (int) $count->fetchColumn();
        $statement = $this->database->prepare('SELECT * FROM ' . $table . $where . ' ORDER BY ' . $order . ' LIMIT ? OFFSET ?');
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

    private function findById(string $table, int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        $statement = $this->database->prepare('SELECT * FROM ' . $table . ' WHERE id = ? LIMIT 1');
        $statement->execute([$id]);
        $row = $statement->fetch();
        return is_array($row) ? $row : null;
    }

    private function saveRow(string $table, ?int $id, array $data): int
    {
        $columns = array_keys($data);
        if ($id === null) {
            $statement = $this->database->prepare(
                'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', array_fill(0, count($columns), '?')) . ')'
            );
            $statement->execute(array_values($data));
            return (int) $this->database->lastInsertId();
        }
        $statement = $this->database->prepare(
            'UPDATE ' . $table . ' SET ' . implode(', ', array_map(static fn (string $column): string => $column . ' = ?', $columns)) . ' WHERE id = ?'
        );
        $statement->execute([...array_values($data), $id]);
        return $id;
    }

    private function deleteById(string $table, int $id): void
    {
        if ($this->findById($table, $id) === null) {
            throw new RuntimeException('The requested record does not exist.');
        }
        $statement = $this->database->prepare('DELETE FROM ' . $table . ' WHERE id = ?');
        $statement->execute([$id]);
    }

    private function nullableText(mixed $value, int $maximum, string $label): ?string
    {
        if ($value !== null && !is_string($value)) {
            throw new InvalidArgumentException($label . ' must be plain text.');
        }
        $text = trim((string) ($value ?? ''));
        if ($this->length($text) > $maximum) {
            throw new InvalidArgumentException($label . ' exceeds its maximum allowed length.');
        }
        return $text === '' ? null : $text;
    }

    private function plainText(mixed $value, string $label): string
    {
        if (!is_string($value) && !is_numeric($value)) {
            throw new InvalidArgumentException($label . ' must be plain text.');
        }
        return trim((string) $value);
    }

    private function boolean(mixed $value, string $label): int
    {
        if (!is_scalar($value) && $value !== null) {
            throw new InvalidArgumentException($label . ' option is invalid.');
        }
        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($parsed === null) {
            throw new InvalidArgumentException($label . ' option is invalid.');
        }
        return $parsed ? 1 : 0;
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
