# Masjid Jami' Nurul Iman

**Sistem Informasi, Pembukuan Kas & Platform Donasi Digital Masjid**

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![PHPMailer](https://img.shields.io/badge/PHPMailer-7.x-31A4DB?style=flat-square&logo=maildotru&logoColor=white)](https://github.com/PHPMailer/PHPMailer)
[![Apache](https://img.shields.io/badge/Apache-2.4-D22128?style=flat-square&logo=apache&logoColor=white)](https://httpd.apache.org)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](#lisensi)
[![Release](https://img.shields.io/badge/Release-v1.1.0-0e90f0?style=flat-square)](RELEASE.md)

---

## Tentang Proyek

Aplikasi web terintegrasi untuk pengelolaan masjid yang mencakup **website publik**, **sistem pembukuan kas**, **platform donasi online**, **e-kwitansi berlapis**, dan **portal donatur**. Dibangun dengan arsitektur modular PHP murni tanpa framework, dirancang untuk transparansi keuangan, kemudahan administrasi DKM, dan pengalaman jamaah yang modern.

---

## Fitur Utama

| Modul | Deskripsi |
|-------|-----------|
| **Website Publik** | Beranda, profil masjid & pengurus DKM, program donasi, berita/kegiatan, kajian YouTube, transparansi keuangan, kontak |
| **Admin Panel** | Dashboard keuangan, manajemen berita, program donasi, pengguna, banner, rekening & QRIS, video YouTube, laporan |
| **Pembukuan Kas** | Pencatatan pemasukan/pengeluaran, kategori transaksi, bukti transaksi, filter periode, ekspor CSV/Excel, laporan cetak |
| **Donasi Online** | Formulir donasi via QRIS & transfer bank, validasi MIME upload, format rupiah realtime, verifikasi admin |
| **e-Kwitansi Berlapis** | Security lock 3 status: Pending (DRAFT/watermark), Diverifikasi (stempel DKM resmi), Ditolak (keterangan alasan) |
| **Inbox Pesan Kontak** | Admin baca & balas pesan jamaah via WhatsApp/Email, badge unread realtime di sidebar |
| **Portal Donatur** | Dashboard donatur, riwayat donasi, update profil, cetak kwitansi mandiri |
| **Autentikasi** | Login multi-role (admin, bendahara, content_admin, donatur), registrasi, lupa/reset password via OTP |
| **Transparansi Kas** | Laporan keuangan publik, saldo kas terbuka |
| **Integrasi YouTube** | Embed video kajian, live streaming, dokumentasi kegiatan |

---

## Struktur Folder

```
mesjid-website/
|
|-- admin/                          Panel Administrator
|   |-- dashboard.php               Dashboard keuangan & eksekutif
|   |-- berita-admin.php            Manajemen berita & artikel
|   |-- banner-admin.php            Manajemen slider & banner
|   |-- program-admin.php           CRUD program donasi
|   |-- rekening-admin.php          Kelola rekening donasi & QRIS
|   |-- transaksi.php               Pembukuan kas
|   |-- verifikasi-donasi.php       Verifikasi & persetujuan donasi
|   |-- laporan.php                 Laporan keuangan & cetak
|   |-- profil-admin.php            Edit profil & pengaturan masjid
|   |-- youtube-admin.php           Kelola video YouTube & kajian
|   |-- users-admin.php             Manajemen pengguna & hak akses
|   |-- pesan-admin.php             [NEW v1.1] Inbox pesan kontak jamaah
|
|-- assets/
|   |-- css/classic-theme.css       Tema klasik Islami (Deep Cypress + Antique Gold)
|   |-- js/app.js                   Interaksi frontend (modal, toast, clipboard, form)
|
|-- auth/                           Autentikasi & Manajemen Sesi
|   |-- login.php / register.php / logout.php
|   |-- forgot-password.php / verify-otp.php / reset-password.php
|
|-- config/
|   |-- database.php                Koneksi DB (PDO), session, helper upload
|   |-- mail.php                    Konfigurasi SMTP (Gmail App Password)
|   |-- helpers.php                 Fungsi bantu: base_url, pagination, RBAC, upload
|
|-- db/
|   |-- MesjidApps.sql              Skema database lengkap (16 tabel)
|   |-- DataMesjidApps.sql          Data seeder & contoh awal
|
|-- donatur/
|   |-- portal-donatur.php          Dashboard mandiri donatur
|   |-- kwitansi.php                e-Kwitansi dengan Security Lock 3 status [v1.1]
|
|-- home/                           Halaman Publik
|   |-- profil.php / program.php / program-detail.php
|   |-- donasi-online.php           Formulir donasi (format rupiah realtime) [v1.1]
|   |-- transparansi.php / berita.php / berita-detail.php
|   |-- kajian.php / kontak.php
|
|-- layouts/                        Komponen Layout
|   |-- header.php / footer.php / sidebar.php  (admin)
|   |-- public_header.php / public_footer.php   (publik)
|
|-- otp/                            Modul OTP Email
|-- uploads/                        Direktori Unggahan
|-- vendor/                         Dependensi Composer (PHPMailer)
|
|-- index.php                       Beranda utama
|-- .htaccess                       Rewrite URL bersih + proteksi keamanan
|-- RELEASE.md                      Catatan rilis lengkap
|-- WALKTHROUGH.md                  Panduan alur penggunaan
|-- PROGRESS.md                     Status fitur & roadmap
|-- README.md                       Dokumentasi proyek ini
```

---

## Database

Sistem menggunakan **16 tabel** dengan engine InnoDB dan charset `utf8mb4_unicode_ci`.

| # | Tabel | Fungsi |
|---|-------|--------|
| 1 | `users` | Pengguna & multi-role RBAC |
| 2 | `profil_masjid` | Profil, sejarah, visi-misi, sosial media |
| 3 | `pengurus_masjid` | Data pengurus DKM |
| 4 | `rekening_donasi` | Rekening bank & QRIS |
| 5 | `program_donasi` | Program donasi (target, terkumpul, status) |
| 6 | `kategori_transaksi` | Master kategori pemasukan & pengeluaran |
| 7 | `transaksi_keuangan` | Buku kas terintegrasi |
| 8 | `donasi_online` | Donasi masuk (pending/diverifikasi/ditolak) |
| 9 | `berita` | Berita, artikel & kegiatan |
| 10 | `banners` | Slider & banner beranda |
| 11 | `youtube_videos` | Video YouTube (kajian, live) |
| 12 | `notifikasi` | Notifikasi pengguna & donatur |
| 13 | `pesan_kontak` | Pesan masuk formulir kontak jamaah |
| 14 | `otp_codes` | Kode OTP verifikasi |
| 15 | `donasi` | (Legacy) Donasi manual versi awal |
| 16 | `kegiatan` | (Legacy) Kegiatan versi awal |

### Akun Default

| Role | Username | Password |
|------|----------|----------|
| Administrator | `admin` | `admin123` |
| Bendahara | `bendahara` | `bendahara123` |
| Content Admin | `konten` | `konten123` |
| Donatur | `donatur` | `donatur123` |

> ⚠️ **Ganti semua password default sebelum deployment ke production.**

---

## Persyaratan

- **PHP** >= 8.0 (PDO MySQL, mbstring, json, openssl, **fileinfo**)
- **MySQL** >= 8.0 / MariaDB >= 10.5
- **Apache** dengan `mod_rewrite` aktif (atau **Laragon** / **XAMPP**)
- **Composer**

---

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/mesjid-website.git
cd mesjid-website
```

### 2. Konfigurasi Web Server

Jika menggunakan **Laragon**, copy folder ke `C:\laragon\www\` dan akses via `http://localhost/mesjid-website/`.

Pastikan `mod_rewrite` aktif di Apache.

### 3. Buat Database

```bash
mysql -u root -p < db/MesjidApps.sql
mysql -u root -p mesjid_website < db/DataMesjidApps.sql
```

Atau gunakan phpMyAdmin untuk mengimpor kedua file secara berurutan.

### 4. Konfigurasi Koneksi

Edit `config/database.php`:

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

Konfigurasi SMTP di `config/mail.php` menggunakan Gmail **App Password**.

### 6. Akses Aplikasi

| URL | Deskripsi |
|-----|-----------|
| `http://localhost/mesjid-website/` | Beranda publik |
| `http://localhost/mesjid-website/login` | Halaman login |
| `http://localhost/mesjid-website/dashboard` | Dashboard admin |
| `http://localhost/mesjid-website/donasi-online` | Formulir donasi |

---

## Tema & Desain

Tema klasik Islami dengan palet warna:

| Warna | Kode | Penggunaan |
|-------|------|------------|
| Deep Cypress Green | `#183728` | Primer, header, navbar |
| Antique Brass Gold | `#c5a059` | Aksen emas, judul, highlight |
| Warm Alabaster | `#fbf9f5` | Background body |

**Font:** Plus Jakarta Sans (body), Amiri & Cinzel (heading klasik Islami)

---

## Catatan Rilis

### v1.1.0 *(2026-09-21)*
- **e-Kwitansi Security Lock** — pengamanan 3 status (pending/diverifikasi/ditolak)
- **Inbox Pesan Kontak Admin** — baca & balas via WA/Email
- **Format Rupiah Realtime** — titik ribuan otomatis saat input nominal
- **Validasi Upload MIME** — cek file asli via `finfo_open`
- **Fix redirect** — donasi selesai langsung ke kwitansi
- **Fix form action** — POST ke URL absolut

### v1.0.0 *(2026-09-04)*
- Rilis perdana — website publik, admin panel, pembukuan kas, donasi online, portal donatur, autentikasi OTP, clean URL

Detail lengkap ada di [RELEASE.md](RELEASE.md).

---

## Dokumentasi Lanjutan

| Dokumen | Isi |
|---------|-----|
| [RELEASE.md](RELEASE.md) | Changelog & catatan rilis lengkap per versi |
| [WALKTHROUGH.md](WALKTHROUGH.md) | Panduan alur penggunaan per peran |
| [PROGRESS.md](PROGRESS.md) | Status fitur, todo, dan roadmap |

---

## Lisensi

Proyek ini menggunakan lisensi **MIT**. Bebas digunakan, dimodifikasi, dan didistribusikan.

---

<div align="center">

**"Memakmurkan Masjid, Mensejahterakan Ummat"**

*Masjid Jami' Nurul Iman &mdash; Pusat Dakwah, Ibadah, dan Pemberdayaan Ummat*

</div>
