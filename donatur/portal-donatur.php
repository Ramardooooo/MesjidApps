<?php
require_once __DIR__ . '/../config/database.php';

cek_login();

$user = $_SESSION['user'];

if (($user['role'] ?? '') !== 'donatur') {
    header('Location: ' . base_url() . '/admin/dashboard');
    exit;
}

$profil = get_profil_masjid();
$pageTitle = 'Portal Donatur · ' . $profil['nama_masjid'];

$pesan = '';
$tipe = '';

/*
|--------------------------------------------------------------------------
| AMBIL DATA USER TERBARU
|--------------------------------------------------------------------------
*/
$stmtUser = $pdo->prepare("
    SELECT
        id,
        username,
        email,
        no_hp,
        nama_lengkap,
        role,
        avatar,
        status,
        created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");
$stmtUser->execute([$user['id']]);
$userDb = $stmtUser->fetch();

if (!$userDb) {
    session_destroy();
    header('Location: ' . base_url() . '/auth/login');
    exit;
}

/*
|--------------------------------------------------------------------------
| HELPER UPLOAD FOTO PROFIL
|--------------------------------------------------------------------------
*/
function upload_foto_profil($inputName = 'foto_profil')
{
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    $file = $_FILES[$inputName];
    if ($file['size'] > 3 * 1024 * 1024) {
        return false;
    }
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        return false;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowedMime, true)) {
        return false;
    }
    $fileName = 'profile_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
    $targetDir = __DIR__ . '/../uploads/profil';
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0777, true);
    }
    $targetPath = $targetDir . '/' . $fileName;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/profil/' . $fileName;
    }
    return false;
}

function hapus_foto_lama($avatar)
{
    if (empty($avatar)) return;
    if (strpos($avatar, 'uploads/profil/') !== 0) return;
    $filePath = __DIR__ . '/../' . $avatar;
    if (is_file($filePath)) {
        @unlink($filePath);
    }
}

/*
|--------------------------------------------------------------------------
| UPDATE PROFIL
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'update_profil') {
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $noHp = trim($_POST['no_hp'] ?? '');
    $pass = $_POST['password_baru'] ?? '';

    if ($nama === '') {
        $pesan = 'Nama lengkap tidak boleh kosong.';
        $tipe = 'error';
    } elseif ($pass !== '' && strlen($pass) < 6) {
        $pesan = 'Kata sandi baru minimal 6 karakter.';
        $tipe = 'error';
    } elseif (!preg_match('/^08[0-9]{8,11}$/', $noHp)) {
        $pesan = 'Nomor HP harus diawali 08 dan minimal 10 digit (contoh: 081234567890).';
        $tipe = 'error';
    } else {
        $fotoBaru = upload_foto_profil('foto_profil');
        if ($fotoBaru === false) {
            $pesan = 'Foto gagal diupload. Gunakan JPG, PNG, atau WEBP dengan ukuran maksimal 3MB.';
            $tipe = 'error';
        } else {
            if ($pass !== '') {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                if ($fotoBaru !== null) {
                    $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, no_hp = ?, password = ?, avatar = ? WHERE id = ?");
                    $stmt->execute([$nama, $noHp, $hash, $fotoBaru, $user['id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, no_hp = ?, password = ? WHERE id = ?");
                    $stmt->execute([$nama, $noHp, $hash, $user['id']]);
                }
            } else {
                if ($fotoBaru !== null) {
                    $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, no_hp = ?, avatar = ? WHERE id = ?");
                    $stmt->execute([$nama, $noHp, $fotoBaru, $user['id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, no_hp = ? WHERE id = ?");
                    $stmt->execute([$nama, $noHp, $user['id']]);
                }
            }

            if ($fotoBaru !== null && !empty($userDb['avatar']) && $userDb['avatar'] !== $fotoBaru) {
                hapus_foto_lama($userDb['avatar']);
            }

            $_SESSION['user']['nama'] = $nama;
            $_SESSION['user']['no_hp'] = $noHp;
            if ($fotoBaru !== null) {
                $_SESSION['user']['avatar'] = $fotoBaru;
            }

            $pesan = $pass !== '' ? 'Profil dan kata sandi berhasil diperbarui.' : 'Profil berhasil diperbarui.';
            $tipe = 'success';

            $stmtUser->execute([$user['id']]);
            $userDb = $stmtUser->fetch();
        }
    }
}

/*
|--------------------------------------------------------------------------
| NOTIFIKASI - TANDAI TERBACA
|--------------------------------------------------------------------------
*/
if (isset($_GET['baca_notif'])) {
    $pdo->prepare("UPDATE notifikasi SET is_read = 1 WHERE user_id = ?")->execute([$user['id']]);
    header('Location: portal-donatur');
    exit;
}

