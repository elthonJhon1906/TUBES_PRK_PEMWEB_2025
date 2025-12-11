<?php
session_start();
require_once '../../koneksi/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../../frontend/login/login.php');
    exit;
}

$role = strtoupper($_SESSION['user']['role'] ?? '');
$allowed_checkout_roles = ['CUSTOMER', 'ADMIN', 'OWNER', 'STAFF'];
if (!in_array($role, $allowed_checkout_roles, true)) {
    header('Location: ../../frontend/dashboard/dashboard.php');
    exit;
}

$allowedScopes = ['customer', 'pos'];
$scope = strtolower($_POST['scope'] ?? 'customer');
if (!in_array($scope, $allowedScopes, true)) {
    $scope = 'customer';
}
$pos_scope_roles = ['ADMIN', 'OWNER', 'STAFF'];
if ($scope === 'pos' && !in_array($role, $pos_scope_roles, true)) {
    $scope = 'customer';
}
$cartKey = $scope === 'pos' ? 'order_cart_pos' : 'order_cart';
$flashKey = $scope === 'pos' ? 'order_cart_flash_pos' : 'order_cart_flash';
$cartRedirect = '../../frontend/order/catalog.php' . ($scope === 'pos' ? '?mode=pos' : '');

$cart = $_SESSION[$cartKey] ?? [];
if (empty($cart)) {
    $_SESSION[$flashKey] = ['status' => 'error', 'message' => 'Keranjang masih kosong. Tambahkan item terlebih dahulu.'];
    header('Location: ' . $cartRedirect);
    exit;
}

try {
    $pdo->beginTransaction();

    $orderCode  = generateOrderCode($pdo);
    $pickupCode = generatePickupCode();
    $customerId = $_SESSION['user']['id'];
    $notes      = trim($_POST['notes'] ?? '');

    $totalAmount = 0;
    foreach ($cart as $item) {
        $totalAmount += $item['price'] * $item['quantity'];
    }

    $staffId = null;
    if ($role !== 'CUSTOMER') {
        $staffId = $_SESSION['user']['id'];
    }

    $autoCompleteRoles = ['ADMIN', 'OWNER', 'STAFF'];
    $isAutoComplete = ($scope === 'pos' && in_array($role, $autoCompleteRoles, true));
    $statusValue = $isAutoComplete ? 'completed' : 'pending';
    $paymentStatusValue = $isAutoComplete ? 'paid' : 'unpaid';
    $paidAmountValue = $isAutoComplete ? $totalAmount : 0;

    $stmtOrder = $pdo->prepare('INSERT INTO orders (order_code, pickup_code, customer_id, staff_id, total_amount, paid_amount, status, payment_status, notes)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmtOrder->execute([
        $orderCode,
        $pickupCode,
        $customerId,
        $staffId,
        $totalAmount,
        $paidAmountValue,
        $statusValue,
        $paymentStatusValue,
        $notes
    ]);
    $orderId = $pdo->lastInsertId();

    $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, item_type, service_id, product_id, quantity, unit_price, subtotal, specifications, upload_type, file_path, file_name, file_mime, file_link, item_status)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "pending")');

    $stmtLockProduct = $pdo->prepare('SELECT current_stock FROM products WHERE id = ? FOR UPDATE');
    $stmtUpdateProduct = $pdo->prepare('UPDATE products SET current_stock = current_stock - ? WHERE id = ?');
    $productReservations = [];

    foreach ($cart as $item) {
        if ($item['item_type'] !== 'product') {
            continue;
        }

        $productId = $item['item_id'];
        $quantity = (float)$item['quantity'];

        if (!isset($productReservations[$productId])) {
            $stmtLockProduct->execute([$productId]);
            $productRow = $stmtLockProduct->fetch(PDO::FETCH_ASSOC);
            if (!$productRow) {
                throw new Exception('Produk tidak ditemukan atau nonaktif.');
            }
            $productReservations[$productId] = (float)$productRow['current_stock'];
        }

        if ($productReservations[$productId] < $quantity) {
            throw new Exception('Stok ' . $item['name'] . ' tidak mencukupi.');
        }

        $productReservations[$productId] -= $quantity;
    }

    foreach ($cart as $item) {
        $serviceId = $item['item_type'] === 'service' ? $item['item_id'] : null;
        $productId = $item['item_type'] === 'product' ? $item['item_id'] : null;
        $quantity  = $item['quantity'];
        $price     = $item['price'];
        $subtotal  = $quantity * $price;

        $specifications = trim((string)($item['specifications'] ?? ''));
        $uploadType = $item['upload_type'] ?? 'none';
        if (!in_array($uploadType, ['none', 'file', 'link'], true)) {
            $uploadType = 'none';
        }

        $filePath = $item['file_path'] ?? null;
        $fileName = $item['file_name'] ?? null;
        $fileMime = $item['file_mime'] ?? null;
        $fileLink = $item['file_link'] ?? null;

        $stmtItem->execute([
            $orderId,
            $item['item_type'],
            $serviceId,
            $productId,
            $quantity,
            $price,
            $subtotal,
            $specifications !== '' ? $specifications : null,
            $uploadType,
            $filePath,
            $fileName,
            $fileMime,
            $fileLink
        ]);

        if ($productId !== null) {
            $stmtUpdateProduct->execute([$quantity, $productId]);
        }
    }

    recordOrderCreationLog($pdo, $_SESSION['user'], $orderId, $scope, $orderCode, $totalAmount);

    $pdo->commit();

    $_SESSION[$cartKey] = [];
    $_SESSION['order_success'] = [
        'message' => 'Pesanan berhasil dibuat dengan kode ' . $orderCode,
        'order_id' => $orderId,
        'order_code' => $orderCode,
        'pickup_code' => $pickupCode,
        'total' => $totalAmount,
        'mode' => $scope
    ];

    if ($scope === 'pos') {
        header('Location: ../../frontend/dashboard/order_detail.php?id=' . $orderId);
    } else {
        header('Location: ../../frontend/order/transaction.php?order_id=' . $orderId);
    }
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION[$flashKey] = ['status' => 'error', 'message' => 'Gagal membuat pesanan: ' . $e->getMessage()];
    header('Location: ' . $cartRedirect);
    exit;
}

function generateOrderCode(PDO $pdo): string
{
    do {
        $code = 'ORD-' . str_pad((string)random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare('SELECT id FROM orders WHERE order_code = ? LIMIT 1');
        $stmt->execute([$code]);
    } while ($stmt->fetch());

    return $code;
}

function generatePickupCode(): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

function recordOrderCreationLog(PDO $pdo, array $userData, int $orderId, string $scope, string $orderCode, float $totalAmount): void
{
    if (!$orderId || empty($userData['id'])) {
        return;
    }

    $username = $userData['username'] ?? ($userData['full_name'] ?? 'User');
    $role = strtoupper($userData['role'] ?? 'UNKNOWN');
    $fullName = $userData['full_name'] ?? $username;
    $modeLabel = $scope === 'pos' ? 'mode kasir (POS)' : 'katalog online';
    $description = sprintf(
        '%s membuat pesanan melalui %s dengan kode %s senilai %s.',
        $fullName,
        $modeLabel,
        $orderCode,
        format_rupiah($totalAmount)
    );

    $stmt = $pdo->prepare('INSERT INTO system_logs (user_id, username, role, action_type, target_id, description) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $userData['id'],
        $username,
        $role,
        'CREATE_ORDER',
        $orderId,
        $description
    ]);
}
