<?php
// config/database.php
// Konfigurasi Database Sistem Informasi & Pembukuan Masjid

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mesjid_website');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}

// Pastikan zona waktu PHP & MySQL konsisten agar OTP tidak dianggap kedaluwarsa
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Asia/Makassar');
}
try {
    $pdo->exec("SET time_zone = '+08:00'");
} catch (Throwable $e) {
    // Abaikan jika gagal set time_zone
}

// Pastikan folder uploads tersedia
$uploadDirs = [
    __DIR__ . '/../uploads',
    __DIR__ . '/../uploads/bukti',
    __DIR__ . '/../uploads/program',
    __DIR__ . '/../uploads/berita',
    __DIR__ . '/../uploads/banner',
    __DIR__ . '/../uploads/qris',
    __DIR__ . '/../uploads/pengurus',
];
foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Load helper global (base_url, cek_login, e, format_rupiah, dsb.)
require_once __DIR__ . '/helpers.php';