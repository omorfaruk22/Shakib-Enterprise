<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use App\Repositories\AdminActivityRepository;
use App\Repositories\MediaRepository;
use App\Security\AdminAccess;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

final class MediaManagementService
{
    private const MAX_FILE_BYTES = 5242880;
    private const IMAGE_TYPES = [
        'image/jpeg' => ['extension' => 'jpg', 'image_type' => IMAGETYPE_JPEG],
        'image/png' => ['extension' => 'png', 'image_type' => IMAGETYPE_PNG],
        'image/webp' => ['extension' => 'webp', 'image_type' => IMAGETYPE_WEBP],
    ];

    private readonly PDO $database;
    private readonly MediaRepository $media;

    public function __construct(?PDO $database = null)
    {
        $this->database = $database ?? Connection::fromConfig(require BASE_PATH . '/config/database.php');
        $this->media = new MediaRepository($this->database);
    }

    public function list(int $page = 1, int $perPage = 24): array
    {
        AdminAccess::requireAdmin($this->database);
        return $this->media->list($page, $perPage);
    }

    public function upload(array $file, string $altText = ''): int
    {
        $admin = AdminAccess::requireAdmin($this->database);
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Choose an image to upload.');
        }
        $temporaryPath = $file['tmp_name'] ?? null;
        $fileSize = filter_var($file['size'] ?? null, FILTER_VALIDATE_INT);
        if (!is_string($temporaryPath) || !is_uploaded_file($temporaryPath) || $fileSize === false || $fileSize < 1 || $fileSize > self::MAX_FILE_BYTES) {
            throw new InvalidArgumentException('Image must be an uploaded file no larger than 5 MB.');
        }
        if (!is_string($altText) || $this->length($altText) > 255) {
            throw new InvalidArgumentException('Image alt text must be 255 characters or fewer.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($temporaryPath);
        $image = @getimagesize($temporaryPath);
        if (!is_string($mime) || !isset(self::IMAGE_TYPES[$mime]) || !is_array($image) || ($image['mime'] ?? '') !== $mime) {
            throw new InvalidArgumentException('Only valid JPEG, PNG and WebP image files are allowed.');
        }
        $width = (int) ($image[0] ?? 0);
        $height = (int) ($image[1] ?? 0);
        if ($width < 1 || $height < 1 || $width > 6000 || $height > 6000 || $width * $height > 25000000) {
            throw new InvalidArgumentException('Image dimensions are too large. Maximum is 6000 by 6000 pixels.');
        }

        $directory = BASE_PATH . '/public/uploads/media';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Media storage could not be initialized.');
        }
        $filename = bin2hex(random_bytes(20)) . '.' . self::IMAGE_TYPES[$mime]['extension'];
        $absolutePath = $directory . '/' . $filename;
        if (!move_uploaded_file($temporaryPath, $absolutePath)) {
            throw new RuntimeException('The image could not be moved into protected media storage.');
        }
        @chmod($absolutePath, 0644);

        $originalName = basename(is_string($file['name'] ?? null) ? $file['name'] : 'image');
        $originalName = preg_replace('/[\x00-\x1F\x7F]/u', '', $originalName) ?? 'image';
        $data = [
            'storage_path' => 'uploads/media/' . $filename,
            'original_name' => $this->truncate($originalName, 255),
            'mime_type' => $mime,
            'file_size' => $fileSize,
            'alt_text' => trim($altText) === '' ? null : trim($altText),
        ];

        try {
            $this->database->beginTransaction();
            $id = $this->media->create($data, $admin['id']);
            (new AdminActivityRepository())->record($this->database, $admin['id'], 'media.uploaded', 'media', $id);
            $this->database->commit();
            return $id;
        } catch (Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            @unlink($absolutePath);
            throw $exception;
        }
    }

    public function delete(int $id): void
    {
        $admin = AdminAccess::requireAdmin($this->database);
        $this->database->beginTransaction();
        try {
            $record = $this->media->find($id);
            if ($record === null) {
                throw new RuntimeException('The requested media record does not exist.');
            }
            $this->media->delete($id);
            (new AdminActivityRepository())->record($this->database, $admin['id'], 'media.deleted', 'media', $id);
            $this->database->commit();
        } catch (Throwable $exception) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            throw $exception;
        }

        $publicRoot = realpath(BASE_PATH . '/public');
        $mediaRoot = realpath(BASE_PATH . '/public/uploads/media');
        $path = is_string($record['storage_path'] ?? null) ? (string) $record['storage_path'] : '';
        if (
            $publicRoot !== false
            && $mediaRoot !== false
            && preg_match('#^uploads/media/[a-f0-9]{40}\.(jpg|png|webp)$#D', $path) === 1
        ) {
            $absolutePath = realpath(BASE_PATH . '/public/' . $path);
            if (
                $absolutePath !== false
                && str_starts_with(strtolower($absolutePath), strtolower($mediaRoot . DIRECTORY_SEPARATOR))
                && str_starts_with(strtolower($absolutePath), strtolower($publicRoot . DIRECTORY_SEPARATOR))
                && is_file($absolutePath)
            ) {
                @unlink($absolutePath);
            }
        }
    }

    private function length(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }
        $length = preg_match_all('/./us', $value, $matches);
        return $length === false ? strlen($value) : $length;
    }

    private function truncate(string $value, int $maximum): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $maximum, 'UTF-8');
        }
        return substr($value, 0, $maximum);
    }
}
