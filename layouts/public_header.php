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

    <!-- Navigasi Utama Sticky -->
    <header class="sticky top-0 z-40 bg-cypress-900 border-b border-antique-500/30 text-white shadow-lg transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo & Brand -->
                <a href="<?= $base ?>/" class="flex items-center gap-2 group shrink-0">
                    <div class="w-10 h-10 rounded-lg bg-cypress-950 border border-antique-500/40 flex items-center justify-center text-antique-400 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-mosque text-base"></i>
                    </div>
                    <div>
                        <span class="font-classic text-sm font-bold tracking-wide block text-white leading-tight">
                            MASJID JAMI'
                        </span>
                        <span class="text-[10px] text-antique-300 font-semibold tracking-widest block">
                            NURUL IMAN
                        </span>
                    </div>
                </a>

                <!-- Desktop Menu -->
                <nav class="hidden lg:flex items-center gap-0.5 text-xs font-medium tracking-wide">
                    <a href="<?= $base ?>/" class="px-4 py-2 rounded-lg transition <?= $activeNav === 'beranda' ? 'text-antique-300 bg-cypress-800/50' : 'text-stone-300 hover:text-white hover:bg-white/5' ?>">
                        Beranda
                    </a>

                    <!-- Dropdown Profil -->
                    <div class="relative group">
                        <button class="px-4 py-2 rounded-lg transition flex items-center gap-1 <?= in_array($activeNav, ['profil', 'tim-kami']) ? 'text-antique-300 bg-cypress-800/50' : 'text-stone-300 hover:text-white hover:bg-white/5' ?>">
                            Profil <i class="fa-solid fa-chevron-down text-[8px] mt-0.5 group-hover:rotate-180 transition-transform"></i>
                        </button>
                        <div class="absolute top-full left-0 mt-0 pt-2 hidden group-hover:block z-50">
                            <div class="bg-cypress-950 border border-antique-500/30 rounded-lg shadow-lg py-1 min-w-48 overflow-hidden">
                                <a href="<?= $base ?>/profil" class="block px-4 py-2.5 text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition">
                                    <span class="font-medium">Tentang Masjid</span>
                                    <span class="text-stone-500 text-[9px] block mt-0.5">Sejarah, visi misi</span>
                                </a>
                                <a href="<?= $base ?>/profil#kepengurusan" class="block px-4 py-2.5 text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition">
                                    <span class="font-medium">Kepengurusan DKM</span>
                                    <span class="text-stone-500 text-[9px] block mt-0.5">Tim pengurus & pengawas</span>
                                </a>
                                <a href="<?= $base ?>/tim-kami" class="block px-4 py-2.5 text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition">
                                    <span class="font-medium">Tim Kami</span>
                                    <span class="text-stone-500 text-[9px] block mt-0.5">Admin & staff profesional</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Dropdown Program -->
                    <div class="relative group">
                        <button class="px-4 py-2 rounded-lg transition flex items-center gap-1 <?= in_array($activeNav, ['program']) ? 'text-antique-300 bg-cypress-800/50' : 'text-stone-300 hover:text-white hover:bg-white/5' ?>">
                            Program <i class="fa-solid fa-chevron-down text-[8px] mt-0.5 group-hover:rotate-180 transition-transform"></i>
                        </button>
                        <div class="absolute top-full left-0 mt-0 pt-2 hidden group-hover:block z-50">
                            <div class="bg-cypress-950 border border-antique-500/30 rounded-lg shadow-lg py-1 min-w-48 overflow-hidden">
                                <a href="<?= $base ?>/program" class="block px-4 py-2.5 text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition">
                                    <span class="font-medium">Program Donasi</span>
                                    <span class="text-stone-500 text-[9px] block mt-0.5">Program aktif masjid</span>
                                </a>
                                <a href="<?= $base ?>/berita" class="block px-4 py-2.5 text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition">
                                    <span class="font-medium">Kegiatan & Berita</span>
                                    <span class="text-stone-500 text-[9px] block mt-0.5">Update kegiatan masjid</span>
                                </a>
                                <a href="<?= $base ?>/kajian" class="block px-4 py-2.5 text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition">
                                    <span class="font-medium">Video Kajian</span>
                                    <span class="text-stone-500 text-[9px] block mt-0.5">Ceramah & pembelajaran</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Dropdown Lainnya -->
                    <div class="relative group">
                        <button class="px-4 py-2 rounded-lg transition flex items-center gap-1 <?= in_array($activeNav, ['transparansi']) ? 'text-antique-300 bg-cypress-800/50' : 'text-stone-300 hover:text-white hover:bg-white/5' ?>">
                            Lainnya <i class="fa-solid fa-chevron-down text-[8px] mt-0.5 group-hover:rotate-180 transition-transform"></i>
                        </button>
                        <div class="absolute top-full left-0 mt-0 pt-2 hidden group-hover:block z-50">
                            <div class="bg-cypress-950 border border-antique-500/30 rounded-lg shadow-lg py-1 min-w-48 overflow-hidden">
                                <a href="<?= $base ?>/transparansi" class="block px-4 py-2.5 text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition">
                                    <span class="font-medium">Transparansi Kas</span>
                                    <span class="text-stone-500 text-[9px] block mt-0.5">Laporan keuangan terbuka</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Dropdown Dokumentasi -->
                    <div class="relative group">
                        <button class="px-4 py-2 rounded-lg transition flex items-center gap-1 <?= in_array($activeNav, ['panduan', 'panduan-user']) ? 'text-antique-300 bg-cypress-800/50' : 'text-stone-300 hover:text-white hover:bg-white/5' ?>">
                            Docs <i class="fa-solid fa-chevron-down text-[8px] mt-0.5 group-hover:rotate-180 transition-transform"></i>
                        </button>
                        <div class="absolute top-full left-0 mt-0 pt-2 hidden group-hover:block z-50">
                            <div class="bg-cypress-950 border border-antique-500/30 rounded-lg shadow-lg py-1 min-w-48 overflow-hidden">
                                <a href="<?= $base ?>/docs/panduan-user.php" class="block px-4 py-2.5 text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition">
                                    <span class="font-medium">Panduan Pengguna</span>
                                    <span class="text-stone-500 text-[9px] block mt-0.5">Cara donasi & transparansi</span>
                                </a>
                                <?php if (isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'bendahara', 'content_admin'])): ?>
                                <a href="<?= $base ?>/docs/panduan-admin.php" class="block px-4 py-2.5 text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition">
                                    <span class="font-medium">Panduan Admin</span>
                                    <span class="text-stone-500 text-[9px] block mt-0.5">Kelola kas & konten</span>
                                </a>
                                <?php endif; ?>
                                <div class="border-t border-white/10 my-1"></div>
                                <a href="<?= $base ?>/docs/privacy.php" class="block px-4 py-2 text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition">
                                    <span>Kebijakan Privasi</span>
                                </a>
                                <a href="<?= $base ?>/docs/terms.php" class="block px-4 py-2 text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition">
                                    <span>Syarat & Ketentuan</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="<?= $base ?>/kontak" class="px-4 py-2 rounded-lg transition <?= $activeNav === 'kontak' ? 'text-antique-300 bg-cypress-800/50' : 'text-stone-300 hover:text-white hover:bg-white/5' ?>">
                        Kontak
                    </a>
                </nav>

                <!-- Action Buttons (Desktop) -->
                <div class="hidden sm:flex items-center gap-3 shrink-0">
                    <a href="<?= $base ?>/donasi-online" class="px-4 py-2 rounded-lg bg-antique-600 hover:bg-antique-500 text-cypress-950 font-bold text-xs transition">
                        Donasi
                    </a>
                    <?php if ($currentUser): ?>
                        <a href="<?= in_array($currentUser['role'], ['admin', 'bendahara', 'content_admin']) ? $base . '/admin/dashboard' : $base . '/donatur/portal-donatur' ?>" class="p-2 rounded-lg bg-cypress-800/60 border border-antique-500/30 text-antique-300 hover:text-white hover:bg-cypress-700 transition text-xs" title="Buka Portal">
                            <i class="fa-solid fa-gauge-high"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?= $base ?>/auth/login" class="p-2 rounded-lg bg-cypress-800/60 border border-antique-500/30 text-antique-300 hover:text-white hover:bg-cypress-700 transition text-xs" title="Masuk">
                            <i class="fa-solid fa-user"></i>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden flex items-center gap-2">
                    <a href="<?= $base ?>/donasi-online" class="px-3 py-1.5 rounded-lg bg-antique-600 hover:bg-antique-500 text-cypress-950 font-bold text-xs transition">
                        Donasi
                    </a>
                    <button type="button" id="mobileMenuBtn" class="p-2 rounded-lg bg-cypress-800 border border-antique-500/30 text-antique-300 hover:text-white focus:outline-none transition" aria-label="Toggle Menu">
                        <i class="fa-solid fa-bars text-base"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden lg:hidden bg-cypress-950/98 border-t border-antique-500/20 px-4 py-3 space-y-1 pb-4">
            <a href="<?= $base ?>/" class="block px-3 py-2.5 rounded-lg text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition <?= $activeNav === 'beranda' ? 'text-antique-300 bg-cypress-800/50' : '' ?>">
                Beranda
            </a>
            
            <!-- Mobile Accordion: Profil -->
            <div class="space-y-1">
                <button type="button" class="mobile-accordion w-full text-left px-3 py-2.5 rounded-lg text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition flex items-center justify-between">
                    Profil <i class="fa-solid fa-chevron-down text-[8px] transition-transform"></i>
                </button>
                <div class="mobile-accordion-content hidden pl-2 space-y-1">
                    <a href="<?= $base ?>/home/profil.php" class="block px-3 py-2 rounded-lg text-xs text-stone-400 hover:text-white hover:bg-cyan-800/40 transition">
                        Tentang Masjid
                    </a>
                    <a href="<?= $base ?>/home/profil.php#kepengurusan" class="block px-3 py-2 rounded-lg text-xs text-stone-400 hover:text-white hover:bg-cyan-800/40 transition">
                        Kepengurusan DKM
                    </a>
                    <a href="<?= $base ?>/home/tim-kami.php" class="block px-3 py-2 rounded-lg text-xs text-stone-400 hover:text-white hover:bg-cyan-800/40 transition">
                        Tim Kami
                    </a>
                </div>
            </div>

            <!-- Mobile Accordion: Program -->
            <div class="space-y-1">
                <button type="button" class="mobile-accordion w-full text-left px-3 py-2.5 rounded-lg text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition flex items-center justify-between">
                    Program <i class="fa-solid fa-chevron-down text-[8px] transition-transform"></i>
                </button>
                <div class="mobile-accordion-content hidden pl-2 space-y-1">
                    <a href="<?= $base ?>/home/program.php" class="block px-3 py-2 rounded-lg text-xs text-stone-400 hover:text-white hover:bg-cyan-800/40 transition">
                        Program Donasi
                    </a>
                    <a href="<?= $base ?>/home/berita.php" class="block px-3 py-2 rounded-lg text-xs text-stone-400 hover:text-white hover:bg-cyan-800/40 transition">
                        Kegiatan & Berita
                    </a>
                    <a href="<?= $base ?>/home/kajian.php" class="block px-3 py-2 rounded-lg text-xs text-stone-400 hover:text-white hover:bg-cyan-800/40 transition">
                        Video Kajian
                    </a>
                </div>
            </div>

            <!-- Mobile Accordion: Lainnya -->
            <div class="space-y-1">
                <button type="button" class="mobile-accordion w-full text-left px-3 py-2.5 rounded-lg text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition flex items-center justify-between">
                    Lainnya <i class="fa-solid fa-chevron-down text-[8px] transition-transform"></i>
                </button>
                <div class="mobile-accordion-content hidden pl-2 space-y-1">
                    <a href="<?= $base ?>/home/transparansi.php" class="block px-3 py-2 rounded-lg text-xs text-stone-400 hover:text-white hover:bg-cyan-800/40 transition">
                        Transparansi Kas
                    </a>
                </div>
            </div>

                    <!-- Mobile Accordion: Dokumentasi -->
            <div class="space-y-1">
                <button type="button" class="mobile-accordion w-full text-left px-3 py-2.5 rounded-lg text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition flex items-center justify-between">
                    Docs <i class="fa-solid fa-chevron-down text-[8px] transition-transform"></i>
                </button>
                <div class="mobile-accordion-content hidden pl-2 space-y-1">
                    <a href="<?= $base ?>/docs/panduan-user.php" class="block px-3 py-2 rounded-lg text-xs text-stone-400 hover:text-white hover:bg-cyan-800/40 transition">
                        Panduan Pengguna
                    </a>
                    <?php if (isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'bendahara', 'content_admin'])): ?>
                    <a href="<?= $base ?>/docs/panduan-admin.php" class="block px-3 py-2 rounded-lg text-xs text-stone-400 hover:text-white hover:bg-cyan-800/40 transition">
                        Panduan Admin
                    </a>
                    <?php endif; ?>
                    <div class="border-t border-white/10 my-1"></div>
                    <a href="<?= $base ?>/docs/privacy.php" class="block px-3 py-2 rounded-lg text-xs text-stone-400 hover:text-white hover:bg-cyan-800/40 transition">
                        Kebijakan Privasi
                    </a>
                    <a href="<?= $base ?>/docs/terms.php" class="block px-3 py-2 rounded-lg text-xs text-stone-400 hover:text-white hover:bg-cyan-800/40 transition">
                        Syarat & Ketentuan
                    </a>
                </div>
            </div>

            <a href="<?= $base ?>/home/kontak.php" class="block px-3 py-2.5 rounded-lg text-xs text-stone-300 hover:text-white hover:bg-cypress-800/60 transition <?= $activeNav === 'kontak' ? 'text-antique-300 bg-cypress-800/50' : '' ?>">
                Kontak
            </a>

            <!-- Mobile Account Section -->
            <div class="pt-3 border-t border-white/10 space-y-2">
                <?php if ($currentUser): ?>
                    <a href="<?= in_array($currentUser['role'], ['admin', 'bendahara', 'content_admin']) ? $base . '/admin/dashboard' : $base . '/donatur/portal-donatur' ?>" class="block w-full text-center py-2 rounded-lg bg-cypress-800 text-antique-300 font-semibold text-xs border border-antique-500/30 hover:text-white transition">
                        Portal (<?= e(substr($currentUser['nama'], 0, 15)) ?>)
                    </a>
                    <a href="<?= $base ?>/auth/logout" class="block w-full text-center py-2 rounded-lg text-red-300 hover:text-white text-xs transition">
                        Keluar
                    </a>
                <?php else: ?>
                    <a href="<?= $base ?>/auth/login" class="block w-full text-center py-2 rounded-lg bg-cypress-800 text-antique-300 font-semibold text-xs border border-antique-500/30 hover:text-white transition">
                        Masuk
                    </a>
                    <a href="<?= $base ?>/auth/register" class="block w-full text-center py-2 rounded-lg text-stone-300 hover:text-white text-xs transition">
                        Daftar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        });

        // Mobile accordion
        document.querySelectorAll('.mobile-accordion').forEach(btn => {
            btn.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const icon = this.querySelector('i');
                content.classList.toggle('hidden');
                icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
            });
        });
    </script>
    <main class="flex-1">
