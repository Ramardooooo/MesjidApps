# 📋 RELEASE NOTES — Masjid Jami' Nurul Iman
**Sistem Informasi, Pembukuan Kas & Platform Donasi Digital Masjid**

---

## v1.2.0 — Security Hardening, Notification & Staff Directory Update *(2026-10-20)*

### ✨ Fitur Baru & Peningkatan Utama

#### 🛡️ Keamanan & Proteksi Bot (Security Hardening)
- **Cloudflare Turnstile CAPTCHA**: Terintegrasi pada seluruh gerbang autentikasi dan formulir publik:
  - Form Login (`auth/login.php`)
  - Form Pendaftaran Akun Donatur (`auth/register.php`)
  - Form Lupa & Reset Password (`auth/forgot-password.php`, `auth/reset-password.php`)
  - Form Donasi Online (`home/donasi-online.php`)
  - Validasi token via server-side cURL ke endpoint verify Cloudflare dengan fallback aman untuk pengujian lokal (`127.0.0.1` / `::1`).
- **Database-Backed Rate Limiting**:
  - Tabel baru `rate_limits` untuk mencatat percobaan akses dan IP client.
  - Membatasi percobaan brute-force login (maksimal 5 percobaan per 15 menit).
  - Tampilan UI countdown timer otomatis (*cooldown*) saat akun/IP terkunci.
  - Skrip pembersihan otomatis untuk rekaman rate limit kedaluwarsa (`cleanup_expired_rate_limits()`).
- **CSRF Token Protection Terpadu**:
  - Implementasi fungsi helper `csrf_token()`, `csrf_field()`, dan `verify_csrf()` di `config/helpers.php` untuk memvalidasi setiap request berjenis POST.
- **Session Security & Timeout**:
  - Regenerasi ID sesi (`session_regenerate_id(true)`) saat login berhasil untuk mencegah *Session Fixation*.
  - Sesi otomatis logout jika tidak ada aktivitas selama 2 jam (7.200 detik) dengan parameter informatif `timeout=1`.
- **Konsistensi Zona Waktu**:
  - Sinkronisasi zona waktu PHP dan MySQL ke `Asia/Makassar` (`+08:00`) agar kode OTP dan timestamp transaksi selalu presisi dan tidak kedaluwarsa prematur.

#### 📧 Notifikasi Email Otomatis Donasi (PHPMailer Integration)
- **Email Verifikasi Berhasil**: Saat bendahara memverifikasi donasi di `/verifikasi-donasi`, sistem secara otomatis mengirimkan email konfirmasi ke donatur berisi detail program, nomor donasi, nominal, dan tautan langsung ke e-kwitansi resmi.
- **Email Penolakan Donasi**: Jika donasi ditolak, sistem otomatis mengirimkan email pemberitahuan yang memuat nomor donasi beserta alasan penolakan secara transparan.
- **Tracking Email**: Kolom baru `notif_email_sent` dan `notif_email_at` pada tabel `donasi_online` untuk audit jejak pengiriman.

#### 👥 Direktori Publik Tim Pengurus DKM & Profil Staff
- **Halaman Tim Kami (`home/tim-kami.php`)**: Halaman publik modern berlatar arabesque gelap yang menampilkan jajaran pengurus harian, bendahara, dan administrator dengan pengurutan hierarki jabatan.
- **Detail Profil Staff (`home/profil-staff.php`)**: Menampilkan biografi lengkap, jabatan, foto avatar, tautan media sosial (Instagram, LinkedIn, Facebook, Twitter), dan counter jumlah pengunjung profil (*profile views*) realtime.
- **Widget Tim Pengelola (`home/profil-staff-dkm.php`)**: Komponen modular yang otomatis disematkan pada halaman Profil Masjid (`/profil`).
- **Panel Profil Saya (`admin/profil-saya.php`)**: Fasilitas bagi pengurus untuk memperbarui data pribadi, foto avatar, nomor WhatsApp, bio, media sosial, serta opsi menampilkan profil ke publik (`is_public_profile`).
- **Integrasi Sidebar Admin**: Avatar foto pengurus langsung ditampilkan pada sidebar navigasi panel admin dengan tautan cepat edit profil.

