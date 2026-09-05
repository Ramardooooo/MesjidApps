-- ==============================================================================
-- DATABASE SISTEM INFORMASI & PEMBUKUAN MASJID JAMI' NURUL IMAN
-- Versi: 2.0 (Terintegrasi Website Publik & Aplikasi Pembukuan Kas)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS mesjid_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mesjid_website;

-- 1. TABEL PENGGUNA (USERS & MULTI-ROLE RBAC)
-- Role: admin (Administrator), bendahara (Pengelola Keuangan), content_admin (Pengelola Konten), donatur (Portal Donatur)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NULL UNIQUE,
    no_hp VARCHAR(25) NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'bendahara', 'content_admin', 'donatur') NOT NULL DEFAULT 'donatur',
    avatar VARCHAR(255) NULL,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    reset_token VARCHAR(100) NULL,
    reset_expires DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. TABEL PROFIL MASJID (PROFIL, SEJARAH, VISI MISI, KONTAK, SOSMED)
CREATE TABLE IF NOT EXISTS profil_masjid (
    id INT PRIMARY KEY DEFAULT 1,
    nama_masjid VARCHAR(150) NOT NULL DEFAULT 'Masjid Jami\' Nurul Iman',
    sebutan VARCHAR(100) NOT NULL DEFAULT 'Pusat Dakwah, Ibadah, dan Pemberdayaan Ummat',
    slogan VARCHAR(255) NOT NULL DEFAULT 'Memakmurkan Masjid, Mensejahterakan Ummat',
    sejarah TEXT NULL,
    visi TEXT NULL,
    misi TEXT NULL,
    alamat TEXT NOT NULL,
    kota VARCHAR(100) NOT NULL DEFAULT 'Banjarmasin',
    google_maps_embed TEXT NULL,
    whatsapp VARCHAR(25) NOT NULL DEFAULT '6281234567890',
    email VARCHAR(100) NOT NULL DEFAULT 'info@masjidnuruliman.id',
    instagram VARCHAR(100) NULL DEFAULT 'masjidnuruliman.official',
    youtube VARCHAR(100) NULL DEFAULT 'MasjidNurulImanTV',
    facebook VARCHAR(100) NULL DEFAULT 'MasjidNurulImanOfficial',
    foto_masjid VARCHAR(255) NULL,
    saldo_awal_kas DECIMAL(15,2) NOT NULL DEFAULT 15000000.00,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. TABEL PENGURUS DKM MASJID
CREATE TABLE IF NOT EXISTS pengurus_masjid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    jabatan VARCHAR(100) NOT NULL,
    bidang VARCHAR(100) NULL,
    no_hp VARCHAR(25) NULL,
    foto VARCHAR(255) NULL,
    urutan INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. TABEL REKENING DONASI & QRIS MASJID (DINAMIS DARI ADMIN)
CREATE TABLE IF NOT EXISTS rekening_donasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_bank VARCHAR(50) NOT NULL,
    nomor_rekening VARCHAR(50) NOT NULL,
    atas_nama VARCHAR(100) NOT NULL,
    kategori_donasi VARCHAR(100) NOT NULL DEFAULT 'Infaq & Kas Umum',
    logo_bank VARCHAR(255) NULL,
    qris_image VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    urutan INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. TABEL PROGRAM & DONASI MASJID
CREATE TABLE IF NOT EXISTS program_donasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_program VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    kategori ENUM('sosial', 'pendidikan', 'keagamaan', 'operasional', 'donasi', 'lainnya') NOT NULL DEFAULT 'donasi',
    deskripsi TEXT NOT NULL,
    deskripsi_lengkap LONGTEXT NULL,
    gambar VARCHAR(255) NULL,
    target_donasi DECIMAL(15,2) NOT NULL DEFAULT 0,
    dana_terkumpul DECIMAL(15,2) NOT NULL DEFAULT 0,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NULL,
    status ENUM('aktif', 'selesai', 'terpenuhi') NOT NULL DEFAULT 'aktif',
    is_featured TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 6. TABEL MASTER KATEGORI TRANSAKSI KAS
CREATE TABLE IF NOT EXISTS kategori_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jenis ENUM('pemasukan', 'pengeluaran') NOT NULL,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi VARCHAR(255) NULL,
    urutan INT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- 7. TABEL BUKU KAS TRANSAKSI KEUANGAN (APLIKASI PEMBUKUAN PROJECT 2)
-- Flag `is_published`: 1 = Data publik/terbuka untuk transparansi web, 0 = Internal/Private pengurus
CREATE TABLE IF NOT EXISTS transaksi_keuangan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi VARCHAR(50) NOT NULL UNIQUE,
    tanggal_transaksi DATE NOT NULL,
    jenis ENUM('pemasukan', 'pengeluaran') NOT NULL,
    kategori_id INT NOT NULL,
    program_id INT NULL,
    akun_kas VARCHAR(50) NOT NULL DEFAULT 'Kas Tunai Utama',
    nominal DECIMAL(15,2) NOT NULL DEFAULT 0,
    keterangan TEXT NOT NULL,
    bukti_transaksi VARCHAR(255) NULL,
    user_id INT NOT NULL,
    metode_pembayaran ENUM('tunai', 'transfer', 'qris') NOT NULL DEFAULT 'tunai',
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tanggal (tanggal_transaksi),
    INDEX idx_jenis (jenis),
    INDEX idx_published (is_published)
) ENGINE=InnoDB;

