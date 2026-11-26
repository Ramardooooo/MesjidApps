# 🚶‍♂️ WALKTHROUGH — Panduan Alur Sistem (v1.4.0)
**Panduan Navigasi, Verifikasi Keuangan, dan Penggunaan Fitur Baru**

---

## 1. 🌐 Navigasi Bersih Berbasis Slug (Clean URL)

### Mengakses Program Donasi:
1. Kunjungi katalog program di `/program`.
2. Klik tombol **Rincian Program** pada salah satu kartu program. Perhatikan tautan di peramban kini berupa:
   $$\texttt{/program/nama-program}$$
3. Di dalam rincian program, klik tombol **Donasi Sekarang**. Anda akan diarahkan langsung ke form donasi terfokus:
   $$\texttt{/donasi-online/nama-program}$$
4. Opsi program otomatis terpilih sesuai tujuan infaq tanpa perlu memilih manual dari dropdown.

### Mengakses Warta & Berita:
1. Buka daftar artikel di `/berita`.
2. Pilih salah satu warta kebaikan, alamat artikel akan tampil dalam format bersih:
   $$\texttt{/berita/judul-berita}$$
3. Gunakan tombol **Bagikan ke WhatsApp** untuk menyebarkan link artikel resmi langsung ke grup jamaah.

---

## 2. 🔐 Membuka & Membagikan e-Kwitansi Donasi

### Alur Tanda Terima Donasi:
1. Setelah donatur mengisi formulir infaq di `/donasi-online` dan mengunggah bukti transfer, sistem mencatat status donasi sebagai `pending`.
2. Halaman e-Kwitansi resmi akan langsung terbuka dengan URL aman ber-token:
   $$\texttt{/donatur/kwitansi?token=a8b3c9...}$$
3. **Status Pending**: Tanda tangan dan stempel resmi DKM terkunci otomatis dengan keterangan *"Menunggu Pengesahan Bendahara"*.
4. **Status Sah**: Setelah bendahara memverifikasi di admin, stempel DKM aktif dan tombol **Cetak / Simpan PDF** dapat digunakan.
5. **Keamanan URL**: Siapapun yang mencoba mengganti token dengan nomor transaksi (`?no=DON-...`) secara sembarangan akan ditolak dan dialihkan ke halaman utama.

---

## 3. 👤 Mengelola Akun & Jejak Amal di Portal Donatur

### Langkah Penggunaan Portal:
1. Masuk ke portal melalui menu akun atau URL `/donatur/portal-donatur`.
2. **Lihat Lencana Kehormatan**: Di samping nama Anda, terdapat pin lencana Islami (*Muhsinin*, *Penyemai Kebaikan*, *Pejuang Infaq Istiqomah*, *Sahabat Utama Masjid*) yang menunjukkan status keistiqomahan infaq.
3. **Pantau Sebaran Amal**: Periksa kartu *Jejak Sebaran Amal Jariyah Anda* untuk melihat persentase kontribusi Anda pada tiap program masjid.
4. **Mutiara Hadits**: Baca kutipan hadits harian yang diperbarui setiap hari untuk menyegarkan niat beramal.
5. **Filter Cepat Riwayat**:
   - Gunakan tab **Semua**, **Sah**, atau **Menunggu** untuk memfilter tabel riwayat.
   - Ketik nama program atau nomor transaksi di kolom pencarian untuk melihat data secara instan tanpa memuat ulang halaman.

---

## 4. 💼 Pembukuan Kas di Panel Admin

### Pencatatan Transaksi Tanpa Gangguan Mobile:
1. Buka menu **Buku Kas & Transaksi** di `/admin/transaksi`.
2. Di layar ponsel/smartphone, Anda dapat menggulir (*scroll*) dan membaca mutasi kas dengan nyaman tanpa khawatir muncul popup *"Pilih Aksi"* yang tidak disengaja.
3. Untuk melihat bukti nota kwitansi, klik ikon nota file di kolom aksi.
4. Untuk menghapus data transaksi yang keliru, klik ikon tempat sampah (dilengkapi dialog konfirmasi keamanan).
5. **Catatan Keamanan**: Beberapa form admin memiliki pembatas karakter (*maxlength*) dan counter realtime (*contoh: `0/150*`). Jika menulis melewati batas, sistem akan memotong otomatis saat disimpan.

---

## 5. ✏️ Manajemen Program & Berita Real-time di Admin

### Mengedit Program atau Berita:
1. Masuk ke `admin/program-admin` atau `admin/berita-admin`.
2. Klik tombol **Edit** pada salah satu baris data.
3. Ubah nama program atau judul artikel lalu simpan.
4. Sistem secara instan memperbarui teks sekaligus men-generate `slug` baru yang bersih di basis data, sehingga tautan publik selalu relevan dan mutakhir.
5. **Catatan**: Jika menulis panjangnya melewati batas maksimal, teks akan dipotong otomatis saat disimpan agar tidak error database.

---

## 6. 🛡️ Rate Limiting & Keamanan Autentikasi

### Batas Percobaan Login & Transaksi:
Sistem melindungi formulir kritis dengan *Rate Limiting* berbasis database (`rate_limits`):

| Modul | Batas Percobaan | Window Waktu |
|-------|----------------|--------------|
| Login | 5x gagal | 15 menit per IP + User |
| Register | 3x gagal | 15 menit per IP + User |
| Lupa Password / OTP | 3x gagal | 15 menit per Email |
| Verifikasi OTP | 5x gagal | 10 menit per User |
| Donasi Online | 5x | 24 jam per User |
| Kirim Pesan Kontak | 3x | 15 menit per IP |

Jika batas terlampaui, sistem akan menampilkan pesan perhitungan mundur (*countdown*) dan menolak request baru hingga waktu habis.

---

## 7. 📊 Laporan Keuangan & Perbaikan Bug

### Membuat Laporan:
1. Buka menu **Laporan Keuangan** di `/admin/laporan`.
2. Pilih mode: **Bulanan**, **Mingguan**, **Aktivitas Dana**, atau **Kustom** (rentang tanggal bebas).
3. Pilih bulan & tahun (atau tanggal dari/sampai untuk mode Kustom).
4. Klik **Tampilkan Laporan** untuk melihat ringkasan kas, pemasukan, pengeluaran, dan detail transaksi per periode.

### Catatan Bug Fix:
- Jika muncul error `Incorrect DATE value: '2026-00-01'`, pastikan opsi bulan dipilih dengan benar (tidak `-- Pilih Bulan --`). Sistem sekarang akan default ke bulan aktif jika pilihan kosong.

---