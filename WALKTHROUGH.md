# 🗺️ WALKTHROUGH — Masjid Jami' Nurul Iman
**Panduan Navigasi, Alur Penggunaan & Arsitektur Sistem**

> Dokumen ini menjelaskan alur penggunaan sistem dari perspektif setiap peran pengguna (Jamaah/Donatur, Pengurus/Admin, dan Pengunjung Umum).

---

## 1. Alur Jamaah & Donatur (Publik)

### A. Berdonasi Online & e-Kwitansi

```
Beranda (/)
  ├─► Hero Slider Interaktif (Geser Banner / Cek Jadwal Shalat WITA)
  └─► Pilih Program Donasi (/program)
        └─► Klik "Donasi Sekarang" → /donasi-online?program_id=X
              │
              ├─► [Wajib Login Akun Donatur]
              │     └─► Jika belum login: diarahkan ke /login?redirect=...
              │
              └─► Pengisian Formulir Donasi:
                    • Program tujuan otomatis terpilih
                    • Masukkan nominal (min Rp 10.000) — auto titik ribuan (misal: 100.000)
                    • Pilih metode pembayaran: QRIS (Scan langsung) atau Transfer Bank
                    • Nama donatur (bisa centang opsi "Hamba Allah" untuk anonim publik)
                    • Nomor WhatsApp & Email aktif (untuk notifikasi status donasi)
                    • Unggah bukti transfer (JPG/PNG/WEBP, maks 3MB) — pengecekan MIME asli
                    • Selesaikan verifikasi Cloudflare Turnstile CAPTCHA
                    • Klik "Konfirmasi Infaq"
              │
              └─► Redirect otomatis → /donatur/kwitansi?no=DON-YYYYMM-XXXX&baru=1
```

#### Status e-Kwitansi & Keamanan (Receipt Security Lock)
- **Status `pending`**:
  - Judul header: *"Tanda Terima Pengajuan Donasi"*.
  - Watermark diagonal: **DRAFT / BELUM SAH / MENUNGGU VERIFIKASI BENDAHARA**.
  - Tanda tangan & stempel DKM **dikunci**.
  - Tombol cetak **dinonaktifkan** (halaman disembunyikan jika dipaksa cetak melalui `@media print`).
  - QR Code validasi ditampilkan dalam mode *grayscale*.
  - Tombol konfirmasi cepat via WhatsApp langsung ke nomor admin.
- **Status `diverifikasi`**:
  - Judul header resmi: *"BUKTI TANDA TERIMA INFAQ / DONASI RESMI"*.
  - Watermark DRAFT hilang.
  - Stempel DKM aktif berwarna emas kehijauan beserta tanda tangan sah bendahara.
  - QR Code validasi aktif (mengarah ke URL kwitansi sah).
  - Tombol **Cetak / Simpan PDF** dan **Bagikan WhatsApp** aktif.
- **Status `ditolak`**:
  - Banner merah di bagian atas berisi alasan penolakan dari bendahara.
  - Watermark merah: **DONASI DITOLAK / TIDAK SAH**.
  - Pengesahan dikunci dan tombol cetak dinonaktifkan.

#### 📧 Notifikasi Email Otomatis
- Saat status donasi diubah oleh bendahara di admin panel, sistem secara otomatis mengirimkan email ke alamat donatur:
  - **Jika Diverifikasi**: Email berisi ucapan terima kasih, detail program, nomor donasi, nominal donasi, dan tombol langsung ke e-Kwitansi resmi.
  - **Jika Ditolak**: Email pemberitahuan penolakan donasi beserta alasan penolakan secara transparan.

---

### B. Transparansi Kas & Saldo Terbuka
```
/transparansi
  ├─► Ringkasan saldo kas saat ini (Total Kas, Total Pemasukan, Total Pengeluaran)
  ├─► Riwayat transaksi kas masuk & keluar yang dipublikasikan
  └─► Filter berdasarkan jenis transaksi dan periode bulan/tahun
```

---

### C. Direktori Tim DKM & Profil Pengurus
```
/profil
  └─► Sejarah, visi, misi, dan susunan dewan pembina/pengawas DKM
  └─► Widget "Pengurus Harian & Tim Teknis" (/home/profil-staff-dkm.php)
        └─► Tautan "Lihat Seluruh Tim Kami" → /tim-kami
              │
              ├─► /tim-kami: Menampilkan seluruh jajaran admin, bendahara, dan staf
              └─► Klik nama pengurus → /profil-staff?id=X
                    • Foto avatar resmi
                    • Jabatan & status kepengurusan
                    • Biografi & profil singkat
                    • Counter jumlah view profil realtime
                    • Tautan media sosial pengurus (Instagram, LinkedIn, Twitter, Facebook)
```

