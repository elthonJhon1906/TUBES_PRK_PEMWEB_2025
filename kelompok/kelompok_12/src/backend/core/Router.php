<?php
declare(strict_types=1);

namespace Core;

use RuntimeException;

/**
 * Very small router with path parameters and middleware support.
 */
final class Router
{
    /**
     * @var array<int, array{method: string, pattern: string, variables: array<int, string>, handler: callable, middleware: array<int, MiddlewareInterface>}>
     */
    private array $routes = [];

    public function add(string $method, string $uri, callable $handler, array $middleware = []): void
    {
        $variables = [];
        $pattern = preg_replace_callback('#\{([\w_]+)\}#', function ($matches) use (&$variables) {
            $variables[] = $matches[1];
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $uri);

        if ($pattern === null) {
            throw new RuntimeException('Invalid route pattern: ' . $uri);
        }

        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'variables' => $variables,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            if (!preg_match($route['pattern'], $request->path(), $matches)) {
                continue;
            }

            $params = [];
            foreach ($route['variables'] as $name) {
                $params[$name] = is_numeric($matches[$name]) ? (int) $matches[$name] : $matches[$name];
            }

            foreach ($route['middleware'] as $middleware) {
                $middleware->handle($request);
            }

            $response = call_user_func($route['handler'], $request, $params);

            if ($response instanceof Response) {
                $response->send();
            } else {
                Response::json(['data' => $response])->send();
            }
            return;
        }

        throw new HttpException('Route not found.', 404);
    }
}