#### 📱 Arsitektur Modular Portal Donatur (`donatur/includes/`)
- Restrukturisasi kode `donatur/portal-donatur.php` menjadi komponen modular terpisah untuk kemudahan pemeliharaan:
  - `portal-controller.php`: Pemrosesan logika data donatur, update profil, upload avatar, dan ganti password.
  - `portal-header.php`: Hero banner personal donatur, saldo kontribusi, dan badge status keanggotaan.
  - `portal-status.php`: Widget metrik total infaq, donasi terverifikasi, dan transaksi pending.
  - `portal-history.php`: Riwayat transaksi donasi lengkap dengan badge status dan akses cetak e-kwitansi.
  - `portal-profile.php` & `portal-account.php`: Formulir pembaruan identitas, foto profil, dan keamanan sandi.
  - `portal-modal.php`: Modal konfirmasi dan detail interaktif.
  - `portal-scripts.php`: Pengelolaan tab navigasi dinamis dan preview upload gambar.
  - `portal-footer.php`: Footer khusus antarmuka portal donatur.
- **Receipt Ownership Guard**: Pengamanan e-Kwitansi di mana donatur hanya dapat mengakses kwitansi milik akun sendiri atau kwitansi publik sah via token nomor donasi.

#### 📚 Pusat Dokumentasi & Halaman Legal (`docs/`)
- **Panduan Pengguna (`docs/panduan-user.php`)**: Petunjuk komprehensif bagi jamaah/donatur seputar tata cara donasi online, pelacakan e-kwitansi, melihat transparansi kas, dan memanfaatkan portal donatur.
- **Panduan Admin Panel (`docs/panduan-admin.php`)**: Manual operasional lengkap untuk pengurus, bendahara, dan admin konten (khusus dapat diakses role pengurus).
- **Kebijakan Privasi (`docs/privacy.php`)**: Pernyataan transparansi pengelolaan dan perlindungan data jamaah.
- **Syarat & Ketentuan (`docs/terms.php`)**: Ketentuan hukum dan operasional donasi digital masjid.

#### 🎨 Desain, Navigasi & UX Frontend
- **Interactive Multi-Slide Hero Slider (`index.php`)**: Slider beranda interaktif dengan indikator titik (*dots*), tombol navigasi prev/next, tombol CTA dinamis, serta widget Jadwal Shalat (WITA) dan saldo kas mini realtime audit.
- **Modern Dropdown Navbar (`layouts/public_header.php`)**: Navigasi sticky terstruktur dengan dropdown terorganisir:
  - Profil (Tentang Masjid, Kepengurusan DKM, Tim Kami)
  - Program (Katalog Program, Kegiatan & Berita, Video Kajian)
  - Lainnya (Transparansi Kas)
  - Docs (Panduan Pengguna, Panduan Admin, Kebijakan Privasi, Syarat & Ketentuan)
  - Tombol aksi Donasi Cepat & Ikon Akses Portal/Login.
- **Quick Copy Nomor Rekening**: Fungsi salin instan nomor rekening di footer dan beranda dengan animasi visual perubahan status tombol menjadi "*Tercopy!*".
- **One-Click Toggle Banner**: Tombol switch aktif/nonaktif banner beranda langsung dari tabel (`admin/banner-admin.php?toggle=ID`).
- **Server-Side Pagination & Helper Terpadu (`config/helpers.php`)**:
  - Pemisahan fungsi global ke `config/helpers.php`.
  - Fungsi `paginate_data()` dan `render_pagination()` bertema masjid untuk navigasi data tabel admin.
  - Fungsi `app_url()` untuk normalisasi routing URL bersih secara konsisten.
  - Fungsi `hapus_file_upload()` untuk otomatis membersihkan berkas fisik gambar/bukti saat data dihapus.
- **Modal Konfirmasi Universal**: Modal konfirmasi penghapusan data bertema Islami modern menggantikan dialog konfirmasi bawaan browser.

