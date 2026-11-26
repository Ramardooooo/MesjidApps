# 📊 PROGRESS — Masjid Jami' Nurul Iman (v1.4.0)

Status pengerjaan pengembangan sistem informasi, modul keuangan, dan portal donasi hingga rilis **v1.4.0**.

---

## ✅ Selesai (Completed — v1.4.0)

- [x] **Clean Slug-Based URLs & Routing**:
  - Implementasi routing `/program/{slug}`, `/donasi-online/{slug}`, dan `/berita/{slug}` via `.htaccess`.
  - Dukungan fallback otomatis 301 Permanent Redirect dari parameter query lama (`?id=X`, `?program_id=X`).
  - Regenerasi slug bersih secara realtime di `program-admin.php` dan `berita-admin.php` saat nama/judul diedit.
- [x] **Penguatan Keamanan e-Kwitansi (Anti-Enumeration Token)**:
  - Penerapan token acak 32 karakter kriptografis (`kwitansi?token=...`) pada tabel `donasi_online`.
  - Pembatasan ketat parameter `?no=` hanya untuk Admin/Bendahara dan pemilik sah donasi.
  - Mekanisme *auto-heal* generate token otomatis untuk data lama.
- [x] **Penyempurnaan Tampilan Mobile Admin Kas**:
  - Penghapusan modal popup `showMobileActions` saat baris tabel kas disentuh di layar HP (`admin/transaksi.php`).
- [x] **Peningkatan Fitur Portal Donatur**:
  - Penambahan Lencana Donatur Islami (*Muhsinin*, *Penyemai Kebaikan*, *Pejuang Infaq Istiqomah*, *Sahabat Utama Masjid*).
  - Kartu visualisasi *Jejak Sebaran Amal Jariyah Anda* (alokasi dana per program).
  - Kartu *Mutiara Hadits Amal Harian* bergilir harian dengan teks Arab dan terjemahan.
  - Tab filter cepat (*Semua*, *Sah*, *Menunggu*) dan *live search* instan tanpa reload halaman.
  - Pembersihan header topbar agar lebih lapang dan minimalis.
- [x] **Pengamanan Folder Komponen Internal**:
  - Pembuatan aturan `.htaccess` di folder `donatur/includes/` dengan respons *403 Forbidden* untuk akses HTTP langsung.
- [x] **Sinkronisasi Dokumentasi**:
  - Pembaruan berkas `README.md`, `RELEASE.md`, `WALKTHROUGH.md`, dan `PROGRESS.md` ke versi **v1.4.0**.
- [x] **Rate Limiting & Anti-Brute-Force**:
  - Login, Register, Forgot Password, Verifikasi OTP → maks 5x / 15 menit per IP + User.
  - Pengajuan Donasi → maks 5x / 24 jam per User.
  - Verifikasi Donasi → maks 5x / 10 menit per User.
  - Kontak → maks 3x / 15 menit per IP.
- [x] **Validasi Panjang Form Semua Admin**:
  - Atribut `maxlength` ditambahkan ke semua input form admin sesuai kolom DB.
  - Live Character Counter (`x/max`) otomatis muncul di setiap input.
  - Backend `mb_substr()` mencegah error `SQLSTATE[22001]: Data too long`.
- [x] **Perbaikan Bug Laporan**:
  - `$bulan = 0` pada `laporan.php` divalidasi agar tidak error `SQLSTATE[HY000]: General error: 1525 Incorrect DATE value`.

---

## 🗺️ Roadmap Selanjutnya (v1.5.0+)

- [ ] Integrasi Payment Gateway otomatis (QRIS Dinamis / Midtrans / Duitku).
- [ ] Fitur Kalkulator Zakat Interaktif langsung di Portal Donatur (Zakat Profesi, Maal, Fidyah).
- [ ] Fitur Pojok Doa & Counter "Aamiin" antar sesama donatur jamaah masjid.
- [ ] Integrasi API Publik Jadwal Sholat Otomatis Kemenag.
- [ ] Export Laporan Keuangan Kas ke format PDF terformat rapi (Dompdf).


# 📊 PROGRESS — Masjid Jami' Nurul Iman
**Catatan Perkembangan & Status Fitur Sistem**

---

## ✅ Selesai (v1.0.0 — v1.2.0)

