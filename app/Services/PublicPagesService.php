<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Helpers\Logger;
use PDO;
use Throwable;

final class PublicPagesService
{
    private ?PDO $database = null;
    private bool $databaseAttempted = false;
    private bool $queryFailed = false;

    /** @var array<string, array<string, mixed>> */
    private const SERVICES = [
        'air-ticket' => [
            'name' => 'Air Ticket',
            'description' => 'Share your route and dates so the team can review your flight inquiry.',
            'intro' => 'Request help planning a flight. The team will review the route and travel details you provide, then contact you about options and next steps.',
            'fields' => ['Departure city', 'Destination', 'Journey date', 'Return date (if needed)', 'Passenger count', 'Travel class'],
            'icon' => '✈',
        ],
        'ship-ticket' => [
            'name' => 'Ship Ticket',
            'description' => 'Ask about a ship route and your preferred travel date.',
            'intro' => 'Send your preferred route and travel date. The team can follow up with information about the available ship travel options.',
            'fields' => ['Origin port', 'Destination port', 'Journey date', 'Passenger count'],
            'icon' => '⛴',
        ],
        'bus-ticket' => [
            'name' => 'Bus Ticket',
            'description' => 'Send your departure, destination and journey date.',
            'intro' => 'Tell the team where and when you would like to travel. They will review your bus ticket inquiry and contact you directly.',
            'fields' => ['Departure city', 'Destination', 'Journey date', 'Passenger count', 'Preferred class (optional)'],
            'icon' => '▰',
        ],
        'tour-travel' => [
            'name' => 'Tour & Travel',
            'description' => 'Ask about a tour that fits your travel plans.',
            'intro' => 'Explore tour options with the business. Share your preferred destination, dates and group size so the team can discuss a suitable plan.',
            'fields' => ['Preferred destination', 'Travel dates', 'Number of travelers', 'Trip preferences'],
            'icon' => '⌖',
        ],
        'holiday-package' => [
            'name' => 'Holiday Packages',
            'description' => 'Request current holiday package details.',
            'intro' => 'Ask the team about holiday package options. Published packages appear on the packages page when confirmed by the business.',
            'fields' => ['Destination or package', 'Travel dates', 'Number of travelers', 'Questions or preferences'],
            'icon' => '☼',
        ],
    ];

