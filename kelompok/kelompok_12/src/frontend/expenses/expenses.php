<?php
require_once '../../koneksi/database.php'; 
require_once '../../backend/manajemenUser/auth_middleware.php'; 

checkAuthorization(['OWNER', 'ADMIN']); 

$current_page = 'expenses.php'; 
$error_msg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';
$status_msg = "";

if ($status == 'success') $status_msg = "✅ Data pengeluaran berhasil dicatat!";
if ($status == 'success_edit') $status_msg = "✅ Data pengeluaran berhasil diperbarui!"; 
if ($status == 'success_delete') $status_msg = "✅ Data pengeluaran berhasil dihapus!";

$expense_categories = ['purchasing' => 'Pembelian Bahan', 'operational' => 'Operasional', 'salary' => 'Gaji', 'maintenance' => 'Perawatan', 'other' => 'Lain-lain'];

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$sql_expenses = "SELECT e.*, u.full_name as creator_name
                 FROM expenses e
                 JOIN users u ON e.created_by = u.id
                 WHERE e.expense_date BETWEEN '$start_date' AND '$end_date'
                 ORDER BY e.expense_date DESC";
$expenses = ambil_banyak_data($sql_expenses);

$sql_total = "SELECT SUM(amount) as total FROM expenses 
              WHERE expense_date BETWEEN '$start_date' AND '$end_date'";
$total_expense = ambil_satu_data($sql_total)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Pengeluaran - NPC System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-[inter] text-slate-800 flex">
    
    <?php include '../../sidebar/sidebar.php'; ?> 

    <div class="flex-1 flex flex-col">
        <?php include '../../header/header.php'; ?> 

        <main class="flex-1 p-6 md:ml-64"> <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 ">Input & Riwayat Pengeluaran</h1>
                        <p class="text-slate-500 text-sm mt-1">Catat semua biaya operasional, gaji, atau pembelian bahan.</p>
                    </div>
                </div>

                <?php if ($status_msg): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 font-medium"><?= $status_msg ?></div>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 font-medium">❌ <?= $error_msg ?></div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-lg border border-slate-100 h-fit">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-3">Catat Pengeluaran Baru</h3>
                        
                        <form action="../../backend/expenses/process.php" method="POST" class="space-y-4">
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Pengeluaran</label>
                                <input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi/Keterangan</label>
                                <input type="text" name="description" placeholder="Beli kertas A4 5 rim" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Kategori</label>
                                <select name="category" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                                    <?php foreach ($expense_categories as $key => $label): ?>
                                        <option value="<?= $key ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Nominal (Rp)</label>
                                <input type="number" name="amount" min="1" placeholder="Cth: 150000" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>

                            <button type="submit" name="add_expense" class="w-full bg-red-600 text-white font-bold py-2.5 rounded-lg hover:bg-red-700 transition-colors mt-6 flex items-center justify-center gap-2">
                                <i data-lucide="minus-circle" class="w-5 h-5"></i> Catat Pengeluaran
                            </button>
                        </form>
                    </div>

                    <div class="lg:col-span-2">
                        
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6">
                            <h4 class="font-bold text-lg text-slate-800 mb-3">Filter Periode</h4>
                            <form action="" method="GET" class="flex gap-4 items-end">
                                <div class="w-full">
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Dari Tanggal</label>
                                    <input type="date" name="start_date" value="<?= $start_date ?>" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-slate-500 outline-none">
                                </div>
                                <div class="w-full">
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Sampai Tanggal</label>
                                    <input type="date" name="end_date" value="<?= $end_date ?>" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-slate-500 outline-none">
                                </div>
                                <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-bold h-[38px] w-auto">Filter</button>
                            </form>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                                <h3 class="font-bold text-slate-800">Riwayat Pengeluaran (<?= date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)) ?>)</h3>
                                <span class="text-sm font-bold text-red-600">Total: <?= format_rupiah($total_expense); ?></span>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-100 text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase">Tanggal</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase">Deskripsi</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase">Kategori</th> 
                                            <th class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase">Nominal</th>
                                            <th class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase">Dicatat Oleh</th>
                                            <th class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase">Aksi</th> </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php if (count($expenses) > 0): ?>
                                            <?php foreach($expenses as $e): ?>
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="px-6 py-4 font-medium text-slate-800"><?= date('d M Y', strtotime($e['expense_date'])) ?></td>
                                                <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($e['description']) ?></td>
                                                <td class="px-6 py-4">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                        <?= $expense_categories[$e['category']] ?? ucfirst($e['category']) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right font-bold text-red-700">
                                                    <?= format_rupiah($e['amount']) ?>
                                                </td>
                                                <td class="px-6 py-4 text-center text-xs text-slate-500">
                                                    <?= htmlspecialchars($e['creator_name']) ?>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <div class="flex justify-center gap-2">
                                                        <button onclick="bukaModalEdit(<?= $e['id'] ?>, '<?= $e['expense_date'] ?>', '<?= htmlspecialchars($e['description']) ?>', '<?= $e['category'] ?>', '<?= $e['amount'] ?>')"
                                                            class="text-slate-400 hover:text-blue-600 p-1 rounded transition" title="Edit">
                                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                                        </button>
                                                        <a href="../../backend/expenses/process.php?delete_id=<?= $e['id'] ?>" 
                                                            onclick="return confirm('Yakin ingin menghapus pengeluaran ini? Tindakan ini tidak dapat dibatalkan.')"
                                                            class="text-slate-400 hover:text-red-600 p-1 rounded transition" title="Hapus">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="6" class="text-center py-8 text-slate-400 italic">Tidak ada data pengeluaran pada periode ini.</td></tr> <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

<div id="modalEdit" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-2xl shadow-lg w-full max-w-lg mx-4">
        <h3 class="font-bold text-xl mb-4 text-slate-800 border-b pb-3">Edit Pengeluaran</h3>
        <form action="../../backend/expenses/process.php" method="POST" class="space-y-4">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="edit_expense" value="1">
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Pengeluaran</label>
                <input type="date" name="expense_date" id="edit_date" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi/Keterangan</label>
                <input type="text" name="description" id="edit_description" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Kategori</label>
                <select name="category" id="edit_category" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                    <?php foreach ($expense_categories as $key => $label): ?>
                        <option value="<?= $key ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nominal (Rp)</label>
                <input type="number" name="amount" id="edit_amount" min="1" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-4 py-2 bg-gray-200 rounded-lg text-sm font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

    <script>
        lucide.createIcons();

        function bukaModalEdit(id, date, desc, category, amount) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_date').value = date;
            document.getElementById('edit_description').value = desc;
            document.getElementById('edit_amount').value = amount;
            
            const categorySelect = document.getElementById('edit_category');
            for (let i = 0; i < categorySelect.options.length; i++) {
                if (categorySelect.options[i].value === category) {
                    categorySelect.selectedIndex = i;
                    break;
                }
            }
            
            document.getElementById('modalEdit').classList.remove('hidden');
        }
    </script>
</body>
</html>