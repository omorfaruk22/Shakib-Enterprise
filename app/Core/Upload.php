<?php
namespace App\Core;

class Upload
{
    public static function handle(?array $file, string $subdir = ''): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload failed.');
        }
        $cfg = require __DIR__ . '/../../config/security.php';
        $up = $cfg['upload'];
        if ($file['size'] > $up['max_size']) {
            throw new \RuntimeException('File is too large. Maximum 5 MB allowed.');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $up['allowed_mimes'], true)) {
            throw new \RuntimeException('Only JPG, PNG and WebP images are allowed.');
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $up['allowed_exts'], true)) {
            throw new \RuntimeException('Invalid file extension.');
        }
        // Verify actual image content
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new \RuntimeException('Invalid image file.');
        }
        $dir = rtrim($up['path'], '/') . ($subdir ? '/' . trim($subdir, '/') : '');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = bin2hex(random_bytes(16)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $dest = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new \RuntimeException('Could not save uploaded file.');
        }
        @chmod($dest, 0644);
        $rel = ($subdir ? trim($subdir, '/') . '/' : '') . $filename;
        return $up['url_prefix'] . '/' . $rel;
    }

    public static function delete(?string $urlPath): void
    {
        if (!$urlPath || str_starts_with($urlPath, 'http')) {
            return;
        }
        $cfg = require __DIR__ . '/../../config/security.php';
        $prefix = $cfg['upload']['url_prefix'] . '/';
        if (str_starts_with($urlPath, $prefix)) {
            $file = $cfg['upload']['path'] . '/' . substr($urlPath, strlen($prefix));
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
