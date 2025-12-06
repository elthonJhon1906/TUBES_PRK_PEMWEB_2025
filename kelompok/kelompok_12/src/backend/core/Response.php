<?php
declare(strict_types=1);

namespace Core;

/**
 * Basic JSON response helper.
 */
final class Response
{
    public function __construct(
        private readonly array $body,
        private readonly int $status = 200,
        private readonly array $headers = []
    ) {
    }

    public static function json(array $body, int $status = 200, array $headers = []): self
    {
        return new self($body, $status, $headers);
    }

    public function send(): void
    {
        http_response_code($this->status);
        header('Content-Type: application/json');
        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }

        echo json_encode($this->body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
