<?php
require_once '../../koneksi/database.php';
require_once '../../backend/manajemenUser/auth_middleware.php';

checkAuthorization(['STAFF', 'ADMIN', 'OWNER']);

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
    <title>Tambah Layanan Baru - NPC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 font-[inter] text-slate-800 flex">
    <?php include '../../sidebar/sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <?php include '../../header/header.php'; ?>

        <main class="flex-1 p-6 flex items-center justify-center">
            <div class="bg-white p-8 rounded-2xl shadow-lg border border-slate-100 w-full max-w-2xl md:ml-64">
                <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                    <a href="services.php" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left"></i></a>
                    <h2 class="text-xl font-bold text-slate-800">Tambah Layanan Baru</h2>
                </div>

                <?php if ($error_msg): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?= $error_msg; ?></span>
                    </div>
                <?php endif; ?>

                <form action="../../backend/layanan/process.php" method="POST" class="space-y-5" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Layanan</label>
                            <input type="text" name="name" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Kode Layanan (unik)</label>
                            <input type="text" name="code" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div id="category-container">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Kategori</label>
                            <select name="category" id="category-select" onchange="toggleCategoryInput(this.value)" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none bg-white">
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <?php foreach ($service_categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['category']); ?>">
                                        <?= htmlspecialchars(ucfirst($cat['category'])); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="Lainnya">Lainnya (input manual)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Unit Satuan</label>
                            <select name="unit" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none bg-white">
                                <option value="" disabled selected>-- Pilih Satuan --</option>
                                <?php foreach ($default_units as $unit): ?>
                                    <option value="<?= $unit; ?>"><?= htmlspecialchars(ucfirst($unit)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Harga Dasar (Rp)</label>
                            <input type="number" name="base_price" min="0" value="0" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Estimasi Waktu (menit)</label>
                            <input type="number" name="estimated_minutes" min="1" value="5" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Gambar Layanan (Max 2MB, JPG/PNG/GIF)</label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/gif" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm bg-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <p class="text-xs text-slate-500 mt-1">Opsional, tambahkan jika layanan punya gambar.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi / Catatan</label>
                        <textarea name="description" rows="4" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="Contoh: Print warna A3, desain flyer, dll."></textarea>
                    </div>

                    <div>
                        <div class="flex items-center gap-3">
                            <input id="is_active" type="checkbox" name="is_active" checked class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <label for="is_active" class="text-sm font-semibold text-slate-700">Aktifkan layanan</label>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Nonaktifkan jika layanan sementara tidak dapat dijual.</p>
                    </div>

                    <button type="submit" name="add_service" class="w-full bg-slate-900 text-white font-bold py-2.5 rounded-lg hover:bg-slate-800 transition-colors mt-4">
                        <i data-lucide="save" class="w-5 h-5 mr-2 inline"></i> Simpan Layanan
                    </button>
                </form>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
        function toggleCategoryInput(value) {
            if (value !== 'Lainnya') return;

            const container = document.getElementById('category-container');
            const select = document.getElementById('category-select');
            select.remove();

            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'category';
            input.required = true;
            input.placeholder = 'Masukkan kategori baru';
            input.className = 'w-full px-4 py-2 border border-green-500 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none bg-white';
            container.appendChild(input);

            const cancel = document.createElement('button');
            cancel.type = 'button';
            cancel.textContent = 'Batalkan (pilih kategori yang ada)';
            cancel.className = 'mt-2 text-xs text-red-500 hover:text-red-700';
            cancel.onclick = () => window.location.reload();
            container.appendChild(cancel);
        }
    </script>
</body>
</html>