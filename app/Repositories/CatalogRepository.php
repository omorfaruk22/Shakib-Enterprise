<?php
declare(strict_types=1);

namespace App\Repositories;

use InvalidArgumentException;
use PDO;
use RuntimeException;

final class CatalogRepository
{
    private const RESOURCES = [
        'services' => [
            'table' => 'services',
            'columns' => ['slug', 'name', 'description', 'image_path', 'is_active', 'sort_order'],
            'required' => ['slug', 'name'],
            'defaults' => ['is_active' => 1, 'sort_order' => 0],
            'checks' => ['is_active'],
            'search' => ['slug', 'name', 'description'],
            'order' => 'sort_order ASC, name ASC',
            'lengths' => ['slug' => 180, 'name' => 160, 'description' => 20000, 'image_path' => 500],
        ],
        'destinations' => [
            'table' => 'destinations',
            'columns' => ['slug', 'name', 'description', 'image_path', 'is_featured', 'is_active'],
            'required' => ['slug', 'name'],
            'defaults' => ['is_featured' => 0, 'is_active' => 1],
            'checks' => ['is_featured', 'is_active'],
            'search' => ['slug', 'name', 'description'],
            'order' => 'is_featured DESC, name ASC',
            'lengths' => ['slug' => 180, 'name' => 160, 'description' => 20000, 'image_path' => 500],
        ],
        'packages' => [
            'table' => 'packages',
            'columns' => ['destination_id', 'package_type', 'slug', 'title', 'summary', 'description', 'duration_days', 'price', 'currency', 'inclusions', 'exclusions', 'itinerary', 'image_path', 'is_featured', 'is_active'],
            'required' => ['package_type', 'slug', 'title'],
            'defaults' => ['currency' => 'BDT', 'is_featured' => 0, 'is_active' => 1],
            'checks' => ['is_featured', 'is_active'],
            'search' => ['slug', 'title', 'summary', 'description'],
            'order' => 'is_featured DESC, updated_at DESC',
            'lengths' => ['slug' => 180, 'title' => 200, 'summary' => 500, 'currency' => 3, 'image_path' => 500],
        ],
        'ship_routes' => [
            'table' => 'ship_routes',
            'columns' => ['origin', 'destination', 'description', 'is_active', 'sort_order'],
            'required' => ['origin', 'destination'],
            'defaults' => ['is_active' => 1, 'sort_order' => 0],
            'checks' => ['is_active'],
            'search' => ['origin', 'destination', 'description'],
            'order' => 'sort_order ASC, origin ASC, destination ASC',
            'lengths' => ['origin' => 160, 'destination' => 160, 'description' => 20000],
        ],
    ];

    public function __construct(private readonly PDO $database)
    {
    }

