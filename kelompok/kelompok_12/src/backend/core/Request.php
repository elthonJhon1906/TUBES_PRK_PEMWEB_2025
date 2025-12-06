<?php
declare(strict_types=1);

namespace Core;

/**
 * Lightweight HTTP request wrapper for the mini framework.
 */
final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $queryParams,
        private readonly array $bodyParams,
        private readonly array $headers
    ) {
    }

    /**
     * Build request instance from globals.
     */
    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        $rawBody = file_get_contents('php://input') ?: '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $body = [];

        if (str_contains($contentType, 'application/json') && $rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $body = $_POST;
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$header] = $value;
            }
        }

        return new self(
            $method,
            $path,
            $_GET,
            $body,
            $headers
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    public function allQuery(): array
    {
        return $this->queryParams;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->bodyParams[$key] ?? $default;
    }

    public function body(): array
    {
        return $this->bodyParams;
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $normalized = str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $name))));
        return $this->headers[$normalized] ?? $default;
    }

    public function headers(): array
    {
        return $this->headers;
    }
}
