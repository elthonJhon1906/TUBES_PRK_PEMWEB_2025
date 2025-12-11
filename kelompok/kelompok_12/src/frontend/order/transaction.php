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

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($orderId === 0 && isset($_SESSION['order_success']['order_id'])) {
    $orderId = (int)$_SESSION['order_success']['order_id'];
}

if ($orderId <= 0) {
    header('Location: ../order/my_orders.php');
    exit;
}

try {
    $stmtOrder = $pdo->prepare('SELECT o.*, u.full_name AS customer_name FROM orders o JOIN users u ON o.customer_id = u.id WHERE o.id = ? AND o.customer_id = ? LIMIT 1');
    $stmtOrder->execute([$orderId, $_SESSION['user']['id']]);
    $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header('Location: ../order/my_orders.php');
        exit;
    }

    $stmtItems = $pdo->prepare('SELECT oi.*, COALESCE(s.name, p.name) AS item_name, COALESCE(s.unit, p.unit, "unit") AS unit
                                 FROM order_items oi
                                 LEFT JOIN services s ON oi.service_id = s.id
                                 LEFT JOIN products p ON oi.product_id = p.id
                                 WHERE oi.order_id = ?');
    $stmtItems->execute([$orderId]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    $stmtTransactions = $pdo->prepare('SELECT * FROM order_payment_logs WHERE order_id = ? ORDER BY created_at DESC');
    $stmtTransactions->execute([$orderId]);
    $transactions = $stmtTransactions->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $items = [];
    $transactions = [];
}

$transaction_flash = $_SESSION['transaction_flash'] ?? null;
unset($_SESSION['transaction_flash']);

$order_success = $_SESSION['order_success'] ?? null;
if ($order_success && (int)$order_success['order_id'] !== $orderId) {
    $order_success = null;
}

$paymentCompleted = in_array($order['payment_status'], ['paid', 'verified'], true);
$step = isset($_GET['step']) ? max(1, min(2, (int)$_GET['step'])) : 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Pembayaran - NPC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .step-indicator {
            background-color: #e2e8f0;
            color: #475569;
        }
        .step-indicator.active {
            background-color: #0f172a;
            color: #fff;
        }
        .step-card.active {
            border-color: #0f172a;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">
    <?php include '../../sidebar/sidebar.php'; ?>
    <?php include '../../header/header.php'; ?>

    <main class="md:ml-64 min-h-screen p-6 md:p-10 transition-all">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
            <div>
                <p class="text-xs uppercase text-slate-400 tracking-widest font-bold mb-1">Checkout</p>
                <h1 class="text-3xl font-extrabold text-slate-900">Pembayaran Order</h1>
                <p class="text-slate-500">Selesaikan instruksi dan unggah bukti pembayaran Anda.</p>
            </div>
            <div class="flex items-center gap-2 text-sm font-semibold">
                <a href="../order/catalog.php" class="px-4 py-2 rounded-full border border-slate-300 text-slate-600 hover:bg-white">Back to Catalog</a>
                <a href="../order/my_orders.php" class="px-4 py-2 rounded-full bg-slate-900 text-white shadow">My Orders</a>
            </div>
        </div>

        <?php if ($order_success): ?>
            <div class="mb-4 px-4 py-3 rounded-2xl border bg-emerald-50 border-emerald-200 text-emerald-700">
                <?php echo htmlspecialchars($order_success['message']); ?>
            </div>
        <?php endif; ?>

        <?php if ($transaction_flash): ?>
            <div class="mb-4 px-4 py-3 rounded-2xl border <?php echo $transaction_flash['status'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700'; ?>">
                <?php echo htmlspecialchars($transaction_flash['message']); ?>
                <?php if (!empty($transaction_flash['code'])): ?>
                    <span class="font-semibold"> • Kode: <?php echo htmlspecialchars($transaction_flash['code']); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="xl:col-span-1">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sticky top-4">
                    <p class="text-xs uppercase text-slate-400 font-semibold">Order Summary</p>
                    <h2 class="text-2xl font-bold text-slate-900 mt-1">Kode: <?php echo htmlspecialchars($order['order_code']); ?></h2>
                    <p class="text-sm text-slate-500 mb-4">Pickup Code: <strong><?php echo htmlspecialchars($order['pickup_code']); ?></strong></p>

                    <div class="space-y-3 max-h-[320px] overflow-y-auto pr-1">
                        <?php foreach ($items as $item): ?>
                            <div class="flex items-start justify-between border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($item['item_name']); ?></p>
                                    <p class="text-xs text-slate-400 uppercase tracking-wide"><?php echo strtoupper($item['item_type']); ?> • <?php echo htmlspecialchars($item['unit']); ?></p>
                                </div>
                                <div class="text-right text-sm">
                                    <p class="font-semibold">x<?php echo rtrim(rtrim(number_format($item['quantity'], 2, ',', '.'), '0'), ','); ?></p>
                                    <p class="text-slate-400"><?php echo format_rupiah($item['unit_price']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-6 space-y-2 text-sm">
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Status Order</span>
                            <span class="font-semibold text-slate-900"><?php echo ucfirst($order['status']); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Status Pembayaran</span>
                            <span class="font-semibold text-slate-900"><?php echo ucfirst($order['payment_status']); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-lg font-extrabold text-slate-900">
                            <span>Total</span>
                            <span><?php echo format_rupiah($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <div class="flex items-center gap-4">
                        <div class="step-indicator w-10 h-10 rounded-full flex items-center justify-center font-semibold <?php echo $step === 1 ? 'active' : ''; ?>">1</div>
                        <div class="flex-1">
                            <p class="text-xs uppercase text-slate-400 font-semibold">Order Notes / Instructions</p>
                            <h3 class="text-xl font-bold text-slate-900">Catatan Tambahan</h3>
                        </div>
                    </div>
                    <div id="step-1" class="mt-6 step-card <?php echo $step === 1 ? 'active' : ''; ?> border border-slate-200 rounded-2xl p-5">
                        <form action="../../backend/order/update_notes.php" method="POST" class="space-y-4">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <label class="block text-sm font-semibold text-slate-600">Spesifikasi Pesanan</label>
                            <textarea name="notes" rows="4" class="w-full border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-slate-900" placeholder="Contoh: Cetak hitam-putih, jilid spiral, ambil sore."><?php echo htmlspecialchars($order['notes'] ?? ''); ?></textarea>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button type="submit" class="flex-1 text-center py-3 rounded-2xl border border-slate-200 font-semibold text-slate-700 hover:bg-slate-50">Simpan Catatan</button>
                                <button type="button" id="to-step-2" class="flex-1 text-center py-3 rounded-2xl bg-slate-900 text-white font-semibold">Next: Payment →</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <div class="flex items-center gap-4">
                        <div class="step-indicator w-10 h-10 rounded-full flex items-center justify-center font-semibold <?php echo $step === 2 ? 'active' : ''; ?>">2</div>
                        <div class="flex-1">
                            <p class="text-xs uppercase text-slate-400 font-semibold">Bank Transfer Details</p>
                            <h3 class="text-xl font-bold text-slate-900">Unggah Bukti Pembayaran</h3>
                        </div>
                    </div>
                    <div id="step-2" class="mt-6 step-card <?php echo $step === 2 ? 'active' : ''; ?> border border-slate-200 rounded-2xl p-5">
                        <?php if ($paymentCompleted): ?>
                            <div class="mb-4 px-4 py-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                                Pembayaran sudah diterima. Anda masih dapat mengunggah ulang bukti jika diperlukan.
                            </div>
                        <?php endif; ?>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div id="bank-details-card" class="border border-slate-100 rounded-2xl p-4">
                                <p class="text-xs uppercase text-slate-400">Bank</p>
                                <p class="text-lg font-bold text-slate-900">BCA</p>
                                <p class="text-xs uppercase text-slate-400 mt-4">No. Rekening</p>
                                <p class="text-lg font-bold text-slate-900">123 456 7890</p>
                                <p class="text-xs uppercase text-slate-400 mt-4">Atas Nama</p>
                                <p class="text-lg font-semibold text-slate-900">Nagoya Print & Copy</p>
                            </div>
                            <div class="border border-slate-100 rounded-2xl p-4">
                                <p class="text-xs uppercase text-slate-400">Metode Lain</p>
                                <p class="text-sm text-slate-500">Pembayaran tunai tersedia di kasir saat pengambilan. Tandai pilihan di bawah.</p>
                                <div class="mt-4 text-sm text-slate-500">
                                    <p>Pastikan jumlah transfer sesuai total tagihan.</p>
                                    <p>Unggah bukti transfer agar tim kami dapat memverifikasi.</p>
                                </div>
                            </div>
                        </div>
                        <form action="../../backend/transactions/process.php" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <input type="hidden" name="amount" value="<?php echo $order['total_amount']; ?>">
                            <label class="text-sm font-semibold text-slate-600">Pilih Metode Pembayaran</label>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                    <input type="radio" name="method" value="transfer" checked class="text-slate-900">
                                    Transfer Bank
                                </label>
                                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                    <input type="radio" name="method" value="cash">
                                    Bayar Tunai
                                </label>
                            </div>
                            <div id="proof-upload-section" class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-600 mb-2">Unggah Bukti Pembayaran</label>
                                <input type="file" name="proof_image" accept="image/*" class="w-full border border-dashed border-slate-300 rounded-2xl px-4 py-3 text-sm">
                                <p class="text-xs text-slate-400 mt-2">Format yang diterima: JPG, PNG, PDF maks 5MB.</p>
                            </div>
                            <?php
                                $transferButtonLabel = $paymentCompleted ? 'Kirim Ulang Bukti / Update Pembayaran' : 'Submit Order & Kirim Bukti';
                                $cashButtonLabel = $paymentCompleted ? 'Perbarui Pembayaran Tunai' : 'Submit Order (Tunai)';
                            ?>
                            <button
                                type="submit"
                                <?php echo $paymentCompleted ? '' : ''; ?>
                                id="payment-submit-btn"
                                data-transfer-label="<?php echo $transferButtonLabel; ?>"
                                data-cash-label="<?php echo $cashButtonLabel; ?>"
                                class="w-full py-3 rounded-2xl font-semibold text-white <?php echo $paymentCompleted ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-slate-900 hover:bg-slate-800'; ?>">
                                <?php echo $transferButtonLabel; ?>
                            </button>
                        </form>
                    </div>
                </div>

                <?php if (!empty($transactions)): ?>
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-xs uppercase text-slate-400 font-semibold">Riwayat Pembayaran</p>
                                <h3 class="text-xl font-bold text-slate-900"><?php echo count($transactions); ?> transaksi</h3>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <?php foreach ($transactions as $trx): ?>
                                <div class="border border-slate-100 rounded-2xl p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($trx['transaction_code']); ?></p>
                                        <p class="text-xs text-slate-400"><?php echo date('d M Y H:i', strtotime($trx['created_at'])); ?> • <?php echo strtoupper($trx['method']); ?></p>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-900"><?php echo format_rupiah($trx['amount']); ?></div>
                                    <div class="text-xs font-semibold px-3 py-1 rounded-full <?php echo $trx['status'] === 'pending' ? 'bg-amber-100 text-amber-700' : ($trx['status'] === 'valid' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'); ?>">
                                        <?php echo ucfirst($trx['status']); ?>
                                    </div>
                                    <?php if (!empty($trx['proof_image'])):
                                        $proofPath = ltrim($trx['proof_image'], '/');
                                        if (strpos($proofPath, 'src/') === 0) {
                                            $proofPath = substr($proofPath, 4);
                                        }
                                        $proofToken = urlencode(base64_encode($proofPath));
                                    ?>
                                        <a href="proof_viewer.php?token=<?php echo $proofToken; ?>" target="_blank" class="text-xs text-slate-600 underline">Lihat Bukti</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        const nextBtn = document.getElementById('to-step-2');
        const stepIndicators = document.querySelectorAll('.step-indicator');
        const stepCards = document.querySelectorAll('.step-card');
    const methodRadios = document.querySelectorAll('input[name="method"]');
    const proofUploadSection = document.getElementById('proof-upload-section');
    const bankDetailsCard = document.getElementById('bank-details-card');
    const submitBtn = document.getElementById('payment-submit-btn');

        const setStep = (targetStep) => {
            stepIndicators.forEach((indicator, index) => {
                if (index + 1 <= targetStep) {
                    indicator.classList.add('active');
                } else {
                    indicator.classList.remove('active');
                }
            });
            stepCards.forEach((card, index) => {
                card.classList.toggle('active', index + 1 === targetStep);
            });
        };

        const togglePaymentUI = () => {
            if (!methodRadios.length || !submitBtn) {
                return;
            }

            const selectedMethod = document.querySelector('input[name="method"]:checked');
            const isCash = selectedMethod && selectedMethod.value === 'cash';

            if (proofUploadSection) {
                proofUploadSection.classList.toggle('hidden', isCash);
            }

            if (bankDetailsCard) {
                bankDetailsCard.classList.toggle('hidden', isCash);
            }

            const targetLabel = isCash ? submitBtn.dataset.cashLabel : submitBtn.dataset.transferLabel;
            if (targetLabel) {
                submitBtn.textContent = targetLabel;
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            setStep(<?php echo $step; ?>);
            togglePaymentUI();
        });

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                setStep(2);
                document.getElementById('step-2').scrollIntoView({ behavior: 'smooth' });
            });
        }

        methodRadios.forEach((radio) => {
            radio.addEventListener('change', togglePaymentUI);
        });
    </script>
</body>
</html>
