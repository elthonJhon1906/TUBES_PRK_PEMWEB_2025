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
use Controllers\ProductBatchController;
use Controllers\ProductCategoryController;
use Controllers\ProductController;
use Controllers\ProofTypeController;
use Controllers\ServiceCategoryController;
use Controllers\ServiceController;
use Controllers\TransactionController;
use Controllers\TransactionMethodController;
use Controllers\UserController;
use Controllers\UserRoleController;
use Controllers\VerificationStatusController;
use Core\HttpException;
use Core\Request;
use Core\Response;
use Core\Router;
use Middleware\AuthMiddleware;
use Middleware\RoleMiddleware;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$request = Request::capture();
$router = new Router();
$authMiddleware = new AuthMiddleware();

$userRoleController = new UserRoleController();
$userController = new UserController();
$authController = new AuthController();
$productCategoryController = new ProductCategoryController();
$productController = new ProductController();
$productBatchController = new ProductBatchController();
$serviceCategoryController = new ServiceCategoryController();
$serviceController = new ServiceController();
$transactionMethodController = new TransactionMethodController();
$verificationStatusController = new VerificationStatusController();
$proofTypeController = new ProofTypeController();
$transactionController = new TransactionController();
$ownerOrStaff = new RoleMiddleware(['owner', 'staff']);

RoleBootstrapper::ensureDefaults();

$router->add('POST', '/api/v1/auth/register', [$authController, 'register']);
$router->add('POST', '/api/v1/auth/login', [$authController, 'login']);
$router->add('POST', '/api/v1/auth/logout', [$authController, 'logout'], [$authMiddleware]);

$router->add('GET', '/api/v1/user-roles', [$userRoleController, 'index'], [$authMiddleware]);
$router->add('GET', '/api/v1/user-roles/{id}', [$userRoleController, 'show'], [$authMiddleware]);
$router->add('POST', '/api/v1/user-roles', [$userRoleController, 'store'], [$authMiddleware]);
$router->add('PATCH', '/api/v1/user-roles/{id}', [$userRoleController, 'update'], [$authMiddleware]);
$router->add('DELETE', '/api/v1/user-roles/{id}', [$userRoleController, 'destroy'], [$authMiddleware]);

$router->add('GET', '/api/v1/users', [$userController, 'index'], [$authMiddleware]);
$router->add('GET', '/api/v1/users/{id}', [$userController, 'show'], [$authMiddleware]);
$router->add('POST', '/api/v1/users', [$userController, 'store'], [$authMiddleware]);
$router->add('PATCH', '/api/v1/users/{id}', [$userController, 'update'], [$authMiddleware]);
$router->add('DELETE', '/api/v1/users/{id}', [$userController, 'destroy'], [$authMiddleware]);

$inventoryGuards = [$authMiddleware, $ownerOrStaff];
$router->add('GET', '/api/v1/inventory/categories', [$productCategoryController, 'index'], $inventoryGuards);
$router->add('GET', '/api/v1/inventory/categories/{id}', [$productCategoryController, 'show'], $inventoryGuards);
$router->add('POST', '/api/v1/inventory/categories', [$productCategoryController, 'store'], $inventoryGuards);
$router->add('PATCH', '/api/v1/inventory/categories/{id}', [$productCategoryController, 'update'], $inventoryGuards);
$router->add('DELETE', '/api/v1/inventory/categories/{id}', [$productCategoryController, 'destroy'], $inventoryGuards);

$router->add('GET', '/api/v1/inventory/products', [$productController, 'index'], $inventoryGuards);
$router->add('GET', '/api/v1/inventory/products/{id}', [$productController, 'show'], $inventoryGuards);
$router->add('POST', '/api/v1/inventory/products', [$productController, 'store'], $inventoryGuards);
$router->add('PATCH', '/api/v1/inventory/products/{id}', [$productController, 'update'], $inventoryGuards);
$router->add('DELETE', '/api/v1/inventory/products/{id}', [$productController, 'destroy'], $inventoryGuards);

