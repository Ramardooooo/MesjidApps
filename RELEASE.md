# 📋 RELEASE NOTES — Masjid Jami' Nurul Iman
**Sistem Informasi, Pembukuan Kas & Platform Donasi Digital Masjid**

---

## v1.4.0 — Clean URL Architecture, Receipt Token Security & Donatur Experience *(2026-11-22)*

Pembaruan besar versi **v1.4.0** menghadirkan arsitektur URL bersih (*Clean Slug URLs*), penguatan privasi data tanda terima donasi dengan token acak (*Anti-Enumeration*), penyempurnaan UI/UX modul kas di perangkat seluler, serta penambahan fitur interaktif pada Portal Donatur.

###  1. Arsitektur Clean Slug URL & SEO
- **Program Donasi**:
  - Halaman rincian program kini beralamat rapi: `/program/{slug}` (menggantikan `/program-detail?id=X`).
  - Halaman formulir donasi langsung spesifik program: `/donasi-online/{slug}` (menggantikan `/donasi-online?program_id=X`).
- **Warta & Artikel Berita**:
  - Halaman detail warta: `/berita/{slug}` (menggantikan `/berita-detail?id=X`).
- **Real-time Slug Update di Admin**:
  - Saat admin mengubah nama program di `admin/program-admin.php` atau judul berita di `admin/berita-admin.php`, nilai `slug` langsung di-regenerate secara realtime tanpa akhiran angka acak, lengkap dengan algoritma pencegahan tabrakan (*collision handling*).
- **Otomatisasi 301 Permanent Redirect**:
  - Tautan lama berbasis parameter `?id=X` tetap aman dan otomatis dialihkan secara permanen (*301 Redirect*) ke URL slug resminya.
- **Pembaruan Link Sosial**:
  - Tombol bagikan ke WhatsApp dan Facebook pada halaman rincian program dan berita secara otomatis menyertakan URL slug resmi.

### 2. Keamanan e-Kwitansi & Anti-Enumeration Token
- **32-Character Cryptographic Token**:
  - Tautan publik e-Kwitansi kini menggunakan parameter token unik 32 karakter heksadesimal (`/donatur/kwitansi?token=...`).
  - Mencegah upaya pihak ketiga mengintip dokumen tanda terima dan bukti transfer dengan menebak nomor urut transaksi (`DON-YYYYMM-XXXX`).
- **Akses Berlapis & Self-Healing**:
  - Akses menggunakan nomor donasi (`?no=`) dibatasi khusus untuk Admin/Bendahara yang login atau akun donatur pemilik sah transaksi tersebut. Akses di luar itu otomatis dialihkan ke halaman utama.
  - Skema *Auto-Healing*: jika transaksi lama belum memiliki token, sistem otomatis meng-generate dan menyimpan token saat kwitansi pertama kali diakses.

### 3. Penyempurnaan Tampilan HP di Modul Kas Admin
- **Penghapusan Popup "Pilih Aksi" Liar**:
  - Menghapus pemicu klik baris tabel (`onclick="showMobileActions(...)"`) di `admin/transaksi.php` yang sebelumnya sering muncul saat layar HP disentuh atau di-scroll.
  - Seluruh tombol aksi (Lihat Bukti Nota & Hapus Catatan Kas) tetap tersedia secara presisi di kolom aksi kanan.
- **Validasi Panjang Form Semua Admin**:
  - Semua input form admin dilengkapi atribut `maxlength` sesuai limit kolom DB (`VARCHAR(50)`–`VARCHAR(255)`).
  - **Live Character Counter**: Otomatis tampil `x/max` (contoh: `12/150`) di setiap input yang memiliki `maxlength`.
  - **Backend Truncation**: `mb_substr($input, 0, $limit)` sebelum INSERT/UPDATE — mencegah error `SQLSTATE[22001]: Data too long` meskipun client-side di-bypass.

### 4. Fitur Baru & Tampilan Bersih Portal Donatur
- **Lencana Donatur Islami**:
  - Lencana penghargaan donatur (*Muhsinin*, *Penyemai Kebaikan*, *Pejuang Infaq Istiqomah*, *Sahabat Utama Masjid*) disematkan secara elegan di samping nama profil.
