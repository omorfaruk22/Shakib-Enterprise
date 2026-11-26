<?php
declare(strict_types=1);

namespace App\Helpers;

final class Router
{
    /** @var list<array{method:string, pattern:string, handler:callable}> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $normalized = $path === '/' ? '/' : '/' . trim($path, '/');
        $segments = explode('/', trim($normalized, '/'));
        if ($normalized === '/') {
            $segments = [];
        }

        $regexSegments = array_map(static function (string $segment): string {
            if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)\}$/', $segment, $matches) === 1) {
                return '(?P<' . $matches[1] . '>[^/]+)';
            }
            return preg_quote($segment, '~');
        }, $segments);
        $pattern = '~^/' . implode('/', $regexSegments) . '/?$~D';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) && $path !== '' ? rawurldecode($path) : '/';
        $path = $path === '/' ? '/' : '/' . trim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method) || preg_match($route['pattern'], $path, $matches) !== 1) {
                continue;
            }

            $parameters = array_filter($matches, static fn ($key): bool => is_string($key), ARRAY_FILTER_USE_KEY);
            ($route['handler'])($parameters);
            return;
        }

        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Not Found';
    }
}
