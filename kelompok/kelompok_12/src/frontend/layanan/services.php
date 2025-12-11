<?php
require_once '../../koneksi/database.php';
require_once '../../backend/manajemenUser/auth_middleware.php';

checkAuthorization(['STAFF', 'ADMIN', 'OWNER']);

$stmt = $pdo->query("SELECT id, code, name, category, description, base_price, unit, estimated_minutes, is_active, image
                      FROM services ORDER BY name ASC");
$services = $stmt->fetchAll();

$current_page = 'services.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Layanan - NPC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 font-[inter] text-slate-800 flex">
    <?php include '../../sidebar/sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <?php include '../../header/header.php'; ?>

        <main class="flex-1 p-6">
            <div class="max-w-full mx-auto md:ml-64">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Master Layanan & Harga</h1>
                        <p class="text-slate-500 text-sm mt-1">Kelola daftar layanan printing / desain yang dapat dimasukkan ke order.</p>
                    </div>
                    <a href="create.php" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl font-medium flex items-center shadow-md transition-all">
                        <i data-lucide="plus" class="w-5 h-5 mr-2"></i> Tambah Layanan
                    </a>
                </div>

                <?php if (isset($_GET['status'])): ?>
                    <div class="mb-4 p-3 rounded-lg text-white font-medium
                        <?php
                            $status = $_GET['status'];
                            echo in_array($status, ['success_add','success_edit','success_delete']) ? 'bg-green-500' : 'bg-red-500';
                        ?>">
                        <?php
                            if ($status === 'success_add') echo '✅ Layanan baru berhasil ditambahkan!';
                            elseif ($status === 'success_edit') echo '✅ Data layanan berhasil diperbarui!';
                            elseif ($status === 'success_delete') echo '✅ Layanan berhasil dihapus!';
                            elseif (isset($_GET['msg'])) echo '❌ ' . htmlspecialchars($_GET['msg']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Nama Layanan</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Kode</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Kategori</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Satuan</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase">Harga Dasar</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase">Estimasi (menit)</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase">Aktif</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($services as $srv): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 rounded-xl overflow-hidden border border-slate-100 bg-slate-100">
                                                    <?php if (!empty($srv['image'])): ?>
                                                        <img src="../../../assets/uploads/services/<?= htmlspecialchars($srv['image']); ?>" alt="<?= htmlspecialchars($srv['name']); ?>" class="w-full h-full object-cover">
                                                    <?php else: ?>
                                                        <div class="w-full h-full flex items-center justify-center text-[10px] text-slate-400">No Img</div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-slate-800"><?= htmlspecialchars($srv['name']); ?></div>
                                                    <div class="text-xs text-slate-400 max-w-xs overflow-hidden text-ellipsis whitespace-nowrap">
                                                        <?= htmlspecialchars($srv['description'] ?? ''); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-500 font-mono"><?= htmlspecialchars($srv['code']); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-500"><?= htmlspecialchars(ucfirst($srv['category'])); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-500"><?= htmlspecialchars($srv['unit']); ?></td>
                                        <td class="px-6 py-4 text-right font-bold text-slate-800"><?= format_rupiah($srv['base_price']); ?></td>
                                        <td class="px-6 py-4 text-center text-sm text-slate-600"><?= (int)$srv['estimated_minutes']; ?> menit</td>
                                        <td class="px-6 py-4 text-center">
                                            <?php if ($srv['is_active']): ?>
                                                <i data-lucide="check-circle" class="w-5 h-5 text-green-500 mx-auto"></i>
                                            <?php else: ?>
                                                <i data-lucide="x-circle" class="w-5 h-5 text-red-400 mx-auto"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right flex justify-end gap-2">
                                            <a href="edit.php?id=<?= $srv['id']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </a>
                                            <a href="../../backend/layanan/process.php?delete_id=<?= $srv['id']; ?>"
                                               onclick="return confirm('Yakin hapus layanan <?= htmlspecialchars($srv['name']); ?>?');"
                                               class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (count($services) === 0): ?>
                                    <tr>
                                        <td colspan="8" class="px-6 py-10 text-center text-slate-400 text-sm">Belum ada layanan terdaftar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>