    public function list(string $resource, string $search = '', ?bool $active = null, int $page = 1, int $perPage = 25): array
    {
        $definition = $this->definition($resource);
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $search = trim($search);
        if ($this->length($search) > 120) {
            throw new InvalidArgumentException('Search must be 120 characters or fewer.');
        }
        $where = [];
        $parameters = [];

        if ($search !== '') {
            $terms = [];
            foreach ($definition['search'] as $column) {
                $terms[] = $column . ' LIKE ?';
                $parameters[] = '%' . $search . '%';
            }
            $where[] = '(' . implode(' OR ', $terms) . ')';
        }
        if ($active !== null && in_array('is_active', $definition['columns'], true)) {
            $where[] = 'is_active = ?';
            $parameters[] = $active ? 1 : 0;
        }

        $clause = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $count = $this->database->prepare('SELECT COUNT(*) FROM ' . $definition['table'] . $clause);
        $count->execute($parameters);
        $total = (int) $count->fetchColumn();

        $statement = $this->database->prepare(
            'SELECT * FROM ' . $definition['table'] . $clause . ' ORDER BY ' . $definition['order'] . ' LIMIT ? OFFSET ?'
        );
        $statement->execute([...$parameters, $perPage, ($page - 1) * $perPage]);

        return [
            'items' => $statement->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function find(string $resource, int $id): ?array
    {
        $definition = $this->definition($resource);
        if ($id < 1) {
            return null;
        }
        $statement = $this->database->prepare('SELECT * FROM ' . $definition['table'] . ' WHERE id = ? LIMIT 1');
        $statement->execute([$id]);
        $row = $statement->fetch();
        return is_array($row) ? $row : null;
    }

    public function save(string $resource, ?int $id, array $input): int
    {
        $definition = $this->definition($resource);
        $existing = null;
        if ($id !== null) {
            $existing = $this->find($resource, $id);
            if ($existing === null) {
                throw new RuntimeException('The requested catalog record does not exist.');
            }
        }

        $values = $this->normalize($resource, array_replace($definition['defaults'], $existing ?? [], $input));
        $columns = $definition['columns'];
        $parameters = array_map(static fn (string $column): mixed => $values[$column], $columns);

        if ($id === null) {
            $columnSql = implode(', ', $columns);
            $placeholderSql = implode(', ', array_fill(0, count($columns), '?'));
            $statement = $this->database->prepare(
                'INSERT INTO ' . $definition['table'] . ' (' . $columnSql . ') VALUES (' . $placeholderSql . ')'
            );
            $statement->execute($parameters);
            return (int) $this->database->lastInsertId();
        }

        $assignments = implode(', ', array_map(static fn (string $column): string => $column . ' = ?', $columns));
        $statement = $this->database->prepare(
            'UPDATE ' . $definition['table'] . ' SET ' . $assignments . ' WHERE id = ?'
        );
        $statement->execute([...$parameters, $id]);
        return $id;
    }

    public function delete(string $resource, int $id): void
    {
        $definition = $this->definition($resource);
        if ($id < 1 || $this->find($resource, $id) === null) {
            throw new RuntimeException('The requested catalog record does not exist.');
        }
        $statement = $this->database->prepare('DELETE FROM ' . $definition['table'] . ' WHERE id = ?');
        $statement->execute([$id]);
    }

    private function normalize(string $resource, array $input): array
    {
        $definition = $this->definition($resource);
        $values = [];

        foreach ($definition['columns'] as $column) {
            $value = $input[$column] ?? null;
            if ($column !== 'itinerary' && $value !== null && !is_scalar($value)) {
                throw new InvalidArgumentException('A catalog field has an invalid value.');
            }

            if (in_array($column, $definition['checks'], true)) {
                $values[$column] = $this->boolean($value, $column);
                continue;
            }
            if (in_array($column, ['destination_id', 'duration_days', 'sort_order'], true)) {
                $values[$column] = $this->integer($value, $column, $column === 'destination_id' || $column === 'duration_days');
                continue;
            }
            if ($column === 'price') {
                if ($value === null || trim((string) $value) === '') {
                    $values[$column] = null;
                } elseif (!is_numeric($value) || (float) $value < 0 || (float) $value > 9999999999.99) {
                    throw new InvalidArgumentException('Package price must be a non-negative amount within the database limit.');
                } else {
                    $values[$column] = number_format((float) $value, 2, '.', '');
                }
                continue;
            }
            if ($column === 'itinerary') {
                $values[$column] = $this->jsonValue($value);
                continue;
            }
            if (!is_string($value) && $value !== null && !is_numeric($value)) {
                throw new InvalidArgumentException('A catalog field has an invalid value.');
            }

            $text = trim((string) ($value ?? ''));
            if (in_array($column, $definition['required'], true) && $text === '') {
                throw new InvalidArgumentException('Please complete all required catalog fields.');
            }
            if ($column === 'slug' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $text) !== 1) {
                throw new InvalidArgumentException('Use lowercase letters, numbers and single hyphens in the URL slug.');
            }
            if ($column === 'package_type' && !in_array($text, ['tour', 'holiday'], true)) {
                throw new InvalidArgumentException('Package type must be tour or holiday.');
            }
            if ($column === 'currency') {
                $text = strtoupper($text);
                if (preg_match('/^[A-Z]{3}$/D', $text) !== 1) {
                    throw new InvalidArgumentException('Currency must use a three-letter code such as BDT.');
                }
            }
            if ($column === 'image_path' && $text !== '' && preg_match('#^(?:assets|uploads)/[A-Za-z0-9/_-]+\.(?:jpe?g|png|webp|avif)$#iD', $text) !== 1) {
                throw new InvalidArgumentException('Choose a local image path under assets/ or uploads/ using JPG, PNG, WebP or AVIF.');
            }
            $maximum = $definition['lengths'][$column] ?? null;
            if ($maximum !== null && $this->length($text) > $maximum) {
                throw new InvalidArgumentException('A catalog field exceeds its maximum allowed length.');
            }
            $values[$column] = $text === '' ? null : $text;
        }

        return $values;
    }

    private function boolean(mixed $value, string $field): int
    {
        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($parsed === null) {
            throw new InvalidArgumentException('A catalog visibility option is invalid (' . $field . ').');
        }
        return $parsed ? 1 : 0;
    }

    private function integer(mixed $value, string $field, bool $nullable): ?int
    {
        if (($value === null || $value === '') && $nullable) {
            return null;
        }
        $parsed = filter_var($value, FILTER_VALIDATE_INT);
        if ($parsed === false) {
            throw new InvalidArgumentException('A catalog number field is invalid (' . $field . ').');
        }
        if ($field === 'destination_id' && $parsed < 1) {
            throw new InvalidArgumentException('Choose a valid destination or leave it blank.');
        }
        if ($field === 'duration_days' && ($parsed < 1 || $parsed > 32767)) {
            throw new InvalidArgumentException('Package duration must be between 1 and 32767 days.');
        }
        if ($field === 'sort_order' && ($parsed < 0 || $parsed > 1000000)) {
            throw new InvalidArgumentException('Sort order must be between 0 and 1000000.');
        }
        return $parsed;
    }

    private function jsonValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true, 32, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                throw new InvalidArgumentException('Package itinerary must contain valid JSON.');
            }
            $value = $decoded;
        }
        if (!is_array($value)) {
            throw new InvalidArgumentException('Package itinerary must be a JSON object or list.');
        }
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function definition(string $resource): array
    {
        if (!isset(self::RESOURCES[$resource])) {
            throw new InvalidArgumentException('Unknown catalog resource.');
        }
        return self::RESOURCES[$resource];
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
