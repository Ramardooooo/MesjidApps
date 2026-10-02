# 📋 RELEASE NOTES — Masjid Jami' Nurul Iman
**Sistem Informasi, Pembukuan Kas & Platform Donasi Digital Masjid**

---

## v1.1.0 — Security & UX Update *(2026-09-21)*

### ✨ Fitur Baru

#### 🧾 Receipt Security Lock (Penguncian Kwitansi Berlapis)
Kwitansi e-donasi kini memiliki sistem pengamanan 3 status berlapis:

| Status | Tampilan |
|--------|----------|
| `pending` | Header = "Tanda Terima Pengajuan Donasi", watermark diagonal **DRAFT / BELUM SAH / MENUNGGU VERIFIKASI BENDAHARA**, stempel DKM & tanda tangan **dikunci**, tombol cetak **dinonaktifkan** |
| `diverifikasi` | Header resmi "BUKTI TANDA TERIMA INFAQ / DONASI RESMI", stempel DKM aktif, tanda tangan bendahara, QR validasi aktif, tombol **Cetak PDF & Bagikan WA** aktif |
| `ditolak` | Banner merah + alasan admin, watermark **DONASI DITOLAK / TIDAK SAH**, pengesahan dikunci |

- Proteksi `@media print` — bila `pending`/`ditolak`, halaman disembunyikan saat cetak
- QR Code validasi di-grayscale saat belum diverifikasi
- Tombol "Cetak PDF (Terkunci)" memunculkan alert informatif

#### 📬 Inbox Pesan Kontak Admin (`admin/pesan-admin.php`)
- Modul inbox lengkap untuk pesan masuk dari formulir kontak publik
- Badge unread realtime di sidebar navigasi admin
- Quick Reply satu klik via WhatsApp dan Email langsung dari panel admin
- Modal detail pesan dengan z-index tinggi

#### 💰 Formatter Nominal Rupiah Realtime
- Input nominal otomatis format titik ribuan saat mengetik: `50000` → `50.000`
- Tombol preset nominal langsung format dengan titik
- Highlight visual pada preset yang dipilih

#### 🛡️ Validasi Upload Bukti Transfer
- Wajib upload bukti transfer (required)
- Pengecekan MIME type asli via `finfo_open(FILEINFO_MIME_TYPE)`
- Hanya menerima: image/jpeg, image/png, image/webp — max 3MB
- Validasi nominal minimum Rp 10.000

### 🐛 Bug Fix

- **Redirect donasi ke Home** — `base_url()` tidak menerima parameter, diperbaiki dengan `base_url() . '/donatur/kwitansi?no=...'`
- **Form action relatif** — `action="donasi-online"` diganti menjadi `action="<?= base_url() ?>/donasi-online"`
- **Sisa hidden input CSRF** menyebabkan PHP Notice
- **Modal blur tidak full-screen** — modal dipindahkan ke `document.body` via JS

### 🔧 File yang Diubah

| File | Perubahan |
|------|-----------|
| `donatur/kwitansi.php` | Receipt Security Lock 3 status, watermark, print lock |
| `home/donasi-online.php` | Fix redirect, fix form action, format rupiah realtime, validasi MIME upload |
| `admin/pesan-admin.php` | Modul inbox baru — baca, balas WA/Email, tandai terbaca |
| `assets/js/app.js` | `attachModalsToBody()` — auto pindahkan modal ke `<body>` |
| `assets/css/classic-theme.css` | `.veil-blur` upgrade ke backdrop-filter blur full viewport |
| `layouts/sidebar.php` | Badge unread Pesan Kontak, menu baru |
| `.htaccess` | Shortcut `/pesan-admin` |

---

## v1.0.0 — Rilis Perdana *(2026-09-04)*

### ✨ Fitur Utama

- **Website Publik Modern** — Beranda, profil masjid & pengurus DKM, katalog program donasi, berita & artikel, kajian YouTube, transparansi kas, kontak jamaah
- **Pembukuan Kas Terpadu** — Pencatatan pemasukan/pengeluaran, kategori, bukti unggah, filter periode, ekspor CSV/Excel, laporan cetak
- **Donasi Online** — Formulir QRIS & transfer bank, verifikasi admin satu klik, e-kwitansi otomatis, notifikasi donatur
- **Portal Donatur Mandiri** — Dashboard pribadi, riwayat donasi, update profil, cetak kwitansi
- **Autentikasi Multi-Role (RBAC)** — Admin, bendahara, content_admin, donatur; OTP via email
- **16 Tabel Database** — Skema lengkap InnoDB utf8mb4
- **Clean URL** — Tanpa ekstensi .php via mod_rewrite Apache

---

## 🗺️ Roadmap

- [ ] Gateway pembayaran otomatis (Midtrans / Duitku / QRIS dinamis + webhook)
- [ ] API publik endpoint cek status donasi
- [ ] Export laporan PDF + grafik keuangan per kategori
- [ ] Jadwal sholat & pengumuman otomatis
- [ ] Notifikasi email berkala
- [ ] Unit & integration tests (PHPUnit)
- [ ] Tema gelap & multibahasa (i18n)

---

## ⚙️ Persyaratan

- PHP >= 8.0 (PDO MySQL, mbstring, json, openssl, fileinfo)
- MySQL >= 8.0 / MariaDB >= 10.5
- Apache + mod_rewrite
- Composer

## 📄 Lisensi

MIT
