<?php 
require_once __DIR__ . '/../../backend/laporan/laporan_logic.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan & Audit - NPC System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        #page-loader {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.7);
            z-index: 9999;
            display: none;
            align-items: center; justify-content: center;
            backdrop-filter: blur(2px);
        }
        
        @media print {
            nav, aside, header, .no-print, form, .filter-section, button, a.btn-action { display: none !important; }
            main { margin: 0 !important; padding: 0 !important; width: 100% !important; overflow: visible !important; }
            body { background: white !important; color: black !important; }
            .print-header { display: block !important; text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
            body.print-mode-chart #section-table, body.print-mode-chart #section-audit, body.print-mode-chart .card-stats-container { display: none !important; }
            body.print-mode-chart #section-chart { display: block !important; }
            body.print-mode-table #section-chart, body.print-mode-table #section-audit, body.print-mode-table .card-stats-container { display: none !important; }
            body.print-mode-table #section-table { display: block !important; }
            table { width: 100% !important; border-collapse: collapse; }
            th, td { border: 1px solid #ccc; padding: 6px !important; font-size: 10px; }
            th { background-color: #f0f0f0 !important; }
        }
        .print-header { display: none; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">
    <div id="page-loader">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
    </div>

    <?php include '../../sidebar/sidebar.php'; ?>
    <?php include '../../header/header.php'; ?>

    <main class="p-4 md:p-8 md:ml-64 transition-all min-h-screen relative">
        <div id="dynamic-content">
            <div id="chart-data" 
                 data-labels='<?php echo $json_labels; ?>' 
                 data-values='<?php echo $json_data; ?>'
                 style="display:none;"></div>

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 no-print">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">
                        <?php echo ($role === 'OWNER') ? 'Laporan Keuangan & Audit' : 'Laporan Omset Penjualan'; ?>
                    </h1>
                    <p class="text-slate-500 text-sm mt-1">
                        Periode: <span class="font-bold text-slate-700"><?php echo date('d F Y', strtotime($start_date)); ?></span> s/d <span class="font-bold text-slate-700"><?php echo date('d F Y', strtotime($end_date)); ?></span>
                    </p>
                </div>
                <div class="flex gap-2">
                    <button onclick="printSection('table')" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-table text-blue-600"></i> Cetak Tabel
                    </button>
                    <button onclick="exportTableToCSV('laporan_omset_npc.csv')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium shadow-lg shadow-green-600/20 transition flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>

            <div class="print-header">
                <h1 class="text-xl font-bold uppercase">Laporan NPC Printing</h1>
                <p class="text-sm">Periode: <?php echo date('d/m/Y', strtotime($start_date)); ?> - <?php echo date('d/m/Y', strtotime($end_date)); ?></p>
                <p class="text-xs text-gray-500">Dicetak: <?php echo date('d M Y H:i'); ?></p>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 no-print filter-section">
                <form id="filterForm" action="" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                    <input type="hidden" name="limit_ord" value="<?php echo $limit_ord_val; ?>">
                    <input type="hidden" name="limit_aud" value="<?php echo $limit_aud_val; ?>">
                    <div class="w-full md:w-auto">
                        <label class="text-xs font-bold text-slate-500 mb-1 block">Dari Tanggal</label>
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div class="w-full md:w-auto">
                        <label class="text-xs font-bold text-slate-500 mb-1 block">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-6 py-2 rounded-lg font-bold text-sm transition h-[38px] w-full md:w-auto shadow-sm">
                        <i class="fa-solid fa-filter mr-1"></i> Filter
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 card-stats-container">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 card-stats relative overflow-hidden group">
                    <div class="absolute right-0 top-0 h-full w-1 bg-green-500"></div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Tagihan (Omset)</p>
                    <h3 class="text-3xl font-bold text-slate-800 mb-1"><?php echo "Rp " . number_format($total_revenue, 0, ',', '.'); ?></h3>
                    <p class="text-xs text-green-600 font-medium flex items-center gap-1">
                        <i class="fa-solid fa-wallet"></i> Cash Masuk: <?php echo "Rp " . number_format($total_cash_in, 0, ',', '.'); ?>
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 card-stats relative overflow-hidden">
                    <div class="absolute right-0 top-0 h-full w-1 bg-red-500"></div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Pengeluaran</p>
                    <h3 class="text-3xl font-bold text-slate-800 mb-1"><?php echo "Rp " . number_format($total_expense, 0, ',', '.'); ?></h3>
                    <p class="text-xs text-red-600 font-medium flex items-center gap-1">
                        <i class="fa-solid fa-arrow-trend-down"></i> Operasional & Bahan
                    </p>
                </div>

                <div class="bg-slate-900 p-6 rounded-2xl shadow-lg shadow-slate-900/20 card-stats relative overflow-hidden text-white">
                    <div class="absolute -right-6 -top-6 bg-white/10 w-24 h-24 rounded-full blur-xl"></div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Estimasi Laba Bersih</p>
                    <h3 class="text-3xl font-bold <?php echo ($net_profit >= 0) ? 'text-green-400' : 'text-red-400'; ?> mb-1">
                        <?php echo "Rp " . number_format($net_profit, 0, ',', '.'); ?>
                    </h3>
                    <p class="text-xs text-slate-400 font-medium">
                        (Omset - Pengeluaran)
                    </p>
                </div>
            </div>

            <div id="section-chart" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-lg text-slate-800">Grafik Tren Pendapatan</h3>
                </div>
                <div class="h-80 w-full relative">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div id="section-table" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                <div class="p-5 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <h3 class="font-bold text-slate-800"><i class="fa-solid fa-list text-slate-400 mr-2"></i>Daftar Pesanan Masuk</h3>
                    <div class="flex items-center gap-2 text-sm text-slate-600 no-print">
                        <span>Baris:</span>
                        <select onchange="softLoad(buildCurrentUrl({page_ord:1, limit_ord:this.value}))" class="border border-slate-300 rounded px-2 py-1 bg-white text-xs focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="5" <?php if($limit_ord_val == 5) echo 'selected'; ?>>5</option>
                            <option value="10" <?php if($limit_ord_val == 10) echo 'selected'; ?>>10</option>
                            <option value="all" <?php if($limit_ord_val == 'all') echo 'selected'; ?>>All</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm" id="revenueTable">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4">Kode / Pickup</th>
                                <th class="px-6 py-4">Pelanggan</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">Bayar</th>
                                <th class="px-6 py-4 text-right">Total Tagihan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (count($orders_report) > 0): ?>
                                <?php foreach ($orders_report as $order): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                        <?php echo date('d/m/y H:i', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-mono font-bold text-slate-700"><?php echo $order['order_code']; ?></div>
                                        <span class="bg-slate-100 text-slate-500 text-[10px] px-1.5 py-0.5 rounded border border-slate-200">
                                            <?php echo $order['pickup_code']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-700">
                                        <?php echo $order['customer_name']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php echo getStatusBadge($order['status']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php echo renderPaymentBadge($order['payment_status']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-slate-800">
                                        <?php echo "Rp " . number_format($order['total_amount'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-8 text-slate-400">Tidak ada data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($total_ord_data > 0 && $limit_ord_val != 'all'): ?>
                <div class="p-4 border-t border-slate-100 flex justify-between items-center bg-slate-50 gap-3 no-print text-xs">
                    <span class="text-slate-500">Hal <?php echo $page_ord; ?> dari <?php echo $total_ord_pages; ?></span>
                    <div class="flex gap-1">
                        <a href="<?php echo ($page_ord > 1) ? buildUrl(['page_ord' => $page_ord - 1]) : '#'; ?>" 
                           class="soft-link px-3 py-1 bg-white border rounded hover:bg-slate-100 <?php echo ($page_ord <= 1) ? 'opacity-50 pointer-events-none' : ''; ?>">Prev</a>
                        <a href="<?php echo ($page_ord < $total_ord_pages) ? buildUrl(['page_ord' => $page_ord + 1]) : '#'; ?>" 
                           class="soft-link px-3 py-1 bg-white border rounded hover:bg-slate-100 <?php echo ($page_ord >= $total_ord_pages) ? 'opacity-50 pointer-events-none' : ''; ?>">Next</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if (in_array($role, ['OWNER'], true)): ?>
            <div id="section-audit" class="mt-12 mb-12 no-print">
                <div class="bg-slate-800 text-white p-4 rounded-t-xl flex items-center justify-between shadow-lg">
                    <h3 class="font-bold flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-yellow-400"></i> Audit Pembayaran & Status
                    </h3>
                    <div class="flex items-center gap-2 text-sm text-slate-300 no-print">
                        <span>Baris:</span>
                        <select onchange="softLoad(buildCurrentUrl({page_aud:1, limit_aud:this.value}))" class="border border-slate-600 rounded px-2 py-1 bg-slate-700 text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                            <option value="5" <?php if($limit_aud_val == 5) echo 'selected'; ?>>5</option>
                            <option value="10" <?php if($limit_aud_val == 10) echo 'selected'; ?>>10</option>
                            <option value="all" <?php if($limit_aud_val == 'all') echo 'selected'; ?>>All</option>
                        </select>
                    </div>
                </div>
                <div class="bg-white border-x border-b border-slate-200 p-6 rounded-b-xl shadow-lg relative">
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-700 flex items-start gap-2">
                        <i class="fa-solid fa-circle-info mt-0.5"></i>
                        <div>
                            <strong>Info Audit:</strong> Tabel ini menampilkan aktivitas pesanan terbaru berdasarkan waktu update. 
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border border-slate-200 rounded-lg overflow-hidden">
                            <thead class="bg-slate-100 text-slate-600 font-bold text-xs uppercase border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Waktu Update</th>
                                    <th class="px-4 py-3">Order Info</th>
                                    <th class="px-4 py-3">Verifikasi Keuangan</th>
                                    <th class="px-4 py-3 text-center">Bukti Bayar</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (count($audit_logs) > 0): ?>
                                    <?php foreach ($audit_logs as $log): ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-4 py-3 align-top">
                                            <div class="font-bold text-slate-700"><?php echo date('d M Y', strtotime($log['updated_at'])); ?></div>
                                            <div class="text-xs text-slate-500 font-mono"><?php echo date('H:i:s', strtotime($log['updated_at'])); ?></div>
                                            <div class="text-[10px] text-slate-400 mt-1 italic">Dibuat: <?php echo date('d/m H:i', strtotime($log['created_at'])); ?></div>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <div class="font-mono text-sm font-bold text-slate-800">#<?php echo htmlspecialchars($log['order_code']); ?></div>
                                            <div class="text-xs text-slate-500"><?php echo htmlspecialchars($log['customer_name']); ?></div>
                                            <div class="mt-1 inline-block px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded text-[10px] font-mono">
                                                Pick: <?php echo htmlspecialchars($log['pickup_code']); ?>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <div class="flex flex-col gap-1 text-xs">
                                                <div class="flex justify-between">
                                                    <span class="text-slate-500">Tagihan:</span>
                                                    <span class="font-bold"><?php echo "Rp " . number_format($log['total_amount'], 0, ',', '.'); ?></span>
                                                </div>
                                                <div class="flex justify-between border-b border-slate-200 pb-1 mb-1">
                                                    <span class="text-slate-500">Masuk:</span>
                                                    <span class="font-bold text-emerald-600"><?php echo "Rp " . number_format($log['paid_amount'] ?? 0, 0, ',', '.'); ?></span>
                                                </div>
                                                <div class="text-center mt-1">
                                                    <?php echo renderPaymentBadge($log['payment_status']); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-middle text-center">
                                            <?php if (!empty($log['proof_image'])): 
                                                $proofPath = ltrim($log['proof_image'], '/');
                                                if (strpos($proofPath, 'src/') === 0) {
                                                    $proofPath = substr($proofPath, 4);
                                                }
                                                $proofToken = urlencode(base64_encode($proofPath));
                                                $viewerUrl = "../order/proof_viewer.php?token=" . $proofToken;
                                            ?>
                                                <a href="<?php echo $viewerUrl; ?>" 
                                                   target="_blank" 
                                                   class="bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 px-3 py-1 rounded text-xs transition flex items-center gap-1 mx-auto w-fit btn-action">
                                                    <i class="fa-solid fa-image"></i> Lihat
                                                </a>
                                                <div class="text-[10px] text-slate-400 mt-1">Transfer</div>
                                            <?php else: ?>
                                                <span class="bg-slate-50 text-slate-400 border border-slate-200 px-2 py-1 rounded text-[10px] font-medium block w-fit mx-auto">
                                                    COD / Tunai
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <div class="flex flex-col gap-1">
                                                <span class="inline-block w-fit"><?php echo getStatusBadge($log['status']); ?></span>
                                                <div class="text-xs text-slate-500 mt-1">
                                                    <i class="fa-solid fa-user-gear text-slate-400"></i>
                                                    <?php echo htmlspecialchars($log['staff_name'] ?? '-'); ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="p-8 text-center text-slate-400 italic">Belum ada aktivitas update order.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if($total_aud_data > 0 && $limit_aud_val != 'all'): ?>
                    <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center gap-3 no-print text-xs">
                        <span class="text-slate-500">Hal <?php echo $page_aud; ?> dari <?php echo $total_aud_pages; ?></span>
                        <div class="flex gap-1">
                            <a href="<?php echo ($page_aud > 1) ? buildUrl(['page_aud' => $page_aud - 1]) : '#'; ?>" 
                               class="soft-link px-3 py-1 bg-white border rounded hover:bg-slate-100 <?php echo ($page_aud <= 1) ? 'opacity-50 pointer-events-none' : ''; ?>">Prev</a>
                            <a href="<?php echo ($page_aud < $total_aud_pages) ? buildUrl(['page_aud' => $page_aud + 1]) : '#'; ?>" 
                               class="soft-link px-3 py-1 bg-white border rounded hover:bg-slate-100 <?php echo ($page_aud >= $total_aud_pages) ? 'opacity-50 pointer-events-none' : ''; ?>">Next</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        let revenueChart = null;

        document.addEventListener('DOMContentLoaded', function() {
            initChart();
            setupSoftNavigation();
        });

        function initChart() {
            const ctx = document.getElementById('revenueChart');
            if(!ctx) return;

            const dataEl = document.getElementById('chart-data');
            if(!dataEl) return;
            
            const labels = JSON.parse(dataEl.dataset.labels);
            const dataValues = JSON.parse(dataEl.dataset.values);

            if (revenueChart) {
                revenueChart.destroy();
            }

            const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(22, 163, 74, 0.5)'); 
            gradient.addColorStop(1, 'rgba(22, 163, 74, 0.0)'); 

            revenueChart = new Chart(ctx, {
                type: 'line', 
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Omset',
                        data: dataValues,
                        backgroundColor: gradient,
                        borderColor: '#16a34a', 
                        borderWidth: 2,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#16a34a',
                        fill: true,
                        tension: 0.3 
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 500 }, 
                    plugins: {
                        legend: { display: false }, 
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) { label += ': '; }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: function(value) { return 'Rp ' + (value/1000).toLocaleString('id-ID') + 'k'; } }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        function setupSoftNavigation() {
            document.querySelectorAll('.soft-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    softLoad(this.href);
                });
            });

            const form = document.getElementById('filterForm');
            if(form) {
                form.onsubmit = function(e) {
                    e.preventDefault();
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData);
                    const url = window.location.pathname + '?' + params.toString();
                    softLoad(url);
                };
            }
        }

        function softLoad(url) {
            document.getElementById('page-loader').style.display = 'flex';
            
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    window.history.pushState({}, '', url);

                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const newContent = doc.getElementById('dynamic-content').innerHTML;
                    document.getElementById('dynamic-content').innerHTML = newContent;

                    initChart();
                    setupSoftNavigation();

                    document.getElementById('page-loader').style.display = 'none';
                })
                .catch(err => {
                    console.error('Gagal load:', err);
                    document.getElementById('page-loader').style.display = 'none';
                    alert('Gagal memuat data. Silakan refresh manual.');
                });
        }

        function buildCurrentUrl(params) {
            const url = new URL(window.location.href);
            for (const key in params) {
                url.searchParams.set(key, params[key]);
            }
            return url.toString();
        }

        function printSection(mode) {
            document.body.classList.remove('print-mode-chart', 'print-mode-table');
            document.body.classList.add('print-mode-' + mode);
            window.print();
            setTimeout(() => { document.body.classList.remove('print-mode-' + mode); }, 1000);
        }

        function exportTableToCSV(filename) {
            var csv = [];
            var rows = document.querySelectorAll("#revenueTable tr");
            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll("td, th");
                for (var j = 0; j < cols.length; j++) 
                    row.push('"' + cols[j].innerText.replace(/\n/g, " ") + '"');
                csv.push(row.join("\n"));        
            }
            var blob = new Blob(["\ufeff", csv.join("\n")], { type: 'text/csv;charset=utf-8;' });
            var url = URL.createObjectURL(blob);
            var downloadLink = document.createElement("a");
            downloadLink.href = url;
            downloadLink.download = filename;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
    </script>
</body>
</html>