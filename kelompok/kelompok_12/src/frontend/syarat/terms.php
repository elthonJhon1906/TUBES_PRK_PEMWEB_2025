<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat & Ketentuan - NPC System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
<body class="bg-gray-50 font-sans text-gray-600">

    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-6 h-16 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="bg-npcGreen text-white font-bold text-lg px-2 py-0.5 rounded">NPC</div>
                <span class="font-bold text-npcDark">Nagoya Print</span>
            </div>
            <a href="../register/register.php">
                <i class="fa-solid fa-xmark mr-1"></i> Tutup
            </a>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-6 py-12">
        
        <div class="bg-white p-8 md:p-12 rounded-2xl shadow-sm border border-gray-100">
            <h1 class="text-3xl font-bold text-npcDark mb-2">Syarat dan Ketentuan</h1>
            <p class="text-gray-400 text-sm mb-8">Terakhir diperbarui: 09 Desember 2025</p>

            <div class="space-y-8 text-justify leading-relaxed">
                
                <section>
                    <h3 class="text-lg font-bold text-npcDark mb-3">1. Pendahuluan</h3>
                    <p>Selamat datang di NPC System (Nagoya Print & Copy). Dengan mengakses atau menggunakan layanan kami (baik online maupun offline), Anda setuju untuk terikat oleh syarat dan ketentuan ini. Jika Anda tidak setuju dengan bagian mana pun dari syarat ini, Anda dilarang menggunakan layanan kami.</p>
                </section>

                <section>
                    <h3 class="text-lg font-bold text-npcDark mb-3">2. Layanan Percetakan</h3>
                    <ul class="list-disc pl-5 space-y-2">
                        <li><strong>File Pelanggan:</strong> Pelanggan bertanggung jawab penuh atas file yang diunggah. NPC tidak bertanggung jawab atas isi dokumen (hak cipta, kerahasiaan) yang dicetak.</li>
                        <li><strong>Kualitas File:</strong> Kualitas hasil cetak sangat bergantung pada resolusi file yang dikirimkan pelanggan. Kami menyarankan file format PDF siap cetak.</li>
                        <li><strong>Waktu Pengerjaan:</strong> Estimasi waktu yang diberikan sistem adalah perkiraan. Keterlambatan akibat kerusakan mesin atau *force majeure* akan diinformasikan kepada pelanggan.</li>
                    </ul>
                </section>

                <section>
                    <h3 class="text-lg font-bold text-npcDark mb-3">3. Pembayaran & Harga</h3>
                    <p>Harga yang tertera di website dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya. Pembayaran untuk pesanan online wajib dilakukan dimuka (transfer) atau sesuai kesepakatan (DP) sebelum proses produksi dimulai.</p>
                </section>

                <section>
                    <h3 class="text-lg font-bold text-npcDark mb-3">4. Kebijakan Pengembalian (Refund) & Revisi</h3>
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <p class="text-sm text-yellow-800 font-medium">
                            Komplain hasil cetak hanya diterima maksimal 1x24 jam setelah barang diambil/diterima.
                        </p>
                    </div>
                    <ul class="list-disc pl-5 space-y-2 mt-3">
                        <li><strong>Kesalahan Produksi:</strong> Jika kesalahan murni dari pihak NPC (salah potong, tinta luntur, salah kertas), kami akan mencetak ulang tanpa biaya tambahan.</li>
                        <li><strong>Kesalahan File:</strong> Kami tidak menerima refund/cetak ulang gratis jika kesalahan berasal dari file pelanggan (typo, gambar pecah, margin salah).</li>
                    </ul>
                </section>

                <section>
                    <h3 class="text-lg font-bold text-npcDark mb-3">5. Pengambilan Barang</h3>
                    <p>Barang yang sudah selesai diproduksi wajib diambil maksimal dalam waktu 30 hari. NPC berhak memusnahkan dokumen/barang yang tidak diambil lebih dari 3 bulan untuk menghemat ruang penyimpanan.</p>
                </section>

                <section>
                    <h3 class="text-lg font-bold text-npcDark mb-3">6. Privasi Data</h3>
                    <p>Kami menjaga kerahasiaan dokumen pelanggan. File yang diupload ke server kami akan dihapus secara berkala (7-14 hari) setelah pesanan selesai untuk menjaga keamanan data Anda.</p>
                </section>

            </div>

            <div class="mt-12 pt-8 border-t border-gray-100 flex justify-between items-center">
                <a href="index.php" class="px-6 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition">Kembali</a>
                <button onclick="window.scrollTo({top: 0, behavior: 'smooth'});" class="text-npcGreen font-bold hover:underline">Ke Atas <i class="fa-solid fa-arrow-up ml-1"></i></button>
            </div>
        </div>

    </main>

    <footer class="bg-white border-t border-gray-200 py-8 text-center">
        <p class="text-gray-400 text-sm">&copy; 2025 Nagoya Print & Copy. All rights reserved.</p>
    </footer>

</body>
</html>