<?php
// config/helpers.php
// Helper Global Sistem Informasi & Pembukuan Masjid

function base_url() {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $pos = strrpos($scriptName, '/');
    $dir = $pos === false ? '' : substr($scriptName, 0, $pos);
    $last = strtolower(substr($dir, strrpos($dir, '/') + 1));
    $subdirs = ['admin', 'auth', 'home', 'donatur', 'docs'];
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
    $subdirs = ['admin', 'auth', 'home', 'donatur', 'docs'];
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

function app_url($path = '') {
    $path = trim((string)($path ?? ''));
    if ($path === '' || $path === '#') return '#';
    // External link / special scheme
    if (preg_match('#^(https?:)?//#i', $path) || strpos($path, 'wa.me') !== false || strpos($path, 'mailto:') === 0 || strpos($path, 'tel:') === 0) {
        return $path;
    }
    // Normalisasi tautan internal: hapus leading slash, prefix home/, dan ekstensi .php
    $path = ltrim($path, '/');
    $path = preg_replace('#^home/#i', '', $path);
    $path = preg_replace('#\.php(\?|$)#i', '$1', $path);

    if ($path === '' || $path === 'index') {
        return base_url() . '/';
    }
    return base_url() . '/' . $path;
}

function mulai_session() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.gc_maxlifetime', 7200);
        session_set_cookie_params(7200);
        session_start();
    }
    
    if (isset($_SESSION['user'])) {
        if (isset($_SESSION['last_activity'])) {
            $idle_time = time() - $_SESSION['last_activity'];
            
            if ($idle_time > 7200) {
                session_unset();
                session_destroy();
                header('Location: ' . base_url() . '/auth/login?timeout=1');
                exit;
            }
        }
        
        $_SESSION['last_activity'] = time();
    }
}

function csrf_token() {
    mulai_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die('CSRF token invalid. Refresh page dan coba lagi.');
    }
}

function get_client_ip() {
    // 1. Prioritaskan Cloudflare Real Visitor IP jika request melewati Cloudflare Proxy
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $cfIp = trim($_SERVER['HTTP_CF_CONNECTING_IP']);
        if (filter_var($cfIp, FILTER_VALIDATE_IP)) {
            return $cfIp;
        }
    }

    // 2. HTTP_X_FORWARDED_FOR (Proxy / Load Balancer)
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($ips as $candidate) {
            $candidate = trim($candidate);
            if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $candidate;
            }
        }
        $first = trim($ips[0]);
        if (filter_var($first, FILTER_VALIDATE_IP)) {
            return $first;
        }
    }

    // 3. HTTP_CLIENT_IP
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $clientIp = trim($_SERVER['HTTP_CLIENT_IP']);
        if (filter_var($clientIp, FILTER_VALIDATE_IP)) {
            return $clientIp;
        }
    }

    // 4. Default REMOTE_ADDR
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    return trim($ip);
}

// Verifikasi Cloudflare Turnstile CAPTCHA secara aman
function verify_turnstile($token, $action = '') {
    if (empty($token) || !is_string($token)) {
        return false;
    }

    if (!defined('TURNSTILE_SECRET') || empty(TURNSTILE_SECRET)) {
        return true; // Jika turnstile belum dikonfigurasi, loloskan
    }

    $ip = get_client_ip();
    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret'   => TURNSTILE_SECRET,
        'response' => $token,
        'remoteip' => $ip,
    ]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $response = curl_exec($ch);
    $curlErr  = curl_errno($ch);
    curl_close($ch);

    if ($curlErr || !$response) {
        // Fallback jika offline / localhost gagal konek ke Cloudflare API
        if (in_array($ip, ['127.0.0.1', '::1'])) {
            return true;
        }
        return false;
    }

    $data = json_decode($response, true);
    return !empty($data['success']);
}

