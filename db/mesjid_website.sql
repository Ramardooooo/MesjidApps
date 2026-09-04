-- ============================================
-- Database Website Masjid
-- Nama database : mesjid_website
-- ============================================
CREATE DATABASE IF NOT EXISTS mesjid_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mesjid_website;

-- ============================================
-- Tabel User (untuk login admin/panitia)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin','panitia') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Tabel Donasi
-- ============================================
CREATE TABLE IF NOT EXISTS donasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_donatur VARCHAR(100) NOT NULL,
    jenis_donasi ENUM('uang','beras','sembako','lainnya') NOT NULL DEFAULT 'uang',
    jumlah DECIMAL(15,2) DEFAULT 0,
    keterangan TEXT,
    tanggal_donasi DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Tabel Kegiatan / Berita masjid
-- ============================================
CREATE TABLE IF NOT EXISTS kegiatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    isi TEXT NOT NULL,
    tanggal DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Seeder: Akun default
-- username : admin  |  password : admin123
-- ============================================
INSERT INTO users (username, password, nama_lengkap, role) VALUES
('admin', '$2y$10$qL/o1bZUlYeDv3ycHHhaMOUHcDYvJIp9wcoashJ74M45jjtlIQls.', 'Pengurus Masjid', 'admin');

INSERT INTO donasi (nama_donatur, jenis_donasi, jumlah, keterangan, tanggal_donasi) VALUES
('Bpk. Ahmad', 'uang', 1000000, 'Untuk pembangunan', '2026-09-01'),
('Ibu Siti', 'beras', 0, '10 kg beras', '2026-09-02');

INSERT INTO kegiatan (judul, isi, tanggal) VALUES
('Pengajian Rutin Jumat', 'Kajian kitab setiap Jumat malam ba\'da Maghrib.', '2026-09-05');
