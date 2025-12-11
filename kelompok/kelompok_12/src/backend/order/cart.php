<?php
session_start();
require_once '../../koneksi/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../../frontend/login/login.php');
    exit;
}

$role = strtoupper($_SESSION['user']['role'] ?? '');
$allowed_cart_roles = ['CUSTOMER', 'ADMIN', 'OWNER', 'STAFF'];
if (!in_array($role, $allowed_cart_roles, true)) {
    header('Location: ../../frontend/dashboard/dashboard.php');
    exit;
}

$allowedScopes = ['customer', 'pos'];
$scope = strtolower($_POST['scope'] ?? $_GET['scope'] ?? 'customer');
if (!in_array($scope, $allowedScopes, true)) {
    $scope = 'customer';
}
$pos_scope_roles = ['ADMIN', 'OWNER', 'STAFF'];
if ($scope === 'pos' && !in_array($role, $pos_scope_roles, true)) {
    $scope = 'customer';
}
$cartKey = $scope === 'pos' ? 'order_cart_pos' : 'order_cart';
$flashKey = $scope === 'pos' ? 'order_cart_flash_pos' : 'order_cart_flash';

$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '../../frontend/order/catalog.php';
if (stripos($redirect, 'http') === 0) {
    $redirect = '../../frontend/order/catalog.php';
}

if (!isset($_SESSION[$cartKey]) || !is_array($_SESSION[$cartKey])) {
    $_SESSION[$cartKey] = [];
}

$isAjax = isAjaxRequest();

$action = $_POST['action'] ?? $_GET['action'] ?? null;
if (!$action) {
    respondAndExit([
        'status' => 'error',
        'message' => 'Aksi keranjang tidak dikenal.'
    ], $isAjax, $redirect, $flashKey);
}

try {
    switch ($action) {
        case 'add':
            $result = handleAdd($pdo, $cartKey);
            respondAndExit($result, $isAjax, $redirect, $flashKey);
            break;
        case 'update':
            $result = handleUpdate($cartKey);
            respondAndExit($result, $isAjax, $redirect, $flashKey);
            break;
        case 'remove':
            $result = handleRemove($cartKey);
            respondAndExit($result, $isAjax, $redirect, $flashKey);
            break;
        case 'clear':
            $result = handleClear($cartKey);
            respondAndExit($result, $isAjax, $redirect, $flashKey);
            break;
        default:
            respondAndExit([
                'status' => 'error',
                'message' => 'Aksi keranjang tidak dikenali.'
            ], $isAjax, $redirect, $flashKey);
    }
} catch (Exception $e) {
    respondAndExit([
        'status' => 'error',
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ], $isAjax, $redirect, $flashKey);
}

function handleAdd(PDO $pdo, string $cartKey): array
{
    $itemType = $_POST['item_type'] ?? '';
    $itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (float)$_POST['quantity'] : 1;
    if ($quantity <= 0) {
        $quantity = 1;
    }
    $specifications = trim((string)($_POST['specifications'] ?? ''));

    $fileData = handleFileUpload($_FILES['upload_file'] ?? null);
    $uploadType = empty($fileData) ? 'none' : 'file';

    $item = fetchItem($pdo, $itemType, $itemId);
    if (!$item) {
        throw new RuntimeException('Item tidak ditemukan atau tidak aktif.');
    }

    $baseKey = $itemType . '-' . $itemId;
    $requiresUniqueKey = ($uploadType !== 'none') || ($specifications !== '');
    $key = $requiresUniqueKey ? $baseKey . '-' . generateCartSuffix() : $baseKey;

    if (!isset($_SESSION[$cartKey][$key])) {
        $_SESSION[$cartKey][$key] = [
            'item_type' => $itemType,
            'item_id' => $itemId,
            'name' => $item['name'],
            'unit' => $item['unit'],
            'price' => $item['price'],
            'quantity' => 0,
            'specifications' => '',
            'upload_type' => 'none',
            'file_path' => null,
            'file_name' => null,
            'file_mime' => null,
            'file_link' => null,
        ];
    }

    $_SESSION[$cartKey][$key]['quantity'] += $quantity;
    $_SESSION[$cartKey][$key]['subtotal'] = $_SESSION[$cartKey][$key]['quantity'] * $_SESSION[$cartKey][$key]['price'];

    if ($specifications !== '') {
        $_SESSION[$cartKey][$key]['specifications'] = $specifications;
    }

    if ($uploadType === 'file' && !empty($fileData)) {
        $_SESSION[$cartKey][$key]['upload_type'] = 'file';
        $_SESSION[$cartKey][$key]['file_path'] = $fileData['file_path'];
        $_SESSION[$cartKey][$key]['file_name'] = $fileData['file_name'];
        $_SESSION[$cartKey][$key]['file_mime'] = $fileData['file_mime'];
    }

    return [
        'status' => 'success',
        'message' => $item['name'] . ' ditambahkan ke keranjang.',
        'cart' => getCartSnapshot($cartKey),
    ];
}

function handleUpdate(string $cartKey): array
{
    $key = $_POST['item_key'] ?? '';
    if ($key === '' || !isset($_SESSION[$cartKey][$key])) {
        throw new RuntimeException('Item keranjang tidak ditemukan.');
    }

    $quantity = isset($_POST['quantity']) ? (float)$_POST['quantity'] : 1;
    if ($quantity <= 0) {
        deleteUploadedFile($_SESSION[$cartKey][$key]);
        unset($_SESSION[$cartKey][$key]);
        $message = 'Item dihapus dari keranjang.';
    } else {
        $_SESSION[$cartKey][$key]['quantity'] = $quantity;
        $_SESSION[$cartKey][$key]['subtotal'] = $quantity * $_SESSION[$cartKey][$key]['price'];
        $message = 'Jumlah item diperbarui.';
    }

    return [
        'status' => 'success',
        'message' => $message,
        'cart' => getCartSnapshot($cartKey),
    ];
}

