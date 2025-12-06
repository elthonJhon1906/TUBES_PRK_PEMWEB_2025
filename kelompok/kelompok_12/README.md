# Sistem Manajemen Operasional dan Keuangan Fotocopy

Proyek ini bertujuan membantu pengelola usaha fotokopi kecil-menengah dalam mengawasi operasional harian, pencatatan transaksi layanan, dan arus kas masuk/keluar secara terpadu sehingga keputusan bisnis bisa diambil berbasis data.

## Anggota Kelompok 12

| NPM        | Nama                          |
| ---------- | ----------------------------- |
| 2315061115 | M. Azmi Edfa Alhafizh         |
| 2315061051 | Arza Restu Arjuna             |
| 2315061116 | Muhamad Rakha Hadyan Pangestu |
| 2315061018 | Elthon Jhon Kevin             |

## Ringkasan Fitur

- **Manajemen Layanan:** pendataan paket fotokopi, print, jilid, dan layanan tambahan sesuai kebutuhan cabang.
- **Pencatatan Operasional:** input bahan/material, pengeluaran harian, jadwal shift operator, serta status mesin.
- **Keuangan & Pelaporan:** rekap pemasukan harian/bulanan, pengeluaran, margin, serta laporan yang siap diunduh.
- **Hak Akses Pengguna:** akun operator, kasir, dan admin dengan batasan akses berbeda.

## Struktur Direktori

```
kelompok_12/
├── src/            # Sumber kode aplikasi PHP
├── database/       # Skrip SQL siap impor
├── erd/            # Diagram ERD (PNG/PDF)
└── screenshots/    # Tangkapan layar antarmuka
```

## Prasyarat

- PHP 8.1+ dengan ekstensi PDO MySQL aktif.
- MySQL Server (disarankan 8.x).
- Web browser modern (Chrome/Firefox/Edge).

## Cara Menjalankan Secara Lokal

1. **Kloning repositori & masuk ke folder kelompok.**
   ```bash
   git clone <repo-anda>.git
   cd TUBES_PRK_PEMWEB_2025/kelompok/kelompok_12
   ```
2. **Import struktur database.**
   ```bash
   mysql -u root -p percetakan < src/database/database.sql
   ```
3. **Atur koneksi database** (opsional bila tidak memakai kredensial bawaan).
   - Default berada pada `127.0.0.1:3306`, DB `percetakan`, user `root`, tanpa sandi.
   - Override dengan mengekspor variabel lingkungan sebelum menjalankan server:
     ```bash
     export DB_HOST=127.0.0.1
     export DB_PORT=3306
     export DB_DATABASE=percetakan
     export DB_USERNAME=root
     export DB_PASSWORD=your_password
     ```
4. **Jalankan server PHP built-in.**
   ```bash
   cd src
   php -S localhost:8000 -t .
   ```
5. **Akses aplikasi** lewat `http://localhost:8000` dan login sesuai data awal pada database.

## Catatan Pengembangan

- ERD dan dokumentasi lain tersedia pada folder `erd/` serta `screenshots/`.
- Gunakan branch/commit terpisah untuk fitur baru supaya integrasi ke CI laboratorium berjalan lancar.

---

> Laboratorium Teknik Komputer — Final Project Praktikum Pemrograman Web 2025