function hapus_file_upload($path) {
    if (empty($path)) return false;
    
    $fullPath = __DIR__ . '/../' . $path;
    
    if (file_exists($fullPath) && is_file($fullPath)) {
        return @unlink($fullPath);
    }
    
    return false;
}

function rate_limit($key, $max_attempts = 100, $decay_minutes = 1) {
    global $pdo;
    $ip = get_client_ip();
    $identifier = substr($key . '_' . md5($ip), 0, 255);
    $max_attempts = max(1, (int)$max_attempts);
    $decay_minutes = max(1, (int)$decay_minutes);

    try {
        $pdo->prepare("
            DELETE FROM rate_limits
            WHERE (locked_until IS NOT NULL AND locked_until <= NOW())
               OR (key_type = 'ip' AND identifier = ? AND locked_until IS NULL AND last_attempt < DATE_SUB(NOW(), INTERVAL ? MINUTE))
        ")->execute([$identifier, $decay_minutes]);

        $stmt = $pdo->prepare("
            SELECT attempts, TIMESTAMPDIFF(SECOND, NOW(), locked_until) AS wait_sec
            FROM rate_limits
            WHERE key_type = 'ip' AND identifier = ?
            LIMIT 1
        ");
        $stmt->execute([$identifier]);
        $row = $stmt->fetch();

        if ($row) {
            if ($row['wait_sec'] !== null && (int)$row['wait_sec'] > 0) {
                return [
                    'allowed' => false,
                    'wait_seconds' => (int)$row['wait_sec'],
                    'attempts' => (int)$row['attempts'],
                ];
            }

            $attempts = (int)$row['attempts'];
            if ($attempts >= $max_attempts) {
                $pdo->prepare("
                    UPDATE rate_limits
                    SET locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE)
                    WHERE key_type = 'ip' AND identifier = ?
                ")->execute([$decay_minutes, $identifier]);
                return [
                    'allowed' => false,
                    'wait_seconds' => $decay_minutes * 60,
                    'attempts' => $attempts,
                ];
            }

            $newAttempts = $attempts + 1;
            if ($newAttempts >= $max_attempts) {
                $pdo->prepare("
                    UPDATE rate_limits
                    SET attempts = ?, locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE)
                    WHERE key_type = 'ip' AND identifier = ?
                ")->execute([$newAttempts, $decay_minutes, $identifier]);
            } else {
                $pdo->prepare("
                    UPDATE rate_limits
                    SET attempts = ?
                    WHERE key_type = 'ip' AND identifier = ?
                ")->execute([$newAttempts, $identifier]);
            }

            return ['allowed' => true, 'attempts' => $newAttempts];
        }

        $locked = ($max_attempts <= 1) ? date('Y-m-d H:i:s', time() + ($decay_minutes * 60)) : null;
        $pdo->prepare("
            INSERT INTO rate_limits (key_type, identifier, attempts, locked_until)
            VALUES ('ip', ?, 1, ?)
        ")->execute([$identifier, $locked]);

        return ['allowed' => true, 'attempts' => 1];
    } catch (Exception $e) {
        return ['allowed' => true, 'attempts' => 1];
    }
}

function clear_rate_limit($key) {
    global $pdo;
    $ip = get_client_ip();
    $identifier = substr($key . '_' . md5($ip), 0, 255);
    try {
        $pdo->prepare("DELETE FROM rate_limits WHERE key_type = 'ip' AND identifier = ?")->execute([$identifier]);
    } catch (Exception $e) {
    }
}

function cleanup_expired_rate_limits() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            DELETE FROM rate_limits 
            WHERE locked_until IS NOT NULL AND locked_until < NOW()
        ');
        $stmt->execute();
        return $stmt->rowCount();
    } catch (Exception $e) {
        return 0;
    }
}

function cek_login() {
    mulai_session();
    if (!isset($_SESSION['user'])) {
        header('Location: ' . base_url() . '/auth/login');
        exit;
    }
}

// Cek hak akses role tertentu
function cek_role(array $allowed_roles) {
    cek_login();
    $userRole = $_SESSION['user']['role'] ?? '';
    if (!in_array($userRole, $allowed_roles)) {
        header('Location: ' . base_url() . '/404?err=unauthorized');
        exit;
    }
}

function get_daftar_bulan() {
    return ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
}

function e($text) {
    return htmlspecialchars((string)($text ?? ''), ENT_QUOTES, 'UTF-8');
}

function validate_input($data, $type = 'string', $max_length = 255) {
    if ($type === 'email') {
        return filter_var(trim($data), FILTER_VALIDATE_EMAIL);
    } elseif ($type === 'int') {
        return filter_var($data, FILTER_VALIDATE_INT, ['min_range' => 1]);
    } elseif ($type === 'float') {
        return filter_var($data, FILTER_VALIDATE_FLOAT);
    } else {
        $trimmed = trim($data);
        return strlen($trimmed) > 0 && strlen($trimmed) <= $max_length ? $trimmed : false;
    }
}

function require_login() {
    if (!isset($_SESSION['user'])) {
        header('Location: ' . base_url() . '/auth/login');
        exit;
    }
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

// Parse User Agent untuk visitor tracking
function parse_user_agent($ua = '') {
    $ua = $ua ?: ($_SERVER['HTTP_USER_AGENT'] ?? '');
    $browser = 'Unknown';
    $browser_ver = '';
    $os = 'Unknown';
    $device = 'desktop';

    // Browser deteksi
    if (preg_match('/MSIE|Trident\/7.0/', $ua)) {
        $browser = 'Internet Explorer';
    } elseif (preg_match('/Edge\/(\S+)/', $ua, $m)) {
        $browser = 'Edge'; $browser_ver = $m[1];
    } elseif (preg_match('/OPR\/(\S+)/', $ua, $m)) {
        $browser = 'Opera'; $browser_ver = $m[1];
    } elseif (preg_match('/Chrome\/(\S+)/', $ua, $m)) {
        $browser = 'Chrome'; $browser_ver = $m[1];
    } elseif (preg_match('/Firefox\/(\S+)/', $ua, $m)) {
        $browser = 'Firefox'; $browser_ver = $m[1];
    } elseif (preg_match('/Safari\/(\S+)/', $ua, $m) && !preg_match('/Chrome/', $ua)) {
        $browser = 'Safari'; $browser_ver = $m[1];
    }

    // OS deteksi
    if (preg_match('/Windows NT (\S+)/', $ua, $m)) {
        $map = ['10.0' => '11/10', '6.3' => '8.1', '6.2' => '8', '6.1' => '7', '6.0' => 'Vista'];
        $os = 'Windows ' . ($map[$m[1]] ?? $m[1]);
    } elseif (preg_match('/Mac OS X (\S+)/', $ua, $m)) {
        $os = 'macOS ' . str_replace('_', '.', $m[1]);
    } elseif (preg_match('/Linux/', $ua)) {
        $os = 'Linux';
        if (preg_match('/Android (\S+)/', $ua, $m)) {
            $os = 'Android ' . $m[1]; $device = 'mobile';
        }
    } elseif (preg_match('/iPhone OS (\S+)/', $ua, $m)) {
        $os = 'iOS ' . str_replace('_', '.', $m[1]); $device = 'mobile';
    } elseif (preg_match('/iPad.*OS (\S+)/', $ua, $m)) {
        $os = 'iPadOS ' . str_replace('_', '.', $m[1]); $device = 'tablet';
    }

    // Bot / Mobile / Tablet
    if (preg_match('/bot|crawl|spider|slurp|facebookexternalhit/i', $ua)) {
        $device = 'bot';
    } elseif ($device !== 'tablet' && preg_match('/Mobile|iPhone|Android.*Mobile/i', $ua)) {
        $device = 'mobile';
    }

    return [
        'browser' => $browser,
        'browser_version' => $browser_ver,
        'os' => $os,
        'device_type' => $device
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