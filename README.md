# Masjid Jami' Nurul Iman

**Sistem Informasi, Pembukuan & Platform Donasi Digital Masjid**

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Apache](https://img.shields.io/badge/Apache-2.4-D22128?style=flat-square&logo=apache&logoColor=white)](https://httpd.apache.org)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](#license)

---

## Tentang Proyek

Aplikasi web terintegrasi untuk pengelolaan masjid yang mencakup **website publik**, **sistem pembukuan kas**, **platform donasi online**, dan **portal donatur**. Dibangun dengan arsitektur modular PHP murni tanpa framework, dirancang untuk transparansi keuangan, kemudahan administrasi, dan pengalaman jamaah yang modern.

---

## Fitur Utama

| Modul | Deskripsi |
|-------|-----------|
| **Website Publik** | Beranda, profil masjid, program donasi, berita/kegiatan, kajian YouTube, transparansi keuangan, kontak |
| **Admin Panel** | Dashboard keuangan, manajemen berita, program donasi, pengguna, banner, rekening, laporan transaksi |
| **Pembukuan Kas** | Pencatatan pemasukan/pengeluaran, kategori transaksi, bukti transaksi, filter periode, laporan cetak |
| **Donasi Online** | Formulir donasi via QRIS & transfer bank, verifikasi admin, e-Kwitansi otomatis, notifikasi donatur |
| **Portal Donatur** | Dashboard donatur, riwayat donasi, update profil, cetak kwitansi mandiri |
| **Autentikasi** | Login multi-role (admin, bendahara, content_admin, donatur), registrasi, lupa/reset password |
| **Transparansi Kas** | Laporan keuangan publik, saldo kas terbuka, grafik pemasukan & pengeluaran |
| **Integrasi YouTube** | Embed video kajian, live streaming, dokumentasi kegiatan masjid |

---

## Struktur Folder

```
mesjid-donasi-main/
|
|-- admin/                          # Panel Administrator
|   |-- dashboard.php               # Dashboard keuangan & eksekutif terpadu
|   |-- berita-admin.php            # Manajemen berita & artikel
|   |-- banner-admin.php            # Manajemen slider & banner promosi
|   |-- program-admin.php           # CRUD program donasi masjid
|   |-- rekening-admin.php          # Kelola rekening donasi & QRIS
|   |-- transaksi.php               # Pencatatan transaksi kas (pembukuan)
|   |-- verifikasi-donasi.php       # Verifikasi & persetujuan donasi online
|   |-- laporan.php                 # Laporan keuangan & cetak
|   |-- profil-admin.php            # Edit profil & pengaturan masjid
|   |-- youtube-admin.php           # Kelola video YouTube & kajian
|   |-- users-admin.php             # Manajemen pengguna & hak akses
|
|-- assets/                         # Aset Statis (CSS, JS, Gambar)
|   |-- css/
|       |-- classic-theme.css       # Tema klasik Islami (Deep Cypress, Antique Gold)
|
|-- auth/                           # Autentikasi & Manajemen Sesi
|   |-- login.php                   # Halaman masuk
|   |-- register.php                # Registrasi akun baru
|   |-- logout.php                  # Proses logout
|   |-- forgot-password.php         # Lupa password (kirim token reset)
|   |-- reset-password.php          # Form reset password via token
|
|-- config/                         # Konfigurasi Aplikasi
|   |-- database.php                # Koneksi DB (PDO), helper functions, session, upload
|
|-- db/                             # Database & Migrasi
|   |-- database_schema_v2.sql      # Skema database lengkap + data seeder awal
|   |-- mesjid_website.sql          # Database dump / backup
|   |-- migrate.php                 # Script migrasi database
|
|-- donatur/                        # Portal Donatur
|   |-- portal-donatur.php          # Dashboard mandiri donatur
|   |-- kwitansi.php                # Cetak e-Kwitansi donasi
|
|-- home/                           # Halaman Publik (Frontend)
|   |-- profil.php                  # Profil, sejarah, visi-misi masjid
|   |-- program.php                 # Katalog seluruh program donasi
|   |-- program-detail.php          # Detail program & progress donasi
|   |-- donasi-online.php           # Formulir donasi online (QRIS & transfer)
|   |-- transparansi.php            # Laporan keuangan transparan publik
|   |-- berita.php                  # Daftar berita & artikel
|   |-- berita-detail.php           # Detail berita (full text)
|   |-- kajian.php                  # Galeri video kajian & live streaming
|   |-- kontak.php                  # Halaman kontak & formulir pesan
|
|-- layouts/                        # Komponen Layout (Reusable)
|   |-- header.php                  # Header admin panel
|   |-- footer.php                  # Footer admin panel
|   |-- sidebar.php                 # Sidebar navigasi admin
|   |-- public_header.php           # Header website publik
|   |-- public_footer.php           # Footer website publik
|
|-- uploads/                        # Direktori Unggahan (User Uploads)
|   |-- banner/                     # Gambar slider & banner
|   |-- berita/                     # Thumbnail berita
|   |-- bukti/                      # Bukti transfer pembayaran donasi
|   |-- program/                    # Gambar program donasi
|   |-- qris/                       # Gambar QRIS
|   |-- pengurus/                   # Foto pengurus DKM
|
|-- index.php                       # Beranda utama (entry point publik)
|-- donasi.php                      # Endpoint donasi cepat
|-- kegiatan.php                    # Halaman kegiatan masjid
|-- 404.php                         # Halaman error 404 kustom
|-- .htaccess                       # Konfigurasi Apache (URL rewrite, keamanan)
|-- add_socmed_columns.php          # Script migrasi tambah kolom sosmed
|-- README.md                       # Dokumentasi proyek
```

---

## Database

Sistem menggunakan **13 tabel** utama dengan engine InnoDB dan charset `utf8mb4`:

| # | Tabel | Fungsi |
|---|-------|--------|
| 1 | `users` | Pengguna & multi-role RBAC (admin, bendahara, content_admin, donatur) |
| 2 | `profil_masjid` | Profil, sejarah, visi-misi, kontak, sosial media masjid |
| 3 | `pengurus_masjid` | Data pengurus DKM (nama, jabatan, bidang, foto) |
| 4 | `rekening_donasi` | Rekening bank & QRIS donasi masjid |
| 5 | `program_donasi` | Program donasi (target, terkumpul, status, kategori) |
| 6 | `kategori_transaksi` | Master kategori pemasukan & pengeluaran kas |
| 7 | `transaksi_keuangan` | Buku kas transaksi (pembukuan terintegrasi) |
| 8 | `donasi_online` | Donasi masuk dari jamaah (pending/verifikasi/ditolak) |
| 9 | `berita` | Berita, artikel & kegiatan masjid |
| 10 | `banners` | Slider & banner promosi beranda |
| 11 | `youtube_videos` | Integrasi video YouTube (kajian, live, dokumentasi) |
| 12 | `notifikasi` | Notifikasi untuk pengguna & donatur |
| 13 | `pesan_kontak` | Pesan masuk dari formulir kontak jamaah |

### Akun Default

| Role | Username | Password |
|------|----------|----------|
| Administrator | `admin` | `admin123` |
| Bendahara | `bendahara` | `bendahara123` |
| Content Admin | `konten` | `konten123` |
| Donatur | `donatur` | `donatur123` |

> Ganti semua password default sebelum deployment ke production.

---

## Persyaratan

- **PHP** >= 8.0 (PDO MySQL, mbstring, json, openssl)
- **MySQL** >= 8.0 / MariaDB >= 10.5
- **Apache** dengan mod `rewrite` aktif (atau **Laragon** / **XAMPP**)
- **Composer** *(tidak wajib, project murni tanpa dependency)*

---

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/mesjid-donasi-main.git
cd mesjid-donasi-main
```

### 2. Konfigurasi Web Server

Jika menggunakan **Laragon**, cukup copy folder project ke `C:\laragon\www\` dan akses melalui `http://mesjid-donasi-main.test`.

Pastikan mod `rewrite` Apache aktif untuk `.htaccess`.

### 3. Buat Database

```sql
CREATE DATABASE mesjid_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Import skema database:

```bash
mysql -u root -p mesjid_website < db/database_schema_v2.sql
```

Atau gunakan **phpMyAdmin** untuk import file `db/database_schema_v2.sql`.

### 4. Konfigurasi Koneksi

Edit `config/database.php` sesuai environment Anda:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mesjid_website');
```

### 5. Akses Aplikasi

| URL | Deskripsi |
|-----|-----------|
| `http://localhost/mesjid-donasi-main/` | Beranda publik |
| `http://localhost/mesjid-donasi-main/auth/login.php` | Halaman login |
| `http://localhost/mesjid-donasi-main/admin/dashboard.php` | Dashboard admin |

---

## Tema & Desain

Menggunakan tema klasik Islami dengan palet warna:

| Warna | Kode | Penggunaan |
|-------|------|------------|
| Deep Cypress Green | `#183728` | Warna primer, header, navbar |
| Antique Brass Gold | `#c5a059` | Aksen emas, judul, highlight |
| Warm Alabaster | `#fbf9f5` | Background body |
| Warm Cream | `#f4efe4` | Background surface/card |

**Font:** Plus Jakarta Sans (body), Amiri & Cinzel (heading klasik Islami)

---

## Lisensi

Proyek ini menggunakan lisensi MIT. Bebas digunakan, dimodifikasi, dan didistribusikan.

---

<div align="center">

**"Memakmurkan Masjid, Mensejahterakan Ummat"**

*Masjid Jami' Nurul Iman &mdash; Pusat Dakwah, Ibadah, dan Pemberdayaan Ummat*

</div>