---

### D. Pusat Dokumentasi & Bantuan Jamaah
```
Navbar Dropdown "Docs"
  ├─► Panduan Pengguna (/docs/panduan-user.php):
  │     • Cara berdonasi via transfer & QRIS
  │     • Cara melacak kwitansi donasi
  │     • Cara membaca transparansi kas
  │     • Cara menggunakan portal donatur
  ├─► Kebijakan Privasi (/docs/privacy.php): Perlindungan data pribadi jamaah
  └─► Syarat & Ketentuan (/docs/terms.php): Ketentuan donasi digital
```

---

## 2. Alur Donatur Terdaftar (Portal Mandiri)

Portal donatur kini menggunakan arsitektur modular (`donatur/includes/`) dengan antarmuka berbasis tab responsif:

```
/login (Turnstile Captcha + Rate Limit Protection)
  └─► /donatur/portal-donatur
        │
        ├── [Tab 1: Ringkasan & Status]
        │     • Hero card donatur (nama, username, status keanggotaan)
        │     • Statistik total infaq yang disalurkan
        │     • Total transaksi berhasil vs pending
        │
        ├── [Tab 2: Riwayat Donasi]
        │     • Daftar seluruh transaksi donasi akun
        │     • Badge status (Pending / Diverifikasi / Ditolak)
        │     • Tombol lihat & cetak e-Kwitansi (dengan proteksi hak milik akun)
        │
        ├── [Tab 3: Profil Saya]
        │     • Edit nama lengkap, email, nomor WhatsApp
        │     • Unggah foto avatar (preview realtime + validasi format)
        │
        └── [Tab 4: Keamanan Akun]
              • Ubah kata sandi dengan verifikasi kata sandi lama
              • Konfirmasi kata sandi baru
```

---

## 3. Alur Pengurus & Admin (Panel Kelola)

### A. Autentikasi Pengurus
```
/login
  ├─► Masukkan Username / Email & Kata Sandi
  ├─► Selesaikan Cloudflare Turnstile CAPTCHA
  ├─► Proteksi Brute-Force: Maksimal 5x percobaan gagal per 15 menit
  │     └─► Jika terkunci: Live countdown timer di layar (misal: "Tunggu 14:32")
  └─► Redirect otomatis sesuai role ke /admin/dashboard
```

---

### B. Dashboard Eksekutif
```
/admin/dashboard
  ├─► Metrik Saldo Kas Realtime, Donasi Bulan Ini, Donasi Pending, dan Pesan Kontak Baru
  ├─► Grafik Trend Keuangan 6 Bulan Terakhir (Single query SQL teroptimasi)
  ├─► Tabel Donasi Pending siap verifikasi
  └─► Tabel 5 Transaksi Terakhir
```

---

### C. Verifikasi Donasi Masuk
```
/admin/verifikasi-donasi
  ├─► Filter status donasi (Semua / Pending / Diverifikasi / Ditolak)
  ├─► Modal pratinjau bukti transfer fisik (zoom gambar)
  │
  ├─► Klik "Verifikasi":
  │     1. Status donasi berubah menjadi 'diverifikasi'
  │     2. Dana otomatis dibukukan ke Kas Masuk (transaksi_keuangan)
  │     3. Dana terkumpul pada program terkait bertambah
  │     4. e-Kwitansi donatur sah (stempel & QR aktif)
  │     5. Sistem otomatis mengirimkan Notifikasi Email ke donatur
  │
  └─► Klik "Tolak":
        1. Masukkan alasan penolakan (misal: "Bukti transfer tidak valid/kurang jelas")
        2. Status donasi berubah menjadi 'ditolak'
        3. e-Kwitansi donatur menampilkan alasan penolakan
        4. Sistem otomatis mengirimkan Email Penolakan dengan alasan
```

---

### D. Pembukuan Kas Digital
```
/admin/transaksi
  ├─► Catat Pemasukan / Pengeluaran baru (Kategori, Nominal, Tanggal, Bukti Fisik)
  ├─► Server-Side Pagination (navigasi halaman bertema masjid)
  ├─► Filter dinamis: Jenis, Kategori, Periode Tanggal, Pencarian Keterangan
  ├─► Toggle Publikasi: Menentukan apakah transaksi ditampilkan di /transparansi
  ├─► Ekspor data kas terfilter ke format CSV / Excel
  ├─► Hapus Transaksi:
  │     └─► Modal konfirmasi bertema islami
  │     └─► Otomatis menghapus berkas fisik bukti dari folder uploads (hapus_file_upload)
  └─► Cetak Laporan Keuangan Periode (/admin/laporan)
```

