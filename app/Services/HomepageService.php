<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Helpers\Logger;
use PDO;
use Throwable;

final class HomepageService
{
    public function load(): array
    {
        $content = $this->fallbackContent();

        try {
            $database = Connection::fromConfig(require BASE_PATH . '/config/database.php');
            $content['settings'] = $this->loadSettings($database, $content['settings']);
            $content['services'] = $this->loadServices($database);
            $content['destinations'] = $database->query(
                'SELECT slug, name, description, image_path
                 FROM destinations
                 WHERE is_active = 1
                 ORDER BY is_featured DESC, name ASC
                 LIMIT 3'
            )->fetchAll();
            $content['packages'] = $database->query(
                'SELECT p.package_type, p.slug, p.title, p.summary, p.duration_days,
                        p.price, p.currency, p.image_path, d.name AS destination_name
                 FROM packages AS p
                 LEFT JOIN destinations AS d ON d.id = p.destination_id
                 WHERE p.is_active = 1
                 ORDER BY p.is_featured DESC, p.updated_at DESC
                 LIMIT 6'
            )->fetchAll();
            $content['testimonials'] = $database->query(
                'SELECT customer_display_name, quote, rating
                 FROM testimonials
                 WHERE is_approved = 1
                 ORDER BY sort_order ASC, created_at DESC
                 LIMIT 4'
            )->fetchAll();
            $content['database_available'] = true;
        } catch (Throwable $exception) {
            Logger::error(
                'Homepage content could not be loaded: ' . $exception->getMessage(),
                BASE_PATH . '/storage/logs/app.log'
            );
        }

        return $content;
    }

    private function loadSettings(PDO $database, array $defaults): array
    {
        $keys = array_keys($defaults);
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $statement = $database->prepare(
            "SELECT setting_key, setting_value FROM website_settings WHERE setting_key IN ($placeholders)"
        );
        $statement->execute($keys);

        foreach ($statement->fetchAll() as $setting) {
            $key = (string) ($setting['setting_key'] ?? '');
            $value = trim((string) ($setting['setting_value'] ?? ''));
            if ($key !== '' && $value !== '' && array_key_exists($key, $defaults)) {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }

    private function loadServices(PDO $database): array
    {
        $fallbacks = $this->fallbackServices();
        $bySlug = [];
        foreach ($fallbacks as $service) {
            $bySlug[$service['slug']] = $service;
        }

        $rows = $database->query(
            'SELECT slug, name, description
             FROM services
             WHERE is_active = 1
             ORDER BY sort_order ASC, name ASC
             LIMIT 8'
        )->fetchAll();

        if ($rows === []) {
            return $fallbacks;
        }

        $services = [];
        foreach ($rows as $row) {
            $slug = (string) ($row['slug'] ?? '');
            $fallback = $bySlug[$slug] ?? ['name' => 'Travel service', 'description' => 'Ask our team about this service.', 'icon' => '✦'];
            $name = trim((string) ($row['name'] ?? ''));
            $description = trim((string) ($row['description'] ?? ''));
            $services[] = [
                'slug' => $slug,
                'name' => $name !== '' ? $name : $fallback['name'],
                'description' => $description !== '' ? $description : $fallback['description'],
                'icon' => $fallback['icon'],
            ];
        }

        return $services;
    }

    private function fallbackContent(): array
    {
        return [
            'database_available' => false,
            'settings' => [
                'site_name' => env_value('APP_NAME', 'SAKIB ENTERPRISE'),
                'site_tagline' => 'YOUR TRUSTED TRAVEL PARTNER',
                'homepage_hero_eyebrow' => 'Travel with confidence',
                'homepage_hero_title' => 'Explore the world with',
                'homepage_hero_highlight' => 'SAKIB ENTERPRISE',
                'homepage_hero_description' => 'From ticket inquiries to memorable tours, get friendly support for planning your next journey.',
                'homepage_meta_title' => '',
                'homepage_meta_description' => '',
                'office_address' => "Cox's Bazar Sadar, Bangladesh",
                'phone' => '',
                'whatsapp' => '',
                'email' => '',
            ],
            'services' => $this->fallbackServices(),
            'destinations' => [],
            'packages' => [],
            'testimonials' => [],
        ];
    }

    private function fallbackServices(): array
    {
        return [
            ['slug' => 'air-ticket', 'name' => 'Air Tickets', 'description' => 'Share your route and dates for a flight inquiry.', 'icon' => '✈'],
            ['slug' => 'ship-ticket', 'name' => 'Ship Tickets', 'description' => 'Ask about a ship route and your preferred travel date.', 'icon' => '⛴'],
            ['slug' => 'bus-ticket', 'name' => 'Bus Tickets', 'description' => 'Send your departure, destination and journey date.', 'icon' => '▰'],
            ['slug' => 'tour-travel', 'name' => 'Tour & Travel', 'description' => 'Ask about a tour that fits your travel plans.', 'icon' => '⌖'],
            ['slug' => 'holiday-package', 'name' => 'Holiday Packages', 'description' => 'Request current holiday package details.', 'icon' => '☼'],
        ];
    }
}
