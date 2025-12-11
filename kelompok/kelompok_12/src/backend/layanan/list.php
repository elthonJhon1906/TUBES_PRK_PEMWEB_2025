<?php
session_start();
require_once '../../koneksi/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, code, name, category, description, base_price, unit, estimated_minutes
                           FROM services WHERE is_active = 1 ORDER BY category, name");
    $stmt->execute();
    $services = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $services
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengambil data layanan',
        'detail' => $e->getMessage()
    ]);
}
