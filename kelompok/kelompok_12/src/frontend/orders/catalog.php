<?php
session_start();
require_once '../../koneksi/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login/login.php');
    exit;
}

$role = strtoupper($_SESSION['user']['role']);
$allowed_catalog_roles = ['CUSTOMER', 'ADMIN', 'OWNER', 'STAFF'];
if (!in_array($role, $allowed_catalog_roles, true)) {
    header('Location: ../dashboard/dashboard.php');
    exit;
}

$pos_roles = ['ADMIN', 'OWNER', 'STAFF'];
$requested_mode = (isset($_GET['mode']) && $_GET['mode'] === 'pos') ? 'pos' : 'shop';
$force_pos_mode = in_array($role, $pos_roles, true);

if (!$force_pos_mode && $requested_mode === 'pos') {
    header('Location: catalog.php');
    exit;
}

if ($force_pos_mode && $requested_mode !== 'pos') {
    header('Location: catalog.php?mode=pos');
    exit;
}

$mode = $requested_mode;

$cart_scope = $mode === 'pos' ? 'pos' : 'customer';
$cart_session_key = $cart_scope === 'pos' ? 'order_cart_pos' : 'order_cart';
$cart_flash_key = $cart_scope === 'pos' ? 'order_cart_flash_pos' : 'order_cart_flash';
$catalog_redirect = '../../frontend/order/catalog.php' . ($mode === 'pos' ? '?mode=pos' : '');

