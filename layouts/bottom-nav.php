<?php
// Layouts: Bottom Navigation Khusus Mobile dengan Tema Islami & Efek Interaktif
?>
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-cypress-950/95 pattern-arabesque-dark backdrop-blur-md border-t border-antique-500/30 shadow-[0_-4px_25px_rgba(0,0,0,0.3)] px-4 py-2.5 flex justify-around items-center pb-safe">
    
    <?php 
    $currentUri = $_SERVER['REQUEST_URI'];
    $isHome = (str_ends_with($currentUri, 'mesjid-donasi-main/') || str_ends_with($currentUri, 'index'));
    $isDonasi = str_contains($currentUri, 'donasi');
    $isBerita = str_contains($currentUri, 'berita');
    $isAkun = str_contains($currentUri, 'auth') || str_contains($currentUri, 'donatur');
    ?>

    <!-- Home -->
    <a href="<?= $base ?>/" class="mobile-nav-item relative flex flex-col items-center gap-1 py-1 px-3 rounded-2xl transition-all duration-300 <?= $isHome ? 'text-antique-300 bg-cypress-900 border border-antique-500/40 scale-105 shadow-inner' : 'text-stone-400 hover:text-antique-200' ?>">
        <i class="fa-solid fa-house text-base transition-transform duration-300 <?= $isHome ? 'animate-bounce text-antique-400' : '' ?>"></i>
        <span class="text-[10px] font-bold tracking-tight">Home</span>
        <?php if($isHome): ?>
            <span class="absolute -top-1 w-1.5 h-1.5 bg-antique-400 rounded-full"></span>
        <?php endif; ?>
    </a>

    <!-- Donasi -->
    <a href="<?= $base ?>/donasi-online" class="mobile-nav-item relative flex flex-col items-center gap-1 py-1 px-3 rounded-2xl transition-all duration-300 <?= ($isDonasi || str_contains($currentUri, 'donasi-online')) ? 'text-antique-300 bg-cypress-900 border border-antique-500/40 scale-105 shadow-inner' : 'text-stone-400 hover:text-antique-200' ?>">
        <i class="fa-solid fa-hand-holding-heart text-base transition-transform duration-300 <?= ($isDonasi || str_contains($currentUri, 'donasi-online')) ? 'animate-pulse text-antique-400' : '' ?>"></i>
        <span class="text-[10px] font-bold tracking-tight">Donasi</span>
        <?php if($isDonasi || str_contains($currentUri, 'donasi-online')): ?>
            <span class="absolute -top-1 w-1.5 h-1.5 bg-antique-400 rounded-full"></span>
        <?php endif; ?>
    </a>

    <!-- Berita / Artikel -->
    <a href="<?= $base ?>/berita" class="mobile-nav-item relative flex flex-col items-center gap-1 py-1 px-3 rounded-2xl transition-all duration-300 <?= $isBerita ? 'text-antique-300 bg-cypress-900 border border-antique-500/40 scale-105 shadow-inner' : 'text-stone-400 hover:text-antique-200' ?>">
        <i class="fa-solid fa-newspaper text-base"></i>
        <span class="text-[10px] font-bold tracking-tight">Artikel</span>
        <?php if($isBerita): ?>
            <span class="absolute -top-1 w-1.5 h-1.5 bg-antique-400 rounded-full"></span>
        <?php endif; ?>
    </a>

    <!-- Akun / Login -->
    <a href="<?= $base ?>/auth/login" class="mobile-nav-item relative flex flex-col items-center gap-1 py-1 px-3 rounded-2xl transition-all duration-300 <?= $isAkun ? 'text-antique-300 bg-cypress-900 border border-antique-500/40 scale-105 shadow-inner' : 'text-stone-400 hover:text-antique-200' ?>">
        <i class="fa-solid fa-user text-base"></i>
        <span class="text-[10px] font-bold tracking-tight">Akun</span>
        <?php if($isAkun): ?>
            <span class="absolute -top-1 w-1.5 h-1.5 bg-antique-400 rounded-full"></span>
        <?php endif; ?>
    </a>

</nav>

<style>
    .pb-safe { padding-bottom: env(safe-area-inset-bottom, 12px); }
    .mobile-nav-item:active {
        transform: scale(0.92);
    }
</style>