<?php
declare(strict_types=1);

namespace Middleware;

use Core\HttpException;
use Core\MiddlewareInterface;
use Core\Request;

final class ApiKeyMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $expectedKey = '')
    {
    }

    public function handle(Request $request): void
    {
        $configuredKey = $this->expectedKey !== '' ? $this->expectedKey : (string) (getenv('API_KEY') ?: 'secret-key');
        $incomingKey = (string) ($request->header('X-Api-Key') ?? '');

        if ($configuredKey === '') {
            return;
        }

        if (!hash_equals($configuredKey, $incomingKey)) {
            throw new HttpException('Unauthorized', 401);
        }
    }
}
