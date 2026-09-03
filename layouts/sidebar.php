<?php
// Layouts: Sidebar & Navigasi Pengurus
$activeMenu   = $activeMenu ?? 'dashboard';
$pageSubtitle = $pageSubtitle ?? 'Portal Administrasi Pengurus';

// Format Tanggal Dinamis
$namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w')];
$namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][date('n')];
$tanggalLengkap = $namaHari . ', ' . date('j') . ' ' . $namaBulan . ' ' . date('Y');
?>

<!-- ==============================================================================
     TOPBAR KHUSUS MOBILE (< lg)
     ============================================================================== -->
<header class="lg:hidden sticky top-0 z-30 bg-cypress-800 text-white border-b border-antique-500/30 px-4 py-3 flex items-center justify-between shadow-sm no-print">
    <div class="flex items-center gap-2.5">
        <button type="button" id="sidebarToggle" class="p-2 rounded-xl bg-cypress-900/80 border border-antique-500/40 text-antique-300 hover:text-white transition focus:outline-none" aria-label="Buka Menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-cypress-900 border border-antique-500/40 flex items-center justify-center text-antique-300">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 0c-3 3-5 6-5 9a5 5 0 0010 0c0-3-2-6-5-9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 21h16M7 21v-4m10 4v-4M9 13h6"/>
                </svg>
            </div>
            <div>
                <span class="font-classic text-sm font-bold tracking-wide block leading-tight">NURUL IMAN</span>
                <span class="text-[9px] text-antique-300 uppercase tracking-wider block">Pengurus</span>
            </div>
        </div>
    </div>

    <a href="logout.php" class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-cypress-900/70 border border-antique-500/30 text-antique-200 text-xs hover:text-white transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        <span>Keluar</span>
    </a>
</header>

<!-- Overlay Backdrop Gelap Mobile -->
<div id="sidebarOverlay" class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs z-40 hidden lg:hidden transition-opacity duration-300 no-print"></div>

<!-- ==============================================================================
     SIDEBAR UTAMA (RESPONSIF MOBILE & DESKTOP)
     ============================================================================== -->
<aside id="mainSidebar" class="fixed top-0 bottom-0 left-0 z-50 w-72 pattern-arabesque-dark text-white border-r border-antique-500/30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col justify-between shadow-2xl lg:shadow-none no-print">
    
    <!-- Bagian Atas: Identitas & Menu -->
    <div>
        <!-- Header Brand Masjid di Sidebar -->
        <div class="p-6 border-b border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-cypress-900 border border-antique-500/40 flex items-center justify-center text-antique-300 shadow-inner shrink-0">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 0c-3 3-5 6-5 9a5 5 0 0010 0c0-3-2-6-5-9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 21h16M7 21v-4m10 4v-4M9 13h6"/>
                    </svg>
                </div>
                <div>
                    <span class="font-classic text-sm sm:text-base font-bold tracking-wider block text-white leading-tight">MASJID JAMI'</span>
                    <span class="font-classic text-xs text-antique-300 font-semibold tracking-widest block">NURUL IMAN</span>
                </div>
            </div>

            <!-- Tombol Tutup Khusus Mobile -->
            <button type="button" id="sidebarClose" class="lg:hidden p-1.5 rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition" aria-label="Tutup Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Menu Navigasi -->
        <div class="p-4 space-y-1.5">
            <span class="px-3 text-[10px] uppercase font-bold tracking-widest text-antique-300/70 block mb-2">
                Menu Kepengurusan
            </span>

            <!-- Menu 1: Dashboard -->
            <a href="dashboard.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl <?= $activeMenu === 'dashboard' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                <svg class="w-4 h-4 <?= $activeMenu === 'dashboard' ? 'text-antique-300' : 'text-antique-300/80' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard Beranda</span>
            </a>

            <!-- Menu 2: Buku Kas & Donasi -->
            <a href="donasi.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl <?= $activeMenu === 'donasi' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                <svg class="w-4 h-4 <?= $activeMenu === 'donasi' ? 'text-antique-300' : 'text-antique-300/80' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Buku Kas &amp; Infaq</span>
            </a>

            <!-- Menu 3: Agenda & Kegiatan -->
            <a href="kegiatan.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl <?= $activeMenu === 'kegiatan' ? 'bg-cypress-900/90 text-white border-l-4 border-antique-500 shadow-xs font-semibold' : 'text-emerald-100/80 hover:text-white hover:bg-white/5 border-l-4 border-transparent font-medium' ?> text-xs transition-luxury">
                <svg class="w-4 h-4 <?= $activeMenu === 'kegiatan' ? 'text-antique-300' : 'text-antique-300/80' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Agenda &amp; Kegiatan</span>
            </a>
        </div>
    </div>

    <!-- Bagian Bawah: Profil Pengurus & Logout -->
    <div class="p-4 border-t border-white/10 bg-cypress-950/60">
        <div class="flex items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-2.5 overflow-hidden">
                <div class="w-9 h-9 rounded-full bg-cypress-800 border border-antique-500/50 flex items-center justify-center text-antique-300 font-bold text-xs shrink-0">
                    <?= strtoupper(substr($user['nama'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="truncate">
                    <span class="text-xs font-bold text-white block truncate leading-tight"><?= e($user['nama'] ?? 'Pengurus') ?></span>
                    <span class="text-[10px] text-antique-300 uppercase tracking-widest block font-medium"><?= e($user['role'] ?? 'admin') ?></span>
                </div>
            </div>
        </div>

        <a href="logout.php" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-red-950/40 hover:bg-red-900/60 border border-red-500/30 text-red-200 text-xs font-medium transition-luxury">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span>Keluar dari Portal</span>
        </a>
    </div>

</aside>

<!-- ==============================================================================
     KONTEN UTAMA (DILENGKAPI PADDING KIRI UNTUK DESKTOP SIDEBAR)
     ============================================================================== -->
<div class="lg:pl-72 flex-1 flex flex-col justify-between">
    
    <!-- Header Desktop -->
    <header class="hidden lg:flex bg-white/90 backdrop-blur-md sticky top-0 z-20 border-b border-antique-300/40 px-8 py-4 items-center justify-between no-print">
        <div class="flex items-center gap-2.5">
            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
            <span class="text-xs font-medium text-warm-800/70"><?= e($pageSubtitle) ?></span>
        </div>

        <div class="flex items-center gap-4 text-xs text-warm-800/80">
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-antique-50 border border-antique-300/50 text-antique-700 font-medium">
                <svg class="w-3.5 h-3.5 text-antique-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span><?= e($tanggalLengkap) ?></span>
            </div>
        </div>
    </header>

    <!-- Kontainer Halaman -->
    <main class="max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">
