# 🗺️ WALKTHROUGH — Masjid Jami' Nurul Iman
**Panduan Navigasi & Alur Penggunaan Sistem**

> Dokumen ini menjelaskan alur penggunaan sistem dari perspektif setiap peran pengguna.

---

## 1. Alur Jamaah / Donatur (Publik)

### A. Berdonasi Online

```
Beranda (/)
  └─► Pilih Program Donasi (/program)
        └─► Klik "Donasi Sekarang" → /donasi-online?program_id=X
              └─► Isi Formulir:
                    • Pilih program tujuan
                    • Masukkan nominal (min Rp 10.000) — auto titik ribuan
                    • Pilih metode: QRIS (scan langsung) atau Transfer Bank
                    • Isi nama / centang Hamba Allah
                    • Isi nomor WhatsApp aktif
                    • Upload bukti transfer (JPG/PNG/WEBP, max 3MB) — WAJIB
                    • Klik "Konfirmasi Infaq"
              └─► Redirect otomatis → /donatur/kwitansi?no=DON-YYYYMM-XXXX&baru=1
```

**Status Kwitansi:**
- **Pending** → Watermark DRAFT, cetak dikunci, konfirmasi WA ke admin
- **Diverifikasi** → Kwitansi resmi, stempel DKM aktif, bisa cetak PDF
- **Ditolak** → Keterangan alasan penolakan dari admin

### B. Transparansi Keuangan
```
/transparansi → Saldo kas terbuka + riwayat transaksi publik
```

### C. Kontak & Pesan
```
/kontak → Isi nama, email, telepon, pesan → Terkirim ke Inbox Admin
```

---

## 2. Alur Admin / Bendahara

### A. Login
```
/login → Username + Password → Dashboard Admin (/dashboard)
```

### B. Verifikasi Donasi
```
/verifikasi-donasi
  └─► Lihat daftar donasi pending
  └─► Klik "Verifikasi" → status berubah ke `diverifikasi`
        → Dana otomatis masuk ke pembukuan kas
        → Kwitansi donatur terupdate (stempel aktif)
  └─► Atau "Tolak" dengan alasan → status `ditolak`
        → Kwitansi donatur tampilkan keterangan penolakan
```

### C. Pembukuan Kas
```
/transaksi
  └─► Catat pemasukan / pengeluaran manual
  └─► Upload bukti transaksi
  └─► Filter periode
  └─► Export CSV / Excel
  └─► Cetak laporan
```

### D. Inbox Pesan Kontak
```
/pesan-admin
  └─► Lihat pesan masuk dari jamaah
  └─► Klik pesan → baca detail (otomatis tandai terbaca)
  └─► Quick Reply via WhatsApp (buka WA web)
  └─► Quick Reply via Email (buka email client)
  └─► Badge sidebar menampilkan jumlah pesan belum dibaca
```

### E. Manajemen Konten
```
/berita-admin     → CRUD berita & artikel
/banner-admin     → Kelola slider & banner beranda
/program-admin    → CRUD program donasi (target, gambar, status)
/youtube-admin    → Kelola video kajian YouTube
/rekening-admin   → Kelola rekening bank & QRIS
/profil-admin     → Edit profil masjid, pengurus DKM, sosial media
/users-admin      → Manajemen pengguna & hak akses role
```

---

## 3. Alur Donatur Terdaftar (Portal)

```
/login → Dashboard Donatur (/portal-donatur)
  └─► Riwayat donasi saya
  └─► Klik donasi → /donatur/kwitansi?no=...
  └─► Update profil pribadi
  └─► Notifikasi status donasi
```

---

## 4. Struktur URL Bersih

| URL Bersih | File Asli |
|------------|-----------|
| `/` | `index.php` |
| `/profil` | `home/profil.php` |
| `/program` | `home/program.php` |
| `/donasi-online` | `home/donasi-online.php` |
| `/transparansi` | `home/transparansi.php` |
| `/berita` | `home/berita.php` |
| `/kajian` | `home/kajian.php` |
| `/kontak` | `home/kontak.php` |
| `/login` | `auth/login.php` |
| `/register` | `auth/register.php` |
| `/dashboard` | `admin/dashboard.php` |
| `/verifikasi-donasi` | `admin/verifikasi-donasi.php` |
| `/transaksi` | `admin/transaksi.php` |
| `/laporan` | `admin/laporan.php` |
| `/pesan-admin` | `admin/pesan-admin.php` |
| `/portal-donatur` | `donatur/portal-donatur.php` |
| `/kwitansi?no=...` | `donatur/kwitansi.php` |

---

## 5. Diagram Alur Donasi

```
[Jamaah] ──submit form──► [donasi-online.php]
                                │
                    Validasi: program, nominal ≥ 10rb,
                    nama, WA, upload bukti MIME check
                                │
                          INSERT DB (status=pending)
                                │
                    ◄── Redirect ke /kwitansi ──►
                    [Kwitansi DRAFT / watermark]
                                │
                    Donatur konfirmasi ke Admin WA
                                │
                    [Admin] /verifikasi-donasi
                    Klik Verifikasi / Tolak
                                │
                    UPDATE status + catat ke kas
                                │
                    [Kwitansi] berubah → RESMI
                    Stempel DKM aktif, cetak PDF
```

---

## 6. Keamanan Sistem

| Layer | Implementasi |
|-------|-------------|
| SQL Injection | PDO Prepared Statements di semua query |
| XSS | Fungsi `e()` (htmlspecialchars) di semua output |
| File Upload | MIME type asli via `finfo_open`, whitelist ekstensi |
| Akses Folder | `.htaccess` blokir `config`, `db`, `otp`, `layouts`, `vendor` |
| RBAC | Cek role di setiap halaman admin via `require_role()` |
| File Sensitif | `.sql`, `.env`, `.log`, `.bak` diblokir `FilesMatch` |

---

*Terakhir diperbarui: 2026-09-21 · Versi: v1.1.0*