### 🌐 Website Publik (Frontend)
- [x] Beranda (`index.php`) — **[v1.2]** Interactive multi-slide hero slider, widget Jadwal Shalat (WITA), statistik, program unggulan, warta terkini, CTA donasi
- [x] Profil Masjid (`/profil`) — sejarah, visi-misi, pengurus DKM, **[v1.2]** widget tim pengelola DKM
- [x] **[NEW v1.2]** Direktori Tim Pengurus (`/tim-kami`) — jajaran pengurus harian dan teknis masjid
- [x] **[NEW v1.2]** Detail Profil Staff (`/profil-staff?id=X`) — biografi staff, foto avatar, sosmed, dan counter pengunjung profil
- [x] Katalog Program Donasi (`/program`) — list semua program aktif + progress bar dana terkumpul
- [x] Detail Program (`/program-detail?id=X`) — rincian + form donasi cepat
- [x] Berita & Artikel (`/berita`, `/berita-detail`) — pagination server-side, kategori, thumbnail
- [x] Kajian YouTube (`/kajian`) — embed video, playlist live streaming
- [x] Transparansi Keuangan (`/transparansi`) — saldo kas terbuka, riwayat transaksi publik
- [x] Kontak & Pesan Jamaah (`/kontak`) — form validasi, peta lokasi
- [x] Donasi Online (`/donasi-online`) — QRIS + transfer bank, format rupiah realtime, upload bukti MIME check, **[v1.2]** Cloudflare Turnstile CAPTCHA

### 🔐 Autentikasi & Keamanan
- [x] Login multi-role (`/login`) — **[v1.2]** Cloudflare Turnstile, Database Rate Limiting (5x gagal/15 menit), UI countdown timer, session regeneration
- [x] Registrasi akun donatur (`/register`) — OTP verifikasi email + Turnstile
- [x] Lupa password (`/forgot-password`) — via OTP email + Turnstile
- [x] Verifikasi OTP (`/verify-otp`)
- [x] Reset password (`/reset-password`) + Turnstile
- [x] Logout (`/logout`)
- [x] **[NEW v1.2]** Session idle timeout otomatis setelah 2 jam (7200 detik)
- [x] **[NEW v1.2]** Proteksi token CSRF terpadu (`csrf_token`, `verify_csrf`)
- [x] **[NEW v1.2]** Sinkronisasi timezone PHP & MySQL ke `Asia/Makassar` (`+08:00`)

### 🏛️ Admin Panel
- [x] Dashboard eksekutif (`/dashboard`) — statistik kas, grafik 6 bulan (single SQL query teroptimasi), donasi pending
- [x] Manajemen Berita (`/berita-admin`) — CRUD + upload thumbnail + server-side pagination
- [x] Manajemen Banner (`/banner-admin`) — slider beranda, CRUD, **[v1.2]** one-click toggle status aktif (`toggle=ID`), auto URL clean
- [x] Manajemen Program Donasi (`/program-admin`) — CRUD + target + gambar
- [x] Manajemen Rekening (`/rekening-admin`) — rekening bank & QRIS aktif
- [x] Manajemen YouTube (`/youtube-admin`) — CRUD video kajian
- [x] Manajemen Pengguna (`/users-admin`) — CRUD + role management (RBAC)
- [x] Profil & Pengaturan Masjid (`/profil-admin`) — info masjid, pengurus, sosial media
- [x] **[NEW v1.2]** Profil Saya (`/admin/profil-saya`) — kelola profil pribadi pengurus, bio, avatar, sosmed, dan switch publikasi staff
- [x] Pembukuan Kas (`/transaksi`) — pemasukan/pengeluaran + bukti + filter + export CSV + **[v1.2]** server-side pagination & file cleanup otomatis
- [x] Laporan Keuangan (`/laporan`) — filter periode + cetak
- [x] Verifikasi Donasi (`/verifikasi-donasi`) — approve/tolak + auto bukukan ke kas + **[v1.2]** auto kirim notifikasi email
- [x] Inbox Pesan Kontak (`/pesan-admin`) — baca, reply WA/Email, badge unread

### 🧾 e-Kwitansi & Portal Donatur
- [x] e-Kwitansi Donasi (`/kwitansi?no=...`) — Receipt Security Lock 3 status
  - [x] Status `pending` — watermark DRAFT, cetak dikunci, stempel tersembunyi
  - [x] Status `diverifikasi` — kwitansi resmi, stempel DKM, QR validasi, cetak PDF aktif
  - [x] Status `ditolak` — keterangan alasan + watermark merah
  - [x] **[v1.2]** Ownership guard — kwitansi diproteksi sesuai kepemilikan akun donatur
