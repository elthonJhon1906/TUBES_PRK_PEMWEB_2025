<?php
require_once '../../koneksi/database.php'; 
require_once '../../backend/manajemenUser/auth_middleware.php'; 

checkAuthorization(['OWNER']);

$stmt = $pdo->query("SELECT id, username, email, full_name, created_at, role
                     FROM users 
                     ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$current_page = 'users.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Fotocopy Nagoya</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 font-[inter] text-slate-800 flex">

    <?php include '../../sidebar/sidebar.php'; ?> 

    <div class="flex-1 flex flex-col">
        <?php include '../../header/header.php'; ?> 

        <main class="flex-1 p-6">
            <div class="max-w-7xl mx-auto md:ml-64">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Manajemen Pengguna</h1>
                        <p class="text-slate-500 text-sm mt-1">Kelola akun staff, owner, dan member aplikasi.</p>
                    </div>
                    <a href="create.php" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl font-medium flex items-center shadow-md transition-all">
                        <i data-lucide="plus" class="w-5 h-5 mr-2"></i> Tambah User
                    </a>
                </div>

                <?php if (isset($_GET['status'])): ?>
                    <div class="mb-4 p-3 rounded-lg text-white font-medium 
                        <?php 
                            if ($_GET['status'] == 'success_add' || $_GET['status'] == 'success_edit' || $_GET['status'] == 'success_delete') echo 'bg-green-500'; 
                            else if ($_GET['status'] == 'error_self') echo 'bg-red-500';
                            else echo 'bg-yellow-500';
                        ?>">
                        <?php 
                            if ($_GET['status'] == 'success_add') echo '✅ User baru berhasil ditambahkan!';
                            else if ($_GET['status'] == 'success_edit') echo '✅ Data user berhasil diperbarui!';
                            else if ($_GET['status'] == 'success_delete') echo '✅ User berhasil dihapus!';
                            else if ($_GET['status'] == 'error_self') echo '❌ Anda tidak bisa menghapus akun Anda sendiri!';
                            else if (isset($_GET['msg'])) echo 'Terjadi kesalahan: ' . htmlspecialchars($_GET['msg']);
                        ?>
                    </div>
                <?php endif; ?>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Nama Lengkap</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Username</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Email</th> 
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Role</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Dibuat Pada</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach($users as $u): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-800"><?= htmlspecialchars($u['full_name']) ?></td>
                                <td class="px-6 py-4 text-sm text-slate-500"><?= htmlspecialchars($u['username']) ?></td>
                                <td class="px-6 py-4 text-sm text-slate-500"><?= htmlspecialchars($u['email']) ?></td> 
                                <td class="px-6 py-4">
                                    <?php 
                                        $role_class = 'bg-blue-100 text-blue-800'; 
                                        if ($u['role'] === 'owner') {
                                            $role_class = 'bg-purple-100 text-purple-800';
                                        } elseif ($u['role'] === 'admin') {
                                            $role_class = 'bg-red-100 text-red-800';
                                        } elseif ($u['role'] === 'staff') {
                                            $role_class = 'bg-indigo-100 text-indigo-800';
                                        } elseif ($u['role'] === 'customer') {
                                            $role_class = 'bg-green-100 text-green-800';
                                        }
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $role_class ?>">
                                        <?= htmlspecialchars(ucfirst($u['role'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    <?= date('d M Y', strtotime($u['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    <a href="edit.php?id=<?= $u['id'] ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    <a href="../../backend/manajemenUser/process.php?delete_id=<?= $u['id'] ?>" 
                                       onclick="return confirm('Yakin hapus user <?= $u['username'] ?>? Tindakan ini tidak dapat dibatalkan.')"
                                       class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>