    public function settings(): array
    {
        $defaults = [
            'site_name' => env_value('APP_NAME', 'SAKIB ENTERPRISE'),
            'site_tagline' => 'YOUR TRUSTED TRAVEL PARTNER',
            'office_address' => "Cox's Bazar Sadar, Bangladesh",
            'phone' => '',
            'whatsapp' => '',
            'email' => '',
            'office_hours' => '',
            'facebook_url' => '',
            'instagram_url' => '',
            'map_embed_url' => '',
        ];
        $keys = array_keys($defaults);
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $rows = $this->fetchAll(
            "SELECT setting_key, setting_value FROM website_settings WHERE setting_key IN ($placeholders)",
            $keys
        );

        foreach ($rows ?? [] as $row) {
            $key = (string) ($row['setting_key'] ?? '');
            $value = trim((string) ($row['setting_value'] ?? ''));
            if ($value !== '' && array_key_exists($key, $defaults)) {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }

    public function services(): array
    {
        $rows = $this->fetchAll(
            'SELECT slug, name, description FROM services WHERE is_active = 1 ORDER BY sort_order ASC, name ASC LIMIT 30'
        );
        if ($rows === []) {
            return $this->fallbackServices();
        }
        if ($rows === null) {
            return $this->fallbackServices();
        }

        return array_map(function (array $row): array {
            $slug = (string) ($row['slug'] ?? '');
            $fallback = self::SERVICES[$slug] ?? [
                'name' => 'Travel service',
                'description' => 'Contact the team to ask about this travel service.',
                'intro' => 'Share your travel needs with the team and they will follow up about the next steps.',
                'fields' => ['Travel dates', 'Destination', 'Number of travelers', 'Questions or preferences'],
                'icon' => '✦',
            ];
            return [
                ...$fallback,
                'slug' => $slug,
                'name' => trim((string) ($row['name'] ?? '')) ?: $fallback['name'],
                'description' => trim((string) ($row['description'] ?? '')) ?: $fallback['description'],
            ];
        }, $rows);
    }

    public function servicePath(string $slug): string
    {
        return match ($slug) {
            'air-ticket' => '/air-tickets',
            'ship-ticket' => '/ship-tickets',
            'bus-ticket' => '/bus-tickets',
            'tour-travel' => '/tour-travel',
            'holiday-package' => '/holiday-packages',
            default => '/services/' . rawurlencode($slug),
        };
    }

    public function service(string $slug): ?array
    {
        if (preg_match('/^[a-z0-9-]{1,180}$/D', $slug) !== 1) {
            return null;
        }
        $row = $this->fetchOne(
            'SELECT slug, name, description, is_active FROM services WHERE slug = ? LIMIT 1',
            [$slug]
        );
        if ($row !== null && (int) ($row['is_active'] ?? 0) !== 1) {
            return null;
        }
        $fallback = self::SERVICES[$slug] ?? null;
        if ($row === null && $fallback === null) {
            return null;
        }
        $fallback ??= [
            'name' => 'Travel service',
            'description' => 'Contact the team to ask about this travel service.',
            'intro' => 'Share your travel needs with the team and they will follow up about the next steps.',
            'fields' => ['Travel dates', 'Destination', 'Number of travelers', 'Questions or preferences'],
            'icon' => '✦',
        ];

        return [
            ...$fallback,
            'slug' => $slug,
            'name' => trim((string) ($row['name'] ?? '')) ?: $fallback['name'],
            'description' => trim((string) ($row['description'] ?? '')) ?: $fallback['description'],
        ];
    }

    public function destinations(): array
    {
        return $this->fetchAll(
            'SELECT slug, name, description, image_path FROM destinations WHERE is_active = 1 ORDER BY is_featured DESC, name ASC LIMIT 30'
        ) ?? [];
    }

    public function shipRoutes(): array
    {
        return $this->fetchAll(
            'SELECT id, origin, destination, description FROM ship_routes WHERE is_active = 1 ORDER BY sort_order ASC, origin ASC LIMIT 30'
        ) ?? [];
    }

    public function destination(string $slug): ?array
    {
        if (preg_match('/^[a-z0-9-]{1,180}$/D', $slug) !== 1) {
            return null;
        }
        return $this->fetchOne(
            'SELECT slug, name, description, image_path FROM destinations WHERE slug = ? AND is_active = 1 LIMIT 1',
            [$slug]
        );
    }

    public function packages(?string $type = null): array
    {
        $sql = 'SELECT p.slug, p.package_type, p.title, p.summary, p.duration_days, p.price, p.currency, p.image_path, d.name AS destination_name
                FROM packages AS p
                LEFT JOIN destinations AS d ON d.id = p.destination_id
                WHERE p.is_active = 1';
        $params = [];
        if (in_array($type, ['tour', 'holiday'], true)) {
            $sql .= ' AND p.package_type = ?';
            $params[] = $type;
        }
        $sql .= ' ORDER BY p.is_featured DESC, p.updated_at DESC LIMIT 60';
        return $this->fetchAll($sql, $params) ?? [];
    }

    public function package(string $slug): ?array
    {
        if (preg_match('/^[a-z0-9-]{1,180}$/D', $slug) !== 1) {
            return null;
        }
        return $this->fetchOne(
            'SELECT p.slug, p.package_type, p.title, p.summary, p.description, p.duration_days, p.price, p.currency,
                    p.inclusions, p.exclusions, p.itinerary, p.image_path, d.name AS destination_name
             FROM packages AS p
             LEFT JOIN destinations AS d ON d.id = p.destination_id
             WHERE p.slug = ? AND p.is_active = 1 LIMIT 1',
            [$slug]
        );
    }

    public function aboutPage(): array
    {
        $page = $this->fetchOne(
            'SELECT title, body, meta_title, meta_description FROM website_pages WHERE slug = ? AND is_published = 1 LIMIT 1',
            ['about-us']
        );
        if ($page !== null) {
            return $page;
        }

        return [
            'title' => 'About ' . ($this->settings()['site_name'] ?? 'SAKIB ENTERPRISE'),
            'body' => "SAKIB ENTERPRISE is a travel business based in Cox's Bazar Sadar, Bangladesh.\n\nThe team helps visitors explore travel service inquiries and package information. Contact the business directly to discuss your plans and confirm current details.",
            'meta_title' => '',
            'meta_description' => '',
        ];
    }

    public function page(string $slug): ?array
    {
        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug) !== 1) {
            return null;
        }
        return $this->fetchOne(
            'SELECT slug, title, body, meta_title, meta_description
             FROM website_pages WHERE slug = ? AND is_published = 1 LIMIT 1',
            [$slug]
        );
    }

    public function databaseAvailable(): bool
    {
        $this->database();
        return $this->database instanceof PDO && !$this->queryFailed;
    }

    private function fallbackServices(): array
    {
        $services = [];
        foreach (self::SERVICES as $slug => $service) {
            $services[] = ['slug' => $slug, ...$service];
        }
        return $services;
    }

    private function fetchAll(string $sql, array $parameters = []): ?array
    {
        $database = $this->database();
        if (!$database instanceof PDO) {
            return null;
        }
        try {
            $statement = $database->prepare($sql);
            $statement->execute($parameters);
            return $statement->fetchAll();
        } catch (Throwable $exception) {
            $this->queryFailed = true;
            $this->logQueryError($exception);
            return null;
        }
    }

    private function fetchOne(string $sql, array $parameters = []): ?array
    {
        $database = $this->database();
        if (!$database instanceof PDO) {
            return null;
        }
        try {
            $statement = $database->prepare($sql);
            $statement->execute($parameters);
            $row = $statement->fetch();
            return is_array($row) ? $row : null;
        } catch (Throwable $exception) {
            $this->queryFailed = true;
            $this->logQueryError($exception);
            return null;
        }
    }

    private function database(): ?PDO
    {
        if ($this->databaseAttempted) {
            return $this->database;
        }
        $this->databaseAttempted = true;
        try {
            $this->database = Connection::fromConfig(require BASE_PATH . '/config/database.php');
        } catch (Throwable $exception) {
            $this->logQueryError($exception);
        }
        return $this->database;
    }

    private function logQueryError(Throwable $exception): void
    {
        Logger::error(
            'Public page content could not be loaded: ' . $exception->getMessage(),
            BASE_PATH . '/storage/logs/app.log'
        );
    }
}