-- 8. TABEL DONASI ONLINE DARI JAMAAH / DONATUR
CREATE TABLE IF NOT EXISTS donasi_online (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_donasi VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NULL,
    nama_donatur VARCHAR(100) NOT NULL,
    is_anonim TINYINT(1) NOT NULL DEFAULT 0,
    email VARCHAR(100) NULL,
    no_wa VARCHAR(25) NULL,
    program_id INT NOT NULL,
    nominal DECIMAL(15,2) NOT NULL DEFAULT 0,
    metode_pembayaran ENUM('qris', 'transfer') NOT NULL DEFAULT 'qris',
    bank_tujuan VARCHAR(100) NULL,
    bukti_pembayaran VARCHAR(255) NULL,
    doa_donatur TEXT NULL,
    status ENUM('pending', 'diverifikasi', 'ditolak') NOT NULL DEFAULT 'pending',
    catatan_admin VARCHAR(255) NULL,
    verified_by INT NULL,
    verified_at DATETIME NULL,
    transaksi_id INT NULL,
    tanggal_donasi DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 9. TABEL BERITA & ARTIKEL MASJID
CREATE TABLE IF NOT EXISTS berita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    isi LONGTEXT NOT NULL,
    ringkasan VARCHAR(300) NULL,
    thumbnail VARCHAR(255) NULL,
    kategori VARCHAR(50) NOT NULL DEFAULT 'Kajian & Kegiatan',
    penulis_id INT NOT NULL,
    tanggal_publikasi DATE NOT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'published',
    views INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 10. TABEL BANNER & SLIDER BERANDA
CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    subjudul VARCHAR(255) NULL,
    gambar VARCHAR(255) NOT NULL,
    link_url VARCHAR(255) NULL,
    tipe ENUM('slider', 'banner_kegiatan', 'banner_program', 'banner_donasi') NOT NULL DEFAULT 'slider',
    urutan INT NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 11. TABEL INTEGRASI YOUTUBE (KAJIAN & LIVE STREAMING)
CREATE TABLE IF NOT EXISTS youtube_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    video_id VARCHAR(50) NOT NULL,
    url_embed VARCHAR(255) NOT NULL,
    kategori ENUM('live_streaming', 'kajian', 'dokumentasi', 'profil') NOT NULL DEFAULT 'kajian',
    deskripsi TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 12. TABEL NOTIFIKASI USER & DONATUR
CREATE TABLE IF NOT EXISTS notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(150) NOT NULL,
    pesan TEXT NOT NULL,
    tipe ENUM('donasi', 'sistem', 'keuangan') NOT NULL DEFAULT 'donasi',
    link VARCHAR(255) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 13. TABEL PESAN / KONTAK DARI JAMAAH
CREATE TABLE IF NOT EXISTS pesan_kontak (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    no_hp VARCHAR(25) NULL,
    subjek VARCHAR(150) NOT NULL,
    pesan TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==============================================================================
-- DATA SEEDER AWAL (REALISTIS & LENGKAP)
-- ==============================================================================

-- Akun Default:
-- 1. admin (Administrator)      : admin / admin123
-- 2. bendahara (Bendahara Kas)  : bendahara / bendahara123
-- 3. konten (Content Admin)     : konten / konten123
-- 4. donatur (Akun Donatur)     : donatur / donatur123
INSERT INTO users (id, username, email, no_hp, password, nama_lengkap, role, status) VALUES
(1, 'admin', 'admin@masjidnuruliman.id', '081234567890', '$2y$10$qL/o1bZUlYeDv3ycHHhaMOUHcDYvJIp9wcoashJ74M45jjtlIQls.', 'H. Ahmad Syukron, S.Ag (Ketua DKM)', 'admin', 'aktif')
ON DUPLICATE KEY UPDATE role = 'admin', nama_lengkap = 'H. Ahmad Syukron, S.Ag (Ketua DKM)';

INSERT INTO users (id, username, email, no_hp, password, nama_lengkap, role, status) VALUES
(2, 'bendahara', 'bendahara@masjidnuruliman.id', '081234567891', '$2y$10$p0cce5m754g.O5y7mJ2W5.H.Gf9H4eM2FjK7X.D4P4Y9pM4f7e2k.', 'H. Muhammad Ridwan, SE (Bendahara)', 'bendahara', 'aktif'),
(3, 'konten', 'media@masjidnuruliman.id', '081234567892', '$2y$10$p0cce5m754g.O5y7mJ2W5.H.Gf9H4eM2FjK7X.D4P4Y9pM4f7e2k.', 'Ust. Fajar Ramadhan (Divisi Media)', 'content_admin', 'aktif'),
(4, 'donatur', 'donatur@gmail.com', '081234567893', '$2y$10$p0cce5m754g.O5y7mJ2W5.H.Gf9H4eM2FjK7X.D4P4Y9pM4f7e2k.', 'Bpk. H. Bambang Subagyo', 'donatur', 'aktif')
ON DUPLICATE KEY UPDATE id=id;

-- Seeder Profil Masjid
INSERT INTO profil_masjid (id, nama_masjid, sebutan, slogan, sejarah, visi, misi, alamat, kota, google_maps_embed, whatsapp, email, instagram, youtube, facebook, saldo_awal_kas) VALUES
(1, 'Masjid Jami\' Nurul Iman', 'Pusat Peradaban, Ibadah, dan Pemberdayaan Ummat', 'Menegakkan Sunnah, Memakmurkan Masjid, Mensejahterakan Ummat', 
'Masjid Jami\' Nurul Iman didirikan pada tahun 1982 atas prakarsa tokoh masyarakat dan ulama setempat yang mendambakan sarana ibadah dan pusat tarbiyah Islam yang representatif di tengah masyarakat. Seiring perkembangan jamaah, masjid telah mengalami beberapa tahap pemugaran dengan memadukan gaya arsitektur Timur Tengah klasik dan kearifan arsitektur Nusantara.',
'Menjadi pusat peradaban Islam yang mandiri, makmur, rahmatan lil \'alamin, serta unggul dalam pelayanan jamaah dan pemberdayaan sosial ekonomi ummat.',
'1. Menyelenggarakan ibadah fardhu dan sunnah secara berjamaah dengan nyaman dan khusyuk.\n2. Mengembangkan majelis taklim, tahsin, tahfidz Al-Qur\'an, dan kajian keislaman kontemporer.\n3. Mengelola dana zakat, infaq, sedekah, dan wakaf (ZISWAF) secara amanah, transparan, dan profesional.\n4. Menyelenggarakan program santunan sosial, pembinaan generasi muda, dan kepedulian dhuafa.',
'Jl. Mesjid Raya No. 45, Kompleks Permata Indah, Kelurahan Sungai Miai, Kec. Banjarmasin Utara',
'Kota Banjarmasin',
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15932.748383823485!2d114.5828453!3d-3.3085289!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2de423c6a6f69dd3%3A0xb3041c2c8f8fa958!2sMasjid%20Raya%20Sabilal%20Muhtadin!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid',
'6281255557890', 'kontak@masjidnuruliman.id', 'masjidnuruliman.official', 'MasjidNurulImanTV', 'MasjidNurulImanOfficial', 18500000.00)
ON DUPLICATE KEY UPDATE nama_masjid = VALUES(nama_masjid);

-- Seeder Pengurus DKM
INSERT INTO pengurus_masjid (id, nama, jabatan, bidang, no_hp, urutan) VALUES
(1, 'H. Ahmad Syukron, S.Ag', 'Ketua DKM', 'Pimpinan Utama', '081234567890', 1),
(2, 'Drs. H. Abdul Wahab, M.Pd.I', 'Wakil Ketua DKM', 'Pimpinan', '081234567895', 2),
(3, 'Ust. H. Mahfuzh Amin, Lc', 'Sekretaris', 'Kesekretariatan', '081234567896', 3),
(4, 'H. Muhammad Ridwan, SE', 'Bendahara Umum', 'Keuangan & Aset', '081234567891', 4),
(5, 'Ust. Fajar Ramadhan', 'Koordinator Media & IT', 'Humas & Dakwah Digital', '081234567892', 5),
(6, 'Bpk. Suparman', 'Koordinator Sarana & Marbot', 'Pemeliharaan', '081234567897', 6)
ON DUPLICATE KEY UPDATE nama=VALUES(nama);

-- Seeder Rekening Donasi
INSERT INTO rekening_donasi (id, nama_bank, nomor_rekening, atas_nama, kategori_donasi, urutan) VALUES
(1, 'Bank Syariah Indonesia (BSI)', '7123-4567-89', 'MASJID JAMI NURUL IMAN', 'Kas Operasional & Infaq Umum', 1),
(2, 'Bank Muamalat', '101-002-3456', 'DKM MASJID NURUL IMAN', 'Sedekah Subuh & Anak Yatim', 2),
(3, 'Bank Rakyat Indonesia (BRI)', '0021-01-003456-50-8', 'MASJID JAMI NURUL IMAN', 'Pembangunan & Renovasi Sarana', 3),
(4, 'Bank Central Asia (BCA)', '782-098-7654', 'H AHMAD SYUKRON QQ NURUL IMAN', 'Infaq Jumat & Sosial Ummat', 4)
ON DUPLICATE KEY UPDATE nama_bank=VALUES(nama_bank);

-- Seeder Program Donasi
INSERT INTO program_donasi (id, nama_program, slug, kategori, deskripsi, deskripsi_lengkap, target_donasi, dana_terkumpul, tanggal_mulai, tanggal_selesai, status, is_featured) VALUES
(1, 'Pembangunan Menara & Perluasan Tempat Wudhu', 'pembangunan-menara-dan-tempat-wudhu', 'operasional', 
'Renovasi dan perluasan fasilitas tempat wudhu ramah lansia dan penyelesaian lantai 2 menara kumandang azan.',
'Program renovasi fisik meliputi pelebaran selasar wudhu ikhwan dan akhwat, penggantian keramik anti-selip, pemasangan kran hemat air otomatis, serta finishing menara setinggi 21 meter sebagai syiar dakwah adzan masjid.',
75000000.00, 48500000.00, '2026-08-01', '2026-11-30', 'aktif', 1),

(2, 'Santunan Bulanan 50 Anak Yatim & Dhuafa', 'santunan-bulanan-anak-yatim-dan-dhuafa', 'sosial', 
'Pemberian paket sembako nutrisi dan beasiswa pendidikan berkala bagi 50 adik-adik yatim piatu di lingkungan masjid.',
'Masjid membina 50 anak yatim dan piatu di lingkungan sekitar kelurahan. Bantuan disalurkan setiap awal bulan berupa santunan tunai pendidikan sebesar Rp 300.000/anak dan paket sembako keluarga.',
25000000.00, 19200000.00, '2026-09-01', '2026-12-31', 'aktif', 1),

(3, 'Operasional Kebersihan, Listrik AC & Genset', 'operasional-kebersihan-listrik-dan-genset', 'operasional', 
'Dukungan kelancaran operasional ibadah harian jamaah: listrik AC sejuk, kebersihan karpet wangi, dan perawatan sound system.',
'Kebutuhan operasional bulanan masjid untuk memastikan kenyamanan jamaah dalam menjalankan shalat fardhu lima waktu, shalat Jumat, dan kajian taklim.',
15000000.00, 11800000.00, '2026-09-01', '2026-09-30', 'aktif', 1),

(4, 'Wakaf 500 Mushaf Al-Qur\'an Standar Madinah', 'wakaf-mushaf-al-quran-madinah', 'keagamaan', 
'Pengadaan mushaf Al-Qur\'an cetakan berstandar internasional untuk para santri Rumah Tahfidz dan jamaah masjid.',
'Penyediaan 500 mushaf Al-Qur\'an baru berukuran besar yang nyaman dibaca oleh jamaah sepuh maupun para santri penghafal Al-Qur\'an.',
35000000.00, 35000000.00, '2026-07-01', '2026-08-31', 'terpenuhi', 0)
ON DUPLICATE KEY UPDATE nama_program=VALUES(nama_program);

-- Seeder Kategori Transaksi Kas
INSERT INTO kategori_transaksi (id, jenis, nama_kategori, deskripsi, urutan) VALUES
(1, 'pemasukan', 'Infaq Kotak Amal Jumat', 'Penerimaan kotak infaq dari jamaah shalat Jumat', 1),
(2, 'pemasukan', 'Kotak Amal Harian & Subuh', 'Kotak infaq harian di selasar dan pintu masuk masjid', 2),
(3, 'pemasukan', 'Donasi Program Khusus', 'Penerimaan donasi terikat program pembangunan/sosial', 3),
(4, 'pemasukan', 'Infaq Transfer & QRIS', 'Donasi digital via QRIS dan transfer rekening bank', 4),
(5, 'pemasukan', 'Zakat, Infaq & Sedekah (ZIS)', 'Penyaluran dana zakat fitrah & mal', 5),
(6, 'pemasukan', 'Penerimaan Kas Lainnya', 'Sewa aula, parkir berkah, dll', 6),
(7, 'pengeluaran', 'Tagihan Listrik PLN & Air PDAM', 'Pembayaran utilitas bulanan masjid', 1),
(8, 'pengeluaran', 'Honorarium Petugas (Marbot & Imam)', 'Insentif bulanan marbot, muadzin, dan imam rawatib', 2),
(9, 'pengeluaran', 'Kebersihan & Perlengkapan Sanitasi', 'Pembersih lantai, pewangi karpet, sabun, kantong kresek', 3),
(10, 'pengeluaran', 'Kajian & Kegiatan Hari Besar Islam', 'Konsumsi taklim, honor pemateri mubaligh kajian rutin', 4),
(11, 'pengeluaran', 'Santunan Sosial & Yatim Dhuafa', 'Penyaluran bantuan tunai dan logistik dhuafa', 5),
(12, 'pengeluaran', 'Pemeliharaan Gedung & Sarana Sound', 'Perbaikan kran wudhu, service AC, amplifier sound system', 6),
(13, 'pengeluaran', 'Pengeluaran Kas Lainnya', 'Biaya administrasi kantor DKM', 7)
ON DUPLICATE KEY UPDATE nama_kategori=VALUES(nama_kategori);

-- Seeder Transaksi Keuangan (Aplikasi Pembukuan)
INSERT INTO transaksi_keuangan (no_transaksi, tanggal_transaksi, jenis, kategori_id, program_id, akun_kas, nominal, keterangan, user_id, metode_pembayaran, is_published) VALUES
('TRX-IN-202609-001', '2026-09-01', 'pemasukan', 1, NULL, 'Kas Tunai Utama', 4850000.00, 'Perolehan Infaq Kotak Amal Shalat Jumat Pekan I September', 2, 'tunai', 1),
('TRX-IN-202609-002', '2026-09-02', 'pemasukan', 4, 1, 'Bank BSI', 5000000.00, 'Infaq transfer dari H. Bambang untuk program pembangunan menara', 2, 'transfer', 1),
('TRX-IN-202609-003', '2026-09-02', 'pemasukan', 2, NULL, 'Kas Tunai Utama', 1250000.00, 'Perolehan kotak amal Subuh Berkah', 2, 'tunai', 1),
('TRX-IN-202609-004', '2026-09-03', 'pemasukan', 4, 2, 'Bank Muamalat', 2500000.00, 'Donasi santunan anak yatim dari jamaah hamba Allah via QRIS', 2, 'qris', 1),
('TRX-OUT-202609-001', '2026-09-02', 'pengeluaran', 7, NULL, 'Kas Tunai Utama', 950000.00, 'Pembayaran tagihan listrik PLN operasional masjid bulan berjalan', 2, 'tunai', 1),
('TRX-OUT-202609-002', '2026-09-03', 'pengeluaran', 9, NULL, 'Kas Tunai Utama', 350000.00, 'Pembelian sabun cair cuci tangan, pembersih lantai, pewangi karpet', 2, 'tunai', 1),
('TRX-OUT-202609-003', '2026-09-03', 'pengeluaran', 12, 1, 'Kas Tunai Utama', 3200000.00, 'Pembelian 40 sak semen dan besi behel pengerjaan menara tahap 2', 2, 'tunai', 1),
('TRX-OUT-202609-004', '2026-09-04', 'pengeluaran', 8, NULL, 'Kas Tunai Utama', 2000000.00, 'Insentif dan mukafaah 2 orang marbot kebersihan bulan Agustus (Internal)', 2, 'tunai', 0)
ON DUPLICATE KEY UPDATE no_transaksi=VALUES(no_transaksi);

-- Seeder Donasi Online Masuk
INSERT INTO donasi_online (no_donasi, user_id, nama_donatur, is_anonim, email, no_wa, program_id, nominal, metode_pembayaran, bank_tujuan, doa_donatur, status, tanggal_donasi) VALUES
('DON-202609-0001', 4, 'Bpk. H. Bambang Subagyo', 0, 'donatur@gmail.com', '081234567893', 1, 5000000.00, 'transfer', 'Bank Syariah Indonesia (BSI)', 'Semoga pembangunan menara berjalan lancar dan berkah untuk seluruh jamaah.', 'diverifikasi', '2026-09-02'),
('DON-202609-0002', NULL, 'Hamba Allah', 1, 'hambaallah@gmail.com', '081987654321', 2, 500000.00, 'qris', 'QRIS Dinamis', 'Mohon doa agar keluarga senantiasa diberi kesehatan dan anak-anak sholeh.', 'diverifikasi', '2026-09-03'),
('DON-202609-0003', NULL, 'Ibu Hj. Aminah', 0, 'aminah@yahoo.com', '081345678912', 3, 250000.00, 'qris', 'QRIS Dinamis', 'Infaq operasional kebersihan masjid tercinta.', 'pending', '2026-09-04')
ON DUPLICATE KEY UPDATE no_donasi=VALUES(no_donasi);

-- Seeder Berita & Kegiatan
INSERT INTO berita (id, judul, slug, isi, ringkasan, kategori, penulis_id, tanggal_publikasi, status, views) VALUES
(1, 'Semarak Peringatan Maulid Nabi Muhammad SAW 1448 H', 'semarak-peringatan-maulid-nabi-muhammad-saw',
'<p>Alhamdulillah, segenap pengurus DKM Masjid Jami\' Nurul Iman bersama panitia hari besar Islam mengundang seluruh kaum muslimin dan muslimat untuk hadir dalam Peringatan Maulid Nabi Muhammad SAW 1448 H.</p><p>Acara ini insya Allah akan diselenggarakan pada hari Ahad malam Senin, dengan menghadirkan penceramah utama Al-Mukarram KH. Hasanuddin dari Jawa Timur. Agenda akan diawali dengan pembacaan Maulid Simthudduror ba\'da shalat Isya berjamaah.</p><p>Mari ajak sanak keluarga untuk bersama-sama meneguhkan rasa cinta kepada Rasulullah SAW serta meneladani akhlak mulia beliau dalam kehidupan sehari-hari.</p>',
'Undangan peringatan Maulid Nabi Muhammad SAW bersama KH. Hasanuddin ba\'da Isya di ruang utama masjid.',
'Kajian & Kegiatan', 1, '2026-09-03', 'published', 245),

(2, 'Laporan Penyaluran Santunan Pendidikan 50 Anak Yatim Periode September', 'laporan-penyaluran-santunan-pendidikan-50-anak-yatim',
'<p>Segala puji bagi Allah Rabb semesta alam. Pada hari Jumat penuh berkah kemarin, Bidang Sosial DKM Masjid Jami\' Nurul Iman telah menyalurkan santunan pendidikan dan paket perlengkapan sekolah kepada 50 anak yatim binaan masjid.</p><p>Setiap anak menerima santunan tunai sebesar Rp 300.000 serta paket tas, buku tulis, dan sembako untuk keluarga. Program ini terwujud berkat kemurahan hati para donatur dan muhsinin sekalian.</p><p>Kami mengucapkan jazakumullahu khairan katsiran atas kepercayaan yang telah diamanahkan kepada panitia sosial masjid.</p>',
'Penyaluran beasiswa dan paket santunan berkala untuk 50 adik-adik yatim piatu binaan DKM.',
'Sosial Ummat', 1, '2026-09-02', 'published', 189),

(3, 'Jadwal Kajian Rutin Fiqih Ibadah Setiap Ahad Subuh', 'jadwal-kajian-rutin-fiqih-ibadah-ahad-subuh',
'<p>Diberitahukan kepada seluruh jamaah Masjid Jami\' Nurul Iman, bahwa kajian rutin Fiqih Ibadah tematik karya Syaikh Sayyid Sabiq diasuh langsung oleh Ust. H. Mahfuzh Amin, Lc.</p><p>Kajian diadakan setiap Ahad Subuh setelah shalat Subuh berjamaah hingga waktu Syuruq, dilanjutkan dengan sarapan bubur berkah bersama di serambi masjid. Jamaah muslimin dan muslimat dipersilakan hadir.</p>',
'Kajian kitab Fiqih Sunnah setiap Ahad Subuh ba\'da shalat berjamaah hingga waktu syuruq.',
'Jadwal Kajian', 1, '2026-09-01', 'published', 312)
ON DUPLICATE KEY UPDATE judul=VALUES(judul);

-- Seeder Banner Slider
INSERT INTO banners (id, judul, subjudul, gambar, link_url, tipe, urutan, is_active) VALUES
(1, 'Selamat Datang di Masjid Jami\' Nurul Iman', 'Pusat Ibadah Khusyuk, Tarbiyah Generasi Qur\'ani, dan Kebangkitan Ekonomi Ummat', '', 'home/profil.php', 'slider', 1, 1),
(2, 'Mari Berwakaf untuk Menara & Tempat Wudhu', 'Alirkan pahala jariyah tanpa putus dengan membantu penyelesaian sarana ibadah jamaah', '', 'home/program-detail.php?id=1', 'slider', 2, 1),
(3, 'Transparansi Kas Masjid Terbuka & Amanah', 'Pantau arus penerimaan infaq dan penyaluran kas masjid secara realtime dan akuntabel', '', 'home/transparansi.php', 'slider', 3, 1)
ON DUPLICATE KEY UPDATE judul=VALUES(judul);

-- Seeder YouTube Videos
INSERT INTO youtube_videos (id, judul, video_id, url_embed, kategori, deskripsi, is_active) VALUES
(1, 'Live Streaming Shalat Jumat & Khutbah Masjid Nurul Iman', 'dQw4w9WgXcQ', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'live_streaming', 'Siaran langsung shalat Jumat mingguan bersama khatib Ust. Dr. H. Mahfuzh Amin, Lc.', 1),
(2, 'Kajian Tafsir Surah Al-Kahfi - Membentengi Diri dari Fitnah Akhir Zaman', 'M7lc1UVf-VE', 'https://www.youtube.com/embed/M7lc1UVf-VE', 'kajian', 'Kajian tematik tafsir Al-Kahfi diasuh oleh Ust. Dr. H. Mahfuzh Amin, Lc.', 1),
(3, 'Dokumentasi Penyaluran Santunan Akbar 100 Anak Yatim & Dhuafa', 'jNQXAC9IVRw', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'dokumentasi', 'Dokumentasi kebahagiaan adik-adik yatim binaan Masjid Jami\' Nurul Iman.', 1)
ON DUPLICATE KEY UPDATE judul=VALUES(judul);

-- Seeder Notifikasi
INSERT INTO notifikasi (id, user_id, judul, pesan, tipe, link) VALUES
(1, 4, 'Donasi Terverifikasi!', 'Alhamdulillah, donasi Anda sebesar Rp 5.000.000 untuk program Pembangunan Menara telah diverifikasi bendahara. e-Kwitansi resmi telah diterbitkan.', 'donasi', 'donatur/kwitansi.php?no=DON-202609-0001')
ON DUPLICATE KEY UPDATE judul=VALUES(judul);
