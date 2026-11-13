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
