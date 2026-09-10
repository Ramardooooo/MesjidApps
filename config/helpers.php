<?php
// config/helpers.php
// Helper Global Sistem Informasi & Pembukuan Masjid

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

// Pagination server-side sederhana.
// $sql adalah query SELECT TANPA LIMIT/OFFSET (boleh mengandung ORDER BY).
// Mengembalikan ['items', 'total', 'halaman', 'totalHalaman', 'dari', 'sampai'].
function paginate_data($pdo, $sql, $params = [], $perHalaman = 10) {
    $perHalaman = max(1, (int)$perHalaman);
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM (" . $sql . ") AS sub_paginator");
    $stmtCount->execute($params);
    $total = (int)$stmtCount->fetchColumn();

    $totalHalaman = (int)ceil($total / $perHalaman);
    $totalHalaman = max(1, $totalHalaman);

    $halaman = (int)($_GET['page'] ?? 1);
    if ($halaman < 1) $halaman = 1;
    if ($halaman > $totalHalaman) $halaman = $totalHalaman;

    $offset = ($halaman - 1) * $perHalaman;
    $stmt = $pdo->prepare($sql . " LIMIT ? OFFSET ?");
    $i = 1;
    foreach ($params as $param) {
        $stmt->bindValue($i++, $param);
    }
    $stmt->bindValue($i++, (int)$perHalaman, PDO::PARAM_INT);
    $stmt->bindValue($i, (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll();

    $dari   = $total === 0 ? 0 : ($offset + 1);
    $sampai = min($offset + $perHalaman, $total);

    return [
        'items'        => $items,
        'total'        => $total,
        'halaman'      => $halaman,
        'totalHalaman' => $totalHalaman,
        'dari'         => $dari,
        'sampai'       => $sampai,
    ];
}

// Render navigasi pagination bertema masjid (info kiri + tombol kanan).
// Mempertahankan seluruh parameter $_GET yang ada, hanya menambah/mengganti 'page'.
function render_pagination($totalHalaman, $halaman, $total = 0, $dari = 0, $sampai = 0) {
    if ($totalHalaman <= 1 && $total === 0) return;

    $query = $_GET;
    $q = function ($page) use ($query) {
        $query['page'] = $page;
        return '?' . http_build_query($query);
    };

    echo '<div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 pb-1 text-xs">';
    echo '<span class="text-[11px] text-warm-800/50 font-semibold">Menampilkan <b class="text-warm-900">' . $dari . '&ndash;' . $sampai . '</b> dari <b class="text-warm-900">' . $total . '</b> data</span>';

    echo '<nav class="flex items-center gap-1.5" aria-label="Pagination">';

    // Prev
    $prevHalaman = max(1, $halaman - 1);
    $prevDisabled = $halaman <= 1;
    echo '<a href="' . e($q($prevHalaman)) . '" class="w-8 h-8 rounded-xl bg-warm-50 border border-antique-300 text-warm-800 flex items-center justify-center transition ' . ($prevDisabled ? 'pointer-events-none opacity-40' : 'hover:bg-antique-100') . '" title="Sebelumnya">';
    echo '<i class="fa-solid fa-chevron-left text-[10px]"></i></a>';

    // Deret nomor dengan ellipsis
    $mulai = max(1, $halaman - 2);
    $akhir = min($totalHalaman, $halaman + 2);
    if ($mulai > 1) {
        echo '<a href="' . e($q(1)) . '" class="w-8 h-8 rounded-xl bg-warm-50 border border-antique-300 text-warm-800 hover:bg-antique-100 flex items-center justify-center font-semibold transition">1</a>';
        if ($mulai > 2) {
            echo '<span class="w-8 h-8 flex items-center justify-center text-warm-800/40">…</span>';
        }
    }
    for ($i = $mulai; $i <= $akhir; $i++) {
        if ($i === $halaman) {
            echo '<span class="w-8 h-8 rounded-xl bg-cypress-700 text-white font-bold shadow-xs flex items-center justify-center">' . $i . '</span>';
        } else {
            echo '<a href="' . e($q($i)) . '" class="w-8 h-8 rounded-xl bg-warm-50 border border-antique-300 text-warm-800 hover:bg-antique-100 flex items-center justify-center font-semibold transition">' . $i . '</a>';
        }
    }
    if ($akhir < $totalHalaman) {
        if ($akhir < $totalHalaman - 1) {
            echo '<span class="w-8 h-8 flex items-center justify-center text-warm-800/40">…</span>';
        }
        echo '<a href="' . e($q($totalHalaman)) . '" class="w-8 h-8 rounded-xl bg-warm-50 border border-antique-300 text-warm-800 hover:bg-antique-100 flex items-center justify-center font-semibold transition">' . $totalHalaman . '</a>';
    }

    // Next
    $nextHalaman = min($totalHalaman, $halaman + 1);
    $nextDisabled = $halaman >= $totalHalaman;
    echo '<a href="' . e($q($nextHalaman)) . '" class="w-8 h-8 rounded-xl bg-warm-50 border border-antique-300 text-warm-800 flex items-center justify-center transition ' . ($nextDisabled ? 'pointer-events-none opacity-40' : 'hover:bg-antique-100') . '" title="Berikutnya">';
    echo '<i class="fa-solid fa-chevron-right text-[10px]"></i></a>';

    echo '</nav>';
    echo '</div>';
}