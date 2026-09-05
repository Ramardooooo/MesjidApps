<?php
// layouts/public_header.php - Header & Navigasi Publik Website Masjid
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pageTitle = $pageTitle ?? ($profil['nama_masjid'] . ' · ' . $profil['sebutan']);
$activeNav = $activeNav ?? 'beranda';
$currentUser = $_SESSION['user'] ?? null;
$base = base_url();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <!-- Favicon & Icons -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23c5a059'><path d='M12 2v2m0 0c-3 3-5 6-5 9a5 5 0 0010 0c0-3-2-6-5-9z M4 21h16M7 21v-4m10 4v-4M9 13h6'/></svg>">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/classic-theme.css">
    
    <!-- Font Awesome / Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cypress: {
                            50: '#f2f7f4',
                            100: '#e1ede6',
                            200: '#c3dbcd',
                            500: '#2d6148',
                            600: '#234b38',
                            700: '#1b3a2b',
                            800: '#142a1f',
                            900: '#0d1d15',
                            950: '#07100b',
                        },
                        antique: {
                            50: '#fdfbf7',
                            100: '#f8f4ec',
                            200: '#eedfbe',
                            300: '#dfc896',
                            400: '#d2b474',
                            500: '#c5a059',
                            600: '#b08a42',
                            700: '#8c6b2d',
                            800: '#694f20',
                            900: '#463414',
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
    <style>
        .gold-gradient-text {
            background: linear-gradient(135deg, #dfc896 0%, #c5a059 50%, #b08a42 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .emerald-glass {
            background: rgba(20, 42, 31, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="pattern-arabesque-light min-h-screen text-warm-900 flex flex-col justify-between antialiased selection:bg-antique-500 selection:text-white">

    <!-- Topbar Syiar & Kontak Cepat -->
    <div class="bg-cypress-950 text-white text-xs border-b border-white/10 py-2 px-4 sm:px-8 hidden md:block">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-6 text-antique-200/90">
                <span class="inline-flex items-center gap-2">
                    <i class="fa-solid fa-mosque text-antique-500"></i>
                    <span><?= e($profil['slogan']) ?></span>
                </span>
                <span class="inline-flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-antique-500"></i>
                   <span><?= e($profil['kota'] ?? '') ?></span>
                </span>
            </div>

            <div class="flex items-center gap-4 text-xs">
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $profil['whatsapp']) ?>" target="_blank" class="hover:text-antique-300 transition flex items-center gap-1.5 text-emerald-300">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>WhatsApp Layanan DKM</span>
                </a>
                <span class="text-white/20">|</span>
                <?php if ($currentUser): ?>
                    <a href="<?= in_array($currentUser['role'], ['admin', 'bendahara', 'content_admin']) ? $base . '/admin/dashboard.php' : $base . '/donatur/portal-donatur.php' ?>" class="text-antique-300 font-semibold hover:text-white transition flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-user"></i>
                        <span><?= e($currentUser['nama']) ?> (<?= ucfirst($currentUser['role']) ?>)</span>
                    </a>
                <?php else: ?>
                    <a href="<?= $base ?>/auth/login.php" class="text-antique-300 hover:text-white transition flex items-center gap-1">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        <span>Masuk Akun / Portal</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navigasi Utama Sticky -->
    <header class="sticky top-0 z-40 bg-cypress-900/95 backdrop-blur-md border-b border-antique-500/30 text-white shadow-lg transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- Logo Brand -->
                <a href="<?= $base ?>/index.php" class="flex items-center gap-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-cypress-950 border border-antique-500/50 flex items-center justify-center text-antique-400 group-hover:scale-105 transition-all shadow-inner shadow-antique-500/10">
                        <i class="fa-solid fa-mosque text-xl text-antique-400"></i>
                    </div>
                    <div>
                        <span class="font-classic text-lg font-bold tracking-wider block text-white leading-tight">
                            <?= strtoupper(e($profil['nama_masjid'])) ?>
                        </span>
                        <span class="text-[11px] text-antique-300/90 font-medium tracking-widest block uppercase">
                            <?= e($profil['sebutan']) ?>
                        </span>
                    </div>
                </a>

                <!-- Desktop Menu Links -->
                <nav class="hidden lg:flex items-center gap-1.5 xl:gap-2 text-xs font-semibold tracking-wide">
                    <a href="<?= $base ?>/index.php" class="px-3.5 py-2 rounded-xl transition <?= $activeNav === 'beranda' ? 'bg-cypress-800 text-antique-300 border border-antique-500/40 shadow-xs' : 'text-stone-200 hover:text-white hover:bg-white/5' ?>">
                        Beranda
                    </a>
                    <a href="<?= $base ?>/home/profil.php" class="px-3.5 py-2 rounded-xl transition <?= $activeNav === 'profil' ? 'bg-cypress-800 text-antique-300 border border-antique-500/40 shadow-xs' : 'text-stone-200 hover:text-white hover:bg-white/5' ?>">
                        Profil &amp; DKM
                    </a>
                    <a href="<?= $base ?>/home/program.php" class="px-3.5 py-2 rounded-xl transition <?= $activeNav === 'program' ? 'bg-cypress-800 text-antique-300 border border-antique-500/40 shadow-xs' : 'text-stone-200 hover:text-white hover:bg-white/5' ?>">
                        Program Donasi
                    </a>
                    <a href="<?= $base ?>/home/transparansi.php" class="px-3.5 py-2 rounded-xl transition <?= $activeNav === 'transparansi' ? 'bg-cypress-800 text-antique-300 border border-antique-500/40 shadow-xs' : 'text-stone-200 hover:text-white hover:bg-white/5' ?>">
                        Transparansi Kas
                    </a>
                    <a href="<?= $base ?>/home/berita.php" class="px-3.5 py-2 rounded-xl transition <?= $activeNav === 'berita' ? 'bg-cypress-800 text-antique-300 border border-antique-500/40 shadow-xs' : 'text-stone-200 hover:text-white hover:bg-white/5' ?>">
                        Berita &amp; Agenda
                    </a>
                    <a href="<?= $base ?>/home/kajian.php" class="px-3.5 py-2 rounded-xl transition <?= $activeNav === 'kajian' ? 'bg-cypress-800 text-antique-300 border border-antique-500/40 shadow-xs' : 'text-stone-200 hover:text-white hover:bg-white/5' ?>">
                        Video Kajian
                    </a>
                    <a href="<?= $base ?>/home/kontak.php" class="px-3.5 py-2 rounded-xl transition <?= $activeNav === 'kontak' ? 'bg-cypress-800 text-antique-300 border border-antique-500/40 shadow-xs' : 'text-stone-200 hover:text-white hover:bg-white/5' ?>">
                                Kontak
                    </a>
                </nav>

                <!-- Action Button -->
                <div class="hidden sm:flex items-center gap-3">
                    <a href="<?= $base ?>/home/donasi-online.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-antique-600 via-antique-500 to-antique-600 text-cypress-950 font-bold text-xs shadow-md shadow-antique-500/20 hover:brightness-110 active:scale-95 transition-all">
                        <i class="fa-solid fa-hand-holding-heart text-sm"></i>
                        <span>Donasi Infaq</span>
                    </a>
                    <?php if ($currentUser): ?>
                        <a href="<?= in_array($currentUser['role'], ['admin', 'bendahara', 'content_admin']) ? $base . '/admin/dashboard.php' : $base . '/donatur/portal-donatur.php' ?>" class="p-2.5 rounded-xl bg-cypress-800 border border-antique-500/40 text-antique-300 hover:text-white hover:bg-cypress-700 transition" title="Buka Portal">
                            <i class="fa-solid fa-gauge-high"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?= $base ?>/auth/login.php" class="p-2.5 rounded-xl bg-cypress-800 border border-antique-500/40 text-antique-300 hover:text-white hover:bg-cypress-700 transition" title="Masuk">
                            <i class="fa-solid fa-user-shield"></i>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex lg:hidden items-center gap-2">
                    <a href="<?= $base ?>/home/donasi-online.php" class="px-3 py-1.5 rounded-lg bg-antique-500 text-cypress-950 font-bold text-xs">
                        Donasi
                    </a>
                    <button type="button" id="mobileMenuBtn" class="p-2 rounded-xl bg-cypress-800 border border-antique-500/40 text-antique-300 hover:text-white focus:outline-none" aria-label="Toggle Menu">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div id="mobileMenu" class="hidden lg:hidden bg-cypress-950/98 border-t border-antique-500/20 px-4 pt-3 pb-6 space-y-2">
            <a href="<?= $base ?>/index.php" class="block px-3.5 py-2.5 rounded-xl <?= $activeNav === 'beranda' ? 'bg-cypress-800 text-antique-300 font-bold' : 'text-stone-300 hover:bg-white/5' ?> text-sm">
                <i class="fa-solid fa-home mr-2 text-antique-500"></i> Beranda
            </a>
            <a href="<?= $base ?>/home/profil.php" class="block px-3.5 py-2.5 rounded-xl <?= $activeNav === 'profil' ? 'bg-cypress-800 text-antique-300 font-bold' : 'text-stone-300 hover:bg-white/5' ?> text-sm">
                <i class="fa-solid fa-mosque mr-2 text-antique-500"></i> Profil &amp; DKM
            </a>
            <a href="<?= $base ?>/home/program.php" class="block px-3.5 py-2.5 rounded-xl <?= $activeNav === 'program' ? 'bg-cypress-800 text-antique-300 font-bold' : 'text-stone-300 hover:bg-white/5' ?> text-sm">
                <i class="fa-solid fa-hand-holding-dollar mr-2 text-antique-500"></i> Program Donasi
            </a>
            <a href="<?= $base ?>/home/transparansi.php" class="block px-3.5 py-2.5 rounded-xl <?= $activeNav === 'transparansi' ? 'bg-cypress-800 text-antique-300 font-bold' : 'text-stone-300 hover:bg-white/5' ?> text-sm">
                <i class="fa-solid fa-chart-line mr-2 text-antique-500"></i> Transparansi Keuangan
            </a>
            <a href="<?= $base ?>/home/berita.php" class="block px-3.5 py-2.5 rounded-xl <?= $activeNav === 'berita' ? 'bg-cypress-800 text-antique-300 font-bold' : 'text-stone-300 hover:bg-white/5' ?> text-sm">
                <i class="fa-solid fa-newspaper mr-2 text-antique-500"></i> Berita &amp; Agenda
            </a>
            <a href="<?= $base ?>/home/kajian.php" class="block px-3.5 py-2.5 rounded-xl <?= $activeNav === 'kajian' ? 'bg-cypress-800 text-antique-300 font-bold' : 'text-stone-300 hover:bg-white/5' ?> text-sm">
                <i class="fa-brands fa-youtube mr-2 text-antique-500"></i> Video Kajian
            </a>
            <a href="<?= $base ?>/home/kontak.php" class="block px-3.5 py-2.5 rounded-xl <?= $activeNav === 'kontak' ? 'bg-cypress-800 text-antique-300 font-bold' : 'text-stone-300 hover:bg-white/5' ?> text-sm">
                <i class="fa-solid fa-envelope mr-2 text-antique-500"></i> Kontak Masjid
            </a>
            <div class="pt-3 border-t border-white/10 flex flex-col gap-2">
                <?php if ($currentUser): ?>
                    <a href="<?= in_array($currentUser['role'], ['admin', 'bendahara', 'content_admin']) ? $base . '/admin/dashboard.php' : $base . '/donatur/portal-donatur.php' ?>" class="w-full text-center py-2.5 rounded-xl bg-cypress-800 text-antique-300 font-semibold text-xs border border-antique-500/30">
                        Buka Portal (<?= e($currentUser['nama']) ?>)
                    </a>
                    <a href="<?= $base ?>/auth/logout.php" class="w-full text-center py-2 rounded-xl text-red-300 hover:text-white text-xs">
                        Keluar
                    </a>
                <?php else: ?>
                    <a href="<?= $base ?>/auth/login.php" class="w-full text-center py-2.5 rounded-xl bg-cypress-800 text-antique-300 font-semibold text-xs border border-antique-500/30">
                        Masuk Pengurus / Donatur
                    </a>
                    <a href="<?= $base ?>/auth/register.php" class="w-full text-center py-2 rounded-xl text-stone-300 hover:text-white text-xs">
                        Daftar Donatur Baru
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <script>
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        });
    </script>
    <main class="flex-1">