$router->add('GET', '/api/v1/inventory/product-batches', [$productBatchController, 'index'], $inventoryGuards);
$router->add('GET', '/api/v1/inventory/product-batches/{id}', [$productBatchController, 'show'], $inventoryGuards);
$router->add('POST', '/api/v1/inventory/product-batches', [$productBatchController, 'store'], $inventoryGuards);
$router->add('PATCH', '/api/v1/inventory/product-batches/{id}', [$productBatchController, 'update'], $inventoryGuards);
$router->add('DELETE', '/api/v1/inventory/product-batches/{id}', [$productBatchController, 'destroy'], $inventoryGuards);

$router->add('GET', '/api/v1/services/categories', [$serviceCategoryController, 'index'], $inventoryGuards);
$router->add('GET', '/api/v1/services/categories/{id}', [$serviceCategoryController, 'show'], $inventoryGuards);
$router->add('POST', '/api/v1/services/categories', [$serviceCategoryController, 'store'], $inventoryGuards);
$router->add('PATCH', '/api/v1/services/categories/{id}', [$serviceCategoryController, 'update'], $inventoryGuards);
$router->add('DELETE', '/api/v1/services/categories/{id}', [$serviceCategoryController, 'destroy'], $inventoryGuards);

$router->add('GET', '/api/v1/services', [$serviceController, 'index'], $inventoryGuards);
$router->add('GET', '/api/v1/services/{id}', [$serviceController, 'show'], $inventoryGuards);
$router->add('POST', '/api/v1/services', [$serviceController, 'store'], $inventoryGuards);
$router->add('PATCH', '/api/v1/services/{id}', [$serviceController, 'update'], $inventoryGuards);
$router->add('DELETE', '/api/v1/services/{id}', [$serviceController, 'destroy'], $inventoryGuards);

$router->add('GET', '/api/v1/payments/methods', [$transactionMethodController, 'index'], $inventoryGuards);
$router->add('GET', '/api/v1/payments/methods/{id}', [$transactionMethodController, 'show'], $inventoryGuards);
$router->add('POST', '/api/v1/payments/methods', [$transactionMethodController, 'store'], $inventoryGuards);
$router->add('PATCH', '/api/v1/payments/methods/{id}', [$transactionMethodController, 'update'], $inventoryGuards);
$router->add('DELETE', '/api/v1/payments/methods/{id}', [$transactionMethodController, 'destroy'], $inventoryGuards);

$router->add('GET', '/api/v1/payments/verification-statuses', [$verificationStatusController, 'index'], $inventoryGuards);
$router->add('GET', '/api/v1/payments/verification-statuses/{id}', [$verificationStatusController, 'show'], $inventoryGuards);
$router->add('POST', '/api/v1/payments/verification-statuses', [$verificationStatusController, 'store'], $inventoryGuards);
$router->add('PATCH', '/api/v1/payments/verification-statuses/{id}', [$verificationStatusController, 'update'], $inventoryGuards);
$router->add('DELETE', '/api/v1/payments/verification-statuses/{id}', [$verificationStatusController, 'destroy'], $inventoryGuards);

$router->add('GET', '/api/v1/payments/proof-types', [$proofTypeController, 'index'], $inventoryGuards);
$router->add('GET', '/api/v1/payments/proof-types/{id}', [$proofTypeController, 'show'], $inventoryGuards);
$router->add('POST', '/api/v1/payments/proof-types', [$proofTypeController, 'store'], $inventoryGuards);
$router->add('PATCH', '/api/v1/payments/proof-types/{id}', [$proofTypeController, 'update'], $inventoryGuards);
$router->add('DELETE', '/api/v1/payments/proof-types/{id}', [$proofTypeController, 'destroy'], $inventoryGuards);

$router->add('GET', '/api/v1/payments/transactions', [$transactionController, 'index'], $inventoryGuards);
$router->add('GET', '/api/v1/payments/transactions/{id}', [$transactionController, 'show'], $inventoryGuards);
$router->add('POST', '/api/v1/payments/transactions', [$transactionController, 'store'], $inventoryGuards);
$router->add('PATCH', '/api/v1/payments/transactions/{id}', [$transactionController, 'update'], $inventoryGuards);

try {
    $router->dispatch($request);
} catch (HttpException $exception) {
    Response::json(['error' => $exception->getMessage()], $exception->getStatusCode())->send();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    Response::json(['error' => 'Internal Server Error'], 500)->send();
}
