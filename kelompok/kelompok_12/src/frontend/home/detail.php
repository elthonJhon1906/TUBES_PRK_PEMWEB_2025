<?php
session_start();
require_once '../../koneksi/database.php';

$kategori = isset($_GET['kategori']) ? bersihkan_input($_GET['kategori']) : '';

$konten = [
    'judul' => 'Layanan Tidak Ditemukan',
    'subjudul' => 'Maaf, kategori layanan yang Anda cari tidak tersedia.',
    'icon' => 'fa-circle-question',
    'banner' => 'https://images.unsplash.com/photo-1594322436404-5a0526db4d13?q=80&w=2029&auto=format&fit=crop',
    'data_tabel' => []
];

switch ($kategori) {
    case 'print':
        $konten['judul'] = 'Jasa Print & Fotokopi';
        $konten['subjudul'] = 'Cetak dokumen A4, F4, A3 dengan berbagai jenis kertas berkualitas.';
        $konten['icon'] = 'fa-print';
        $konten['banner'] = '../../img/gambar_print.jpg';
        
        $sql = "SELECT name as nama, description as spek, base_price as harga, unit 
                FROM services 
                WHERE (category = 'Print' OR category = 'Fotokopi' OR category = 'Scan' OR category = 'Cetak' OR category = 'Fotocopy') 
                AND is_active = 1 
                ORDER BY category ASC, base_price ASC";
        $konten['data_tabel'] = ambil_banyak_data($sql);
        break;

    case 'jilid':
        $konten['judul'] = 'Jasa Jilid Profesional';
        $konten['subjudul'] = 'Finishing rapi untuk skripsi, laporan, dan dokumen penting Anda.';
        $konten['icon'] = 'fa-book-open';
        $konten['banner'] = '../../img/gambar_jilid.jpg';
        
        $sql = "SELECT name as nama, description as spek, base_price as harga, unit 
                FROM services 
                WHERE (category = 'Jilid' OR category = 'Finishing') 
                AND is_active = 1 
                ORDER BY base_price ASC";
        $konten['data_tabel'] = ambil_banyak_data($sql);
        break;

    case 'atk':
        $konten['judul'] = 'Suplai Alat Tulis Kantor (ATK)';
        $konten['subjudul'] = 'Sedia stok lengkap untuk kebutuhan operasional kantor dan sekolah.';
        $konten['icon'] = 'fa-box-open';
        $konten['banner'] = '../../img/gambar_atk.jpg';
        
        $sql = "SELECT name as nama, CONCAT(category, ' - ', unit) as spek, selling_price as harga, unit 
                FROM products 
                WHERE is_active = 1 
                ORDER BY category ASC, name ASC";
        $konten['data_tabel'] = ambil_banyak_data($sql);
        break;

    default:
        header("Location: ../../index.php");
        exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $konten['judul']; ?> - NPC System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { npcGreen: '#10B981', npcDark: '#0F172A' }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-700 font-sans">

    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="../../index.php" class="flex items-center gap-2 group text-gray-600 hover:text-npcGreen transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="font-bold">Kembali ke Beranda</span>
            </a>
            <div class="font-bold text-npcDark flex items-center gap-2">
                <i class="fa-solid fa-print text-npcGreen"></i> NPC System
            </div>
        </div>
    </nav>

    <header class="relative h-64 md:h-80 flex items-center justify-center overflow-hidden bg-npcDark">
        <img src="<?php echo $konten['banner']; ?>" alt="Banner" class="absolute inset-0 w-full h-full object-cover opacity-40">
        <div class="absolute inset-0 bg-gradient-to-t from-npcDark to-transparent"></div>
        
        <div class="relative z-10 text-center px-6 mt-8">
            <div class="w-16 h-16 bg-npcGreen rounded-2xl flex items-center justify-center text-white text-3xl mx-auto mb-4 shadow-lg shadow-green-500/30">
                <i class="fa-solid <?php echo $konten['icon']; ?>"></i>
            </div>
            <h1 class="text-3xl md:text-5xl font-bold text-white mb-2"><?php echo $konten['judul']; ?></h1>
            <p class="text-gray-300 text-lg max-w-2xl mx-auto"><?php echo $konten['subjudul']; ?></p>
        </div>
    </header>

    <div class="max-w-5xl mx-auto px-6 py-12 -mt-10 relative z-20">
        
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center bg-gray-50 gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-white p-2 rounded-lg text-npcGreen shadow-sm"><i class="fa-solid fa-list-check"></i></div>
                    <h3 class="font-bold text-npcDark text-lg">Daftar Harga & Spesifikasi</h3>
                </div>
                <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-bold uppercase tracking-wider flex items-center gap-1">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Live Stok
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="p-4 pl-6 font-bold">Nama Layanan / Produk</th>
                            <th class="p-4 font-bold">Spesifikasi / Detail</th>
                            <th class="p-4 font-bold text-right pr-6">Harga Satuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(count($konten['data_tabel']) > 0): ?>
                            <?php foreach($konten['data_tabel'] as $item): ?>
                            <tr class="hover:bg-green-50/30 transition group">
                                <td class="p-4 pl-6 font-semibold text-npcDark group-hover:text-npcGreen transition">
                                    <?php echo htmlspecialchars($item['nama']); ?>
                                </td>
                                <td class="p-4 text-gray-500 text-sm">
                                    <?php 
                                        echo !empty($item['spek']) ? htmlspecialchars($item['spek']) : '-'; 
                                    ?>
                                </td>
                                <td class="p-4 pr-6 text-right font-bold text-npcDark">
                                    <?php 
                                        if(function_exists('format_rupiah')) {
                                            echo format_rupiah($item['harga']);
                                        } else {
                                            echo "Rp " . number_format($item['harga'], 0, ',', '.');
                                        }
                                    ?>
                                    <span class="text-xs text-gray-400 font-normal ml-1">/ <?php echo strtolower($item['unit']); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="p-8 text-center text-gray-400">
                                    <i class="fa-solid fa-box-open text-4xl mb-3 block opacity-30"></i>
                                    Belum ada data tersedia untuk kategori ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 bg-blue-50 border border-blue-100 rounded-xl p-6 flex gap-4 items-start">
                <div class="bg-white p-2 rounded-full shadow-sm text-blue-500"><i class="fa-solid fa-circle-info text-xl"></i></div>
                <div>
                    <h4 class="font-bold text-blue-800 mb-1">Informasi Penting</h4>
                    <p class="text-sm text-blue-600 leading-relaxed">
                        Harga yang tertera diambil langsung dari sistem database kami. Untuk pemesanan dalam jumlah besar (grosir) atau kebutuhan perusahaan, silakan hubungi admin untuk mendapatkan penawaran khusus.
                    </p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-6 text-center shadow-lg sticky top-24">
                <p class="text-sm text-gray-500 mb-4 font-medium">Tertarik dengan layanan ini?</p>
                
                <?php if(isset($_SESSION['status']) && $_SESSION['status'] == "login"): ?>
                    <a href="create_order.php" class="block w-full bg-npcDark text-white font-bold py-3.5 rounded-xl hover:bg-slate-800 transition transform hover:-translate-y-1 shadow-lg">
                        <i class="fa-solid fa-cart-plus mr-2"></i> Buat Pesanan
                    </a>
                    <p class="text-xs text-gray-400 mt-3">Masuk ke halaman Order Entry</p>
                <?php else: ?>
                    <a href="../login/login.php?redirect=order" class="block w-full bg-npcGreen text-white font-bold py-3.5 rounded-xl hover:bg-green-600 transition shadow-glow transform hover:-translate-y-1 mb-2">
                        <i class="fa-solid fa-lock mr-2"></i> Login untuk Pesan
                    </a>
                    <p class="text-xs text-red-400 font-medium bg-red-50 py-1 px-2 rounded mt-2 inline-block">
                        *Wajib login akun member
                    </p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <footer class="bg-white border-t border-gray-100 py-8 mt-12 text-center text-sm text-gray-400">
        <p>&copy; <?php echo date('Y'); ?> Nagoya Print & Copy. Harga dapat berubah sewaktu-waktu.</p>
    </footer>

</body>
</html>