---

### E. Kelola Profil Saya (Pengurus)
```
/admin/profil-saya
  ├─► Perbarui Nama Lengkap, Nomor HP, Jabatan DKM, dan Biografi
  ├─► Unggah Foto Avatar Pengurus
  ├─► Tautan Media Sosial (Instagram, LinkedIn, Facebook, Twitter)
  ├─► Opsi Publikasi Profil ("Tampilkan profil di halaman Tim Kami")
  └─► Avatar otomatis tampil di sidebar panel admin
```

---

### F. Manajemen Banner & Slider
```
/admin/banner-admin
  ├─► Tambah / Edit Banner Slider Beranda
  ├─► Unggah gambar banner beresolusi tinggi
  ├─► Normalisasi tautan otomatis (Clean URL)
  ├─► One-Click Toggle Status Aktif (klik status untuk on/off tanpa masuk form edit)
  └─► Urutan tampilan banner
```

---

### G. Inbox Pesan Kontak Jamaah
```
/admin/pesan-admin
  ├─► Menampilkan pesan masuk dari formulir /kontak
  ├─► Badge unread berdenyut pada menu sidebar admin
  ├─► Modal baca detail pesan (otomatis menandai pesan telah dibaca)
  ├─► Tombol Quick Reply via WhatsApp Web (buka chat dengan nomor jamaah)
  └─► Tombol Quick Reply via Email (buka compose client default)
```

---

### H. Manajemen Pengguna & Panduan Admin
```
/admin/users-admin   → CRUD pengguna, aktivasi/blokir akun, manajemen hak akses RBAC
/docs/panduan-admin  → Panduan operasional lengkap untuk pengurus DKM
```

---

## 4. Matriks Rute URL Bersih (Clean URLs)

| URL Publik / Admin | File Handler Asli | Keterangan |
|--------------------|-------------------|------------|
| `/` | `index.php` | Beranda utama, slider, jadwal sholat |
| `/profil` | `home/profil.php` | Profil sejarah, visi misi masjid |
| `/tim-kami` | `home/tim-kami.php` | [NEW] Direktori publik pengurus DKM |
| `/profil-staff?id=X`| `home/profil-staff.php` | [NEW] Detail biografi staff & counter view |
| `/program` | `home/program.php` | Katalog program donasi & wakaf |
| `/program-detail?id=X` | `home/program-detail.php` | Rincian program donasi |
| `/donasi-online` | `home/donasi-online.php` | Formulir donasi + Turnstile |
| `/transparansi` | `home/transparansi.php` | Transparansi kas & audit publik |
| `/berita` | `home/berita.php` | Warta berita & kegiatan |
| `/kajian` | `home/kajian.php` | Video kajian & rekaman YouTube |
| `/kontak` | `home/kontak.php` | Formulir kontak & lokasi masjid |
| `/docs/panduan-user.php` | `docs/panduan-user.php` | [NEW] Panduan pengguna & donatur |
| `/docs/panduan-admin.php`| `docs/panduan-admin.php`| [NEW] Panduan operasional admin |
| `/docs/privacy.php` | `docs/privacy.php` | [NEW] Kebijakan privasi |
| `/docs/terms.php` | `docs/terms.php` | [NEW] Syarat & ketentuan |
| `/login` | `auth/login.php` | Login akun + Turnstile + Rate limit |
| `/register` | `auth/register.php` | Pendaftaran donatur baru |
| `/forgot-password` | `auth/forgot-password.php` | Lupa password via OTP |
| `/verify-otp` | `auth/verify-otp.php` | Verifikasi token OTP |
| `/reset-password` | `auth/reset-password.php` | Reset password |
| `/logout` | `auth/logout.php` | Keluar dari sesi |
| `/dashboard` | `admin/dashboard.php` | Dashboard keuangan admin |
| `/verifikasi-donasi`| `admin/verifikasi-donasi.php` | Verifikasi donasi + auto-email |
| `/transaksi` | `admin/transaksi.php` | Buku kas pemasukan/pengeluaran |
| `/laporan` | `admin/laporan.php` | Laporan keuangan & cetak periode |
| `/banner-admin` | `admin/banner-admin.php` | Slider banner & toggle status |
| `/berita-admin` | `admin/berita-admin.php` | CRUD berita & artikel |
| `/program-admin` | `admin/program-admin.php` | CRUD program donasi |
| `/rekening-admin`| `admin/rekening-admin.php`| Rekening donasi & QRIS |
| `/profil-admin` | `admin/profil-admin.php` | Edit profil masjid |
| `/profil-saya` | `admin/profil-saya.php` | [NEW] Kelola profil personal pengurus |
| `/pesan-admin` | `admin/pesan-admin.php` | Inbox pesan kontak jamaah |
| `/users-admin` | `admin/users-admin.php` | Manajemen akun & RBAC |
| `/portal-donatur` | `donatur/portal-donatur.php` | Portal donatur modular |
| `/kwitansi?no=...` | `donatur/kwitansi.php` | e-Kwitansi resmi / DRAFT |

