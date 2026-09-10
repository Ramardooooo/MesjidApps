# Masjid Jami' Nurul Iman

**Sistem Informasi, Pembukuan Kas & Platform Donasi Digital Masjid**

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![PHPMailer](https://img.shields.io/badge/PHPMailer-7.x-31A4DB?style=flat-square&logo=maildotru&logoColor=white)](https://github.com/PHPMailer/PHPMailer)
[![Apache](https://img.shields.io/badge/Apache-2.4-D22128?style=flat-square&logo=apache&logoColor=white)](https://httpd.apache.org)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](#license)

---

## Tentang Proyek

Aplikasi web terintegrasi untuk pengelolaan masjid yang mencakup **website publik**, **sistem pembukuan kas**, **platform donasi online**, dan **portal donatur**. Dibangun dengan arsitektur modular PHP murni tanpa framework, dirancang untuk transparansi keuangan, kemudahan administrasi, dan pengalaman jamaah yang modern.

---

## Fitur Utama

| Modul | Deskripsi |
|-------|-----------|
| **Website Publik** | Beranda, profil masjid & pengurus DKM, program donasi, berita/kegiatan, kajian YouTube, transparansi keuangan, kontak |
| **Admin Panel** | Dashboard keuangan, manajemen berita, program donasi, pengguna, banner, rekening & QRIS, video YouTube, laporan transaksi |
| **Pembukuan Kas** | Pencatatan pemasukan/pengeluaran, kategori transaksi, bukti transaksi, filter periode, ekspor CSV/Excel, laporan cetak |
| **Donasi Online** | Formulir donasi via QRIS & transfer bank, verifikasi admin, e-Kwitansi otomatis, notifikasi donatur |
| **Portal Donatur** | Dashboard donatur, riwayat donasi, update profil, cetak kwitansi mandiri |
| **Autentikasi** | Login multi-role (admin, bendahara, content_admin, donatur), registrasi, lupa/reset password via OTP |
| **Transparansi Kas** | Laporan keuangan publik, saldo kas terbuka, grafik pemasukan & pengeluaran |
| **Integrasi YouTube** | Embed video kajian, live streaming, dokumentasi kegiatan masjid |

---

## Struktur Folder

```
mesjid-website/
|
|-- admin/                          Panel Administrator
|   |-- dashboard.php               Dashboard keuangan & eksekutif terpadu
|   |-- berita-admin.php            Manajemen berita & artikel
|   |-- banner-admin.php            Manajemen slider & banner promosi
|   |-- program-admin.php           CRUD program donasi masjid
|   |-- rekening-admin.php          Kelola rekening donasi & QRIS
|   |-- transaksi.php               Pencatatan transaksi kas (pembukuan)
|   |-- verifikasi-donasi.php       Verifikasi & persetujuan donasi online
|   |-- laporan.php                 Laporan keuangan & cetak
|   |-- profil-admin.php            Edit profil & pengaturan masjid
|   |-- youtube-admin.php           Kelola video YouTube & kajian
|   |-- users-admin.php             Manajemen pengguna & hak akses
|
|-- assets/                         Aset Statis
|   |-- css/
|   |   |-- classic-theme.css       Tema klasik Islami (Deep Cypress, Antique Gold)
|   |-- js/
|       |-- app.js                  Interaksi frontend (modal, toast, clipboard, form)
|
|-- auth/                           Autentikasi & Manajemen Sesi
|   |-- login.php                   Halaman masuk
|   |-- register.php                Registrasi akun baru
|   |-- logout.php                  Proses logout
|   |-- forgot-password.php         Lupa password (kirim OTP ke email)
|   |-- verify-otp.php              Verifikasi kode OTP
|   |-- reset-password.php          Form reset password via OTP
|
|-- config/                         Konfigurasi Aplikasi
|   |-- database.php                Koneksi DB (PDO), session, helper upload
|   |-- mail.php                    Konfigurasi SMTP (Gmail App Password)
|   |-- helpers.php                 Fungsi bantu: base_url, pagination, RBAC, upload
|
|-- db/                             Database
|   |-- MesjidApps.sql              Skema database lengkap (16 tabel)
|   |-- DataMesjidApps.sql          Data seeder & contoh awal
|
|-- donatur/                        Portal Donatur
|   |-- portal-donatur.php          Dashboard mandiri donatur
|   |-- kwitansi.php                Cetak e-Kwitansi donasi
|
|-- home/                           Halaman Publik (Frontend)
|   |-- profil.php                  Profil, sejarah, visi-misi masjid
|   |-- program.php                 Katalog seluruh program donasi
|   |-- program-detail.php          Detail program & progress donasi
|   |-- donasi-online.php           Formulir donasi online (QRIS & transfer)
|   |-- transparansi.php            Laporan keuangan transparan publik
|   |-- berita.php                  Daftar berita & artikel
|   |-- berita-detail.php           Detail berita (full text)
|   |-- kajian.php                  Galeri video kajian & live streaming
|   |-- kontak.php                  Halaman kontak & formulir pesan
|
|-- layouts/                        Komponen Layout (Reusable)
|   |-- header.php                  Header admin panel
|   |-- footer.php                  Footer admin panel
|   |-- sidebar.php                 Sidebar navigasi admin
|   |-- public_header.php           Header website publik
|   |-- public_footer.php           Footer website publik
|
|-- otp/                            Modul Autentikasi OTP
|   |-- otp.php                     Generator & pengirim kode OTP
|   |-- email-template.php          Template email OTP
|
|-- uploads/                        Direktori Unggahan (User Uploads)
|   |-- banner/                     Gambar slider & banner
|   |-- berita/                     Thumbnail berita
|   |-- bukti/                      Bukti transfer pembayaran donasi
|   |-- home/                       Salinan cadangan halaman home (backup)
|   |-- pengurus/                   Foto pengurus DKM
|   |-- profil/                     Foto masjid
|   |-- program/                    Gambar program donasi
|   |-- qris/                       Gambar QRIS
|
|-- vendor/                         Dependensi Composer
|   |-- phpmailer/phpmailer/        PHPMailer (pengiriman email OTP)
|   |-- composer/                   Autoloader Composer
|   |-- autoload.php                Autoload entry point
|
|-- index.php                       Beranda utama (entry point publik)
|-- donasi.php                      Endpoint donasi cepat
|-- kegiatan.php                    Halaman kegiatan masjid
|-- dashboard.php                   (Legacy) Dashboard lama berbasis tabel donasi/kegiatan
|-- 404.php                         Halaman error 404 kustom
|-- .htaccess                       Konfigurasi Apache (URL rewrite, keamanan)
|-- .gitattributes                  Git line ending normalization
|-- composer.json                   Konfigurasi dependensi Composer
|-- composer.lock                   Lock file Composer
|-- README.md                       Dokumentasi proyek
```

---

## Database

Sistem menggunakan **16 tabel** dengan engine InnoDB dan charset `utf8mb4_unicode_ci`. Skema lengkap terdapat pada `db/MesjidApps.sql`, sedangkan data seeder & contoh awal (akun default, kategori transaksi, rekening, program donasi) berada pada `db/DataMesjidApps.sql`. Dua tabel terakhir adalah tabel **legacy** dari versi awal aplikasi yang masih dipertahankan untuk kompatibilitas `dashboard.php`.

| # | Tabel | File | Fungsi |
|---|-------|------|--------|
| 1 | `users` | `MesjidApps.sql` | Pengguna & multi-role RBAC (admin, bendahara, content_admin, donatur) |
| 2 | `profil_masjid` | `MesjidApps.sql` | Profil, sejarah, visi-misi, kontak, sosial media masjid |
| 3 | `pengurus_masjid` | `MesjidApps.sql` | Data pengurus DKM |
| 4 | `rekening_donasi` | `MesjidApps.sql` | Rekening bank & QRIS donasi |
| 5 | `program_donasi` | `MesjidApps.sql` | Program donasi (target, terkumpul, status, kategori) |
| 6 | `kategori_transaksi` | `MesjidApps.sql` | Master kategori pemasukan & pengeluaran kas |
| 7 | `transaksi_keuangan` | `MesjidApps.sql` | Buku kas transaksi (pembukuan terintegrasi) |
| 8 | `donasi_online` | `MesjidApps.sql` | Donasi masuk dari jamaah (pending/diverifikasi/ditolak) |
| 9 | `berita` | `MesjidApps.sql` | Berita, artikel & kegiatan masjid |
| 10 | `banners` | `MesjidApps.sql` | Slider & banner promosi beranda |
| 11 | `youtube_videos` | `MesjidApps.sql` | Integrasi video YouTube (kajian, live, dokumentasi) |
| 12 | `notifikasi` | `MesjidApps.sql` | Notifikasi untuk pengguna & donatur |
| 13 | `pesan_kontak` | `MesjidApps.sql` | Pesan masuk dari formulir kontak jamaah |
| 14 | `otp_codes` | `MesjidApps.sql` | Kode OTP untuk verifikasi register & reset password |
| 15 | `donasi` | `MesjidApps.sql` | (Legacy) Donasi manual versi awal aplikasi |
| 16 | `kegiatan` | `MesjidApps.sql` | (Legacy) Kegiatan versi awal aplikasi |

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
- **Composer** (untuk menginstal PHPMailer)

---

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/mesjid-website.git
cd mesjid-website
```

### 2. Konfigurasi Web Server

Jika menggunakan **Laragon**, cukup copy folder project ke `C:\laragon\www\` dan akses melalui `http://localhost/mesjid-website/`.

Pastikan mod `rewrite` Apache aktif untuk `.htaccess`.

### 3. Buat Database

Jalankan skema database lengkap (membuat database `mesjid_website` dan 16 tabel):

```bash
mysql -u root -p < db/MesjidApps.sql
```

Import data seeder & contoh (akun default, kategori transaksi, rekening, program donasi, dan data lainnya):

```bash
mysql -u root -p mesjid_website < db/DataMesjidApps.sql
```

Atau gunakan **phpMyAdmin** untuk mengimpor kedua file tersebut secara berurutan.

### 4. Konfigurasi Koneksi

Edit `config/database.php` sesuai environment Anda:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mesjid_website');
```

### 5. Install Dependensi

```bash
composer install
```

Instalasi ini mencakup PHPMailer untuk pengiriman email OTP. Selanjutnya konfigurasi SMTP di `config/mail.php`. Gunakan Gmail **App Password** (aktifkan 2-Step Verification → buat App Password), lalu isi `MAIL_USERNAME`, `MAIL_PASSWORD`, dan `MAIL_FROM`.

### 6. Akses Aplikasi

| URL | Deskripsi |
|-----|-----------|
| `http://localhost/mesjid-website/` | Beranda publik |
| `http://localhost/mesjid-website/auth/login.php` | Halaman login |
| `http://localhost/mesjid-website/admin/dashboard.php` | Dashboard admin |

---

## Tema & Desain

Menggunakan tema klasik Islami dengan palet warna:

| Warna | Kode | Penggunaan |
|-------|------|------------|
| Deep Cypress Green | `#183728` | Warna primer, header, navbar |
| Antique Brass Gold | `#c5a059` | Aksen emas, judul, highlight |
| Warm Alabaster | `#fbf9f5` | Background body |

**Font:** Plus Jakarta Sans (body), Amiri & Cinzel (heading klasik Islami)

---

## Lisensi

Proyek ini menggunakan lisensi MIT. Bebas digunakan, dimodifikasi, dan didistribusikan.

---

<div align="center">

**"Memakmurkan Masjid, Mensejahterakan Ummat"**

*Masjid Jami' Nurul Iman &mdash; Pusat Dakwah, Ibadah, dan Pemberdayaan Ummat*

</div>
