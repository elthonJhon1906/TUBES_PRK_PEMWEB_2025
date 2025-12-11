<?php
session_start();
require_once '../../koneksi/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../../frontend/login/login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$role = strtoupper($_SESSION['user']['role']);
if ($role !== 'CUSTOMER') {
    header('Location: ../../frontend/dashboard/dashboard.php');
    exit;
}

$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$notes = trim($_POST['notes'] ?? '');

if ($orderId <= 0) {
    $_SESSION['transaction_flash'] = ['status' => 'error', 'message' => 'Order tidak ditemukan.'];
    header('Location: ../../frontend/order/catalog.php');
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE orders SET notes = ?, updated_at = NOW() WHERE id = ? AND customer_id = ?');
    $stmt->execute([$notes, $orderId, $userId]);

    if ($stmt->rowCount() === 0) {
        $_SESSION['transaction_flash'] = ['status' => 'error', 'message' => 'Anda tidak berhak mengubah catatan untuk order ini.'];
    } else {
        $_SESSION['transaction_flash'] = ['status' => 'success', 'message' => 'Catatan order berhasil diperbarui.'];
    }

    header('Location: ../../frontend/order/transaction.php?order_id=' . $orderId . '&step=1');
    exit;
} catch (Exception $e) {
    $_SESSION['transaction_flash'] = ['status' => 'error', 'message' => 'Gagal menyimpan catatan: ' . $e->getMessage()];
    header('Location: ../../frontend/order/transaction.php?order_id=' . $orderId . '&step=1');
    exit;
}
