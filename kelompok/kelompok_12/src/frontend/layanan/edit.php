<?php
require_once '../../koneksi/database.php';
require_once '../../backend/manajemenUser/auth_middleware.php';

checkAuthorization(['STAFF', 'ADMIN', 'OWNER']);

if (!isset($_GET['id'])) {
    header('Location: services.php');
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
$stmt->execute([$id]);
$service = $stmt->fetch();

if (!$service) die('Layanan tidak ditemukan.');

$service_categories = ambil_banyak_data("SELECT DISTINCT category FROM services ORDER BY category ASC");
$default_units = ['lembar', 'paket', 'jam', 'item'];
$error_msg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
$current_page = 'services.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Layanan - NPC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 font-[inter] text-slate-800 flex">
    <?php include '../../sidebar/sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <?php include '../../header/header.php'; ?>

        <main class="flex-1 p-6 flex items-center justify-center">
            <div class="bg-white p-8 rounded-2xl shadow-lg border border-slate-100 w-full max-w-2xl  md:ml-64">
                <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                    <a href="services.php" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left"></i></a>
                    <h2 class="text-xl font-bold text-slate-800">Edit Layanan: <?= htmlspecialchars($service['name']); ?></h2>
                </div>

                <?php if ($error_msg): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?= $error_msg; ?></span>
                    </div>
                <?php endif; ?>

                <form action="../../backend/layanan/process.php" method="POST" class="space-y-5" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $service['id']; ?>">
                    <input type="hidden" name="old_image" value="<?= htmlspecialchars($service['image'] ?? ''); ?>">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Layanan</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($service['name']); ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Kode Layanan</label>
                            <input type="text" name="code" value="<?= htmlspecialchars($service['code']); ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                            <p class="text-[10px] text-slate-400 mt-1">Pastikan kode tetap unik.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Kategori</label>
                            <select name="category" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none bg-white">
                                <?php
                                    $all_categories = array_unique(array_merge([$service['category']], array_column($service_categories, 'category')));
                                    foreach ($all_categories as $cat):
                                ?>
                                    <option value="<?= htmlspecialchars($cat); ?>" <?= $service['category'] === $cat ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars(ucfirst($cat)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Unit</label>
                            <select name="unit" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none bg-white">
                                <?php
                                    $all_units = array_unique(array_merge([$service['unit']], $default_units));
                                    foreach ($all_units as $unit):
                                ?>
                                    <option value="<?= htmlspecialchars($unit); ?>" <?= $service['unit'] === $unit ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars(ucfirst($unit)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Harga Dasar (Rp)</label>
                            <input type="number" name="base_price" value="<?= htmlspecialchars($service['base_price']); ?>" min="0" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Estimasi Waktu (menit)</label>
                            <input type="number" name="estimated_minutes" value="<?= htmlspecialchars($service['estimated_minutes']); ?>" min="1" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi</label>
                        <textarea name="description" rows="4" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="Detail layanan"><?= htmlspecialchars($service['description']); ?></textarea>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-slate-700">Gambar Layanan</label>
                        <div class="flex items-center gap-4">
                            <?php if (!empty($service['image'])): ?>
                                <img src="../../../assets/uploads/services/<?= htmlspecialchars($service['image']); ?>" alt="Preview Layanan" class="w-20 h-20 object-cover rounded-lg border border-slate-200">
                            <?php else: ?>
                                <div class="text-xs text-slate-500">Belum ada gambar diunggah.</div>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/gif" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm bg-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <p class="text-xs text-slate-500">Kosongkan jika tidak ingin mengganti gambar saat ini.</p>
                    </div>

                    <div>
                        <div class="flex items-center gap-3">
                            <input id="is_active" type="checkbox" name="is_active" <?= $service['is_active'] ? 'checked' : ''; ?> class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <label for="is_active" class="text-sm font-semibold text-slate-700">Aktif</label>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Nonaktifkan jika layanan tidak tersedia sementara.</p>
                    </div>

                    <button type="submit" name="edit_service" class="w-full bg-green-600 text-white font-bold py-2.5 rounded-lg hover:bg-green-700 transition-colors mt-4">
                        <i data-lucide="refresh-ccw" class="w-5 h-5 mr-2 inline"></i> Perbarui Data
                    </button>
                </form>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>