#### 🗄️ Database Skema & Migrasi V1.1 / V1.2
- Penambahan tabel `rate_limits`.
- Penambahan kolom profil staff pada tabel `users` (`avatar`, `bio`, `jabatan`, `social_links`, `is_public_profile`, `profile_views`).
- Penambahan kolom tracking email pada `donasi_online` (`notif_email_sent`, `notif_email_at`, `user_id`).
- File migrasi terstruktur di folder `db/`: `create_rate_limits_table.sql`, `add_indexes.sql`, `add_columns.sql`, `add_foreign_keys.sql`, `add_staff_profile_columns.sql`, `add_user_id_to_pengurus.sql`, serta dokumentasi `db/README_MIGRATIONS.md`.

---

## v1.1.0 — Security & UX Update *(2026-09-21)*

### ✨ Fitur Utama
- **Receipt Security Lock (Penguncian Kwitansi Berlapis)**: Sistem pengamanan 3 status e-Kwitansi:
  - `pending`: Header DRAFT, watermark diagonal belum sah, stempel/tanda tangan dikunci, tombol cetak terkunci.
  - `diverifikasi`: Bukti resmi sah, stempel DKM & tanda tangan bendahara aktif, QR validasi aktif, tombol cetak PDF & bagikan WhatsApp.
  - `ditolak`: Banner merah dengan alasan penolakan dari admin, watermark tidak sah.
- **Inbox Pesan Kontak Admin (`admin/pesan-admin.php`)**:
  - Modul pembacaan dan pengelolaan pesan masuk dari kontak publik.
  - Badge unread realtime beranimasi di sidebar panel admin.
  - Fitur Quick Reply via WhatsApp Web dan Email.
- **Formatter Nominal Rupiah Realtime**: Input nominal otomatis memformat titik ribuan saat diketik (`50000` → `50.000`).
- **Validasi Ketat Upload Bukti Transfer**: Pengecekan MIME type asli via `finfo_open(FILEINFO_MIME_TYPE)` (hanya JPEG, PNG, WEBP max 3MB).
- **Perbaikan Routing & Tautan**: Perbaikan redirect kwitansi dan form action donasi online.

---

## v1.0.0 — Rilis Perdana *(2026-09-04)*

### ✨ Fitur Utama
- **Website Publik Modern**: Beranda, profil masjid, katalog program donasi, berita/kegiatan, kajian YouTube, transparansi kas, kontak.
- **Pembukuan Kas Terpadu**: Catat pemasukan/pengeluaran, kategori transaksi, bukti unggah, filter periode, export CSV/Excel, cetak laporan.
- **Platform Donasi Online**: Dukungan QRIS dan transfer bank terintegrasi.
- **Portal Donatur Mandiri**: Riwayat donasi personal dan unduh kwitansi.
- **Autentikasi Multi-Role & OTP**: Role admin, bendahara, content_admin, donatur dengan verifikasi OTP via email.
- **Clean URL**: Konfigurasi mod_rewrite Apache tanpa ekstensi `.php`.

---

## 🗺️ Roadmap Selanjutnya

- [ ] Integrasi Payment Gateway Otomatis (Midtrans / Duitku / QRIS Statis Dinamis + Webhook IPN)
- [ ] Integrasi API Publik Jadwal Sholat Otomatis Berdasarkan Koordinat GPS
- [ ] Export Laporan Keuangan ke format PDF murni (Dompdf / TCPDF)
- [ ] Broadcast Pengumuman WhatsApp Gateway ke Jamaah Terdaftar
- [ ] Dukungan Mode Gelap (Dark Mode) & Multi-bahasa (Indonesia / Arab / Inggris)
- [ ] Automated Unit & Integration Testing (PHPUnit)

---

## ⚙️ Persyaratan Sistem

- **PHP**: >= 8.0 (Ekstensi: PDO MySQL, mbstring, json, openssl, cURL, **fileinfo**)
- **Database**: MySQL >= 8.0 atau MariaDB >= 10.5
- **Web Server**: Apache dengan `mod_rewrite` aktif (Laragon / XAMPP / VPS Linux)
- **Email**: Layanan SMTP Aktif (Gmail App Password / SendGrid / Mailgun)
- **Composer**: Untuk pengelolaan pustaka PHPMailer
- **Cloudflare**: Akun Cloudflare Turnstile (Site Key & Secret Key)

## 📄 Lisensi

MIT License &copy; 2026 DKM Masjid Jami' Nurul Iman
