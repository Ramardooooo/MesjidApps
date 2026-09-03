<?php
http_response_code(404);

// Cek session pengguna jika berkas config ada
if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
    mulai_session();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

$isLoggedIn = isset($_SESSION['user']);
$targetBeranda = $isLoggedIn ? 'dashboard.php' : 'login.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 · Halaman Tidak Ditemukan · Masjid Nurul Iman</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom Classic Theme -->
    <link rel="stylesheet" href="assets/css/classic-theme.css">
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
                            700: '#1b3a2b',
                            800: '#142a1f',
                            900: '#0d1d15',
                        },
                        antique: {
                            50: '#fdfbf7',
                            100: '#f8f4ec',
                            300: '#dfc896',
                            500: '#c5a059',
                            600: '#b08a42',
                            700: '#8c6b2d',
                        },
                        warm: {
                            50: '#fbf9f5',
                            100: '#f5f1e8',
                            200: '#ebe4d3',
                            800: '#242b26',
                            900: '#191f1b',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui'],
                        classic: ['Cinzel', 'serif'],
                        arabic: ['Amiri', 'serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="pattern-arabesque-light min-h-screen flex flex-col justify-between items-center p-4 sm:p-6 lg:p-8 text-warm-900">

    <!-- Header Sederhana -->
    <header class="w-full max-w-2xl text-center pt-4">
        <a href="<?= $targetBeranda ?>" class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-white/80 border border-antique-300/40 shadow-xs hover:border-cypress-700 transition-luxury">
            <div class="w-6 h-6 rounded-md bg-cypress-800 flex items-center justify-center text-antique-300">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 0c-3 3-5 6-5 9a5 5 0 0010 0c0-3-2-6-5-9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 21h16M7 21v-4m10 4v-4M9 13h6"/>
                </svg>
            </div>
            <span class="font-classic text-xs font-bold tracking-wider text-cypress-800">MASJID NURUL IMAN</span>
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

                <!-- Pesan Penjelasan yang Santun -->
                <div class="my-6 space-y-2">
                    <h1 class="text-xl font-bold text-warm-900">
                        Afwan, halaman yang Anda cari tidak tersedia.
                    </h1>
                    <p class="text-xs sm:text-sm text-warm-800/70 leading-relaxed max-w-sm mx-auto">
                        Alamat URL yang Anda masukkan mungkin salah ketik, sudah dipindahkan, atau telah diperbarui dalam portal sistem masjid.
                    </p>
                </div>

                <!-- Tombol Navigasi Alternatif -->
                <div class="space-y-3 pt-2">
                    <a href="<?= $targetBeranda ?>" 
                       class="w-full py-3 px-5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-semibold text-xs tracking-wide shadow-md shadow-cypress-900/15 flex items-center justify-center gap-2 transition-luxury">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Kembali ke <?= $isLoggedIn ? 'Dashboard Pengurus' : 'Halaman Masuk' ?></span>
                    </a>

                    <div class="flex items-center justify-center gap-2">
                        <button type="button" onclick="history.back()"
                                class="px-4 py-2.5 rounded-lg border border-warm-200 hover:bg-warm-50 text-warm-800/80 text-xs font-medium transition-luxury flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span>Halaman Sebelumnya</span>
                        </button>

                        <?php if ($isLoggedIn): ?>
                        <a href="donasi.php" class="px-4 py-2.5 rounded-lg border border-antique-300/60 hover:bg-antique-50 text-antique-700 text-xs font-medium transition-luxury">
                            Kelola Donasi
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Footer Santun -->
    <footer class="w-full text-center pb-4 text-xs text-warm-800/60">
        <p>© <?= date('Y') ?> Masjid Nurul Iman · Menjaga Amanah, Memakmurkan Masjid</p>
    </footer>

</body>
</html>
