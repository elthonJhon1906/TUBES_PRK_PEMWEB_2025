# Sistem Manajemen Operasional & Keuangan NPC

## 👥 Daftar Anggota Kelompok 04

| NPM | Nama |
| :---------- | :---------------------------- |
| 2315061115 | M. Azmi Edfa Alhafizh |
| 2315061051 | Arza Restu Arjuna |
| 2315061116 | Muhamad Rakha Hadyan Pangestu |
| 2315061018 | Elthon Jhon Kevin |

## 📘 Judul & Ringkasan Proyek

*Judul:* Sistem Manajemen Operasional & Keuangan Nagoya Print & Copy (NPC System)

Portal berbasis PHP untuk mengelola order cetak, layanan kasir POS, stok ATK, serta pelaporan keuangan pada outlet fotokopi. Aplikasi menyediakan dashboard role-based (customer, staff, admin, owner), modul katalog/keranjang, manajemen produk & layanan, input pengeluaran, hingga laporan omset dengan grafik. Sistem ini bertujuan untuk menyediakan layanan cetak dokumen **Tanpa Antre, Tanpa Ribet** bagi pelanggan.

## ✨ Fitur Utama Sistem

Berdasarkan implementasi, sistem ini menyediakan fitur sebagai berikut:

1.  **Halaman Beranda & Informasi Umum**
    * Tampilan utama yang menyajikan informasi layanan cetak dokumen, jilid skripsi, fotokopi, dan pengadaan ATK secara digital.
    * **Keunggulan NPC System:** Transparan (status pengerjaan *real-time*), Fleksibel (Upload PDF, Word, Gambar), Aman (Dokumen dijamin kerahasiaannya).
    ![Halaman Beranda NPC System](dokumentasi/Screenshot%202025-12-11%20214558.jpg)
    ![Tentang Kami & Layanan](dokumentasi/Screenshot%202025-12-11%20214614.png)

2.  **Katalog & Daftar Harga Produk**
    * Menampilkan detail layanan seperti **Jasa Print & Fotokopi** (contoh: Fotokopi Rp 500/lembar) dan **Jasa Jilid Profesional**.
    * Tersedia informasi **Live Stok** dan spesifikasi detail produk yang tersedia.
    ![Daftar Harga Jasa Print & Fotokopi](dokumentasi/Screenshot%202025-12-11%20214646.jpg)
    ![Daftar Harga Jasa Jilid Profesional](dokumentasi/Screenshot%202025-12-11%20214704.jpg)
    ![Daftar Harga Suplai Alat Tulis Kantor (ATK)](dokumentasi/Screenshot%202025-12-11%20214722.jpg)

3.  **Sistem Pelacakan Pesanan (*Order Tracking*)**
    * Pelanggan dapat memasukkan Kode Pickup untuk melihat status pengerjaan secara *real-time*.
    * Status yang disajikan meliputi: **Standby**, **Diproses**, **Siap Ambil**, dan **Selesai (*Completed*)**.
    ![Lacak Pesanan Anda](dokumentasi/Screenshot%202025-12-11%20214547.png)

4.  **Manajemen Akun Pengguna**
    * Terdapat modul **Login** untuk pengguna yang sudah terdaftar.
    * Fitur **Lupa Password** yang memungkinkan pengguna melakukan *reset* melalui email.
    ![Halaman Login](dokumentasi/Screenshot%202025-12-11%20214733.jpg)
    ![Halaman Lupa Password](dokumentasi/Screenshot%202025-12-11%20214743.png)

## 🚀 Cara Menjalankan di Windows (XAMPP)

1. **Salin proyek ke htdocs**
    - `git clone <url-repo>` atau ekstrak ZIP ke `C:\xampp\htdocs\`.
2. **Jalankan Apache & MySQL** dari XAMPP Control Panel.
3. **Import database**
    - Buka `http://localhost/phpmyadmin`.
    - Buat database misal `npc_printing_db`.
    - Import `sql/database.sql`.
4. **Atur koneksi** di `src\koneksi\database.php` (host `localhost`, user `root`, password kosong).
5. **Akses aplikasi** via `http://localhost/TUBES_PRK_PEMWEB_2025/kelompok/kelompok_12/index.php`.
6. **Login contoh**: `admin/admin123`, `staff/staff123`, `owner/owner123`, `edfa/123`.

*Catatan Kaki:*

* **Alamat Toko:** Jl. Kampung Baru, Bandar Lampung.
* **Kontak:** (0898) 123-4567.
* **Jam Operasional:** Senin - Minggu (08:00 - 20:00).
![Footer Informasi Kontak](dokumentasi/Screenshot%202025-12-11%20214629.png)
