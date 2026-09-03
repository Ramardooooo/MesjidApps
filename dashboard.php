<?php
require_once __DIR__ . '/config/database.php';
cek_login();
$user = $_SESSION['user'];

// Statistik Donasi Uang & Kegiatan
$totalDonasi = $pdo->query("SELECT COALESCE(SUM(jumlah),0) AS uang, COUNT(*) AS jumlah FROM donasi WHERE jenis_donasi='uang'")->fetch();
$jumlahKegiatan = $pdo->query("SELECT COUNT(*) AS c FROM kegiatan")->fetch()['c'];

// 5 Catatan Donasi Terakhir untuk Pratinjau Cepat
$donasiTerbaru = $pdo->query("SELECT * FROM donasi WHERE jenis_donasi='uang' ORDER BY tanggal_donasi DESC, id DESC LIMIT 5")->fetchAll();

// Konfigurasi Halaman & Navigasi
$pageTitle    = 'Dashboard Pengurus · Masjid Nurul Iman';
$activeMenu   = 'dashboard';
$pageSubtitle = 'Portal Administrasi Pengurus Aktif';

// Panggil Header & Sidebar Bercabang
require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

<!-- Banner Sambutan Klasik -->
<div class="bg-white rounded-2xl p-6 sm:p-8 border border-antique-300/40 shadow-sm relative overflow-hidden">
    <!-- Watermark Kubah -->
    <div class="absolute -right-8 -bottom-8 w-44 h-44 text-warm-100 pointer-events-none opacity-40">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2v2m0 0c-3 3-5 6-5 9a5 5 0 0010 0c0-3-2-6-5-9z M4 21h16M7 21v-4m10 4v-4M9 13h6"/>
        </svg>
    </div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-antique-50 border border-antique-300/50 text-antique-700 text-xs font-semibold uppercase tracking-wider mb-2.5">
                <span class="w-1.5 h-1.5 rounded-full bg-cypress-700"></span>
                <span>Selamat Datang</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-warm-900 tracking-tight">
                Assalamu’alaikum, <span class="text-cypress-700 font-classic"><?= e($user['nama']) ?></span>
            </h1>
            <p class="text-xs sm:text-sm text-warm-800/70 mt-1 max-w-2xl leading-relaxed">
                Semoga setiap langkah kita dalam memelihara dan memakmurkan rumah Allah senantiasa bernilai ibadah dan penuh keberkahan.
            </p>
        </div>

        <div class="shrink-0 flex items-center gap-3">
            <a href="donasi.php" 
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-semibold text-xs tracking-wide shadow-md shadow-cypress-900/15 transition-luxury">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Catat Infaq Kas</span>
            </a>
        </div>
    </div>
</div>

<!-- Kartu Statistik -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
    
    <!-- Stat 1: Total Donasi Uang -->
    <div class="bg-white rounded-2xl p-6 border border-antique-300/40 shadow-sm hover:shadow-md transition-luxury flex items-start justify-between">
        <div>
            <span class="text-xs uppercase tracking-wider font-semibold text-warm-800/60 block mb-1">
                Total Kas Terkumpul
            </span>
            <h3 class="text-2xl sm:text-3xl font-bold text-warm-900 tracking-tight">
                Rp <?= number_format($totalDonasi['uang'], 0, ',', '.') ?>
            </h3>
            <span class="inline-block mt-2 text-[11px] text-cypress-700 font-medium">
                ✓ Terverifikasi dalam sistem
            </span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-antique-50 border border-antique-300/50 flex items-center justify-center text-antique-600 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
    </div>

    <!-- Stat 2: Total Catatan Donatur -->
    <div class="bg-white rounded-2xl p-6 border border-antique-300/40 shadow-sm hover:shadow-md transition-luxury flex items-start justify-between">
        <div>
            <span class="text-xs uppercase tracking-wider font-semibold text-warm-800/60 block mb-1">
                Jumlah Donatur Terdata
            </span>
            <h3 class="text-2xl sm:text-3xl font-bold text-warm-900 tracking-tight">
                <?= (int)$totalDonasi['jumlah'] ?> <span class="text-sm font-normal text-warm-800/60">Catatan</span>
            </h3>
            <span class="inline-block mt-2 text-[11px] text-cypress-700 font-medium">
                Infaq kas tunai &amp; transfer
            </span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-cypress-50 border border-cypress-200/50 flex items-center justify-center text-cypress-700 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
    </div>

    <!-- Stat 3: Agenda Kegiatan -->
    <a href="kegiatan.php" class="bg-white rounded-2xl p-6 border border-antique-300/40 shadow-sm hover:shadow-md hover:border-cypress-600 transition-luxury flex items-start justify-between group">
        <div>
            <span class="text-xs uppercase tracking-wider font-semibold text-warm-800/60 block mb-1">
                Agenda &amp; Kegiatan
            </span>
            <h3 class="text-2xl sm:text-3xl font-bold text-warm-900 tracking-tight">
                <?= (int)$jumlahKegiatan ?> <span class="text-sm font-normal text-warm-800/60">Kegiatan</span>
            </h3>
            <span class="inline-block mt-2 text-[11px] text-antique-600 group-hover:text-cypress-700 font-medium">
                Kajian &amp; program takmir →
            </span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-warm-100 border border-warm-200 group-hover:bg-cypress-700 group-hover:text-white flex items-center justify-center text-warm-800 shrink-0 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
    </a>

