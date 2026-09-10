<?php
// Layouts: Sidebar & Navigasi Pengurus Terintegrasi (Multi-Role RBAC)
require_once __DIR__ . '/../config/database.php';
mulai_session();
$user = $_SESSION['user'] ?? ['nama' => 'Pengurus', 'role' => 'admin'];
$userRole = $user['role'] ?? 'admin';

$activeMenu   = $activeMenu ?? 'dashboard';
$pageSubtitle = $pageSubtitle ?? 'Portal Administrasi & Pembukuan Masjid';

// Hitung Donasi Online Pending untuk Badge
$countPendingDonasi = 0;
if (in_array($userRole, ['admin', 'bendahara'])) {
    $countPendingDonasi = (int)$pdo->query("SELECT COUNT(*) FROM donasi_online WHERE status = 'pending'")->fetchColumn();
}

// Format Tanggal Dinamis
$namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w')];
$namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][date('n')];
$tanggalLengkap = $namaHari . ', ' . date('j') . ' ' . $namaBulan . ' ' . date('Y');
?>

<!-- ==============================================================================
     TOPBAR KHUSUS MOBILE (< lg)
     ============================================================================== -->
<header class="lg:hidden sticky top-0 z-30 bg-cypress-900/70 backdrop-blur-md text-white border-b border-antique-500/30 px-4 py-3 flex items-center justify-between shadow-sm no-print">
    <div class="flex items-center gap-2.5">
        <button type="button" id="sidebarToggle" class="p-2 rounded-xl bg-cypress-950 border border-antique-500/40 text-antique-300 hover:text-white transition focus:outline-none" aria-label="Buka Menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-cypress-950 border border-antique-500/40 flex items-center justify-center text-antique-300">
                <i class="fa-solid fa-mosque text-sm"></i>
            </div>
            <div>
                <span class="font-classic text-xs font-bold tracking-wide block leading-tight">NURUL IMAN</span>
                <span class="text-[9px] text-antique-300 uppercase tracking-wider block font-semibold"><?= strtoupper($userRole) ?></span>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <a href="../index.php" target="_blank" class="p-1.5 rounded-lg bg-white/10 text-stone-200 hover:text-white text-xs" title="Lihat Web Publik">
            <i class="fa-solid fa-globe"></i>
        </a>
        <a href="../auth/logout.php" class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-950/70 border border-red-500/30 text-red-200 text-xs hover:text-white transition">
            <i class="fa-solid fa-arrow-right-from-bracket text-[10px]"></i>
            <span>Keluar</span>
        </a>
    </div>
</header>

<!-- Overlay Backdrop Gelap Mobile -->
<div id="sidebarOverlay" class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs z-40 hidden lg:hidden transition-opacity duration-300 no-print"></div>

<!-- ==============================================================================
     SIDEBAR UTAMA (RESPONSIF MOBILE & DESKTOP DENGAN RBAC)
     ============================================================================== -->
