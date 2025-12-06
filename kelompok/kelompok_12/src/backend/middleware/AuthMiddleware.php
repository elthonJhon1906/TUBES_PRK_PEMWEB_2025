<?php
declare(strict_types=1);

namespace Middleware;

use Core\HttpException;
use Core\MiddlewareInterface;
use Core\Request;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['user'])) {
            throw new HttpException('Unauthenticated', 401);
        }
    }
}