- [x] Portal Donatur (`/portal-donatur`) — **[v1.2]** Arsitektur modular (`donatur/includes/`):
  - [x] Tab status & metrik kontribusi infaq
  - [x] Tab riwayat donasi lengkap
  - [x] Tab profil saya & upload avatar
  - [x] Tab keamanan akun & ubah password

### 📚 Pusat Dokumentasi (`docs/`)
- [x] **[NEW v1.2]** Panduan Pengguna (`/docs/panduan-user.php`)
- [x] **[NEW v1.2]** Panduan Admin Panel (`/docs/panduan-admin.php`)
- [x] **[NEW v1.2]** Kebijakan Privasi (`/docs/privacy.php`)
- [x] **[NEW v1.2]** Syarat & Ketentuan (`/docs/terms.php`)

### 🔒 Keamanan & Infrastruktur
- [x] Clean URL tanpa ekstensi `.php` (mod_rewrite Apache)
- [x] Redirect 301 dari URL lama berekstensi ke URL bersih
- [x] Proteksi akses folder sensitif (403 Forbidden)
- [x] Blokir file berbahaya (`.sql`, `.env`, `.log`, `.bak`)
- [x] Halaman error kustom 400, 401, 403, 404, 500, 503
- [x] PDO Prepared Statements — anti SQL Injection
- [x] Output escaping fungsi `e()` — anti XSS
- [x] MIME type check upload file via `finfo_open`
- [x] Validasi nominal minimum & wajib upload bukti
- [x] Modal full-viewport blur & universal modal delete

---

## 🚧 Dalam Pengembangan / Planned

### Prioritas Tinggi
- [ ] **Gateway pembayaran otomatis** — Midtrans / Duitku / QRIS dinamis dengan webhook IPN
- [ ] **Export PDF murni** untuk laporan keuangan & e-kwitansi (Dompdf / TCPDF)

### Prioritas Menengah
- [ ] **API publik** — endpoint JSON untuk status donasi & transparansi keuangan
- [ ] **Grafik keuangan lanjutan** — per kategori, perbandingan tahunan
- [ ] **Integrasi API waktu sholat otomatis** (sinkronisasi online via koordinat GPS)
- [ ] **Broadcast pesan WhatsApp Gateway** ke donatur terdaftar

### Prioritas Rendah
- [ ] **Tema gelap** (dark mode)
- [ ] **Multibahasa** (i18n) — Indonesia + Arab + Inggris
- [ ] **Unit & Integration Tests** (PHPUnit)
- [ ] **CI/CD Pipeline**
- [ ] **Push notification** browser / Web Push
- [ ] **Aplikasi mobile** (PWA)

---

## 📈 Statistik Codebase

| Komponen | File | Keterangan |
|----------|------|------------|
| Admin Panel | 13 file | dashboard, transaksi, verifikasi, laporan, profil-saya, dll |
| Halaman Publik | 12 file | beranda, donasi, profil, tim-kami, profil-staff, berita, dll |
| Pusat Dokumentasi | 4 file | panduan-user, panduan-admin, privacy, terms |
| Portal Donatur | 10 file | kwitansi, portal + 8 modul di `donatur/includes/` |
| Autentikasi | 6 file | login, register, OTP, reset |
| Layouts | 5 file | header, footer, sidebar (admin & publik) |
| Config & Helper | 3 file | database, mail, helpers |
| Otp & Notifikasi | 3 file | template, otp, send_donasi_notification |
| Database | 17 tabel | InnoDB + utf8mb4 (termasuk `rate_limits`) |

---

## 🗓️ Changelog Singkat

| Tanggal | Versi | Catatan |
|---------|-------|---------|
| 2026-10-20 | v1.2.0 | Cloudflare Turnstile, Database Rate Limiting, Email Notifikasi PHPMailer, Direktori Tim Kami & Profil Staff, Modular Portal Donatur, Pusat Docs, Hero Slider Interaktif |
| 2026-09-21 | v1.1.0 | Receipt Security Lock, Inbox Pesan Kontak, format Rupiah realtime, fix redirect kwitansi |
| 2026-09-04 | v1.0.0 | Rilis perdana — website publik, admin panel, pembukuan, donasi, portal donatur |

---

*Terakhir diperbarui: 2026-11-25*
