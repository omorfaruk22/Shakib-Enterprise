<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Repositories\AdminActivityRepository;
use App\Repositories\ContentRepository;
use App\Security\AdminAccess;
use PDO;

final class AdminContentService
{
    private readonly PDO $database;
    private readonly ContentRepository $content;
    private readonly AdminActivityRepository $activity;

    public function __construct(?PDO $database = null)
    {
        $this->database = $database ?? Connection::fromConfig(require BASE_PATH . '/config/database.php');
        $this->content = new ContentRepository($this->database);
        $this->activity = new AdminActivityRepository();
    }

    public function listPages(string $search = '', int $page = 1, int $perPage = 25): array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->content->listPages($search, $page, $perPage);
    }

    public function findPage(int $id): ?array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->content->findPage($id);
    }

    public function savePage(?int $id, array $input): int
    {
        $admin = AdminAccess::requireAdmin($this->database);
        return $this->mutate(function () use ($id, $input, $admin): int {
            $recordId = $this->content->savePage($id, $input);
            $this->activity->record($this->database, $admin['id'], $id === null ? 'page.created' : 'page.updated', 'website_page', $recordId);
            return $recordId;
        });
    }

    public function deletePage(int $id): void
    {
        $admin = AdminAccess::requireAdmin($this->database);
        $this->mutate(function () use ($id, $admin): void {
            $this->content->deletePage($id);
            $this->activity->record($this->database, $admin['id'], 'page.deleted', 'website_page', $id);
        });
    }

    public function saveSetting(string $key, mixed $value): void
    {
        $admin = AdminAccess::requireAdmin($this->database);
        $this->mutate(function () use ($key, $value, $admin): void {
            $this->content->saveSetting($key, $value, $admin['id']);
            $this->activity->record($this->database, $admin['id'], 'setting.updated', 'website_setting', null);
        });
    }

    public function settings(): array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->content->settings();
    }

    public function listTestimonials(string $search = '', int $page = 1, int $perPage = 25): array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->content->listTestimonials($search, $page, $perPage);
    }

    public function findTestimonial(int $id): ?array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->content->findTestimonial($id);
    }

    public function saveTestimonial(?int $id, array $input): int
    {
        $admin = AdminAccess::requireAdmin($this->database);
        return $this->mutate(function () use ($id, $input, $admin): int {
            $recordId = $this->content->saveTestimonial($id, $input);
            $this->activity->record($this->database, $admin['id'], $id === null ? 'testimonial.created' : 'testimonial.updated', 'testimonial', $recordId);
            return $recordId;
        });
    }

    public function deleteTestimonial(int $id): void
    {
        $admin = AdminAccess::requireAdmin($this->database);
        $this->mutate(function () use ($id, $admin): void {
            $this->content->deleteTestimonial($id);
            $this->activity->record($this->database, $admin['id'], 'testimonial.deleted', 'testimonial', $id);
        });
    }

    private function mutate(callable $operation): mixed
    {
        $this->database->beginTransaction();
        try {
            $result = $operation();
            $this->database->commit();
            return $result;
        } catch (\Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }
    }
}
