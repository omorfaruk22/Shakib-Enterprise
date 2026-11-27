<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $pattern, callable|array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable|array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable|array $handler): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';
        $this->routes[] = ['method' => $method, 'regex' => $regex, 'handler' => $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = '/' . trim($uri, '/');
        if ($uri === '/') {
            $uri = '/';
        }
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['regex'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    $controller->$action(...array_values($params));
                } else {
                    $handler(...array_values($params));
                }
                return;
            }
        }
        http_response_code(404);
        View::render('404', ['title' => 'Page Not Found'], 'main');
    }
}