---

## 5. Diagram Alur Transaksi & Notifikasi Email

```
[Jamaah / Donatur]
       │
       ▼
1. Masuk ke /donasi-online (Wajib Login Donatur)
       │
       ├─► Isi Formulir + Validasi Nominal (Format Titik Ribuan)
       ├─► Unggah Bukti Transfer (Validasi MIME File Asli)
       └─► Selesaikan Cloudflare Turnstile CAPTCHA
       │
       ▼
2. Sistem simpan donasi (Status: 'pending')
       │
       ▼
3. Donatur dialihkan ke /donatur/kwitansi (Status: DRAFT)
       │ (Cetak dikunci, Watermark Belum Sah, Tombol Konfirmasi WA)
       │
       ▼
[Bendahara di /verifikasi-donasi]
       │
       ├─────────────────────────────────┬─────────────────────────────────┐
       ▼                                 ▼                                 ▼
   [VERIFIKASI]                       [TOLAK]                          [PENDING]
       │                                 │                                 │
       ├─► Status: 'diverifikasi'        ├─► Status: 'ditolak'             └─► Menunggu review
       ├─► Otomatis catat Kas Masuk      ├─► Catat alasan penolakan
       ├─► Update dana program           │
       │                                 │
       ▼                                 ▼
4. [PHPMailer Service]            4. [PHPMailer Service]
   Kirim Email Konfirmasi:           Kirim Email Penolakan:
   "✅ Donasi Telah Diverifikasi"     "❌ Donasi Tidak Dapat Diverifikasi"
   + Tautan e-Kwitansi Sah           + Penjelasan Alasan
       │                                 │
       ▼                                 ▼
5. [e-Kwitansi Donatur]           5. [e-Kwitansi Donatur]
   Stempel DKM aktif, tanda          Banner penolakan merah,
   tangan sah, tombol cetak aktif    watermark tidak sah
```

---

## 6. Matriks Keamanan Sistem

| Layer Keamanan | Mekanisme & Implementasi | Lokasi Kode |
|----------------|--------------------------|-------------|
| **Bot & Spam Protection** | Cloudflare Turnstile CAPTCHA di Login, Register, Reset Password, dan Donasi | `auth/login.php`, `auth/register.php`, `home/donasi-online.php` |
| **Brute-Force Protection** | Database-backed Rate Limiting (5x gagal / 15 menit) + UI Live Countdown Timer | `rate_limits` table, `config/helpers.php`, `auth/login.php` |
| **CSRF Protection** | Validasi token acak 32-byte pada setiap request POST (`csrf_token`, `verify_csrf`) | `config/helpers.php` |
| **Session Security** | `session_regenerate_id(true)` saat login, idle timeout otomatis 2 jam (7200s) | `config/helpers.php`, `auth/login.php` |
| **SQL Injection** | PDO Prepared Statements dengan parameter binding pada 100% query | Seluruh file backend |
| **XSS Prevention** | Sanitasi output menggunakan helper `e()` (`htmlspecialchars ENT_QUOTES`) | Seluruh file tampilan/template |
| **File Upload Security** | Pengecekan MIME type asli via `finfo_open(FILEINFO_MIME_TYPE)` + hashing nama unik | `home/donasi-online.php`, `config/helpers.php` |
| **File Cleanup** | Berkas fisik bukti/banner otomatis dihapus saat record database dihapus | `hapus_file_upload()` di `config/helpers.php` |
| **Folder & Sensitive Files**| Apache `.htaccess` memblokir akses ke `config`, `db`, `otp`, `layouts`, `.sql`, `.env`, `.log` | `.htaccess` |
| **Receipt Privacy** | Kwitansi donatur diproteksi kepemilikan akun (`user_id` check) | `donatur/kwitansi.php` |

---

*Terakhir diperbarui: 2026-10-20 · Versi Sistem: v1.2.0*
