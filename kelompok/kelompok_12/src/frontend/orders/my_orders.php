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

$customerId = $_SESSION['user']['id'];
$order_success = $_SESSION['order_success'] ?? null;
unset($_SESSION['order_success']);

$sql = "SELECT o.*, 
               GROUP_CONCAT(CONCAT(COALESCE(s.name,p.name),'|',oi.quantity,'|',oi.item_type) ORDER BY oi.id SEPARATOR '~~') AS items
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.id
        LEFT JOIN services s ON oi.service_id = s.id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.customer_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$customerId]);
$orders = $stmt->fetchAll();

$ongoing = [];
$history = [];
foreach ($orders as $order) {
    if (in_array($order['status'], ['pending', 'processing', 'ready'])) {
        $ongoing[] = $order;
    } else {
        $history[] = $order;
    }
}

function parseItems(?string $raw): array {
    if (!$raw) return [];
    $items = [];
    foreach (explode('~~', $raw) as $chunk) {
        [$name, $qty, $type] = array_pad(explode('|', $chunk), 3, '');
        if ($name === '') continue;
        $items[] = [
            'name' => $name,
            'quantity' => (float)$qty,
            'type' => $type
        ];
    }
    return $items;
}

function statusBadge(string $status): array {
    $map = [
        'pending' => ['bg-amber-100 text-amber-700', 'Pending'],
        'processing' => ['bg-blue-100 text-blue-700', 'Processing'],
        'ready' => ['bg-emerald-100 text-emerald-700', 'Ready'],
        'completed' => ['bg-green-100 text-green-700', 'Completed'],
        'cancelled' => ['bg-rose-100 text-rose-700', 'Cancelled'],
    ];
    return $map[$status] ?? ['bg-slate-100 text-slate-600', ucfirst($status)];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - NPC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tab-btn {
            color: #94a3b8;
            border-bottom: 2px solid transparent;
        }
        .tab-btn.tab-active {
            color: #0f172a;
            border-color: #0f172a;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">
    <?php include '../../sidebar/sidebar.php'; ?>
    <?php include '../../header/header.php'; ?>

    <main class="md:ml-64 min-h-screen p-6 md:p-10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
            <div>
                <p class="text-xs uppercase text-slate-400 tracking-widest font-bold mb-1">Order Tracking</p>
                <h1 class="text-3xl font-extrabold text-slate-900">My Orders</h1>
                <p class="text-slate-500">Pantau status dan riwayat pesanan Anda.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="../order/catalog.php" class="px-4 py-2 rounded-full border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-white">Shop Catalog</a>
                <a href="#" class="px-4 py-2 rounded-full bg-slate-900 text-white text-sm font-semibold shadow">My Orders</a>
            </div>
        </div>

        <?php if ($order_success): ?>
            <div class="mb-6 px-4 py-3 rounded-2xl border bg-emerald-50 border-emerald-200 text-emerald-700">
                <?php echo htmlspecialchars($order_success['message']); ?> • Pickup Code: <strong><?php echo htmlspecialchars($order_success['pickup_code']); ?></strong>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center gap-8 border-b border-slate-100 mb-6 text-sm font-semibold text-slate-400">
                <button class="pb-4 flex items-center gap-2 tab-btn tab-active" data-target="ongoing">
                    <i class="fa-solid fa-clipboard-list"></i> Ongoing Orders
                </button>
                <button class="pb-4 flex items-center gap-2 tab-btn" data-target="history">
                    <i class="fa-solid fa-clock-rotate-left"></i> Order History
                </button>
            </div>

            <div id="tab-ongoing" class="tab-panel space-y-4">
                <?php if (empty($ongoing)): ?>
                    <div class="text-center text-slate-400 py-10">Belum ada pesanan aktif.</div>
                <?php else: ?>
                    <?php foreach ($ongoing as $order): $badge = statusBadge($order['status']); ?>
                        <div class="border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                    <p class="text-sm text-slate-400">Order Code</p>
                                    <h3 class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($order['order_code']); ?></h3>
                                    <p class="text-xs text-slate-400"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $badge[0]; ?>"><?php echo $badge[1]; ?></span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Pembayaran: <?php echo ucfirst($order['payment_status']); ?></span>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-slate-400">Total Amount</p>
                                    <p class="text-2xl font-extrabold text-slate-900"><?php echo format_rupiah($order['total_amount']); ?></p>
                                    <?php if (!in_array($order['payment_status'], ['paid', 'verified'])): ?>
                                        <a href="../order/transaction.php?order_id=<?php echo $order['id']; ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-900 border border-slate-200 rounded-full px-3 py-1 mt-2 hover:bg-slate-900 hover:text-white transition">
                                            <i class="fa-solid fa-credit-card"></i> Complete Payment
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-slate-600">
                                <?php foreach (parseItems($order['items']) as $item): ?>
                                    <div class="flex justify-between border-b border-slate-100 py-2 last:border-b-0">
                                        <span><?php echo htmlspecialchars($item['name']); ?> <span class="text-xs text-slate-400">(<?php echo strtoupper($item['type']); ?>)</span></span>
                                        <span class="font-semibold">x<?php echo rtrim(rtrim(number_format($item['quantity'], 2, ',', '.'), '0'), ','); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div id="tab-history" class="tab-panel hidden space-y-4">
                <?php if (empty($history)): ?>
                    <div class="text-center text-slate-400 py-10">Belum ada riwayat pesanan.</div>
                <?php else: ?>
                    <?php foreach ($history as $order): $badge = statusBadge($order['status']); ?>
                        <div class="border border-slate-100 rounded-2xl p-5">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                    <p class="text-sm text-slate-400">Order Code</p>
                                    <h3 class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($order['order_code']); ?></h3>
                                    <p class="text-xs text-slate-400"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $badge[0]; ?>"><?php echo $badge[1]; ?></span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Pembayaran: <?php echo ucfirst($order['payment_status']); ?></span>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-slate-400">Total Amount</p>
                                    <p class="text-2xl font-extrabold text-slate-900"><?php echo format_rupiah($order['total_amount']); ?></p>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-slate-600">
                                <?php foreach (parseItems($order['items']) as $item): ?>
                                    <div class="flex justify-between border-b border-slate-100 py-2 last:border-b-0">
                                        <span><?php echo htmlspecialchars($item['name']); ?> <span class="text-xs text-slate-400">(<?php echo strtoupper($item['type']); ?>)</span></span>
                                        <span class="font-semibold">x<?php echo rtrim(rtrim(number_format($item['quantity'], 2, ',', '.'), '0'), ','); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabPanels = document.querySelectorAll('.tab-panel');

        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.target;
                tabButtons.forEach(b => b.classList.remove('tab-active'));
                btn.classList.add('tab-active');
                tabPanels.forEach(panel => {
                    panel.classList.toggle('hidden', panel.id !== `tab-${target}`);
                });
            });
        });
    </script>
</body>
</html>