/*
|--------------------------------------------------------------------------
| RIWAYAT DONASI
|--------------------------------------------------------------------------
*/
$stmtDonasi = $pdo->prepare("
    SELECT
        d.*,
        p.nama_program
    FROM donasi_online d
    JOIN program_donasi p
        ON d.program_id = p.id
    WHERE
        d.user_id = ?
        OR d.email = ?
    ORDER BY
        d.tanggal_donasi DESC,
        d.id DESC
");
$stmtDonasi->execute([$user['id'], $user['email'] ?? '']);
$riwayatDonasi = $stmtDonasi->fetchAll();

/*
|--------------------------------------------------------------------------
| STATISTIK DONASI
|--------------------------------------------------------------------------
*/
$totalDonasiSaya = 0;
$totalDonasiVerif = 0;
$jumlahTransaksi = count($riwayatDonasi);
$sebaranProgram = [];
$countVerif = 0;
$countPending = 0;
$countTolak = 0;

foreach ($riwayatDonasi as $r) {
    if ($r['status'] === 'diverifikasi') {
        $totalDonasiVerif += (float)$r['nominal'];
        $countVerif++;
        $pNama = $r['nama_program'] ?: 'Infaq Umum';
        $sebaranProgram[$pNama] = ($sebaranProgram[$pNama] ?? 0) + (float)$r['nominal'];
    } elseif ($r['status'] === 'ditolak') {
        $countTolak++;
    } else {
        $countPending++;
    }
    $totalDonasiSaya += (float)$r['nominal'];
}

// Gamifikasi & Lencana Donatur (Badge Kehormatan Islami)
if ($totalDonasiVerif >= 2000000 || $countVerif >= 10) {
    $tier = [
        'nama'  => 'Sahabat Utama Masjid',
        'level' => 4,
        'badge' => 'bg-gradient-to-r from-amber-400 via-antique-500 to-amber-600 text-cypress-950 border border-amber-300 font-extrabold shadow-md',
        'icon'  => 'fa-crown',
        'pesan' => 'Pilar utama kebaikan dalam memakmurkan rumah Allah.'
    ];
} elseif ($totalDonasiVerif >= 500000 || $countVerif >= 5) {
    $tier = [
        'nama'  => 'Pejuang Infaq Istiqomah',
        'level' => 3,
        'badge' => 'bg-antique-500/20 text-antique-300 border border-antique-400/50 font-bold',
        'icon'  => 'fa-medal',
        'pesan' => 'Istiqomah mengalirkan pahala jariyah tiada putus.'
    ];
} elseif ($countVerif >= 2 || $totalDonasiVerif >= 100000) {
    $tier = [
        'nama'  => 'Penyemai Kebaikan',
        'level' => 2,
        'badge' => 'bg-antique-500/15 text-antique-300 border border-antique-400/40 font-bold',
        'icon'  => 'fa-hand-holding-heart',
        'pesan' => 'Benih amal shalih yang terus bertumbuh membawa berkah.'
    ];
} else {
    $tier = [
        'nama'  => 'Muhsinin',
        'level' => 1,
        'badge' => 'bg-white/10 text-stone-200 border border-white/20 font-bold',
        'icon'  => 'fa-certificate',
        'pesan' => 'Langkah awal kebaikan yang diridhai Allah SWT.'
    ];
}

// Hadits Harian Bergilir
$haditsKoleksi = [
    [
        'arab'    => 'إِذَا مَاتَ ابْنُ آدَمَ انْقَطَعَ عَمَلُهُ إِلَّا مِنْ ثَلَاثٍ: صَدَقَةٍ جَارِيَةٍ، أَوْ عِلْمٍ يُنْتَفَعُ بِهِ، أَوْ وَلَدٍ صَالِحٍ يَدْعُو لَهُ',
        'arti'    => 'Jika seseorang meninggal dunia, maka terputuslah amalnya kecuali tiga perkara: sedekah jariyah, ilmu yang bermanfaat, atau doa anak yang shalih.',
        'riwayat' => 'HR. Muslim no. 1631'
    ],
    [
        'arab'    => 'مَا نَقَصَتْ صَدَقَةٌ مِنْ مَالٍ',
        'arti'    => 'Harta tidak akan pernah berkurang karena sedekah.',
        'riwayat' => 'HR. Muslim no. 2588'
    ],
    [
        'arab'    => 'كُلُّ امْرِئٍ فِي ظِلِّ صَدَقَتِهِ حَتَّى يُفْصَلَ بَيْنَ النَّاسِ',
        'arti'    => 'Setiap orang akan berada di bawah naungan sedekahnya hingga diputuskan perkara di antara manusia pada hari kiamat.',
        'riwayat' => 'HR. Ahmad no. 17333'
    ],
    [
        'arab'    => 'دَاوُوا مَرْضَاكُمْ بِالصَّدَقَةِ',
        'arti'    => 'Obatilah orang-orang yang sakit di antara kalian dengan memperbanyak sedekah.',
        'riwayat' => 'HR. Al-Baihaqi & Ath-Thabrani'
    ]
];
$haditsHariIni = $haditsKoleksi[(int)date('z') % count($haditsKoleksi)];

/*
|--------------------------------------------------------------------------
| NOTIFIKASI
|--------------------------------------------------------------------------
*/
$stmtNotif = $pdo->prepare("
    SELECT *
    FROM notifikasi
    WHERE user_id = ?
    ORDER BY id DESC
    LIMIT 5
");
$stmtNotif->execute([$user['id']]);
$daftarNotifikasi = $stmtNotif->fetchAll();

/*
|--------------------------------------------------------------------------
| DATA PROFIL
|--------------------------------------------------------------------------
*/
$namaDonatur = $userDb['nama_lengkap'] ?: ($user['nama'] ?? 'Donatur');
$emailDonatur = $userDb['email'] ?? ($user['email'] ?? '');
$noHpDonatur = $userDb['no_hp'] ?? ($user['no_hp'] ?? '');
$statusAkun = $userDb['status'] ?? 'aktif';
$tanggalDaftar = $userDb['created_at'] ?? date('Y-m-d');
$avatarPath = trim((string)($userDb['avatar'] ?? ''));
$avatarUrl = $avatarPath !== '' ? upload_url($avatarPath) : '';

$namaBersih = preg_replace('/[^A-Za-z0-9]/', '', $namaDonatur);
$initials = strtoupper(substr($namaBersih, 0, 2));
if ($initials === '') $initials = 'D';

$statusAktif = $statusAkun === 'aktif';
$statusLabel = $statusAktif ? 'Aktif' : ucfirst($statusAkun);
$statusDot = $statusAktif ? 'bg-emerald-500' : 'bg-rose-500';
$statusText = $statusAktif ? 'text-emerald-700' : 'text-rose-700';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cypress: {
                            50: '#f2f7f4',
                            100: '#e1ede6',
                            200: '#c3dbcd',
                            600: '#234b38',
                            700: '#183728',
                            800: '#11271d',
                            900: '#0d1d15',
                            950: '#07130d'
                        },
                        antique: {
                            50: '#fdfbf7',
                            100: '#f8f4ec',
                            200: '#ebe1cc',
                            300: '#dfc896',
                            500: '#c5a059',
                            600: '#b08a42',
                            700: '#8c6b2d',
                            800: '#6d521f'
                        },
                        warm: {
                            50: '#fbf9f5',
                            100: '#f5f1e8',
                            200: '#ebe4d3',
                            800: '#242b26',
                            900: '#191f1b'
                        }
                    },
                    boxShadow: {
                        soft: '0 10px 30px rgba(13,29,21,.07)',
                        luxury: '0 16px 40px rgba(13,29,21,.13)'
                    }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="../assets/css/classic-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background-color: #fbf9f5;
            background-image: radial-gradient(circle at 50% 0%, rgba(197, 160, 89, .12), transparent 38%), url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' viewBox='0 0 120 120'%3E%3Cg fill='none' stroke='%23183728' stroke-opacity='.075' stroke-width='1'%3E%3Cpath d='M60 4L116 60 60 116 4 60Z'/%3E%3Cpath d='M60 22L98 60 60 98 22 60Z'/%3E%3Cpath d='M60 40L80 60 60 80 40 60Z'/%3E%3Ccircle cx='60' cy='60' r='16'/%3E%3Cpath d='M60 4V116M4 60H116M20 20L100 100M100 20L20 100'/%3E%3C/g%3E%3Cg fill='none' stroke='%23c5a059' stroke-opacity='.055' stroke-width='1'%3E%3Cpath d='M0 0L120 120M120 0L0 120'/%3E%3C/g%3E%3C/svg%3E");
            background-size: auto, 120px 120px;
            background-attachment: fixed;
        }
        .sidebar-pattern {
            background-color: #07130d;
            background-image: radial-gradient(circle at 20% 10%, rgba(197, 160, 89, .08), transparent 30%), url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='none' stroke='%23c5a059' stroke-opacity='.045'%3E%3Cpath d='M40 0L80 40 40 80 0 40Z'/%3E%3Cpath d='M40 15L65 40 40 65 15 40Z'/%3E%3Ccircle cx='40' cy='40' r='10'/%3E%3C/g%3E%3C/svg%3E");
            background-size: auto, 80px 80px;
        }
        .islamic-card {
            background-color: #0d1d15;
            background-image: linear-gradient(135deg, rgba(197, 160, 89, .08), transparent 35%), url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill='none' stroke='%23c5a059' stroke-opacity='.075' stroke-width='1'%3E%3Cpath d='M50 3L97 50 50 97 3 50Z'/%3E%3Cpath d='M50 18L82 50 50 82 18 50Z'/%3E%3Cpath d='M50 32L68 50 50 68 32 50Z'/%3E%3Ccircle cx='50' cy='50' r='8'/%3E%3Cpath d='M50 3V97M3 50H97M15 15L85 85M85 15L15 85'/%3E%3C/g%3E%3C/svg%3E");
            background-size: auto, 100px 100px;
        }
        .profile-hero {
            background-color: #0d1d15;
            background-image: radial-gradient(circle at 80% 20%, rgba(197, 160, 89, .15), transparent 35%), url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140' viewBox='0 0 140 140'%3E%3Cg fill='none' stroke='%23c5a059' stroke-opacity='.08' stroke-width='1'%3E%3Cpath d='M70 3L137 70 70 137 3 70Z'/%3E%3Cpath d='M70 25L115 70 70 115 25 70Z'/%3E%3Cpath d='M70 45L95 70 70 95 45 70Z'/%3E%3Ccircle cx='70' cy='70' r='12'/%3E%3Cpath d='M70 3V137M3 70H137M21 21L119 119M119 21L21 119'/%3E%3C/g%3E%3C/svg%3E");
            background-size: auto, 140px 140px;
        }
        .profile-photo {
            box-shadow: 0 0 0 4px rgba(197, 160, 89, .18), 0 12px 30px rgba(0, 0, 0, .25);
        }
        .photo-upload-button {
            position: absolute;
            right: -4px;
            bottom: -4px;
            width: 32px;
            height: 32px;
            border-radius: 9999px;
            background: #c5a059;
            color: #11271d;
            border: 3px solid #0d1d15;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .2s ease;
        }
        .photo-upload-button:hover {
            transform: scale(1.08);
            filter: brightness(1.08);
        }
        #foto_profil { display: none; }
        .modal-backdrop {
            background: rgba(7, 19, 13, .72);
            backdrop-filter: blur(5px);
        }
        .mobile-sidebar-open .sidebar-mobile { transform: translateX(0); }
        .mobile-sidebar-open .sidebar-overlay { display: block; }

        /* Donor dashboard refinement: calm, premium, and readable */
        :root { color-scheme: light; }
        body { background-color: #f8f7f1; }
        .sidebar-mobile {
            box-shadow: 12px 0 36px rgba(7, 19, 13, .12);
        }
        .sidebar-mobile nav a { min-height: 43px; }
        .sidebar-mobile nav a:focus-visible,
        button:focus-visible, a:focus-visible, input:focus-visible {
            outline: 3px solid rgba(197, 160, 89, .55);
            outline-offset: 2px;
        }
        .profile-hero {
            border-radius: 26px;
            box-shadow: 0 18px 48px rgba(13, 29, 21, .14);
        }
        .dashboard-stat-card {
            min-height: 112px;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(13, 29, 21, .045);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .dashboard-stat-card:hover {
            transform: translateY(-2px);
            border-color: rgba(197, 160, 89, .55);
            box-shadow: 0 14px 30px rgba(13, 29, 21, .08);
        }
        main > section, main > div > div, main > div > section {
            scroll-margin-top: 92px;
        }
        table thead th { white-space: nowrap; }
        table tbody tr { transition: background-color .18s ease; }
        .notification-panel { box-shadow: 0 20px 50px rgba(7, 19, 13, .18); }
        @media (max-width: 640px) {
            .profile-hero { padding: 22px 18px; border-radius: 22px; }
            .profile-hero .flex.items-center.gap-5 { align-items: flex-start; gap: 14px; }
            .profile-hero h1 { font-size: 1.35rem; line-height: 1.35; }
            .dashboard-stat-card { padding: 18px; min-height: 94px; }
            .dashboard-stat-card .w-14 { width: 48px; height: 48px; border-radius: 15px; }
            main { padding-top: 20px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; animation-duration: .01ms !important; }
        }
    </style>
</head>
<body class="min-h-screen text-warm-900 antialiased">

<div id="appShell" class="min-h-screen flex flex-col">

    <!-- SIDEBAR OVERLAY -->
    <div class="sidebar-overlay hidden fixed inset-0 z-40 bg-black/50 lg:hidden" onclick="closeSidebar()"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar-mobile fixed inset-y-0 left-0 z-50 w-[260px] sidebar-pattern text-white border-r border-antique-500/15 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-300">
        <!-- BRAND: wordmark only, without mosque logo or Portal Donatur label -->
        <div class="h-[72px] px-6 border-b border-white/10 flex items-center">
            <a href="../" class="text-[15px] font-bold tracking-wide text-white leading-snug">
                <?= e($profil['nama_masjid']) ?>
            </a>
        </div>

        <!-- USER MINI PROFILE -->
        <div class="px-3 pt-4">
            <div class="rounded-2xl bg-[#1b2822] border border-antique-500/20 p-3 flex items-center gap-3">
                <?php if ($avatarUrl): ?>
                    <img src="<?= e($avatarUrl) ?>" alt="Foto" class="w-10 h-10 rounded-xl object-cover border border-antique-500/40">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-xl bg-cypress-800 border border-antique-500/40 flex items-center justify-center text-antique-300 font-bold text-xs">
                        <?= e($initials) ?>
                    </div>
                <?php endif; ?>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-white truncate"><?= e($namaDonatur) ?></div>

                </div>
            </div>
        </div>

        <!-- MENU -->
        <nav class="px-3 pt-5 space-y-1.5 flex-1 overflow-y-auto">
            <div class="px-3 mb-2 text-[10px] uppercase tracking-widest text-antique-500/70 font-bold">Navigasi Utama</div>

            <a href="portal-donatur" class="flex items-center gap-3 px-3.5 py-3 rounded-xl bg-antique-500 text-cypress-950 font-bold text-xs shadow-md">
                <i class="fa-solid fa-chart-pie w-4 text-center"></i>
                <span>Dashboard & Profil</span>
            </a>

            <a href="../home/donasi-online" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-emerald-100/80 hover:text-white hover:bg-white/5 text-xs font-semibold transition">
                <i class="fa-solid fa-hand-holding-heart w-4 text-center text-antique-400"></i>
                <span>Salurkan Donasi / Infaq</span>
            </a>

            <a href="../index.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-emerald-100/80 hover:text-white hover:bg-white/5 text-xs font-semibold transition">
                <i class="fa-solid fa-globe w-4 text-center text-antique-400"></i>
                <span>Beranda Website</span>
            </a>

            <a href="../home/transparansi.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-emerald-100/80 hover:text-white hover:bg-white/5 text-xs font-semibold transition">
                <i class="fa-solid fa-file-invoice-dollar w-4 text-center text-antique-400"></i>
                <span>Transparansi Kas</span>
            </a>

            <div class="pt-4 px-3 mb-2 text-[10px] uppercase tracking-widest text-antique-500/70 font-bold">Pengaturan</div>

            <a href="#informasi-akun" onclick="openEditModal(false)" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-emerald-100/80 hover:text-white hover:bg-white/5 text-xs font-semibold transition">
                <i class="fa-solid fa-user-gear w-4 text-center text-antique-400"></i>
                <span>Ubah Profil / Sandi</span>
            </a>
        </nav>

        <!-- QUOTE CARD -->
        <div class="p-3">
            <div class="rounded-2xl border border-antique-500/20 bg-[#10251b] p-3.5">
                <div class="flex items-center gap-2 text-antique-300 mb-1.5">
                    <i class="fa-solid fa-quote-left text-xs"></i>
                    <span class="text-xs font-bold">Sedekah Terbaik</span>
                </div>
                <p class="text-[10px] leading-relaxed text-emerald-100/80">
                    "Naungi dirimu dengan sedekah, karena ia penolak bala dan pembersih harta."
                </p>
            </div>
        </div>

        <!-- LOGOUT -->
        <div class="px-3 pb-4">
            <a href="../auth/logout" onclick="return confirm('Apakah Anda yakin ingin keluar dari portal?')" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-red-500/20 bg-red-950/30 text-red-200 hover:bg-red-900/50 hover:text-white transition duration-200 text-xs font-bold shadow-sm">
                <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i>
                <span>Keluar Akun</span>
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="lg:pl-[260px] min-h-screen flex flex-col flex-1">

        <!-- TOPBAR -->
        <header class="h-[72px] bg-[#0d1d15] text-white border-b border-antique-500/20 sticky top-0 z-30 px-4 sm:px-6 lg:px-8 flex items-center justify-between shadow-md">
            <div class="flex items-center gap-3.5">
                <button type="button" onclick="openSidebar()" class="lg:hidden w-10 h-10 rounded-xl bg-white/5 border border-white/10 text-antique-300 flex items-center justify-center hover:bg-white/10 transition">
                    <i class="fa-solid fa-bars text-sm"></i>
                </button>
            </div>

            <!-- RIGHT ACTIONS -->
            <div class="flex items-center gap-3">
                <!-- NOTIFICATION DROPDOWN -->
                <div class="relative">
                    <?php
                    $unreadCount = 0;
                    foreach ($daftarNotifikasi as $notif) {
                        if ((int)$notif['is_read'] === 0) {
                            $unreadCount++;
                        }
                    }
                    ?>
                    <button type="button" onclick="toggleNotification()" class="relative w-10 h-10 rounded-xl bg-antique-500 text-cypress-950 font-bold flex items-center justify-center hover:brightness-110 transition shadow-sm">
                        <i class="fa-regular fa-bell text-sm"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] rounded-full bg-red-500 text-white text-[9px] font-bold flex items-center justify-center border-2 border-[#0d1d15]">
                                <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <!-- NOTIFICATION PANEL -->
                    <div id="notificationPanel" class="hidden absolute right-0 top-12 w-[340px] max-w-[calc(100vw-32px)] bg-white text-warm-900 rounded-2xl shadow-luxury border border-antique-200 overflow-hidden z-50 animate-in fade-in duration-200">
                        <div class="px-4 py-3.5 bg-cypress-900 text-white border-b border-antique-500/20 flex items-center justify-between">
                            <span class="text-xs font-bold text-antique-400 flex items-center gap-2">
                                <i class="fa-solid fa-bell"></i> Notifikasi Terbaru
                            </span>
                            <a href="portal-donatur?baca_notif=1" class="text-[10px] text-antique-300 hover:text-white font-semibold underline">Tandai Dibaca</a>
                        </div>
                        <div class="max-h-80 overflow-y-auto divide-y divide-antique-100">
                            <?php if (empty($daftarNotifikasi)): ?>
                                <div class="px-4 py-8 text-center text-xs text-warm-800/50">Belum ada notifikasi baru.</div>
                            <?php else: ?>
                                <?php foreach ($daftarNotifikasi as $n): ?>
                                    <div class="p-3.5 hover:bg-warm-50 transition flex gap-3">
                                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 <?= (int)$n['is_read'] === 0 ? 'bg-antique-500' : 'bg-stone-300' ?>"></span>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <strong class="text-xs font-bold text-warm-900"><?= e($n['judul']) ?></strong>
                                                <span class="text-[9px] text-warm-800/40"><?= date('d/m H:i', strtotime($n['created_at'])) ?></span>
                                            </div>
                                            <p class="text-[11px] text-warm-800/70 mt-0.5 leading-relaxed"><?= e($n['pesan']) ?></p>
                                            <?php if (!empty($n['link'])): ?>
                                                <a href="<?= e($n['link']) ?>" class="inline-flex items-center gap-1 text-[10px] text-cypress-700 hover:text-cypress-900 font-bold mt-1.5 hover:underline">
                                                    <span>Lihat Selengkapnya</span>
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- TOPBAR AVATAR -->
                <div class="flex items-center gap-3 pl-2 border-l border-white/10">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= e($avatarUrl) ?>" alt="Foto" class="w-10 h-10 rounded-xl object-cover border border-antique-500/50">
                    <?php else: ?>
                        <div class="w-10 h-10 rounded-xl bg-cypress-800 border border-antique-500/50 flex items-center justify-center text-antique-300 text-xs font-bold">
                            <?= e($initials) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- MAIN CONTENT -->
        <main class="max-w-[1440px] w-full mx-auto px-4 sm:px-7 xl:px-10 py-7 lg:py-9 flex-1 space-y-6 lg:space-y-7">

            <!-- ALERT MESSAGE -->
            <?php if ($pesan): ?>
                <div class="rounded-2xl px-5 py-4 text-xs font-semibold flex items-center gap-3 shadow-sm <?= $tipe === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
                    <i class="fa-solid <?= $tipe === 'success' ? 'fa-circle-check text-emerald-600 text-base' : 'fa-circle-exclamation text-rose-600 text-base' ?>"></i>
                    <span><?= e($pesan) ?></span>
                </div>
            <?php endif; ?>

            <!-- HERO PROFILE -->
            <section class="profile-hero rounded-[28px] border border-antique-500/30 shadow-luxury overflow-hidden px-6 sm:px-10 py-8 relative">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 relative z-10">
                    <div class="flex items-center gap-5 sm:gap-7">
                        <div class="relative shrink-0">
                            <?php if ($avatarUrl): ?>
                                <img id="heroAvatar" src="<?= e($avatarUrl) ?>" alt="Foto" class="profile-photo w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-antique-500/70">
                            <?php else: ?>
                                <div id="heroAvatar" class="profile-photo w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-cypress-800 border-2 border-antique-500/70 flex items-center justify-center text-antique-300 text-2xl font-bold">
                                    <?= e($initials) ?>
                                </div>
                            <?php endif; ?>
                            <button type="button" onclick="openEditModal(true)" class="photo-upload-button shadow-md" title="Ubah Foto Profil">
                                <i class="fa-solid fa-camera text-xs"></i>
                            </button>
                        </div>
                        <div>
                            <div class="text-antique-400 text-xs sm:text-sm font-semibold uppercase tracking-wider mb-1">
                                Assalamu'alaikum, Wr. Wb.
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-wide truncate">
                                    <?= e($namaDonatur) ?>
                                </h1>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs <?= $tier['badge'] ?>" title="<?= e($tier['pesan']) ?>">
                                    <i class="fa-solid <?= $tier['icon'] ?>"></i>
                                    <span><?= e($tier['nama']) ?></span>
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 mt-3 text-xs text-emerald-100/90 font-medium">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 border border-white/10">
                                    <i class="fa-solid fa-envelope text-antique-400"></i> <?= e($emailDonatur ?: 'Email belum diatur') ?>
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 border border-white/10">
                                    <i class="fa-brands fa-whatsapp text-antique-400"></i> <?= e($noHpDonatur ?: 'WhatsApp belum diatur') ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 flex sm:flex-col gap-2 w-full sm:w-auto">
                        <a href="<?= base_url() ?>/donasi-online" class="flex-1 sm:flex-none px-5 py-3 rounded-xl bg-antique-500 hover:bg-antique-600 text-cypress-950 text-xs font-bold shadow-lg text-center transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-hand-holding-heart"></i>
                            <span>Donasi / Infaq Baru</span>
                        </a>
                        <button type="button" onclick="openEditModal(false)" class="flex-1 sm:flex-none px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold border border-white/20 text-center transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-user-pen"></i>
                            <span>Edit Profil</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- STATS CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-white/95 rounded-[22px] p-6 border border-antique-200 shadow-soft flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-cypress-100 text-cypress-800 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div>
                        <div class="text-xs text-warm-800/60 font-semibold uppercase tracking-wide">Total Donasi Disetujui</div>
                        <div class="text-lg sm:text-xl font-black text-cypress-900 mt-0.5"><?= format_rupiah($totalDonasiVerif) ?></div>
                    </div>
                </div>

                <div class="bg-white/95 rounded-[22px] p-6 border border-antique-200 shadow-soft flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <div class="text-xs text-warm-800/60 font-semibold uppercase tracking-wide">Total Pengajuan Infaq</div>
                        <div class="text-lg sm:text-xl font-black text-warm-900 mt-0.5"><?= $jumlahTransaksi ?> <span class="text-xs font-normal text-warm-800/60">transaksi</span></div>
                    </div>
                </div>

                <div class="dashboard-stat-card bg-white/95 rounded-[22px] p-6 border border-antique-200 shadow-soft flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/>
                            <path d="m8.5 12 2.2 2.2 4.8-4.8"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs text-warm-800/60 font-semibold uppercase tracking-wide">Status Akun</div>
                        <div class="text-sm font-bold <?= e($statusText) ?> mt-1 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full <?= e($statusDot) ?> shrink-0"></span>
                            <span><?= e($statusLabel) ?> (Sejak <?= date('Y', strtotime($tanggalDaftar)) ?>)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FITUR EKSKLUSIF DONATUR: JEJAK AMAL JARIYAH & MUTIARA HADITS -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                
                <!-- KARTU 1: JEJAK SEBARAN AMAL JARIYAH (IMPACT TRACKER) -->
                <div class="lg:col-span-7 bg-white/95 rounded-[24px] p-6 border border-antique-200 shadow-soft space-y-4">
                    <div class="flex items-center justify-between border-b border-antique-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-antique-100 text-antique-700 flex items-center justify-center text-sm font-bold">
                                <i class="fa-solid fa-chart-pie"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-warm-900">Jejak Sebaran Amal Jariyah Anda</h3>
                                <p class="text-[11px] text-warm-800/60">Alokasi infaq yang telah terverifikasi dan dibukukan resmi.</p>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($sebaranProgram)): ?>
                        <div class="py-6 text-center text-xs text-warm-800/50">
                            <i class="fa-solid fa-seedling text-3xl text-antique-400/50 mb-2 block"></i>
                            Belum ada infaq terverifikasi. Infaq pertama Anda akan menumbuhkan jejak kebaikan di sini.
                        </div>
                    <?php else: ?>
                        <div class="space-y-3 pt-1">
                            <?php foreach ($sebaranProgram as $progNama => $nominalProg): 
                                $persenProg = $totalDonasiVerif > 0 ? round(($nominalProg / $totalDonasiVerif) * 100) : 0;
                            ?>
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-bold text-warm-900 truncate max-w-[220px] sm:max-w-xs"><?= e($progNama) ?></span>
                                        <span class="font-semibold text-cypress-800"><?= format_rupiah($nominalProg) ?> <span class="text-[10px] text-warm-800/50 font-normal">(<?= $persenProg ?>%)</span></span>
                                    </div>
                                    <div class="w-full h-2.5 bg-warm-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-antique-500 to-cypress-700 rounded-full transition-all duration-500" style="width: <?= $persenProg ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- KARTU 2: MUTIARA HADITS AMAL HARI INI -->
                <div class="lg:col-span-5 bg-gradient-to-br from-cypress-950 via-cypress-900 to-cypress-950 text-white rounded-[24px] p-6 border border-antique-500/30 shadow-soft flex flex-col justify-between pattern-arabesque-dark relative overflow-hidden">
                    <div class="space-y-3 relative z-10">
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-antique-300 uppercase tracking-wider">
                                <i class="fa-solid fa-quran"></i>
                                <span>Mutiara Hadits Hari Ini</span>
                            </span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-white/10 text-stone-300">
                                <?= date('d M Y') ?>
                            </span>
                        </div>
                        <p class="font-classic text-sm leading-relaxed text-antique-100 text-right font-medium" dir="rtl">
                            "<?= $haditsHariIni['arab'] ?>"
                        </p>
                        <p class="text-xs text-stone-200 leading-relaxed italic">
                            "<?= $haditsHariIni['arti'] ?>"
                        </p>
                    </div>

                    <div class="pt-3 border-t border-white/10 flex items-center justify-between text-[11px] text-antique-300 font-semibold relative z-10 mt-3">
                        <span><?= $haditsHariIni['riwayat'] ?></span>
                        <span class="text-stone-400 font-normal">Semoga Berkah &amp; Istiqomah</span>
                    </div>
                </div>

            </div>

            <!-- RIWAYAT DONASI SECTION -->
            <section class="bg-white/95 rounded-[24px] border border-antique-200 shadow-soft overflow-hidden">
                <div class="px-6 py-5 border-b border-antique-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-cypress-900 flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-antique-600"></i> Riwayat Infaq &amp; Donasi Anda
                        </h2>
                        <p class="text-xs text-warm-800/60 mt-0.5">Daftar lengkap transaksi dan status verifikasi e-Kwitansi resmi masjid.</p>
                    </div>
                    
                    <!-- FILTER TABS & INSTANT SEARCH -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                        <div class="inline-flex p-1 rounded-xl bg-warm-100 text-xs font-bold text-warm-800 shrink-0">
                            <button type="button" onclick="setFilterStatus('all')" id="btnTab-all" class="tab-filter-btn px-3 py-1.5 rounded-lg bg-white shadow-xs text-cypress-900 transition">
                                Semua (<?= $jumlahTransaksi ?>)
                            </button>
                            <button type="button" onclick="setFilterStatus('diverifikasi')" id="btnTab-diverifikasi" class="tab-filter-btn px-3 py-1.5 rounded-lg text-warm-800/70 hover:text-warm-900 transition">
                                Sah (<?= $countVerif ?>)
                            </button>
                            <button type="button" onclick="setFilterStatus('pending')" id="btnTab-pending" class="tab-filter-btn px-3 py-1.5 rounded-lg text-warm-800/70 hover:text-warm-900 transition">
                                Menunggu (<?= $countPending ?>)
                            </button>
                        </div>

                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-xs text-stone-400"></i>
                            <input type="text" id="cariDonasiInput" onkeyup="filterDonasiRealtime()" placeholder="Cari riwayat..." class="w-full sm:w-44 pl-8 pr-3 py-1.5 rounded-xl border border-antique-300 text-xs bg-warm-50/70 focus:bg-white focus:outline-none focus:ring-1 focus:ring-antique-500">
                        </div>
                    </div>
                </div>

                <?php if (empty($riwayatDonasi)): ?>
                    <div class="text-center py-16 px-4">
                        <div class="w-16 h-16 rounded-full bg-antique-50 text-antique-600 flex items-center justify-center mx-auto text-2xl mb-4 border border-antique-200">
                            <i class="fa-solid fa-hand-holding-heart"></i>
                        </div>
                        <h3 class="text-sm font-bold text-warm-900">Belum ada riwayat donasi</h3>
                        <p class="text-xs text-warm-800/60 mt-1 max-w-sm mx-auto">Mari salurkan infaq terbaik Anda untuk kemakmuran masjid dan program sosial.</p>
                        <a href="<?= base_url() ?>/donasi-online" class="inline-flex items-center gap-2 mt-5 px-5 py-3 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white text-xs font-bold shadow-md transition">
                            <i class="fa-solid fa-plus"></i> Salurkan Infaq Sekarang
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-antique-200 text-[10px] uppercase font-bold tracking-wider text-warm-800/60 bg-warm-50/50">
                                    <th class="px-6 py-3.5">No. Transaksi</th>
                                    <th class="px-6 py-3.5">Program Infaq</th>
                                    <th class="px-6 py-3.5">Nominal</th>
                                    <th class="px-6 py-3.5">Metode</th>
                                    <th class="px-6 py-3.5">Status</th>
                                    <th class="px-6 py-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-antique-100">
                                <?php foreach ($riwayatDonasi as $r): ?>
                                    <tr class="riwayat-row hover:bg-warm-50/70 transition" data-status="<?= e($r['status']) ?>" data-search="<?= strtolower(e($r['no_donasi'] . ' ' . $r['nama_program'] . ' ' . $r['metode_pembayaran'])) ?>">
                                        <td class="px-6 py-4">
                                            <span class="font-mono font-bold text-cypress-900 block"><?= e($r['no_donasi']) ?></span>
                                            <span class="text-[10px] text-warm-800/50 mt-0.5 block"><?= tanggal_indo($r['tanggal_donasi']) ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-warm-900 block"><?= e($r['nama_program']) ?></span>
                                            <?php if (!empty($r['keterangan'])): ?>
                                                <span class="text-[10px] text-warm-800/60 italic block mt-0.5">"<?= e($r['keterangan']) ?>"</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-black text-cypress-800 text-sm"><?= format_rupiah($r['nominal']) ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 rounded-lg bg-stone-100 text-stone-700 text-[10px] font-semibold">
                                                <?= e($r['metode_pembayaran']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if ($r['status'] === 'diverifikasi'): ?>
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                    <i class="fa-solid fa-circle-check"></i> Diterima & Sah
                                                </span>
                                            <?php elseif ($r['status'] === 'ditolak'): ?>
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                                    <i class="fa-solid fa-circle-xmark"></i> Ditolak
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                    <i class="fa-solid fa-clock"></i> Menunggu Verifikasi
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <?php 
                                            $kwHref = !empty($r['kwitansi_token']) 
                                                ? 'kwitansi?token=' . urlencode($r['kwitansi_token']) 
                                                : 'kwitansi?no=' . urlencode($r['no_donasi']); 
                                            ?>
                                            <a href="<?= $kwHref ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-cypress-50 hover:bg-cypress-100 text-cypress-800 text-xs font-bold transition border border-cypress-200">
                                                <i class="fa-solid fa-file-invoice"></i>
                                                <span>e-Kwitansi</span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div id="noFilterResult" class="hidden text-center py-12 px-4">
                            <i class="fa-solid fa-filter-circle-xmark text-3xl text-stone-300 mb-2 block"></i>
                            <p class="text-xs font-semibold text-stone-600">Tidak ada riwayat donasi yang sesuai filter atau pencarian Anda.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

            <!-- FOOTER -->
            <footer class="pt-6 pb-4 text-center border-t border-antique-200/60 mt-10">
                <div class="text-xs font-semibold text-warm-800/70">
                    &copy; <?= date('Y') ?> <?= e($profil['nama_masjid']) ?> &middot; All Rights Reserved
                </div>
                <div class="text-[10px] text-warm-800/50 mt-1">
                    Portal Donatur Resmi &middot; Amanah, Transparan, dan Profesional
                </div>
            </footer>
        </main>
    </div>
</div>

<!-- MODAL EDIT PROFIL -->
<div id="editModal" class="hidden fixed inset-0 z-[100] items-center justify-center p-4">
    <div class="absolute inset-0 modal-backdrop" onclick="closeEditModal()"></div>
    <div class="relative w-full max-w-lg bg-white rounded-3xl border border-antique-200 shadow-luxury overflow-hidden animate-in fade-in zoom-in duration-200">
        <!-- HEADER -->
        <div class="px-6 py-5 bg-cypress-900 text-white flex items-center justify-between border-b border-antique-500/20">
            <div>
                <h3 class="text-base font-bold text-antique-400">Pengaturan Profil Akun</h3>
                <p class="text-xs text-white/70 mt-0.5">Perbarui informasi data diri atau ubah kata sandi.</p>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-9 h-9 rounded-xl bg-white/10 text-white/80 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- FORM -->
        <form method="POST" action="portal-donatur" enctype="multipart/form-data" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="update_profil">
            <!-- FOTO UPLOAD -->
            <div class="flex flex-col items-center pb-2">
                <div class="relative">
                    <?php if ($avatarUrl): ?>
                        <img id="modalAvatar" src="<?= e($avatarUrl) ?>" class="w-24 h-24 rounded-2xl object-cover border-2 border-antique-500/50 shadow-md">
                    <?php else: ?>
                        <div id="modalAvatar" class="w-24 h-24 rounded-2xl bg-cypress-800 border-2 border-antique-500/50 flex items-center justify-center text-antique-300 text-xl font-bold shadow-md">
                            <?= e($initials) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <label for="foto_profil" class="mt-3 cursor-pointer px-4 py-2 rounded-xl bg-antique-50 border border-antique-300 text-antique-800 text-xs font-bold hover:bg-antique-100 transition shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-camera"></i> Ganti Foto Profil
                </label>
                <input id="foto_profil" type="file" name="foto_profil" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" onchange="previewPhoto(this)">
                <span class="text-[10px] text-warm-800/50 mt-1.5">Format: JPG, PNG, WEBP (Max. 3MB)</span>
            </div>

            <!-- NAMA LENGKAP -->
            <div>
                <label for="nama_lengkap" class="block text-xs font-bold text-warm-900 mb-1.5">Nama Lengkap</label>
                <input id="nama_lengkap" type="text" name="nama_lengkap" value="<?= e($namaDonatur) ?>" required class="w-full px-4 py-3 rounded-xl bg-warm-50 border border-antique-200 text-xs text-warm-900 outline-none focus:border-antique-500 focus:ring-2 focus:ring-antique-500/20 font-medium">
            </div>

            <!-- EMAIL (DISABLED) -->
            <div>
                <label class="block text-xs font-bold text-warm-900 mb-1.5">Email (Terdaftar)</label>
                <input type="email" value="<?= e($emailDonatur) ?>" disabled class="w-full px-4 py-3 rounded-xl bg-stone-100 border border-stone-200 text-xs text-warm-800/50 cursor-not-allowed">
            </div>

            <!-- WHATSAPP -->
            <div>
                <label for="no_hp" class="block text-xs font-bold text-warm-900 mb-1.5">Nomor WhatsApp</label>
                <input id="no_hp" type="text" name="no_hp" value="<?= e($noHpDonatur) ?>" placeholder="08xxxxxxxxxx" class="w-full px-4 py-3 rounded-xl bg-warm-50 border border-antique-200 text-xs text-warm-900 outline-none focus:border-antique-500 focus:ring-2 focus:ring-antique-500/20 font-medium">
            </div>

            <!-- PASSWORD BARU -->
            <div>
                <label for="password_baru" class="block text-xs font-bold text-warm-900 mb-1.5">
                    Kata Sandi Baru <span class="font-normal text-warm-800/50">(Opsional, kosongkan jika tidak ingin mengubah)</span>
                </label>
                <input id="password_baru" type="password" name="password_baru" placeholder="Minimal 6 karakter" class="w-full px-4 py-3 rounded-xl bg-warm-50 border border-antique-200 text-xs text-warm-900 outline-none focus:border-antique-500 focus:ring-2 focus:ring-antique-500/20">
            </div>

            <!-- BUTTONS -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-antique-100">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 rounded-xl border border-antique-200 text-xs font-semibold text-warm-800 hover:bg-warm-50 transition">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-antique-500 hover:bg-antique-600 text-cypress-950 text-xs font-bold shadow-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JAVASCRIPT -->
<script>
const appShell = document.getElementById('appShell');
const editModal = document.getElementById('editModal');
const notificationPanel = document.getElementById('notificationPanel');

function openSidebar() {
    appShell.classList.add('mobile-sidebar-open');
}

function closeSidebar() {
    appShell.classList.remove('mobile-sidebar-open');
}

function openEditModal(focusPhoto = false) {
    editModal.classList.remove('hidden');
    editModal.classList.add('flex');
    document.body.classList.add('overflow-hidden');

    if (focusPhoto) {
        setTimeout(() => {
            document.getElementById('foto_profil')?.click();
        }, 150);
    } else {
        setTimeout(() => {
            document.getElementById('nama_lengkap')?.focus();
        }, 100);
    }
}

function closeEditModal() {
    editModal.classList.add('hidden');
    editModal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function previewPhoto(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (file.size > 3 * 1024 * 1024) {
        alert('Ukuran foto maksimal 3MB.');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const modalAvatar = document.getElementById('modalAvatar');
        const heroAvatar = document.getElementById('heroAvatar');

        if (modalAvatar) {
            if (modalAvatar.tagName.toLowerCase() === 'img') {
                modalAvatar.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.id = 'modalAvatar';
                img.src = e.target.result;
                img.className = 'w-24 h-24 rounded-2xl object-cover border-2 border-antique-500/50 shadow-md';
                modalAvatar.replaceWith(img);
            }
        }
        if (heroAvatar) {
            if (heroAvatar.tagName.toLowerCase() === 'img') {
                heroAvatar.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.id = 'heroAvatar';
                img.src = e.target.result;
                img.className = 'profile-photo w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-antique-500/70';
                heroAvatar.replaceWith(img);
            }
        }
    };
    reader.readAsDataURL(file);
}

function toggleNotification() {
    notificationPanel.classList.toggle('hidden');
}

// Filter Riwayat Donasi Realtime
let currentStatusFilter = 'all';

function setFilterStatus(status) {
    currentStatusFilter = status;
    document.querySelectorAll('.tab-filter-btn').forEach(b => {
        b.classList.remove('bg-white', 'shadow-xs', 'text-cypress-900');
        b.classList.add('text-warm-800/70');
    });
    const activeBtn = document.getElementById('btnTab-' + status);
    if (activeBtn) {
        activeBtn.classList.add('bg-white', 'shadow-xs', 'text-cypress-900');
        activeBtn.classList.remove('text-warm-800/70');
    }
    filterDonasiRealtime();
}

function filterDonasiRealtime() {
    const q = (document.getElementById('cariDonasiInput')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.riwayat-row');
    let visibleCount = 0;

    rows.forEach(r => {
        const rowStatus = r.getAttribute('data-status') || '';
        const rowSearch = r.getAttribute('data-search') || '';
        
        const matchStatus = (currentStatusFilter === 'all') || (rowStatus === currentStatusFilter);
        const matchSearch = (q === '') || rowSearch.includes(q);

        if (matchStatus && matchSearch) {
            r.classList.remove('hidden');
            visibleCount++;
        } else {
            r.classList.add('hidden');
        }
    });

    const noResultBox = document.getElementById('noFilterResult');
    if (noResultBox) {
        if (visibleCount === 0 && rows.length > 0) {
            noResultBox.classList.remove('hidden');
        } else {
            noResultBox.classList.add('hidden');
        }
    }
}

document.addEventListener('click', function(event) {
    const button = event.target.closest('button');
    const panel = event.target.closest('#notificationPanel');
    if (!panel && !button?.onclick) {
        notificationPanel.classList.add('hidden');
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEditModal();
        notificationPanel.classList.add('hidden');
        closeSidebar();
    }
});
</script>

</body>
</html>