try {
    $stmtProducts = $pdo->prepare("SELECT id, code, name, category, unit, current_stock, selling_price, image
                                    FROM products WHERE is_active = 1 ORDER BY name ASC");
    $stmtProducts->execute();
    $products = $stmtProducts->fetchAll();

    $stmtServices = $pdo->prepare("SELECT id, code, name, category, description, base_price, unit, image
                                    FROM services WHERE is_active = 1 ORDER BY category, name ASC");
    $stmtServices->execute();
    $services = $stmtServices->fetchAll();
} catch (Exception $e) {
    $products = [];
    $services = [];
}

function badgeColor($category) {
    $palette = [
        'PRINTING' => 'bg-green-100 text-green-700',
        'COPYING' => 'bg-emerald-100 text-emerald-700',
        'DESIGN' => 'bg-blue-100 text-blue-700',
        'STATIONERY' => 'bg-orange-100 text-orange-700',
        'PAPER' => 'bg-yellow-100 text-yellow-700'
    ];
    $key = strtoupper($category);
    return $palette[$key] ?? 'bg-slate-100 text-slate-600';
}

function resolveImagePath($filename, $subDir, $default = 'default.png') {
    $name = trim((string)($filename ?? ''));
    if ($name === '') {
        $name = $default;
    }

    $safeName = basename($name);
    $cleanSubDir = trim($subDir, '/');

    return '../../../' . $cleanSubDir . '/' . $safeName;
}

function getServiceImagePath($filename) {
    return resolveImagePath($filename, 'assets/uploads/services');
}

function getProductImagePath($filename) {
    return resolveImagePath($filename, 'assets/uploads/products');
}

$full_name = $_SESSION['user']['full_name'] ?? 'Customer';
$cart_items = $_SESSION[$cart_session_key] ?? [];
$cart_flash = $_SESSION[$cart_flash_key] ?? null;
unset($_SESSION[$cart_flash_key]);

$cart_total = 0;
$cart_quantity = 0;
foreach ($cart_items as $ci) {
    $cart_total += $ci['price'] * $ci['quantity'];
    $cart_quantity += $ci['quantity'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Layanan & Produk - NPC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .tab-active { color: #0f172a; }
        .tab-active::after {
            content: '';
            display: block;
            width: 100%;
            height: 3px;
            margin-top: 8px;
            border-radius: 999px;
            background: #0f172a;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">
    <?php include '../../sidebar/sidebar.php'; ?>
    <?php include '../../header/header.php'; ?>

    <main class="md:ml-64 min-h-screen p-6 md:p-10 transition-all">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
            <div>
                <p class="text-xs uppercase text-slate-400 tracking-widest font-bold mb-1">Halo, <?php echo htmlspecialchars($full_name); ?></p>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">
                    <?php echo $mode === 'pos' ? 'Kasir Input Order' : 'Service Catalog'; ?>
                </h1>
                <p class="text-slate-500">
                    <?php echo $mode === 'pos' ? 'Gunakan mode kasir untuk mencatat pesanan offline dan tandai langsung selesai.' : 'Pilih layanan dan produk untuk ditambahkan ke pesanan Anda.'; ?>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <?php if (!$force_pos_mode): ?>
                    <a href="catalog.php" class="px-4 py-2 rounded-full text-sm font-semibold <?php echo $mode !== 'pos' ? 'bg-slate-900 text-white shadow' : 'border border-slate-300 text-slate-700 hover:bg-white'; ?>">Shop Catalog</a>
                <?php endif; ?>
                <a href="my_orders.php" class="px-4 py-2 rounded-full border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-white">My Orders</a>
                <?php if ($force_pos_mode): ?>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold bg-emerald-600 text-white shadow">Input Order (Kasir)</span>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($mode === 'pos'): ?>
            <div class="mb-4 px-4 py-3 rounded-2xl border bg-emerald-50 border-emerald-200 text-emerald-700 flex items-start gap-3">
                <i class="fa-solid fa-bolt text-lg mt-1"></i>
                <div>
                    <p class="font-semibold">Mode Kasir Aktif</p>
                    <p class="text-sm">Pesanan yang dibuat pada mode ini akan langsung berstatus <strong>Completed</strong> dan pembayaran tercatat lunas untuk transaksi offline.</p>
                    <?php if ($force_pos_mode): ?>
                        <p class="text-xs mt-2">Anda masuk sebagai <?php echo strtolower($role); ?>, sehingga katalog ini terkunci pada mode kasir.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($cart_flash): ?>
            <div class="mb-6 px-4 py-3 rounded-2xl border <?php echo $cart_flash['status'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700'; ?>">
                <?php echo htmlspecialchars($cart_flash['message']); ?>
            </div>
        <?php endif; ?>

        <div id="cart-toast" class="hidden mb-6 px-4 py-3 rounded-2xl border text-sm font-semibold"></div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <div class="flex items-center gap-8 border-b border-slate-100 mb-6 text-sm font-semibold text-slate-400">
                    <button class="pb-4 flex items-center gap-2 tab-btn tab-active" data-target="services">
                        <i class="fa-solid fa-print"></i> Printing Services
                    </button>
                    <button class="pb-4 flex items-center gap-2 tab-btn" data-target="products">
                        <i class="fa-solid fa-box"></i> ATK & Products
                    </button>
                </div>

                <div id="tab-services" class="tab-panel grid gap-6 md:grid-cols-2 xl:grid-cols-2">
                    <?php if (count($services) === 0): ?>
                        <div class="col-span-full text-center text-slate-400 py-10">
                            Belum ada layanan aktif.
                        </div>
                    <?php else: ?>
                        <?php foreach ($services as $srv): ?>
                            <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex flex-col">
                                <div class="relative mb-4 h-40 rounded-2xl overflow-hidden bg-slate-100">
                                    <img src="<?php echo htmlspecialchars(getServiceImagePath($srv['image'] ?? '')); ?>" alt="<?php echo htmlspecialchars($srv['name']); ?>" class="w-full h-full object-cover">
                                    <span class="absolute top-3 left-3 text-xs font-bold px-3 py-1 rounded-full <?php echo badgeColor($srv['category']); ?> backdrop-blur bg-white/70">
                                        <?php echo strtoupper($srv['category']); ?>
                                    </span>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($srv['name']); ?></h3>
                                <p class="text-sm text-slate-500 mb-4">
                                    <?php echo htmlspecialchars($srv['description'] ?? ''); ?>
                                </p>
                                <div class="text-xs uppercase text-slate-400 tracking-wider">Price per <?php echo htmlspecialchars($srv['unit']); ?></div>
                                <div class="text-xl font-extrabold text-slate-900 mb-4">
                                    <?php echo format_rupiah($srv['base_price']); ?>
                                </div>
                                <button type="button"
                                        class="mt-auto w-full flex items-center justify-center gap-2 rounded-xl border border-slate-200 text-slate-600 font-semibold py-2 hover:bg-slate-900 hover:text-white transition"
                                        title="Tambah ke Keranjang"
                                        data-open-upload-modal
                                        data-service-id="<?php echo htmlspecialchars((string)$srv['id']); ?>"
                                        data-service-name="<?php echo htmlspecialchars($srv['name']); ?>"
                                        data-service-unit="<?php echo htmlspecialchars($srv['unit']); ?>"
                                        data-service-price="<?php echo htmlspecialchars((string)$srv['base_price']); ?>"
                                        data-service-category="<?php echo htmlspecialchars($srv['category']); ?>">
                                    <i class="fa-solid fa-plus"></i> Tambah
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div id="tab-products" class="tab-panel grid gap-6 md:grid-cols-2 xl:grid-cols-2 hidden">
                    <?php if (count($products) === 0): ?>
                        <div class="col-span-full text-center text-slate-400 py-10">
                            Belum ada produk aktif.
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $prd): ?>
                            <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex flex-col">
                                <div class="relative mb-4 h-40 rounded-2xl overflow-hidden bg-slate-100">
                                    <img src="<?php echo htmlspecialchars(getProductImagePath($prd['image'] ?? '')); ?>" alt="<?php echo htmlspecialchars($prd['name']); ?>" class="w-full h-full object-cover">
                                    <span class="absolute top-3 left-3 text-xs font-bold px-3 py-1 rounded-full <?php echo badgeColor($prd['category']); ?> backdrop-blur bg-white/70">
                                        <?php echo strtoupper($prd['category']); ?>
                                    </span>
                                    <span class="absolute bottom-3 right-3 text-xs font-bold px-3 py-1 rounded-full bg-white/80 text-emerald-700 border border-emerald-100">
                                        <?php echo number_format($prd['current_stock'], 0, ',', '.'); ?> stock
                                    </span>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($prd['name']); ?></h3>
                                <p class="text-sm text-slate-500 mb-4">Unit: <?php echo htmlspecialchars($prd['unit']); ?></p>
                                <div class="text-xs uppercase text-slate-400 tracking-wider">Price</div>
                                <div class="text-xl font-extrabold text-slate-900 mb-4">
                                    <?php echo format_rupiah($prd['selling_price']); ?>
                                </div>
                                <form action="../../backend/order/cart.php" method="POST" class="mt-auto" data-cart-action="add">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="item_type" value="product">
                                    <input type="hidden" name="item_id" value="<?php echo $prd['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="scope" value="<?php echo $cart_scope; ?>">
                                    <input type="hidden" name="redirect" value="<?php echo $catalog_redirect; ?>">
                                    <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl border border-slate-200 text-slate-600 font-semibold py-2 hover:bg-slate-900 hover:text-white transition" title="Tambah ke Keranjang">
                                        <i class="fa-solid fa-plus"></i> Tambah
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs uppercase text-slate-400 tracking-widest font-bold mb-1">Keranjang</p>
                        <h2 class="text-xl font-bold text-slate-900">Ringkasan Order</h2>
                    </div>
                    <form action="../../backend/order/cart.php" method="POST" data-cart-action="clear" data-confirm="Kosongkan seluruh keranjang?">
                        <input type="hidden" name="action" value="clear">
                        <input type="hidden" name="scope" value="<?php echo $cart_scope; ?>">
                        <input type="hidden" name="redirect" value="<?php echo $catalog_redirect; ?>">
                        <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-700">Clear</button>
                    </form>
                </div>

                <div id="cart-items-wrapper" class="space-y-4 flex-1 overflow-y-auto pr-1">
                    <?php if (empty($cart_items)): ?>
                        <div class="text-center text-slate-400 py-12 cart-empty-state">Keranjang masih kosong.</div>
                    <?php else: ?>
                        <?php foreach ($cart_items as $key => $item): ?>
                            <div class="border border-slate-100 rounded-2xl p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($item['name']); ?></p>
                                        <p class="text-xs uppercase tracking-wide text-slate-400"><?php echo strtoupper($item['item_type']); ?> • <?php echo htmlspecialchars($item['unit']); ?></p>
                                    </div>
                                    <form action="../../backend/order/cart.php" method="POST" data-cart-action="remove">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="item_key" value="<?php echo htmlspecialchars($key); ?>">
                                        <input type="hidden" name="scope" value="<?php echo $cart_scope; ?>">
                                        <input type="hidden" name="redirect" value="<?php echo $catalog_redirect; ?>">
                                        <button type="submit" class="text-slate-400 hover:text-rose-500" title="Hapus item">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </form>
                                </div>
                                <?php
                                    $uploadType = $item['upload_type'] ?? 'none';
                                    $attachedName = trim((string)($item['file_name'] ?? ''));
                                    $specNotes = trim((string)($item['specifications'] ?? ''));
                                ?>
                                <?php if (($uploadType === 'file' && $attachedName !== '') || $specNotes !== ''): ?>
                                    <div class="mt-2 space-y-1 text-xs text-slate-500">
                                        <?php if ($uploadType === 'file' && $attachedName !== ''): ?>
                                            <p class="flex items-center gap-1"><i class="fa-solid fa-paperclip text-slate-400"></i> Lampiran: <?php echo htmlspecialchars($attachedName); ?></p>
                                        <?php endif; ?>
                                        <?php if ($specNotes !== ''): ?>
                                            <p>Catatan: <?php echo nl2br(htmlspecialchars($specNotes)); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex items-center justify-between mt-3">
                                    <form action="../../backend/order/cart.php" method="POST" class="flex items-center gap-2" data-cart-action="update">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="item_key" value="<?php echo htmlspecialchars($key); ?>">
                                        <input type="hidden" name="scope" value="<?php echo $cart_scope; ?>">
                                        <input type="hidden" name="redirect" value="<?php echo $catalog_redirect; ?>">
                                        <input type="number" name="quantity" step="<?php echo $item['item_type'] === 'product' ? '1' : '0.1'; ?>" min="0" value="<?php echo htmlspecialchars($item['quantity']); ?>" class="w-24 border border-slate-200 rounded-lg px-3 py-1 text-sm">
                                        <button type="submit" class="text-xs font-semibold text-slate-600 hover:text-slate-900">Update</button>
                                    </form>
                                    <div class="text-right">
                                        <p class="text-xs text-slate-400">Subtotal</p>
                                        <p class="text-lg font-bold text-slate-900"><?php echo format_rupiah($item['price'] * $item['quantity']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="mt-6 border-t border-slate-100 pt-4 space-y-4">
                    <div class="flex items-center justify-between text-sm text-slate-500">
                        <span>Total Item</span>
                        <span id="cart-total-quantity">
                            <?php echo rtrim(rtrim(number_format($cart_quantity, 2, ',', '.'), '0'), ','); ?> item
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-lg font-bold text-slate-900">
                        <span>Total Tagihan</span>
                        <span id="cart-total-amount"><?php echo format_rupiah($cart_total); ?></span>
                    </div>
                    <form action="../../backend/order/checkout.php" method="POST" class="space-y-3">
                        <input type="hidden" name="scope" value="<?php echo $cart_scope; ?>">
                        <?php if ($mode === 'pos'): ?>
                            <input type="hidden" name="pos_mode" value="1">
                        <?php endif; ?>
                        <label class="block text-sm font-semibold text-slate-600">Catatan (opsional)</label>
                        <textarea name="notes" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none" placeholder="Contoh: tolong cetak glossy"></textarea>
                        <button type="submit" id="checkout-button" <?php echo empty($cart_items) ? 'disabled' : ''; ?> class="w-full py-3 rounded-2xl text-white font-semibold transition <?php echo empty($cart_items) ? 'bg-slate-300 cursor-not-allowed' : 'bg-slate-900 hover:bg-slate-800'; ?>">
                            Checkout &nbsp;•&nbsp; <span id="checkout-amount"><?php echo format_rupiah($cart_total); ?></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <div id="file-upload-modal" class="fixed inset-0 hidden z-[9999]" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" data-close-upload-modal></div>
        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-xl rounded-3xl bg-white p-6 md:p-8 shadow-2xl border border-slate-100" data-upload-modal-panel>
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <p class="text-xs uppercase text-slate-400 tracking-widest font-semibold mb-1">Printing Service</p>
                        <h3 class="text-2xl font-bold text-slate-900" id="modal-service-name">Nama Layanan</h3>
                        <p class="text-sm text-slate-500" id="modal-service-meta"></p>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-600" data-close-upload-modal title="Tutup">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                <div class="mb-5 rounded-2xl bg-slate-50 border border-slate-100 p-4">
                    <p class="text-xs uppercase text-slate-400 tracking-wider">Harga Dasar</p>
                    <p class="text-xl font-bold text-slate-900" id="modal-service-price">Rp0</p>
                </div>
                <form id="file-upload-form" action="../../backend/order/cart.php" method="POST" enctype="multipart/form-data" class="space-y-4" data-cart-action="add" data-modal-form="file-upload">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="item_type" value="service">
                    <input type="hidden" name="item_id" id="modal-service-id" value="">
                    <input type="hidden" name="scope" value="<?php echo $cart_scope; ?>">
                    <input type="hidden" name="redirect" value="<?php echo $catalog_redirect; ?>">
                    <input type="hidden" name="upload_type" value="file">
                    <div>
                        <label for="modal-quantity" class="block text-sm font-semibold text-slate-600">Jumlah</label>
                        <input id="modal-quantity" name="quantity" type="number" min="0.1" step="0.1" value="1" class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        <p class="text-xs text-slate-400 mt-1">Satuan: <span id="modal-service-unit">lembar</span></p>
                    </div>
                    <div>
                        <label for="modal-upload-file" class="block text-sm font-semibold text-slate-600">File Desain / Dokumen</label>
                        <input id="modal-upload-file" name="upload_file" type="file" class="mt-1 w-full text-sm text-slate-600" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,image/*" required>
                        <p class="text-xs text-slate-400 mt-1">Maksimal 20MB. Format yang didukung: PDF, JPG, PNG, DOCX, PPTX, XLSX, ZIP.</p>
                    </div>
                    <div>
                        <label for="modal-specifications" class="block text-sm font-semibold text-slate-600">Instruksi Cetak (opsional)</label>
                        <textarea id="modal-specifications" name="specifications" rows="3" class="mt-1 w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none" placeholder="Contoh: Cetak full color di kertas art paper 210gsm"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50" data-close-upload-modal>Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">Tambah ke Keranjang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabPanels = {
            services: document.getElementById('tab-services'),
            products: document.getElementById('tab-products')
        };

        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.target;
                tabButtons.forEach(b => b.classList.remove('tab-active'));
                btn.classList.add('tab-active');

                Object.keys(tabPanels).forEach(key => {
                    tabPanels[key].classList.toggle('hidden', key !== target);
                });
            });
        });

        const CART_ENDPOINT = '../../backend/order/cart.php';
        const CART_SCOPE = <?php echo json_encode($cart_scope); ?>;
        const CART_REDIRECT = <?php echo json_encode($catalog_redirect); ?>;

        const cartItemsWrapper = document.getElementById('cart-items-wrapper');
        const cartToast = document.getElementById('cart-toast');
        const cartTotalQuantityEl = document.getElementById('cart-total-quantity');
        const cartTotalAmountEl = document.getElementById('cart-total-amount');
        const checkoutButton = document.getElementById('checkout-button');
        const checkoutAmountEl = document.getElementById('checkout-amount');
        const fileUploadModal = document.getElementById('file-upload-modal');
        const fileUploadForm = document.getElementById('file-upload-form');
        const modalServiceName = document.getElementById('modal-service-name');
        const modalServiceMeta = document.getElementById('modal-service-meta');
        const modalServicePrice = document.getElementById('modal-service-price');
        const modalServiceUnit = document.getElementById('modal-service-unit');
        const modalServiceIdInput = document.getElementById('modal-service-id');
        const modalQuantityInput = document.getElementById('modal-quantity');
        const modalUploadFileInput = document.getElementById('modal-upload-file');
        const modalSpecifications = document.getElementById('modal-specifications');
        const modalOpenButtons = document.querySelectorAll('[data-open-upload-modal]');
        const modalCloseTriggers = document.querySelectorAll('[data-close-upload-modal]');
        const modalPanel = fileUploadModal ? fileUploadModal.querySelector('[data-upload-modal-panel]') : null;
        const currencyFormatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
        let toastTimeout = null;

        const defaultMessages = {
            add: 'Item ditambahkan.',
            update: 'Jumlah diperbarui.',
            remove: 'Item dihapus dari keranjang.',
            clear: 'Keranjang dikosongkan.'
        };

        modalOpenButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const serviceData = {
                    id: btn.dataset.serviceId || '',
                    name: btn.dataset.serviceName || 'Printing Service',
                    unit: btn.dataset.serviceUnit || 'lembar',
                    price: Number(btn.dataset.servicePrice || '0'),
                    category: btn.dataset.serviceCategory || ''
                };
                openFileUploadModal(serviceData);
            });
        });

        modalCloseTriggers.forEach(trigger => {
            trigger.addEventListener('click', () => closeFileUploadModal());
        });

        if (fileUploadModal) {
            fileUploadModal.addEventListener('click', (event) => {
                const target = event.target;
                if (!target) return;
                if (target === fileUploadModal || target.hasAttribute('data-close-upload-modal')) {
                    closeFileUploadModal();
                }
            });
        }

        if (modalPanel) {
            modalPanel.addEventListener('click', (event) => event.stopPropagation());
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && isUploadModalOpen()) {
                closeFileUploadModal();
            }
        });

        document.addEventListener('submit', async (event) => {
            const form = event.target;
            if (!form.matches('form[data-cart-action]')) return;

            if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
                event.preventDefault();
                return;
            }

            event.preventDefault();
            await handleCartFormSubmit(form);
        });

        async function handleCartFormSubmit(form) {
            const actionType = form.dataset.cartAction || 'add';
            const submitBtn = form.querySelector('button[type="submit"]');
            const restoreButton = setButtonLoading(submitBtn, actionType);

            try {
                const endpointUrl = getAbsoluteAction(form);
                const response = await fetch(endpointUrl, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                const data = await parseJsonResponse(response);
                const message = data.message || defaultMessages[actionType] || 'Permintaan diproses.';
                showCartToast(message, data.status || 'info');
                if (data.status === 'success' && data.cart) {
                    renderCart(data.cart);
                    if (form.id === 'file-upload-form') {
                        form.reset();
                        closeFileUploadModal();
                    }
                }
            } catch (error) {
                console.error('Cart request failed:', error);
                showCartToast(error.message || 'Gagal memproses keranjang.', 'error');
                setTimeout(() => form.submit(), 150);
            } finally {
                restoreButton();
            }
        }

        function openFileUploadModal(service) {
            if (!fileUploadModal) return;

            if (modalServiceIdInput) {
                modalServiceIdInput.value = service.id || '';
            }
            if (modalServiceName) {
                modalServiceName.textContent = service.name || 'Printing Service';
            }
            if (modalServiceMeta) {
                const parts = [];
                if (service.category) {
                    parts.push(service.category.toUpperCase());
                }
                if (service.unit) {
                    parts.push('Per ' + service.unit);
                }
                modalServiceMeta.textContent = parts.join(' • ');
            }
            if (modalServicePrice) {
                modalServicePrice.textContent = formatRupiah(service.price || 0);
            }
            if (modalServiceUnit) {
                modalServiceUnit.textContent = service.unit || 'lembar';
            }
            if (modalQuantityInput) {
                modalQuantityInput.step = '0.1';
                modalQuantityInput.min = '0.1';
                modalQuantityInput.value = '1';
            }
            if (modalUploadFileInput) {
                modalUploadFileInput.value = '';
            }
            if (modalSpecifications) {
                modalSpecifications.value = '';
            }

            setUploadModalVisible(true);

            if (modalUploadFileInput) {
                setTimeout(() => {
                    try {
                        modalUploadFileInput.focus({ preventScroll: true });
                    } catch (error) {
                        modalUploadFileInput.focus();
                    }
                }, 150);
            }
        }

        function closeFileUploadModal() {
            if (!fileUploadModal) return;
            if (fileUploadForm) {
                fileUploadForm.reset();
            }
            if (modalQuantityInput) {
                modalQuantityInput.value = '1';
            }
            setUploadModalVisible(false);
        }

        function isUploadModalOpen() {
            return !!(fileUploadModal && !fileUploadModal.classList.contains('hidden'));
        }

        function setUploadModalVisible(visible) {
            if (!fileUploadModal) return;
            fileUploadModal.classList.toggle('hidden', !visible);
            fileUploadModal.setAttribute('aria-hidden', visible ? 'false' : 'true');
            setBodyScrollLocked(visible);
        }

        function setBodyScrollLocked(locked) {
            document.body.classList.toggle('overflow-hidden', locked);
        }

        function setButtonLoading(button, actionType) {
            if (!button) {
                return () => {};
            }

            const originalHtml = button.innerHTML;
            button.disabled = true;
            if (actionType === 'add') {
                button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menambahkan';
            }

            return () => {
                button.disabled = false;
                button.innerHTML = originalHtml;
            };
        }

        function getAbsoluteAction(form) {
            if (form.dataset.absAction) {
                return form.dataset.absAction;
            }

            const rawAction = form.getAttribute('action') || form.action || window.location.href;
            let absolute = rawAction;
            try {
                absolute = new URL(rawAction, window.location.href).toString();
            } catch (error) {
                console.warn('Gagal mengonversi action URL', error);
            }
            form.dataset.absAction = absolute;
            return absolute;
        }

        async function parseJsonResponse(response) {
            const contentType = response.headers.get('content-type') || '';

            if (!response.ok) {
                const message = `Server error (${response.status})`;
                throw new Error(message);
            }

            if (contentType.includes('application/json')) {
                return response.json();
            }

            const text = await response.text();
            if (text && text.toLowerCase().includes('login')) {
                throw new Error('Sesi berakhir, silakan login ulang.');
            }
            throw new Error('Respon server tidak valid.');
        }

        function renderCart(cart) {
            if (!cartItemsWrapper) return;

            if (!cart.items || cart.items.length === 0) {
                cartItemsWrapper.innerHTML = '<div class="text-center text-slate-400 py-12 cart-empty-state">Keranjang masih kosong.</div>';
            } else {
                cartItemsWrapper.innerHTML = cart.items.map(item => createCartItemHtml(item)).join('');
            }

            if (cartTotalQuantityEl) {
                cartTotalQuantityEl.textContent = `${formatQuantity(cart.total_quantity)} item`;
            }

            if (cartTotalAmountEl) {
                cartTotalAmountEl.textContent = formatRupiah(cart.total_amount);
            }

            updateCheckoutState(cart);
        }

        function createCartItemHtml(item) {
            const safeName = escapeHtml(item.name || '');
            const safeUnit = escapeHtml(item.unit || '');
            const safeType = escapeHtml((item.item_type || '').toUpperCase());
            const key = escapeHtml(item.key || '');
            const quantity = item.quantity ?? 0;
            const stepValue = (item.item_type || '').toLowerCase() === 'product' ? '1' : '0.1';
            const fileInfo = (item.upload_type === 'file' && item.file_name) ? `<p class="flex items-center gap-1"><i class="fa-solid fa-paperclip text-slate-400"></i> Lampiran: ${escapeHtml(item.file_name)}</p>` : '';
            const specInfo = item.specifications ? `<p>Catatan: ${formatMultiline(item.specifications)}</p>` : '';
            const extraInfoHtml = (fileInfo || specInfo) ? `<div class="mt-2 space-y-1 text-xs text-slate-500">${fileInfo}${specInfo}</div>` : '';

            return `
                <div class="border border-slate-100 rounded-2xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">${safeName}</p>
                            <p class="text-xs uppercase tracking-wide text-slate-400">${safeType} • ${safeUnit}</p>
                        </div>
                        <form action="${CART_ENDPOINT}" method="POST" data-cart-action="remove">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="item_key" value="${key}">
                            <input type="hidden" name="scope" value="${CART_SCOPE}">
                            <input type="hidden" name="redirect" value="${CART_REDIRECT}">
                            <button type="submit" class="text-slate-400 hover:text-rose-500" title="Hapus item">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </form>
                    </div>
                    ${extraInfoHtml}
                    <div class="flex items-center justify-between mt-3">
                        <form action="${CART_ENDPOINT}" method="POST" class="flex items-center gap-2" data-cart-action="update">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="item_key" value="${key}">
                            <input type="hidden" name="scope" value="${CART_SCOPE}">
                            <input type="hidden" name="redirect" value="${CART_REDIRECT}">
                            <input type="number" name="quantity" step="${stepValue}" min="0" value="${escapeHtml(quantity)}" class="w-24 border border-slate-200 rounded-lg px-3 py-1 text-sm">
                            <button type="submit" class="text-xs font-semibold text-slate-600 hover:text-slate-900">Update</button>
                        </form>
                        <div class="text-right">
                            <p class="text-xs text-slate-400">Subtotal</p>
                            <p class="text-lg font-bold text-slate-900">${formatRupiah(item.subtotal)}</p>
                        </div>
                    </div>
                </div>
            `;
        }

        function showCartToast(message, status) {
            if (!cartToast) return;

            const baseClass = 'mb-6 px-4 py-3 rounded-2xl border text-sm font-semibold';
            const successClass = 'bg-emerald-50 border-emerald-200 text-emerald-700';
            const errorClass = 'bg-red-50 border-red-200 text-red-700';
            const infoClass = 'bg-slate-50 border-slate-200 text-slate-600';

            cartToast.className = `${baseClass} ${status === 'success' ? successClass : status === 'error' ? errorClass : infoClass}`;
            cartToast.textContent = message;
            cartToast.classList.remove('hidden');

            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                cartToast.classList.add('hidden');
            }, 3200);
        }

        function formatQuantity(value) {
            const num = Number(value) || 0;
            if (Number.isInteger(num)) return num.toString();
            return num.toFixed(2).replace(/\.0+$/, '').replace(/(\.\d*?)0+$/, '$1');
        }

        function formatRupiah(value) {
            const amount = Number(value) || 0;
            return currencyFormatter.format(amount);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatMultiline(value) {
            return escapeHtml(value ?? '').replace(/\r?\n/g, '<br>');
        }

        function updateCheckoutState(cart) {
            if (!checkoutButton) return;

            const hasItems = Array.isArray(cart.items) && cart.items.length > 0;
            checkoutButton.disabled = !hasItems;

            checkoutButton.classList.toggle('bg-slate-300', !hasItems);
            checkoutButton.classList.toggle('cursor-not-allowed', !hasItems);
            checkoutButton.classList.toggle('bg-slate-900', hasItems);
            checkoutButton.classList.toggle('hover:bg-slate-800', hasItems);

            if (checkoutAmountEl) {
                checkoutAmountEl.textContent = formatRupiah(cart.total_amount || 0);
            }
        }

        window.closeFileUploadModal = closeFileUploadModal;
    </script>
</body>
</html>
