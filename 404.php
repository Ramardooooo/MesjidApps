<?php
/**
 * 404.php — Halaman Error Tidak Ditemukan
 * Masjid Jami' Nurul Iman
 *
 * Ditampilkan ketika halaman yang diminta tidak ditemukan.
 * Fallback dari .htaccess: RewriteRule ^ 404.php [L]
 */

// HTTP status 404
http_response_code(404);

// Cegah caching halaman error
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// --- Bootstrap: config & helpers dengan error handling ---
$configPath  = __DIR__ . '/config/database.php';
$helpersPath = __DIR__ . '/config/helpers.php';

try {
    if (file_exists($configPath)) {
        require_once $configPath;
    }
} catch (Throwable $e) {
    // Silent fail
}

try {
    if (file_exists($helpersPath)) {
        require_once $helpersPath;
    }
} catch (Throwable $e) {
    // Silent fail
}

// --- Session ---
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// --- Deteksi login ---
$isLoggedIn = isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
$userRole   = $_SESSION['user']['role'] ?? null;
$userName   = $_SESSION['user']['nama'] ?? null;

// --- Base URL ---
$base = '';
if (function_exists('base_url')) {
    $base = rtrim(base_url(), '/');
} else {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $base      = rtrim($scriptDir, '/');
    if ($base === '' || $base === '/') $base = '';
}

// --- Tentukan halaman "kembali" sesuai role ---
$targetBeranda = $base . '/login';
$backLabel     = 'Halaman Masuk';

if ($isLoggedIn && $userRole) {
    switch ($userRole) {
        case 'admin':
        case 'bendahara':
        case 'content_admin':
            $targetBeranda = $base . '/dashboard';
            $backLabel     = 'Dashboard Pengurus';
            break;
        case 'donatur':
            $targetBeranda = $base . '/portal-donatur';
            $backLabel     = 'Portal Donatur';
            break;
    }
}

// --- Nama masjid (bisa dari DB jika ada) ---
$namaMasjid = "Masjid Jami' Nurul Iman";
if (function_exists('get_profil_masjid')) {
    try {
        $profil = get_profil_masjid();
        if (!empty($profil['nama_masjid'])) {
            $namaMasjid = $profil['nama_masjid'];
        }
    } catch (Throwable $e) {
        // Biarkan default
    }
}