<aside id="mainSidebar" class="fixed top-0 bottom-0 left-0 z-50 w-72 pattern-arabesque-dark text-white border-r border-antique-500/30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col justify-between shadow-2xl lg:shadow-none no-print overflow-y-auto">
    
    <!-- Bagian Atas: Identitas & Menu RBAC -->
    <div>
        <!-- Header Brand Masjid di Sidebar -->
        <div class="p-5 border-b border-white/10 flex items-center justify-between">
            <a href="../index.php" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-cypress-900 border border-antique-500/40 flex items-center justify-center text-antique-300 shadow-inner shrink-0">
                    <i class="fa-solid fa-mosque text-lg"></i>
                </div>
                <div>
                    <span class="font-classic text-sm font-bold tracking-wider block text-white leading-tight">MASJID JAMI'</span>
                    <span class="font-classic text-xs text-antique-300 font-semibold tracking-widest block">NURUL IMAN</span>
                </div>
            </a>

            <!-- Tombol Tutup Khusus Mobile -->
            <button type="button" id="sidebarClose" class="lg:hidden p-1.5 rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition" aria-label="Tutup Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Menu Navigasi Sesuai Role -->
        <div class="p-4 space-y-4">
            
            <!-- GROUP 1: UTAMA & DASHBOARD -->
            <div class="space-y-1">
                <a href="dashboard.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl <?= $activeMenu === 'dashboard' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                    <i class="fa-solid fa-gauge-high w-4 text-center <?= $activeMenu === 'dashboard' ? 'text-antique-300' : 'text-antique-300/80' ?>"></i>
                    <span>Dashboard Utama</span>
                </a>
            </div>

            <!-- GROUP 2: PEMBUKUAN & KEUANGAN (Admin & Bendahara) -->
            <?php if (in_array($userRole, ['admin', 'bendahara'])): ?>
                <div class="space-y-1">
                    <span class="px-3 text-[10px] uppercase font-bold tracking-widest text-antique-300/70 block mb-1.5">
                        Pembukuan &amp; Kas
                    </span>

                    <a href="transaksi.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl <?= $activeMenu === 'transaksi' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                        <i class="fa-solid fa-book-journal-whills w-4 text-center <?= $activeMenu === 'transaksi' ? 'text-antique-300' : 'text-antique-300/80' ?>"></i>
                        <span>Buku Kas Transaksi</span>
                    </a>

                    <a href="verifikasi-donasi.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl <?= $activeMenu === 'verifikasi' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-check-to-slot w-4 text-center <?= $activeMenu === 'verifikasi' ? 'text-antique-300' : 'text-antique-300/80' ?>"></i>
                            <span>Verifikasi Donasi</span>
                        </div>
                        <?php if ($countPendingDonasi > 0): ?>
                            <span class="px-2 py-0.5 rounded-full bg-amber-500 text-cypress-950 font-bold text-[10px] animate-pulse">
                                <?= $countPendingDonasi ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <a href="laporan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl <?= $activeMenu === 'laporan' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                        <i class="fa-solid fa-chart-pie w-4 text-center <?= $activeMenu === 'laporan' ? 'text-antique-300' : 'text-antique-300/80' ?>"></i>
                        <span>Laporan Keuangan</span>
                    </a>
                </div>
            <?php endif; ?>

            <!-- GROUP 3: KONTEN WEBSITE & MEDIA (Admin & Content Admin) -->
            <?php if (in_array($userRole, ['admin', 'content_admin'])): ?>
                <div class="space-y-1">
                    <span class="px-3 text-[10px] uppercase font-bold tracking-widest text-antique-300/70 block mb-1.5">
                        Konten &amp; Media Web
                    </span>

                    <a href="program-admin.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl <?= $activeMenu === 'program-admin' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                        <i class="fa-solid fa-hand-holding-dollar w-4 text-center <?= $activeMenu === 'program-admin' ? 'text-antique-300' : 'text-antique-300/80' ?>"></i>
                        <span>Program Donasi</span>
                    </a>

                    <a href="berita-admin.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl <?= $activeMenu === 'berita-admin' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                        <i class="fa-solid fa-newspaper w-4 text-center <?= $activeMenu === 'berita-admin' ? 'text-antique-300' : 'text-antique-300/80' ?>"></i>
                        <span>Warta &amp; Berita</span>
                    </a>

                    <a href="banner-admin.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl <?= $activeMenu === 'banner-admin' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                        <i class="fa-solid fa-images w-4 text-center <?= $activeMenu === 'banner-admin' ? 'text-antique-300' : 'text-antique-300/80' ?>"></i>
                        <span>Slider &amp; Banner</span>
                    </a>

                    <a href="youtube-admin.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl <?= $activeMenu === 'youtube-admin' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                        <i class="fa-brands fa-youtube w-4 text-center <?= $activeMenu === 'youtube-admin' ? 'text-antique-300' : 'text-antique-300/80' ?>"></i>
                        <span>Integrasi YouTube</span>
                    </a>

                    <a href="rekening-admin.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl <?= $activeMenu === 'rekening-admin' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                        <i class="fa-solid fa-credit-card w-4 text-center <?= $activeMenu === 'rekening-admin' ? 'text-antique-300' : 'text-antique-300/80' ?>"></i>
                        <span>Rekening &amp; QRIS</span>
                    </a>

                    <a href="profil-admin.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl <?= $activeMenu === 'profil-admin' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                        <i class="fa-solid fa-mosque w-4 text-center <?= $activeMenu === 'profil-admin' ? 'text-antique-300' : 'text-antique-300/80' ?>"></i>
                        <span>Profil &amp; DKM</span>
                    </a>
                </div>
            <?php endif; ?>

            <!-- GROUP 4: PENGATURAN SISTEM & USER (Khusus Administrator) -->
            <?php if ($userRole === 'admin'): ?>
                <div class="space-y-1">
                    <span class="px-3 text-[10px] uppercase font-bold tracking-widest text-antique-300/70 block mb-1.5">
                        Administrasi &amp; Akses
                    </span>

                    <a href="users-admin.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl <?= $activeMenu === 'users-admin' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                        <i class="fa-solid fa-users-gear w-4 text-center <?= $activeMenu === 'users-admin' ? 'text-antique-300' : 'text-antique-300/80' ?>"></i>
                        <span>Kelola Pengguna (RBAC)</span>
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Bagian Bawah: Profil Pengurus, Link Web Publik & Logout -->
    <div class="p-4 border-t border-white/10 bg-cypress-950/80 space-y-3">
        
        <!-- Tautan Cepat ke Web Publik -->
        <a href="../index.php" target="_blank" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-antique-300 text-xs font-semibold transition">
            <i class="fa-solid fa-globe text-xs"></i>
            <span>Buka Website Publik</span>
        </a>

        <!-- Profil Akun Pengurus -->
        <div class="flex items-center justify-between gap-3 pt-1">
            <div class="flex items-center gap-2.5 overflow-hidden">
                <div class="w-9 h-9 rounded-full bg-cypress-800 border border-antique-500/50 flex items-center justify-center text-antique-300 font-bold text-xs shrink-0">
                    <?= strtoupper(substr($user['nama'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="truncate">
                    <span class="text-xs font-bold text-white block truncate leading-tight"><?= e($user['nama'] ?? 'Pengurus') ?></span>
                    <span class="text-[10px] text-antique-300 uppercase tracking-widest block font-semibold"><?= e(str_replace('_', ' ', $userRole)) ?></span>
                </div>
            </div>
        </div>

        <a href="../auth/logout.php" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-red-950/40 hover:bg-red-900/60 border border-red-500/30 text-red-200 text-xs font-medium transition-luxury">
            <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
            <span>Keluar dari Portal</span>
        </a>
    </div>

</aside>

<!-- ==============================================================================
     KONTEN UTAMA (DILENGKAPI PADDING KIRI UNTUK DESKTOP SIDEBAR)
     ============================================================================== -->
<div class="lg:pl-72 flex-1 flex flex-col justify-between min-h-screen">
    
    <!-- Header Desktop -->
    <header class="hidden lg:flex bg-white/70 backdrop-blur-md sticky top-0 z-20 border-b border-antique-300/40 px-8 py-3.5 items-center justify-between no-print shadow-xs">
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
            <span class="text-xs font-semibold text-warm-900"><?= e($pageSubtitle) ?></span>
        </div>

        <div class="flex items-center gap-4 text-xs text-warm-800/80">
            <a href="../index.php" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-cypress-700 hover:text-cypress-900 font-semibold transition">
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                <span>Lihat Website Publik</span>
            </a>
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-antique-50 border border-antique-300/50 text-antique-800 font-medium text-xs">
                <i class="fa-regular fa-calendar text-antique-600"></i>
                <span><?= e($tanggalLengkap) ?></span>
            </div>
        </div>
    </header>

    <!-- Kontainer Halaman -->
    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8 flex-1">