- **Jejak Sebaran Amal Jariyah (Impact Tracker)**:
  - Kartu visualisasi persentase dan alokasi dana donatur yang telah verifikasi per program kebaikan masjid.
- **Mutiara Hadits Harian**:
  - Kutipan hadits shahih tematik seputar sedekah dan jariyah lengkap dengan teks Arab, terjemahan, dan perawinya yang berganti otomatis setiap hari.
- **Tab Filter & Instant Live Search**:
  - Tab penyaring cepat: *Semua*, *Sah & Diterima*, dan *Menunggu Verifikasi*.
  - Kotak pencarian realtime di riwayat donasi yang memfilter baris seketika tanpa *page reload*.
- **Pembersihan Header**:
  - Menghilangkan teks ucapan panjang di bar navigasi atas sehingga tampilan lebih lapang dan fokus pada konten esensial.

### 5. Proteksi Direktori Server
- Menambahkan aturan berkas `.htaccess` di dalam folder `donatur/includes/` untuk memblokir seluruh akses HTTP langsung ke berkas PHP parsial, namun tetap dapat di-include secara aman oleh server-side PHP (`Require all denied`).

### 6. Rate Limiting & Anti-Brute-Force
- **Autentikasi**: Login, Register, Forgot Password, Verifikasi OTP → **5x / 15 menit per IP + User**.
- **Verifikasi Donasi**: **5x / 10 menit per User**.
- **Pengajuan Donasi**: **5x / 24 jam per User** (mencegah spamming donasi palsu).
- **Kirim Pesan Kontak**: **3x / 15 menit per IP**.
- Implementasi via tabel `rate_limits` dengan tracking `attempts`, `last_attempt`, `locked_until`.

### Bug Fixes
- **laporan.php**: Fixed `$bulan = 0` (dari opsi `-- Pilih Bulan --` value kosong) yang menimbulkan `SQLSTATE[HY000]: General error: 1525 Incorrect DATE value: '2026-00-01'`. Sekarang divalidasi ke rentang 1–12, default ke bulan aktif.

---

# RELEASE NOTES — Masjid Jami' Nurul Iman
**Sistem Informasi, Pembukuan Kas & Platform Donasi Digital Masjid**

## v1.3.0 — Comprehensive Security Hardening & Bug Fixes *(2026-10-23)*

###  Peningkatan Keamanan & Perbaikan Utama
- Nominal input switched to text with automatic thousand‑separator formatting and max 999 999 999.99 validation.
- Target donasi input switched to text, max 99 999 999, auto‑format thousands.
- Saldo awal kas input switched to text, max 1 000 000 000, auto‑format thousands.
- Added validation for nominal transaction: reject ≤0 and > 999 999 999.99.
- Adjusted target donation upper bound to 99 999 999.
- Program‑detail donor query now left‑joins `users` to fetch avatar; displays avatar or initial placeholder.
- Transparansi page now paginated (5 items per page) with navigation UI.

- **Password‑reset token security** – token OTP yang dipakai untuk reset kata sandi kini disimpan dalam tabel `users` dengan hash SHA‑256 (`reset_token`), dan diverifikasi dengan `hash('sha256', $token)` sebelum mengubah password.
- **SQL‑i hardening** – seluruh query yang menerima input dinamis (filter laporan, transaksi, pencarian, export CSV/Excel, dll.) di‑ubah menjadi *prepared statements* dengan placeholder `?`.
- **Secure session management** – cookie sesi di‑set `HttpOnly`, `Secure` (jika HTTPS) dan `SameSite=Lax`. `session_regenerate_id(true)` dipanggil setelah login berhasil untuk mencegah *session fixation*.
- **HTTP security headers** – `.htaccess` kini mencakup:
  - `Strict-Transport-Security`
  - `X‑XSS‑Protection`
  - `X‑Content‑Type‑Options`
  - `X‑Frame‑Options`
  - `Referrer-Policy`
  - `Content‑Security‑Policy` (whitelist `cdn.tailwindcss.com`, `cdnjs.cloudflare.com`, `cdn.jsdelivr.net`, `fonts.googleapis.com`, `fonts.gstatic.com`)