</div>

<!-- Grid: Aksi Cepat & Catatan Infaq Terkini -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    
    <!-- Kolom Kiri: Navigasi Cepat (4 Kolom) -->
    <div class="lg:col-span-4 space-y-5">
        
        <div class="bg-white rounded-2xl p-6 border border-antique-300/40 shadow-sm">
            <h4 class="text-sm font-bold uppercase tracking-wider text-warm-800 mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-antique-500"></span>
                Aksi Cepat
            </h4>
            <div class="space-y-3">
                <a href="donasi.php" class="flex items-center justify-between p-3.5 rounded-xl border border-warm-200/80 bg-warm-50/50 hover:bg-white hover:border-cypress-600 transition-luxury group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-cypress-50 text-cypress-700 flex items-center justify-center group-hover:bg-cypress-700 group-hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-warm-900">Buku Kas &amp; Infaq</span>
                    </div>
                    <svg class="w-4 h-4 text-warm-800/40 group-hover:text-cypress-700 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="kegiatan.php" class="flex items-center justify-between p-3.5 rounded-xl border border-warm-200/80 bg-warm-50/50 hover:bg-white hover:border-antique-500 transition-luxury group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-antique-50 text-antique-600 flex items-center justify-center group-hover:bg-antique-500 group-hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-warm-900">Agenda &amp; Kajian</span>
                    </div>
                    <svg class="w-4 h-4 text-warm-800/40 group-hover:text-antique-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Mutiara Renungan -->
        <div class="pattern-arabesque-dark rounded-2xl p-6 text-white border border-antique-500/30">
            <span class="text-[10px] text-antique-300 font-semibold tracking-widest uppercase block mb-1.5">Renungan Takmir</span>
            <p class="text-xs leading-relaxed text-emerald-100/90 italic">
                "Hanya yang memakmurkan masjid-masjid Allah ialah orang-orang yang beriman kepada Allah dan hari kemudian, serta tetap mendirikan shalat dan menunaikan zakat."
            </p>
            <span class="block mt-2 text-[10px] text-antique-300 font-medium">— QS. At-Taubah: 18</span>
        </div>

    </div>

    <!-- Kolom Kanan: 5 Infaq Terakhir (8 Kolom) -->
    <div class="lg:col-span-8 bg-white rounded-2xl p-6 border border-antique-300/40 shadow-sm flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-warm-100">
                <div>
                    <h4 class="text-sm font-bold text-warm-900">Catatan Infaq Terkini</h4>
                    <p class="text-xs text-warm-800/60 mt-0.5">Daftar transaksi kas jamaah yang baru masuk</p>
                </div>
                <a href="donasi.php" class="text-xs font-semibold text-cypress-700 hover:text-cypress-800 hover:underline">
                    Buka Semua →
                </a>
            </div>

            <!-- Tabel Ringkas Donasi -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-warm-800/50 uppercase tracking-wider border-b border-warm-100 text-[10px]">
                            <th class="pb-2.5 font-semibold">Nama Donatur</th>
                            <th class="pb-2.5 font-semibold">Nominal Infaq</th>
                            <th class="pb-2.5 font-semibold">Keterangan</th>
                            <th class="pb-2.5 font-semibold text-right">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm-100">
                        <?php if (empty($donasiTerbaru)): ?>
                        <tr>
                            <td colspan="4" class="py-6 text-center text-warm-800/40">
                                Belum ada data infaq yang dicatat.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($donasiTerbaru as $d): ?>
                            <tr class="hover:bg-warm-50/70 transition">
                                <td class="py-3 font-semibold text-warm-900">
                                    <?= e($d['nama_donatur']) ?>
                                </td>
                                <td class="py-3 font-bold text-cypress-700">
                                    Rp <?= number_format($d['jumlah'], 0, ',', '.') ?>
                                </td>
                                <td class="py-3 text-warm-800/70">
                                    <?= e($d['keterangan'] ?: 'Infaq Umum') ?>
                                </td>
                                <td class="py-3 text-right text-warm-800/60">
                                    <?= e(date('d/m/Y', strtotime($d['tanggal_donasi']))) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-warm-100 text-right">
            <a href="donasi.php" class="inline-flex items-center gap-1.5 text-xs font-semibold text-antique-600 hover:text-antique-700 transition">
                <span>Kelola Buku Kas Lengkap</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>

</div>

<?php
// Panggil Footer Bercabang
require_once __DIR__ . '/layouts/footer.php';
?>