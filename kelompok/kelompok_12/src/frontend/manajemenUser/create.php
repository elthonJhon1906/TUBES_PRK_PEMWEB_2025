<?php
require_once '../../koneksi/database.php'; 
require_once '../../backend/manajemenUser/auth_middleware.php'; 
checkAuthorization(['OWNER']);

$roles = ['owner', 'admin', 'staff', 'customer'];

$error_msg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
$current_page = 'users.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User - Fotocopy Nagoya</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-slate-50 font-[inter] text-slate-800 flex">
    
    <?php include '../../sidebar/sidebar.php'; ?> 

    <div class="flex-1 flex flex-col">
        <?php include '../../header/header.php'; ?> 

        <main class="flex-1 p-6 flex items-center justify-center">
            
            <div class="bg-white p-8 rounded-2xl shadow-lg border border-slate-100 w-full max-w-md md:ml-64">
                <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                    <a href="index.php" class="text-slate-400 hover:text-slate-600"><i data-lucide="arrow-left"></i></a>
                    <h2 class="text-xl font-bold text-slate-800">Tambah User Baru</h2>
                </div>
                
                <?php if ($error_msg): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?= $error_msg ?></span>
                    </div>
                <?php endif; ?>

                <form action="../../backend/manajemenUser/process.php" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="full_name" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Username</label>
                        <input type="text" name="username" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Role / Jabatan</label>
                        <select name="role" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>">
                                    <?= htmlspecialchars(ucfirst($role)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" name="add_user" class="w-full bg-slate-900 text-white font-bold py-2.5 rounded-lg hover:bg-slate-800 transition-colors mt-4">
                        Simpan User
                    </button>
                </form>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>