- **Error sanitization & logging** – semua `catch (Exception $e)` kini menulis `$e->getMessage()` ke `error_log()` dan menampilkan pesan umum ke pengguna.
- **Input validation** – fungsi `validate_input()` kini mendukung tipe `phone` dengan regex `^08[0-9]{8,12}$`, email, integer, float, serta panjang karakter maksimum.
- **Pagination sanitization** – parameter `page` dibaca memakai `filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options'=>['default'=>1,'min_range'=>1]])` untuk mencegah manipulasi.
- **Misc** – penambahan hidden input `aksi` pada semua form admin, perbaikan UI/UX minor, komentar kode bersih.


---

### Pembaruan Aset Visual & Branding
- **Custom Favicon & Logo**: Mengganti ikon default/masjid SVG dengan file logo kustom dari `uploads/logo/logo.png` pada ikon browser (favicon), header publik, footer, dan sidebar admin.

---

### Pembaruan
- **Bottom Navbar Islami**: Navigasi khusus mobile dengan tema ornamen Islami, animasi interaktif, dan penyesuaian posisi floating WhatsApp.

---

### Peningkatan Navigasi
- **Bottom Navigation Mobile**: Menambahkan navigasi akses cepat (Home, Donasi, Artikel, Akun) yang menempel di bagian bawah layar khusus perangkat mobile (menyesuaikan dengan safe area notch).
- **UX Refinement**: Tombol WhatsApp floating kini otomatis menyesuaikan posisi (naik sedikit) jika Bottom Nav aktif agar tidak bertumpuk.

---

### Peningkatan UI & Mobile Experience
- **Islamic Header Mobile**: Menyesuaikan topbar mobile admin dengan tema ornamen Islami (`bg-cypress-900 pattern-arabesque-dark`) selaras dengan halaman publik.
- **Optimasi Tabel & Touch Action**: Memperlebar padding dan ukuran font tabel agar lebih nyaman dilihat di Android tanpa terlalu rapat.
- **Targeted Click Edit**: Membatasi trigger modal edit berita hanya pada kolom judul berita, sehingga tidak memicu pop-up di area baris kosong.

---

### Peningkatan Antarmuka & Perbaikan Mobile UI
- **Full Mobile Responsiveness**: Optimasi layout tabel admin di perangkat Android/mobile dengan kontainer `overflow-x-auto`, padding ringkas (`4px 2px`), dan ukuran font optimal (`9px`).
- **Direct Row Tap for Edit**: Integrasi interaksi klik baris tabel di modul admin (`berita-admin.php`, dll.) untuk langsung memicu modal edit secara instan.
- **Table Layout Optimization**: Tabel admin disesuaikan agar rapi dan mudah diakses tanpa harus repot menggeser layar secara horizontal.

---


---

## v1.2.0 — Security Hardening, Notification & Staff Directory Update *(2026-10-20)*
*(daftar lengkap ada di file sebelumnya)*

---

## v1.1.0 — Security & UX Update *(2026-09-21)*
*(daftar lengkap ada di file sebelumnya)*

---

## v1.0.0 — Rilis Perdana *(2026-09-04)*
*(daftar lengkap ada di file sebelumnya)*

---

## 🗺️ Roadmap Selanjutnya
- Integrasi payment gateway otomatis (Midtrans / Duitku / QRIS).
- API publik jadwal sholat otomatis.
- Export laporan ke PDF (Dompdf / TCPDF).
- Broadcast WhatsApp.
- Dark mode & multi‑bahasa.
- Unit & integration testing (PHPUnit).

---

## ⚙️ Persyaratan Sistem
- **PHP** >= 8.0 (ext: PDO‑MySQL, mbstring, json, openssl, cURL, **fileinfo**)
- **MySQL** >= 8.0 atau MariaDB >= 10.5
- **Apache** dengan `mod_rewrite`
- **SMTP** aktif untuk email
- **Cloudflare Turnstile** (site & secret key)

---

## 📄 Lisensi
MIT License © 2026 DKM Masjid Jami' Nurul Iman
