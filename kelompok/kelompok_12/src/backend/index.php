<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'Core\\' => __DIR__ . '/core/',
        'Controllers\\' => __DIR__ . '/controllers/',
        'Models\\' => __DIR__ . '/models/',
        'Middleware\\' => __DIR__ . '/middleware/',
        'Bootstrap\\' => __DIR__ . '/bootstrap/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        }
    }
});

use Bootstrap\RoleBootstrapper;
use Controllers\AuthController;
use Controllers\UserController;
use Controllers\UserRoleController;
use Core\HttpException;
use Core\Request;
use Core\Response;
use Core\Router;
use Middleware\AuthMiddleware;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$request = Request::capture();
$router = new Router();
$authMiddleware = new AuthMiddleware();

$userRoleController = new UserRoleController();
$userController = new UserController();
$authController = new AuthController();

RoleBootstrapper::ensureDefaults();

$router->add('POST', '/api/v1/auth/register', [$authController, 'register']);
$router->add('POST', '/api/v1/auth/login', [$authController, 'login']);
$router->add('POST', '/api/v1/auth/logout', [$authController, 'logout'], [$authMiddleware]);

$router->add('GET', '/api/v1/user-roles', [$userRoleController, 'index'], [$authMiddleware]);
$router->add('GET', '/api/v1/user-roles/{id}', [$userRoleController, 'show'], [$authMiddleware]);
$router->add('POST', '/api/v1/user-roles', [$userRoleController, 'store'], [$authMiddleware]);
$router->add('PUT', '/api/v1/user-roles/{id}', [$userRoleController, 'update'], [$authMiddleware]);
$router->add('DELETE', '/api/v1/user-roles/{id}', [$userRoleController, 'destroy'], [$authMiddleware]);

$router->add('GET', '/api/v1/users', [$userController, 'index'], [$authMiddleware]);
$router->add('GET', '/api/v1/users/{id}', [$userController, 'show'], [$authMiddleware]);
$router->add('POST', '/api/v1/users', [$userController, 'store'], [$authMiddleware]);
$router->add('PUT', '/api/v1/users/{id}', [$userController, 'update'], [$authMiddleware]);
$router->add('DELETE', '/api/v1/users/{id}', [$userController, 'destroy'], [$authMiddleware]);

try {
    $router->dispatch($request);
} catch (HttpException $exception) {
    Response::json(['error' => $exception->getMessage()], $exception->getStatusCode())->send();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    Response::json(['error' => 'Internal Server Error'], 500)->send();
}
