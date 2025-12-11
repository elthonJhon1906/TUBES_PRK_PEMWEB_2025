<?php
session_start();
require_once '../../koneksi/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login/login.php');
    exit;
}

$role = strtoupper($_SESSION['user']['role']);
if ($role !== 'CUSTOMER') {
    header('Location: ../dashboard/dashboard.php');
    exit;
}

$full_name = $_SESSION['user']['full_name'] ?? 'Customer';

try {
    $stmtServices = $pdo->prepare("SELECT id, code, name, category, description, base_price, unit
                                    FROM services WHERE is_active = 1 ORDER BY category, name");
    $stmtServices->execute();
    $services = $stmtServices->fetchAll();
} catch (Exception $e) {
    $services = [];
}

function badgeColor($category) {
    $palette = [
        'PRINTING' => 'bg-green-100 text-green-700',
        'COPYING' => 'bg-emerald-100 text-emerald-700',
        'DESIGN' => 'bg-blue-100 text-blue-700'
    ];
    $key = strtoupper($category);
    return $palette[$key] ?? 'bg-slate-100 text-slate-600';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Layanan - NPC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">
    <?php include '../../sidebar/sidebar.php'; ?>
    <?php include '../../header/header.php'; ?>

    <main class="md:ml-64 min-h-screen p-6 md:p-10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
            <div>
                <p class="text-xs uppercase text-slate-400 tracking-widest font-bold mb-1">Halo, <?php echo htmlspecialchars($full_name); ?></p>
                <h1 class="text-3xl font-extrabold text-slate-900">Service Catalog</h1>
                <p class="text-slate-500">Pilih layanan printing terbaik untuk dimasukkan ke keranjang order Anda.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="../order/catalog.php" class="px-4 py-2 rounded-full border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-white">Order Mix</a>
                <a href="../products/catalog.php" class="px-4 py-2 rounded-full bg-slate-900 text-white text-sm font-semibold shadow">Lihat Produk ATK</a>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center gap-8 border-b border-slate-100 mb-6 text-sm font-semibold">
                <span class="pb-4 flex items-center gap-2 text-slate-900 border-b-2 border-slate-900">
                    <i class="fa-solid fa-print"></i> Printing Services
                </span>
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                <?php if (count($services) === 0): ?>
                    <div class="col-span-full text-center text-slate-400 py-10">Belum ada layanan aktif.</div>
                <?php else: ?>
                    <?php foreach ($services as $srv): ?>
                        <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
                            <span class="text-xs font-bold px-3 py-1 rounded-full <?php echo badgeColor($srv['category']); ?>">
                                <?php echo strtoupper($srv['category']); ?>
                            </span>
                            <h3 class="mt-4 text-lg font-bold text-slate-900"><?php echo htmlspecialchars($srv['name']); ?></h3>
                            <p class="text-sm text-slate-500 mb-4"><?php echo htmlspecialchars($srv['description'] ?? ''); ?></p>
                            <div class="text-xs uppercase text-slate-400 tracking-wider">Price per <?php echo htmlspecialchars($srv['unit']); ?></div>
                            <div class="text-xl font-extrabold text-slate-900 mb-4"><?php echo format_rupiah($srv['base_price']); ?></div>
                            <button class="w-10 h-10 rounded-full border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-900 hover:text-white transition" title="Tambah ke Pesanan">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
