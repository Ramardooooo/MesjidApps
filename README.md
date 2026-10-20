# Masjid Jami' Nurul Iman

**Sistem Informasi, Pembukuan Kas & Platform Donasi Digital Masjid**

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![PHPMailer](https://img.shields.io/badge/PHPMailer-7.x-31A4DB?style=flat-square&logo=maildotru&logoColor=white)](https://github.com/PHPMailer/PHPMailer)
[![Cloudflare Turnstile](https://img.shields.io/badge/Turnstile-Protected-F38020?style=flat-square&logo=cloudflare&logoColor=white)](https://www.cloudflare.com/products/turnstile/)
[![Apache](https://img.shields.io/badge/Apache-2.4-D22128?style=flat-square&logo=apache&logoColor=white)](https://httpd.apache.org)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](#lisensi)
[![Release](https://img.shields.io/badge/Release-v1.2.0-0e90f0?style=flat-square)](release.md)

---

## Daftar Isi
- [Tentang Proyek](#tentang-proyek)
- [Fitur Utama](#fitur-utama)
- [Struktur Folder](#struktur-folder)
- [Database & Skema](#database--skema)
- [Panduan Integrasi Cloudflare Turnstile](#panduan-integrasi-cloudflare-turnstile)
- [Panduan Instalasi & Deployment Lengkap](#panduan-instalasi--deployment-lengkap)
  - [1. Kebutuhan Sistem & Ekstensi PHP](#1-kebutuhan-sistem--ekstensi-php)
  - [2. Konfigurasi Web Server Apache & Virtual Host](#2-konfigurasi-web-server-apache--virtual-host)
  - [3. Setup Database (Fresh Install vs Migrasi)](#3-setup-database-fresh-install-vs-migrasi)
  - [4. Konfigurasi Email SMTP (PHPMailer & Gmail App Password)](#4-konfigurasi-email-smtp-phpmailer--gmail-app-password)
  - [5. Izin Berkas & Keamanan Direktori Uploads](#5-izin-berkas--keamanan-direktori-uploads)
  - [6. Checklist Sebelum Go-Live (Production)](#6-checklist-sebelum-go-live-production)
- [Tema & Desain](#tema--desain)
- [Catatan Rilis](#catatan-rilis)
- [Dokumentasi Pendukung](#dokumentasi-pendukung)
- [Lisensi](#lisensi)

---

## Tentang Proyek

Aplikasi web terpadu untuk digitalisasi dan tata kelola masjid modern yang mencakup **website profil & syiar publik**, **sistem pembukuan kas akuntabel**, **platform donasi & infaq online**, **e-kwitansi dengan Security Lock berlapis**, **notifikasi email otomatis donatur**, **direktori tim pengurus DKM**, **portal donatur modular**, serta **pusat dokumentasi & panduan pengguna**. 

Dibangun dengan arsitektur PHP murni modern tanpa framework (*vanilla PHP*), aplikasi dirancang untuk memberikan performa tinggi yang ringan, transparansi penuh bagi para jamaah, keamanan berlapis anti-bot & brute-force, dan kemudahan pengoperasian oleh pengurus DKM.

---

## Fitur Utama

| Modul | Deskripsi |
|-------|-----------|
| **Website Publik & Hero Slider** | Beranda dengan interactive multi-slide hero banner, widget Jadwal Shalat WITA, counter saldo kas audit, profil masjid, katalog program, warta berita, video kajian YouTube, dan formulir kontak |
| **Proteksi Anti-Bot & Brute-Force** | Integrasi **Cloudflare Turnstile CAPTCHA** pada login, register, reset password, dan formulir donasi. Proteksi **Database-backed Rate Limiting** dengan UI live countdown timer |
| **Notifikasi Email Donasi** | Otomatis mengirim email notifikasi ke donatur via PHPMailer saat donasi berhasil diverifikasi (lengkap tautan kwitansi sah) atau ditolak (beserta alasan) |
| **Direktori Pengurus & Tim Kami** | Halaman publik Tim Kami (`/tim-kami`), detail profil staff pengurus (`/profil-staff?id=X`) dengan counter view, widget DKM di profil masjid, dan panel pengurus mandiri (`/admin/profil-saya`) |
| **Portal Donatur Modular** | Dashboard donatur berbasis komponen modular (`donatur/includes/`) dengan tab statistik infaq, riwayat donasi berstatus, edit profil & avatar, serta keamanan sandi |
| **e-Kwitansi Security Lock** | Proteksi 3 status kwitansi: Pending (DRAFT/watermark/kunci cetak), Diverifikasi (stempel resmi DKM, tanda tangan, QR validasi aktif, PDF), Ditolak (alasan penolakan) |
| **Pusat Dokumentasi (Docs)** | Halaman Panduan Pengguna (`/docs/panduan-user`), Panduan Khusus Admin (`/docs/panduan-admin`), Kebijakan Privasi, dan Syarat & Ketentuan |
| **Pembukuan Kas Akuntabel** | Pencatatan pemasukan/pengeluaran, kategori transaksi, unggah bukti fisik, filter periode, server-side pagination, ekspor CSV/Excel, dan laporan siap cetak |
| **Verifikasi Donasi Otomatis** | Verifikasi satu klik yang otomatis mencatat donasi ke kas masuk, memperbarui dana terkumpul pada program terkait, dan mengirim email ke donatur |
| **Inbox Pesan Jamaah** | Admin menerima pesan dari form kontak, notifikasi badge unread di sidebar, dan fasilitas Quick Reply via WhatsApp Web atau Email |
| **Keamanan & Sesi** | Proteksi CSRF token terpadu, regenerasi session ID, auto-logout idle 2 jam, sanitasi input XSS (`e()`), PDO prepared statements, dan upload validation (MIME check) |

---

## Struktur Folder

```
mesjid-donasi-main/
|
|-- admin/                          Panel Administrator & Pengurus
|   |-- dashboard.php               Dashboard keuangan eksekutif & grafik 6 bulan
|   |-- berita-admin.php            Manajemen warta berita & kegiatan
|   |-- banner-admin.php            Manajemen banner/slider & one-click toggle aktif
|   |-- program-admin.php           CRUD program donasi & target dana
|   |-- rekening-admin.php          Kelola rekening bank & QRIS resmi
|   |-- transaksi.php               Pembukuan kas (pemasukan/pengeluaran & pagination)
|   |-- verifikasi-donasi.php       Verifikasi donasi masuk + auto-mail notification
|   |-- laporan.php                 Laporan keuangan & cetak periode
|   |-- profil-admin.php            Edit profil masjid & pengaturan umum
|   |-- profil-saya.php             Pengaturan profil personal pengurus & publikasi staff
|   |-- youtube-admin.php           Kelola video kajian YouTube & streaming
|   |-- users-admin.php             Manajemen pengguna & hak akses RBAC
|   |-- pesan-admin.php             Inbox pesan kontak jamaah + Quick Reply WA/Email
|
|-- assets/
|   |-- css/classic-theme.css       Tema klasik Islami (Deep Cypress + Antique Gold)
|   |-- js/app.js                   Interaksi frontend (modal, clipboard, toast, attach)
|
|-- auth/                           Autentikasi & Manajemen Sesi
|   |-- login.php                   Login multi-role + Cloudflare Turnstile + Rate Limit
|   |-- register.php                Registrasi akun donatur + Turnstile + OTP
|   |-- forgot-password.php         Lupa kata sandi via OTP email
|   |-- verify-otp.php              Verifikasi token OTP email
|   |-- reset-password.php          Reset kata sandi baru
|   |-- logout.php                  Pembersihan sesi dan redirect
|
|-- config/
|   |-- database.php                Koneksi database PDO & sinkronisasi timezone
|   |-- helpers.php                 Helper global (app_url, CSRF, pagination, sanitasi)
|   |-- mail.php                    Konfigurasi SMTP PHPMailer
|
|-- db/                                  Skema Database & Migrasi
|   |-- MesjidAppsV1.0.sql               Skema Database Utama
|   |-- MesjidAppsV1.1.sql               Migrasi Database V1.1
|   |-- MesjidAppsV1.2.sql               Migrasi Database V1.2
|   |-- DataMesjidApps.sql               Data Seeder & Contoh Awal
|
|-- docs/                           Pusat Dokumentasi & Legalitas
|   |-- panduan-user.php            Panduan interaktif jamaah & donatur
|   |-- panduan-admin.php           Panduan operasional administrator panel
|   |-- privacy.php                 Kebijakan privasi data pengguna
|   |-- terms.php                   Syarat & ketentuan layanan donasi
|
|-- donatur/                        Area Donatur Terdaftar
|   |-- portal-donatur.php          Halaman utama portal donatur
|   |-- kwitansi.php                e-Kwitansi resmi dengan Security Lock 3 status
|   |-- includes/                   Arsitektur komponen modular portal donatur
|       |-- portal-controller.php   Pengendali logika donatur & keamanan sandi
|       |-- portal-header.php       Header hero & profil ringkas donatur
|       |-- portal-status.php       Widget ringkasan statistik kontribusi
|       |-- portal-history.php      Tabel riwayat donasi & unduh kwitansi
|       |-- portal-profile.php      Formulir update data profil & avatar
|       |-- portal-account.php      Pengaturan keamanan akun & ubah password
|       |-- portal-modal.php        Modal dialog interaktif portal
|       |-- portal-scripts.php      Script navigasi tab & preview upload
|       |-- portal-footer.php       Footer antarmuka portal
|
|-- home/                           Halaman Website Publik
|   |-- profil.php                  Tentang masjid, sejarah, visi misi & DKM
|   |-- tim-kami.php                Direktori publik tim pengurus harian DKM
|   |-- profil-staff.php            Detail biografi & sosmed individual staff
|   |-- profil-staff-dkm.php        Widget tim pengelola di profil masjid
|   |-- program.php                 Katalog seluruh program infaq & wakaf aktif
|   |-- program-detail.php          Rincian program + form donasi kilat
|   |-- donasi-online.php           Formulir donasi online + Turnstile + Rupiah realtime
|   |-- transparansi.php            Buku kas terbuka & transparansi publik
|   |-- berita.php / berita-detail.php Warta & artikel kegiatan masjid
|   |-- kajian.php                  Video kajian & arsip ceramah YouTube
|   |-- kontak.php                  Formulir kontak & informasi sekretariat
|
|-- layouts/                        Komponen Tata Letak (Layout)
|   |-- public_header.php           Navbar sticky modern, dropdown navigasi & CTA
|   |-- public_footer.php           Footer islami elegan, quick copy rekening & WA float
|   |-- sidebar.php                 Sidebar admin dengan avatar pengurus & badge pesan
|   |-- footer.php                  Footer admin & universal delete confirmation modal
|
|-- otp/                            Layanan Email & Notifikasi
|   |-- email-template.php          Template email OTP
|   |-- otp.php                     Fungsi pengiriman & verifikasi OTP
|   |-- send_donasi_notification.php Fungsi pengiriman email status donasi
|
|-- uploads/                        Direktori Unggahan Berkas (Banner, Berita, Bukti, Avatar)
|-- vendor/                         Dependensi Pustaka PHPMailer
|-- index.php                       Beranda utama dengan multi-slide hero & jadwal shalat
|-- .htaccess                       Apache rewrite URL bersih, HTTPS & proteksi berkas
|-- release.md                      Catatan rilis lengkap per versi
|-- WALKTHROUGH.md                  Panduan alur navigasi & alur pengguna
|-- PROGRESS.md                     Status fitur & roadmap pengembangan
|-- README.md                       Dokumentasi proyek ini
```

---

## Database & Skema

Sistem didukung oleh **17 tabel** relasional dengan engine InnoDB dan charset `utf8mb4_unicode_ci`:

| # | Tabel | Deskripsi |
|---|-------|-----------|
| 1 | `users` | Akun pengguna, multi-role RBAC, avatar, bio, jabatan & profil staff |
| 2 | `rate_limits` | Pencatatan rate limit, IP, attempts & waktu penguncian brute-force |
| 3 | `profil_masjid` | Profil masjid, identitas, kontak, dan saldo awal kas |
| 4 | `pengurus_masjid` | Data susunan kepengurusan DKM |
| 5 | `rekening_donasi` | Rekening bank tujuan dan QRIS resmi |
| 6 | `program_donasi` | Program donasi (target, dana terkumpul, status) |
| 7 | `kategori_transaksi` | Kategori pemasukan dan pengeluaran kas |
| 8 | `transaksi_keuangan` | Buku kas digital terpadu |
| 9 | `donasi_online` | Catatan transaksi donasi online & metadata notifikasi email |
| 10 | `berita` | Berita, artikel, dan warta kegiatan masjid |
| 11 | `banners` | Slider beranda interaktif & banner promosi |
| 12 | `youtube_videos` | Video kajian, ceramah, dan siaran langsung |
| 13 | `notifikasi` | Pemberitahuan sistem untuk pengurus & donatur |
| 14 | `pesan_kontak` | Pesan masuk dari formulir kontak jamaah |
| 15 | `otp_codes` | Kode token OTP verifikasi email |
| 16 | `donasi` | *(Legacy)* Pencatatan donasi manual versi awal |
| 17 | `kegiatan` | *(Legacy)* Jadwal kegiatan versi awal |

### Akun Bawaan Pengujian

| Peran (Role) | Username | Password |
|--------------|----------|----------|
| Administrator | `admin` | `admin123` |
| Bendahara Kas | `bendahara` | `bendahara123` |
| Pengelola Konten | `konten` | `konten123` |
| Akun Donatur | `donatur` | `donatur123` |

> ⚠️ **Peringatan Keamanan:** Selalu ubah kata sandi akun-akun default di atas sebelum mempublikasikan sistem ke server production.

---

## Panduan Integrasi Cloudflare Turnstile

Sistem menggunakan **Cloudflare Turnstile** sebagai pengganti reCAPTCHA konvensional. Turnstile melindungi formulir dari bot tanpa memerlukan tantangan teka-teki gambar yang mengganggu kenyamanan jamaah.

### 1. Cara Mendapatkan Kredensial Turnstile
1. Buka dan login ke [Cloudflare Dashboard](https://dash.cloudflare.com/).
2. Pada navigasi sidebar kiri, pilih menu **Turnstile**.
3. Klik tombol **Add Widget**.
4. Isi data konfigurasi:
   - **Widget Name**: Contoh `Masjid Jami' Nurul Iman`.
   - **Domains**: 
     - Masukkan domain website produksi Anda (misal: `masjidnuruliman.id` atau `donasi.masjidnuruliman.id`).
     - **Penting untuk Pengujian Lokal**: Tambahkan juga domain `localhost` dan `127.0.0.1` agar widget dapat muncul di komputer lokal Anda.
   - **Widget Mode**: Pilih **Managed** (direkomendasikan karena Cloudflare otomatis memutuskan tingkat interaksi pengunjung secara cerdas).
5. Klik tombol **Create**.
6. Anda akan mendapatkan dua kunci:
   - **Site Key**: Kunci publik untuk menampilkan widget di antarmuka frontend HTML.
   - **Secret Key**: Kunci rahasia untuk validasi respon token dari server backend PHP.

### 2. Memasang Kunci pada Aplikasi
Buka file `config/database.php`, lalu masukkan kedua kunci tersebut:

```php
// Cloudflare Turnstile Credentials
define('TURNSTILE_SITE_KEY', '0x4AAAAAAFcTxJ1R4nVL_gYW'); // Ganti dengan Site Key Anda
define('TURNSTILE_SECRET', '0x4AAAAAAFcTxMgMG6u4eUV812AM_eB_AOM'); // Ganti dengan Secret Key Anda
```

### 3. Alur Verifikasi Server-Side & Fallback Lokal
Validasi token dilakukan melalui cURL POST ke endpoint Cloudflare:
`https://challenges.cloudflare.com/turnstile/v0/siteverify`

```php
$ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'secret'   => TURNSTILE_SECRET,
    'response' => $turnstileToken,
    'remoteip' => get_client_ip(),
]));
$response = curl_exec($ch);
curl_close($ch);
```

> 💡 **Fallback Cerdas untuk Developer:**  
> Jika aplikasi dijalankan di lingkungan lokal (`127.0.0.1` atau `::1`) dan komputer tidak terhubung ke internet atau cURL gagal menjangkau server Cloudflare, sistem akan secara otomatis mengizinkan verifikasi (*fail-open for localhost*) sehingga developer tetap dapat melakukan pengujian sistem tanpa terhalang.

---

## Panduan Instalasi & Deployment Lengkap

### 1. Kebutuhan Sistem & Ekstensi PHP

- **PHP Version**: PHP 8.0, 8.1, 8.2, atau 8.3.
- **Ekstensi PHP Wajib**:
  Pastikan ekstensi berikut telah diaktifkan pada file `php.ini` Anda:
  ```ini
  extension=pdo_mysql
  extension=mbstring
  extension=openssl
  extension=curl
  extension=fileinfo
  extension=gd
  extension=zip
  ```
- **Rekomendasi Konfigurasi `php.ini` untuk Server Produksi**:
  ```ini
  upload_max_filesize = 10M
  post_max_size = 12M
  memory_limit = 256M
  max_execution_time = 120
  display_errors = Off
  display_startup_errors = Off
  log_errors = On
  error_log = /var/log/php_errors.log
  date.timezone = Asia/Makassar
  ```

---

### 2. Konfigurasi Web Server Apache & Virtual Host

Aplikasi mengandalkan modul `mod_rewrite` dan `mod_headers` untuk URL bersih (*Clean URLs*) dan pengamanan HTTP Headers.

#### A. Mengaktifkan Modul Apache (Linux / Ubuntu / Debian)
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2
```

#### B. Konfigurasi Virtual Host Apache
Pastikan direktori web Anda disetel dengan `AllowOverride All` agar file `.htaccess` di root proyek berfungsi:

```apache
<VirtualHost *:80>
    ServerName masjidnuruliman.id
    ServerAlias www.masjidnuruliman.id
    DocumentRoot "/var/www/mesjid-donasi-main"

    <Directory "/var/www/mesjid-donasi-main">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/mesjid_error.log
    CustomLog ${APACHE_LOG_DIR}/mesjid_access.log combined
</VirtualHost>
```

#### C. Penanganan Subfolder (Laragon / XAMPP)
Jika Anda menggunakan web server lokal seperti Laragon atau XAMPP, proyek dapat ditempatkan di subfolder:
- Laragon: `C:\laragon\www\mesjid-donasi-main`
- XAMPP: `C:\xampp\htdocs\mesjid-donasi-main`

Sistem secara dinamis mendeteksi prefix path melalui fungsi helper `base_url()` dan `app_url()` di `config/helpers.php`, sehingga Anda **tidak perlu mengubah kode routing** meskipun berpindah dari subfolder lokal ke root domain di VPS/cPanel.

---

### 3. Setup Database (Fresh Install vs Migrasi)

#### Opsi A: Instalasi Baru dari Nol (Fresh Installation)
1. Buat database baru di MySQL/MariaDB:
   ```sql
   CREATE DATABASE mesjid_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Impor skema dan data awal secara berurutan:
   ```bash
   # Masuk ke folder proyek
   cd /path/to/mesjid-donasi-main

   # Eksekusi via terminal mysql
   mysql -u root -p mesjid_website < db/MesjidAppsV1.2.sql
   mysql -u root -p mesjid_website < db/DataMesjidApps.sql
   ```
   *(Atau impor kedua file `.sql` di atas melalui tab Import di phpMyAdmin).*

3. **Pembersihan Data Orphaned (Sebelum Menambahkan Foreign Key)**:
   ```sql
   -- Bersihkan foreign key user_id jika ada data donatur yang user-nya sudah terhapus
   UPDATE donasi_online SET user_id = NULL WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM users);
   UPDATE donasi_online SET verified_by = NULL WHERE verified_by IS NOT NULL AND verified_by NOT IN (SELECT id FROM users);
   UPDATE berita SET penulis_id = NULL WHERE penulis_id NOT IN (SELECT id FROM users);
   ```
4. **Terapkan Foreign Key Relasional**:
   ```bash
   mysql -u root -p mesjid_website < db/add_foreign_keys.sql
   mysql -u root -p mesjid_website < db/add_user_id_to_pengurus.sql
   ```

---

### 4. Konfigurasi Email SMTP (PHPMailer & Gmail App Password)

Aplikasi menggunakan **PHPMailer** untuk mengirimkan kode OTP dan notifikasi status donasi. Disarankan menggunakan **Gmail SMTP dengan App Password**.

#### Langkah Mendapatkan Gmail App Password:
1. Login ke akun Google masjid / panitia.
2. Buka [Google Account Security](https://myaccount.google.com/security).
3. Pastikan **Verifikasi 2 Langkah (2-Step Verification)** dalam keadaan **Aktif**.
4. Cari menu **Sandi Aplikasi (App Passwords)** atau akses langsung ke [Google App Passwords](https://myaccount.google.com/apppasswords).
5. Masukkan nama aplikasi, contoh: `MesjidApps Server`, lalu klik **Buat (Create)**.
6. Salin 16 karakter sandi khusus yang muncul di layar (misal: `abcd efgh ijkl mnop`).

#### Pengaturan di `config/mail.php`:
Buka file `config/mail.php` dan sesuaikan parameter berikut:

```php
<?php
// config/mail.php
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'panitia.nuruliman@gmail.com');      // Email Gmail Anda
define('MAIL_PASSWORD', 'abcdefghijklmnop');                 // 16 Karakter Sandi Aplikasi (tanpa spasi)
define('MAIL_FROM', 'panitia.nuruliman@gmail.com');          // Email Pengirim
define('MAIL_FROM_NAME', 'DKM Masjid Jami\' Nurul Iman');     // Nama Pengirim
define('MAIL_SECURE', 'tls');                                // 'tls' (port 587) atau 'ssl' (port 465)
```

---

### 5. Izin Berkas & Keamanan Direktori Uploads

Sistem mengunggah berbagai berkas media (foto profil staff, gambar program donasi, banner beranda, warta berita, dan bukti transfer donatur) ke direktori `uploads/`.

#### A. Mengatur Hak Akses Direktori (Di Server Linux/VPS):
```bash
# Berikan kepemilikan kepada user web server (misalnya www-data atau nginx)
sudo chown -R www-data:www-data /var/www/mesjid-donasi-main/uploads

# Atur permission direktori ke 755 dan berkas ke 644
sudo find /var/www/mesjid-donasi-main/uploads -type d -exec chmod 755 {} \;
sudo find /var/www/mesjid-donasi-main/uploads -type f -exec chmod 644 {} \;
```

#### B. Proteksi Eksekusi Script di Folder Uploads:
Di dalam folder `uploads/` terdapat file `uploads/.htaccess` yang secara ketat memblokir eksekusi skrip PHP:

```apache
<FilesMatch "\.(php|phtml|php3|php4|php5|php7|phps|sh|cgi|pl)$">
    Require all denied
</FilesMatch>
```
Hal ini memastikan bahwa bahkan jika ada peretas yang mencoba mengunggah berkas web shell dengan ekstensi `.php` melalui formulir upload bukti, berkas tersebut tidak akan pernah dapat dieksekusi oleh web server.

---

### 6. Checklist Sebelum Go-Live (Production)

Gunakan daftar periksa berikut sebelum mempublikasikan website ke publik:

- [ ] **Ganti Kredensial Default**: Ganti kata sandi seluruh akun default (`admin`, `bendahara`, `konten`, `donatur`) melalui menu Users Admin atau Portal Profil.
- [ ] **Ganti Kunci Turnstile**: Pastikan `TURNSTILE_SITE_KEY` dan `TURNSTILE_SECRET` menggunakan domain produksi Anda, bukan kunci testing localhost.
- [ ] **Konfigurasi Database Production**: Pastikan `DB_USER` dan `DB_PASS` di `config/database.php` menggunakan akun MySQL berhak akses terbatas (bukan user `root` tanpa password).
- [ ] **Uji Coba Pengiriman Email**: Lakukan registrasi akun pengujian atau submit donasi untuk memastikan email OTP dan email kwitansi terkirim tanpa kendala SMTP.
- [ ] **Aktifkan Sertifikat SSL/HTTPS**: Pastikan domain telah memiliki sertifikat SSL aktif (misalnya melalui Let's Encrypt / Certbot atau Cloudflare Flexible/Full SSL).
- [ ] **Matikan Debugging & Error Display**: Pastikan `display_errors = Off` diatur pada server agar struktur internal aplikasi tidak bocor saat terjadi pengecualian.
- [ ] **Verifikasi Folder Uploads**: Pastikan web server memiliki izin tulis (*writeable*) pada folder `uploads/bukti`, `uploads/banner`, `uploads/berita`, `uploads/pengurus`, dan `uploads/profil`.

---

## Tema & Desain

Desain antarmuka memadukan keanggunan arsitektur Islam klasik dengan kesederhanaan tata letak web modern:
- **Deep Cypress Green (`#183728`)**: Warna primer bernuansa hijau hutan pinus yang mencerminkan keteduhan, kedamaian, dan keikhlasan.
- **Antique Brass Gold (`#c5a059`)**: Aksen emas antik melambangkan kemuliaan syiar dan transparansi pengelolaan amanah umat.
- **Warm Alabaster (`#fbf9f5`)**: Latar belakang lembut yang nyaman di mata pada segala kondisi pencahayaan layar.
- **Arabesque Pattern**: Tekstur ornamen geometris Islam halus yang diterapkan pada header dan footer.

---

## Catatan Rilis

### v1.2.0 *(2026-10-20)*
- **Security Hardening**: Cloudflare Turnstile CAPTCHA di semua form, Database Rate Limiting dengan countdown timer, CSRF Token protection, dan Session Idle Timeout.
- **Email Notifications**: Otomatisasi pengiriman email verifikasi dan penolakan donasi via PHPMailer.
- **Direktori Pengurus DKM & Staff**: Halaman Tim Kami publik, detail staff individual, counter view profil, dan panel Profil Saya pengurus.
- **Arsitektur Modular Portal Donatur**: Pemecahan modul `donatur/includes/` (controller, header, status, history, profile, account, modal, scripts).
- **Pusat Dokumentasi**: Halaman Panduan Pengguna, Panduan Admin, Kebijakan Privasi, dan Syarat Ketentuan di folder `docs/`.
- **UX & Interaktivitas**: Multi-slide hero banner interaktif, widget jadwal shalat WITA, live copy nomor rekening, one-click toggle status banner, dan server-side pagination.

Rincian lengkap riwayat pembaruan dapat dibaca pada [release.md](release.md).

---

## Dokumentasi Pendukung

| Dokumen | Deskripsi |
|---------|-----------|
| [release.md](release.md) | Catatan rilis lengkap dan changelog per versi |
| [WALKTHROUGH.md](WALKTHROUGH.md) | Panduan lengkap alur penggunaan dan peran sistem |
| [PROGRESS.md](PROGRESS.md) | Status perkembangan fitur dan roadmap masa depan |
---

## Lisensi

Proyek ini dirilis di bawah lisensi **MIT License**. Bebas digunakan, dipelajari, dan dikembangkan demi kemaslahatan umat serta kemakmuran masjid.

<div align="center">

**"Memakmurkan Masjid, Mensejahterakan Ummat"**  
*Masjid Jami' Nurul Iman &mdash; Pusat Ibadah, Tarbiyah, dan Amanah Sosial Ummat*

</div>