// --- URL halaman yang diminta (untuk debug ringan) ---
$requestedUri = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>404 · Halaman Tidak Ditemukan · <?= htmlspecialchars($namaMasjid) ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cinzel:wght@500;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cypress: {
                            50:  '#f2f7f4',
                            100: '#e1ede6',
                            200: '#c3dbcd',
                            600: '#234b38',
                            700: '#1b3a2b',
                            800: '#142a1f',
                            900: '#0d1d15',
                        },
                        antique: {
                            50:  '#fdfbf7',
                            100: '#f8f4ec',
                            300: '#dfc896',
                            500: '#c5a059',
                            600: '#b08a42',
                            700: '#8c6b2d',
                        },
                        warm: {
                            50:  '#fbf9f5',
                            100: '#f5f1e8',
                            200: '#ebe4d3',
                            800: '#242b26',
                            900: '#191f1b',
                        }
                    },
                    fontFamily: {
                        sans:    ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui'],
                        classic: ['Cinzel', 'serif'],
                        arabic:  ['Amiri', 'serif'],
                    }
                }
            }
        }
    </script>

    <!-- Custom Classic Theme -->
    <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/classic-theme.css">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .pattern-arabesque-light {
            background-color: #fbf9f5;
            background-image:
                radial-gradient(circle at 15% 20%, rgba(197, 160, 89, 0.06) 0%, transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(24, 55, 40, 0.05) 0%, transparent 45%);
        }
        .transition-luxury {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="pattern-arabesque-light min-h-screen flex flex-col justify-between items-center p-4 sm:p-6 lg:p-8 text-warm-900">

    <!-- Header Sederhana -->
    <header class="w-full max-w-2xl text-center pt-4">
        <a href="<?= htmlspecialchars($base ?: '/') ?>"
           class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-white/80 border border-antique-300/40 shadow-sm hover:border-cypress-700 transition-luxury">
            <div class="w-6 h-6 rounded-md bg-cypress-800 flex items-center justify-center text-antique-300">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 0c-3 3-5 6-5 9a5 5 0 0010 0c0-3-2-6-5-9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 21h16M7 21v-4m10 4v-4M9 13h6"/>
                </svg>
            </div>
            <span class="font-classic text-xs font-bold tracking-wider text-cypress-800 uppercase">
                <?= htmlspecialchars($namaMasjid) ?>
            </span>
        </a>
    </header>

    <!-- Kartu Utama 404 Klasik -->
    <main class="w-full max-w-lg my-8">
        <div class="bg-white rounded-2xl p-8 sm:p-10 border border-antique-300/50 shadow-xl shadow-stone-900/5 text-center relative overflow-hidden">

            <!-- Ornamen Aksen Sudut -->
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-antique-50 rounded-full opacity-60 pointer-events-none"></div>
            <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-cypress-50 rounded-full opacity-60 pointer-events-none"></div>

            <div class="relative z-10">
                <!-- Ikon Kubah & Kompas Klasik -->
                <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-antique-50 border border-antique-300/60 shadow-inner flex items-center justify-center text-antique-600">
                    <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 0c-3 3-5 6-5 9a5 5 0 0010 0c0-3-2-6-5-9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 21h16M7 21v-4m10 4v-4M9 13h6"/>
                        <circle cx="12" cy="12" r="9" stroke-dasharray="2 2" opacity="0.5"/>
                    </svg>
                </div>

                <!-- Angka 404 Tipografi Klasik -->
                <div class="mb-2">
                    <span class="font-classic text-6xl sm:text-7xl font-bold tracking-wider text-cypress-800 block select-none">
                        404
                    </span>
                    <span class="inline-block mt-1 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-semibold tracking-wider uppercase">
                        Halaman Tidak Ditemukan
                    </span>
                </div>

                <!-- Pesan Penjelasan -->
                <div class="my-6 space-y-2">
                    <h1 class="text-xl font-bold text-warm-900">
                        Afwan, halaman yang Anda cari tidak tersedia.
                    </h1>
                    <p class="text-xs sm:text-sm text-warm-800/70 leading-relaxed max-w-sm mx-auto">
                        Alamat URL yang Anda masukkan mungkin salah ketik, sudah dipindahkan,
                        atau telah diperbarui dalam portal sistem masjid.
                    </p>

                    <?php if ($isLoggedIn && $userRole === 'admin'): ?>
                        <p class="text-[10px] text-gray-400 break-all pt-2">
                            Requested:
                            <code class="bg-gray-100 px-2 py-0.5 rounded">
                                <?= htmlspecialchars($requestedUri) ?>
                            </code>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Tombol Navigasi Alternatif -->
                <div class="space-y-3 pt-2">
                    <a href="<?= htmlspecialchars($targetBeranda) ?>"
                       class="w-full py-3 px-5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-semibold text-xs tracking-wide shadow-md shadow-cypress-900/15 flex items-center justify-center gap-2 transition-luxury">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Kembali ke <?= htmlspecialchars($backLabel) ?></span>
                    </a>

                    <div class="flex items-center justify-center gap-2 flex-wrap">
                        <button type="button" onclick="history.back()"
                                class="px-4 py-2.5 rounded-lg border border-warm-200 hover:bg-warm-50 text-warm-800/80 text-xs font-medium transition-luxury flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span>Halaman Sebelumnya</span>
                        </button>

                        <?php if ($isLoggedIn): ?>
                            <a href="<?= htmlspecialchars($linkDonasi) ?>"
                               class="px-4 py-2.5 rounded-lg border border-antique-300/60 hover:bg-antique-50 text-antique-700 text-xs font-medium transition-luxury">
                                Kelola Donasi
                            </a>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars($base . '/') ?>"
                               class="px-4 py-2.5 rounded-lg border border-antique-300/60 hover:bg-antique-50 text-antique-700 text-xs font-medium transition-luxury">
                                Beranda
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full text-center pb-4 text-xs text-warm-800/60">
        <p>
            &copy; <?= date('Y') ?>
            <?= htmlspecialchars($namaMasjid) ?>
            &middot; Menjaga Amanah, Memakmurkan Masjid
        </p>
    </footer>

</body>
</html>