function handleRemove(string $cartKey): array
{
    $key = $_POST['item_key'] ?? '';
    if ($key === '' || !isset($_SESSION[$cartKey][$key])) {
        throw new RuntimeException('Item keranjang tidak ditemukan.');
    }

    deleteUploadedFile($_SESSION[$cartKey][$key]);
    unset($_SESSION[$cartKey][$key]);

    return [
        'status' => 'success',
        'message' => 'Item dihapus dari keranjang.',
        'cart' => getCartSnapshot($cartKey),
    ];
}

function handleClear(string $cartKey): array
{
    foreach ($_SESSION[$cartKey] as $item) {
        deleteUploadedFile($item);
    }
    $_SESSION[$cartKey] = [];

    return [
        'status' => 'success',
        'message' => 'Keranjang berhasil dikosongkan.',
        'cart' => getCartSnapshot($cartKey),
    ];
}

function fetchItem(PDO $pdo, string $itemType, int $itemId): ?array
{
    if ($itemType === 'service') {
        $stmt = $pdo->prepare('SELECT name, unit, base_price as price FROM services WHERE id = ? AND is_active = 1');
    } elseif ($itemType === 'product') {
        $stmt = $pdo->prepare('SELECT name, unit, selling_price as price FROM products WHERE id = ? AND is_active = 1');
    } else {
        return null;
    }

    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    return $item ?: null;
}

function getCartSnapshot(string $cartKey): array
{
    $items = [];
    $totalQty = 0;
    $totalAmount = 0;

    foreach ($_SESSION[$cartKey] as $key => $item) {
        $quantity = (float)($item['quantity'] ?? 0);
        $price = (float)($item['price'] ?? 0);
        $subtotal = $quantity * $price;

        $items[] = [
            'key' => $key,
            'item_type' => $item['item_type'] ?? 'service',
            'item_id' => $item['item_id'] ?? 0,
            'name' => $item['name'] ?? '',
            'unit' => $item['unit'] ?? '',
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'specifications' => $item['specifications'] ?? '',
            'upload_type' => $item['upload_type'] ?? 'none',
            'file_path' => $item['file_path'] ?? null,
            'file_name' => $item['file_name'] ?? null,
            'file_mime' => $item['file_mime'] ?? null,
            'file_link' => $item['file_link'] ?? null,
        ];

        $totalQty += $quantity;
        $totalAmount += $subtotal;
    }

    return [
        'items' => $items,
        'total_quantity' => $totalQty,
        'total_amount' => $totalAmount,
    ];
}

function isAjaxRequest(): bool
{
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return true;
    }
    if (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        return true;
    }
    return false;
}

function respondAndExit(array $payload, bool $isAjax, string $redirect, string $flashKey): void
{
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($payload);
    } else {
        $_SESSION[$flashKey] = ['status' => $payload['status'] ?? 'info', 'message' => $payload['message'] ?? ''];
        header("Location: {$redirect}");
    }
    exit;
}

function handleFileUpload(?array $file): array
{
    if (!$file || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Gagal mengunggah file (kode: ' . $file['error'] . ').');
    }

    $maxSize = 20 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxSize) {
        throw new RuntimeException('Ukuran file melebihi batas 20MB.');
    }

    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar'];
    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if ($extension !== '' && !in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Format file tidak didukung.');
    }

    $uploadDir = ensureUploadDirectory();
    $uniqueName = 'order_' . date('Ymd_His') . '_' . generateCartSuffix();
    if ($extension !== '') {
        $uniqueName .= '.' . $extension;
    }

    $destination = $uploadDir . '/' . $uniqueName;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Gagal menyimpan file upload.');
    }

    return [
        'file_path' => 'assets/uploads/files/' . $uniqueName,
        'file_name' => $file['name'] ?? $uniqueName,
        'file_mime' => $file['type'] ?? 'application/octet-stream',
    ];
}

function deleteUploadedFile(array $item): void
{
    if (($item['upload_type'] ?? 'none') !== 'file') {
        return;
    }

    $relativePath = $item['file_path'] ?? '';
    if ($relativePath === '') {
        return;
    }

    $root = getProjectRoot();
    $fullPath = realpath($root . '/' . ltrim($relativePath, '/'));
    if ($fullPath === false) {
        return;
    }

    $uploadsDir = ensureUploadDirectory();
    if (strpos($fullPath, $uploadsDir) !== 0) {
        return;
    }

    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

function ensureUploadDirectory(): string
{
    $dir = getProjectRoot() . '/assets/uploads/files';
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Gagal menyiapkan folder upload.');
        }
    }
    return $dir;
}

function getProjectRoot(): string
{
    static $root = null;
    if ($root === null) {
        $root = realpath(__DIR__ . '/../../..');
        if ($root === false) {
            $root = dirname(__DIR__, 3);
        }
    }
    return $root;
}

function generateCartSuffix(): string
{
    try {
        return bin2hex(random_bytes(6));
    } catch (Exception $e) {
        return substr(md5((string)microtime(true) . mt_rand()), 0, 12);
    }
}
