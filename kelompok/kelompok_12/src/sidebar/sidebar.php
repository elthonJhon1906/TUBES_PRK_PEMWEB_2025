<?php
$role = isset($_SESSION['user']['role']) ? strtoupper($_SESSION['user']['role']) : 'GUEST';

if (!function_exists('hasAccess')) {
    function hasAccess($allowed_roles, $current_role) {
        $allowed_upper = array_map('strtoupper', $allowed_roles);
        return in_array($current_role, $allowed_upper);
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
$current_view  = isset($_GET['view']) ? $_GET['view'] : null;
?>

<aside class="w-64 bg-slate-900 text-gray-400 flex flex-col h-screen fixed left-0 top-0 z-30 hidden md:flex border-r border-slate-800 no-print">
    
    <div class="h-16 flex items-center gap-3 px-6 bg-slate-950 border-b border-slate-800">
        <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center shadow-lg shadow-green-900/20">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
        </div>
        <span class="text-gray-100 font-bold text-lg tracking-wide">NPC System</span>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto custom-scrollbar">
        
        <div class="px-3 mb-2 mt-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Utama</div>

        <?php if (hasAccess(['ADMIN', 'OWNER', 'STAFF', 'CUSTOMER'], $role)): ?>
        <a href="../../frontend/dashboard/dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?php echo ($current_page == 'dashboard.php' && $current_view !== 'orders') ? 'bg-green-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            <span class="font-medium text-sm">Dashboard</span>
        </a>
        <?php endif; ?>

        <?php if (hasAccess(['CUSTOMER'], $role)): ?>
        <div class="px-3 mb-2 mt-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Order</div>

        <a href="../../frontend/order/catalog.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?php echo ($current_page == 'catalog.php' && strpos($_SERVER['PHP_SELF'], '/order/') !== false) ? 'bg-green-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9m5-9v9m4-9v9m4-13h2"></path></svg>
            <span class="font-medium text-sm">Shop Catalog</span>
        </a>

        <a href="../../frontend/order/my_orders.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?php echo ($current_page == 'my_orders.php') ? 'bg-green-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-medium text-sm">My Orders</span>
        </a>
        <?php endif; ?>

        <?php if (hasAccess(['ADMIN', 'STAFF', 'OWNER'], $role)): ?>
        <div class="px-3 mb-2 mt-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Transaksi</div>

        <?php $is_pos_mode = ($current_page == 'catalog.php' && isset($_GET['mode']) && $_GET['mode'] === 'pos'); ?>
        <a href="../../frontend/order/catalog.php?mode=pos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?php echo $is_pos_mode ? 'bg-green-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span class="font-medium text-sm">Input Order (Kasir)</span>
        </a>
        <?php endif; ?>

        <?php if (hasAccess(['ADMIN', 'OWNER'], $role)): ?>
        <div class="px-3 mb-2 mt-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Keuangan</div>

        <a href="../../frontend/laporan/reports.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?php echo ($current_page == 'reports.php') ? 'bg-green-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
            <span class="font-medium text-sm">Laporan Omset</span>
        </a>

        <a href="../../frontend/expenses/expenses.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?php echo ($current_page == 'expenses.php') ? 'bg-green-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span class="font-medium text-sm">Input Pengeluaran</span>
        </a>
        <?php endif; ?>


        <?php if (hasAccess(['ADMIN', 'OWNER'], $role)): ?>
        <div class="px-3 mb-2 mt-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Master Data</div>

    <a href="../../frontend/catalog/products.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?php echo ($current_page == 'products.php') ? 'bg-green-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            <span class="font-medium text-sm">Stok Bahan (ATK)</span>
        </a>

    <a href="../../frontend/layanan/services.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?php echo ($current_page == 'services.php') ? 'bg-green-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            <span class="font-medium text-sm">Layanan</span>
        </a>

        <a href="../../frontend/manajemenUser/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?php echo ($current_page == 'index.php') ? 'bg-green-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <span class="font-medium text-sm">Manajemen User</span>
        </a>
        <?php endif; ?>

    </nav>

    <div class="p-4 bg-slate-950 border-t border-slate-800">
        <a href="../../backend/logout/logout.php" class="flex items-center justify-center gap-2 w-full py-2 bg-slate-800 hover:bg-red-600 text-slate-300 hover:text-white rounded-lg transition-colors text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Keluar Aplikasi
        </a>
    </div>
</aside>
