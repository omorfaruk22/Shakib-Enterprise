<?php
namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewPath = str_replace('.', '/', $view);
        $candidates = [];

        if ($layout === 'admin') {
            $candidates[] = __DIR__ . '/../../resources/views/admin/' . $viewPath . '.php';
        }
        if ($layout === 'auth') {
            $candidates[] = __DIR__ . '/../../resources/views/pages/auth/' . basename($viewPath) . '.php';
        }
        $candidates[] = __DIR__ . '/../../resources/views/' . $viewPath . '.php';
        $candidates[] = __DIR__ . '/../../resources/views/pages/' . $viewPath . '.php';

        $viewFile = null;
        foreach ($candidates as $c) {
            if (is_file($c)) { $viewFile = $c; break; }
        }

        if (!$viewFile) {
            http_response_code(500);
            die('View not found: ' . htmlspecialchars($view) . ' (looked in ' . implode(', ', $candidates) . ')');
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = __DIR__ . '/../../resources/views/layouts/' . $layout . '.php';
        if (is_file($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    public static function partial(string $partialName, array $data = []): void
    {
        $file = __DIR__ . '/../../resources/views/components/' . str_replace('.', '/', $partialName) . '.php';
        if (is_file($file)) {
            extract($data, EXTR_SKIP);
            require $file;
        }
    }
}
