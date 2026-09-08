<?php
// config/database.php
// Konfigurasi Database & Helper Global Sistem Informasi & Pembukuan Masjid

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

function base_url() {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $pos = strrpos($scriptName, '/');
    $dir = $pos === false ? '' : substr($scriptName, 0, $pos);
    $last = strtolower(substr($dir, strrpos($dir, '/') + 1));
    $subdirs = ['admin', 'auth', 'home', 'donatur'];
    if (in_array($last, $subdirs)) {
        $up = strrpos($dir, '/');
        $dir = $up === false ? '' : substr($dir, 0, $up);
    }
    return rtrim($dir, '/');
}

function root_prefix() {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $dir = $pos = strrpos($scriptName, '/') === false ? '' : substr($scriptName, 0, strrpos($scriptName, '/'));
    $last = strtolower(substr($dir, strrpos($dir, '/') + 1));
    $subdirs = ['admin', 'auth', 'home', 'donatur'];
    return in_array($last, $subdirs) ? '../' : '';
}

function upload_url($path) {
    $path = (string)($path ?? '');
    if ($path === '') return '';
    // Path upload relatif (disimpan sebagai "uploads/...") di-resolve dari akar proyek
    if (strpos($path, 'uploads/') === 0) {
        return base_url() . '/' . $path;
    }
    // Biarkan URL absolut/eksternal apa adanya
    if (preg_match('#^(https?:)?//#i', $path) || strpos($path, '/') === 0) {
        return $path;
    }
    return base_url() . '/' . $path;
}

function mulai_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function cek_login() {
    mulai_session();
    if (!isset($_SESSION['user'])) {
        header('Location: ' . base_url() . '/auth/login.php');
        exit;
    }
}

// Cek hak akses role tertentu
function cek_role(array $allowed_roles) {
    cek_login();
    $userRole = $_SESSION['user']['role'] ?? '';
    if (!in_array($userRole, $allowed_roles)) {
        header('Location: ' . base_url() . '/404.php?err=unauthorized');
        exit;
    }
}

function e($text) {
    return htmlspecialchars((string)($text ?? ''), ENT_QUOTES, 'UTF-8');
}

function format_rupiah($nominal) {
    return 'Rp ' . number_format((float)$nominal, 0, ',', '.');
}

function tanggal_indo($tanggal, $cetak_hari = false) {
    if (empty($tanggal) || $tanggal === '0000-00-00') return '-';
    
    $hari = [
        'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
    ];
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($tanggal);
    if (!$timestamp) return $tanggal;
    
    $tgl = date('j', $timestamp);
    $bln = $bulan[(int)date('n', $timestamp)];
    $thn = date('Y', $timestamp);
    
    if ($cetak_hari) {
        $nama_hari = $hari[(int)date('w', $timestamp)];
        return $nama_hari . ', ' . $tgl . ' ' . $bln . ' ' . $thn;
    }
    
    return $tgl . ' ' . $bln . ' ' . $thn;
}

function get_profil_masjid() {
    global $pdo;
    static $profil = null;
    if ($profil === null) {
        $stmt = $pdo->query("SELECT * FROM profil_masjid WHERE id = 1 LIMIT 1");
        $profil = $stmt->fetch();
        if (!$profil) {
                $profil = [
                    'nama_masjid' => 'Masjid Jami\' Nurul Iman',
                    'sebutan' => 'Pusat Dakwah & Ibadah',
                    'slogan' => 'Memakmurkan Masjid, Mensejahterakan Ummat',
                    'alamat' => 'Jl. Mesjid Raya No. 45, Banjarmasin',
                    'kota' => 'Banjarmasin',
                    'whatsapp' => '6281255557890',
                    'email' => 'info@masjidnuruliman.id',
                    'saldo_awal_kas' => 18500000,
                ];
        }
    }
    return $profil;
}

function upload_berkas($fileInput, $subfolder = 'bukti') {
    if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$fileInput];
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return false;
    }

    // Batasi ukuran file max 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetDir = __DIR__ . '/../uploads/' . $subfolder;
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0777, true);
    }
    $targetPath = $targetDir . '/' . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/' . $subfolder . '/' . $fileName;
    }

    return false;
}
