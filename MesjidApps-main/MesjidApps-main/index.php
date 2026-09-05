<?php
// index.php - Beranda Publik Utama Masjid Jami' Nurul Iman
require_once __DIR__ . '/config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pageTitle = $profil['nama_masjid'] . ' · ' . $profil['sebutan'];
$activeNav = 'beranda';

// 1. Ambil Slider / Banner Aktif
$sliders = $pdo->query("SELECT * FROM banners WHERE tipe = 'slider' AND is_active = 1 ORDER BY urutan ASC")->fetchAll();
if (empty($sliders)) {
    $sliders = [
        [
            'judul' => 'Selamat Datang di ' . $profil['nama_masjid'],
            'subjudul' => 'Pusat Ibadah Khusyuk, Tarbiyah Generasi Qur\'ani, dan Kebangkitan Ekonomi Ummat.',
            'link_url' => 'home/profil.php'
        ]
    ];
}

// 2. Ambil Program Donasi Unggulan (Featured)
$programs = $pdo->query("SELECT * FROM program_donasi WHERE status = 'aktif' ORDER BY is_featured DESC, id DESC LIMIT 3")->fetchAll();

// 3. Ringkasan Kas Transparansi Publik (Hanya data is_published = 1)
$totalPemasukanPublik = $pdo->query("SELECT COALESCE(SUM(nominal),0) FROM transaksi_keuangan WHERE jenis = 'pemasukan' AND is_published = 1")->fetchColumn();
$totalPengeluaranPublik = $pdo->query("SELECT COALESCE(SUM(nominal),0) FROM transaksi_keuangan WHERE jenis = 'pengeluaran' AND is_published = 1")->fetchColumn();
$saldoKasPublik = (float)$profil['saldo_awal_kas'] + (float)$totalPemasukanPublik - (float)$totalPengeluaranPublik;

// 4. Ambil Berita Terbaru
$beritaTerbaru = $pdo->query("SELECT * FROM berita WHERE status = 'published' ORDER BY tanggal_publikasi DESC, id DESC LIMIT 3")->fetchAll();

// 5. Ambil Video YouTube Utama
$videoUtama = $pdo->query("SELECT * FROM youtube_videos WHERE is_active = 1 ORDER BY id ASC LIMIT 1")->fetch();

// 6. Rekening Donasi
$rekeningList = $pdo->query("SELECT * FROM rekening_donasi WHERE is_active = 1 ORDER BY urutan ASC LIMIT 4")->fetchAll();

require_once __DIR__ . '/layouts/public_header.php';
?>

<!-- ==============================================================================
     1. HERO SECTION & PROMOTION SLIDER
     ============================================================================== -->
<section class="relative bg-cypress-950 text-white overflow-hidden pattern-arabesque-dark border-b border-antique-500/30">
    <!-- Gradient Glow Effects -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-antique-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 right-10 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Hero Text Content -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-cypress-900/80 border border-antique-500/40 text-antique-300 text-xs font-semibold uppercase tracking-widest shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Pusat Dakwah &amp; Ibadah Ummat</span>
                </div>

                <h1 class="text-3xl sm:text-4xl xl:text-5xl font-extrabold tracking-tight leading-tight">
                    <?= e($sliders[0]['judul'] ?? 'Makmurkan Rumah Allah') ?><br class="hidden sm:inline">
                    <span class="gold-gradient-text font-classic">Alirkan Pahala Abadi</span>
                </h1>

                <p class="text-stone-300 text-sm sm:text-base leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    <?= e($sliders[0]['subjudul'] ?? 'Mari bersama menegakkan syiar Islam, mendukung kegiatan taklim, dan menyalurkan kepedulian bagi anak-anak yatim serta dhuafa melalui sistem donasi masjid yang transparan, amanah, dan akuntabel.') ?>
                </p>

                <!-- Action Buttons -->
                <div class="pt-2 flex flex-wrap items-center justify-center lg:justify-start gap-4">
                    <a href="<?= e($sliders[0]['link_url'] ?? 'home/donasi-online.php') ?>" class="px-7 py-3.5 rounded-xl bg-gradient-to-r from-antique-500 to-antique-600 hover:from-antique-400 hover:to-antique-500 text-cypress-950 font-bold text-sm tracking-wide shadow-lg shadow-antique-500/25 transition-luxury flex items-center gap-2.5">
                        <i class="fa-solid fa-hand-holding-heart text-base"></i>
                        <span>Infaq / Donasi Sekarang</span>
                    </a>
                    <a href="home/transparansi.php" class="px-6 py-3.5 rounded-xl bg-cypress-900/90 hover:bg-cypress-800 border border-antique-500/40 text-antique-200 hover:text-white font-semibold text-sm transition-luxury flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-sm text-antique-400"></i>
                        <span>Lihat Transparansi Kas</span>
                    </a>
                </div>

                <!-- Fast Ayat/Hadith Box -->
                <div class="pt-4 border-t border-white/10 text-xs text-stone-300/80 flex items-start gap-3">
                    <i class="fa-solid fa-quote-left text-antique-500 text-lg shrink-0 mt-0.5"></i>
                    <p class="italic leading-relaxed">
                        "Perumpamaan orang yang menginfakkan hartanya di jalan Allah seperti sebutir biji yang menumbuhkan tujuh tangkai, pada setiap tangkai ada seratus biji." <span class="text-antique-400 font-semibold">(QS. Al-Baqarah: 261)</span>
                    </p>
                </div>
            </div>

            <!-- Hero Slider Image -->
            <div class="lg:col-span-5">
                <?php if (!empty($sliders[0]['gambar'])): ?>
                    <div class="relative h-96 rounded-3xl overflow-hidden border border-antique-500/40 shadow-2xl">
                        <img src="<?= e(upload_url($sliders[0]['gambar'])) ?>" alt="<?= e($sliders[0]['judul']) ?>" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-cypress-950/80 to-transparent"></div>
                    </div>
                <?php else: ?>
                    
                    <!-- Header Widget -->
                    <div class="bg-cypress-900/90 backdrop-blur-md rounded-3xl border border-antique-500/40 p-6 sm:p-7 shadow-2xl relative overflow-hidden">
                        <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-cypress-950 border border-antique-500/40 flex items-center justify-center text-antique-400">
                                    <i class="fa-regular fa-clock"></i>
                                </div>
                                <div>
                                    <span class="font-classic text-sm font-bold text-white block">Jadwal Shalat Hari Ini</span>
                                    <span class="text-[10px] text-antique-300 block"><?= tanggal_indo(date('Y-m-d'), true) ?></span>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-1 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                WITA
                            </span>
                        </div>

                        <!-- Grid Waktu Shalat -->
                        <div class="grid grid-cols-5 gap-2 text-center">
                            <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                                <span class="text-[10px] text-antique-300 uppercase block font-semibold">Subuh</span>
                                <span class="text-xs sm:text-sm font-bold text-white block mt-1">05:08</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                                <span class="text-[10px] text-antique-300 uppercase block font-semibold">Dzuhur</span>
                                <span class="text-xs sm:text-sm font-bold text-white block mt-1">12:28</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                                <span class="text-[10px] text-antique-300 uppercase block font-semibold">Ashar</span>
                                <span class="text-xs sm:text-sm font-bold text-white block mt-1">15:40</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-antique-500/20 border border-antique-500/50 shadow-inner">
                                <span class="text-[10px] text-antique-300 uppercase block font-semibold">Maghrib</span>
                                <span class="text-xs sm:text-sm font-bold text-antique-300 block mt-1">18:30</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                                <span class="text-[10px] text-antique-300 uppercase block font-semibold">Isya</span>
                                <span class="text-xs sm:text-sm font-bold text-white block mt-1">19:39</span>
                            </div>
                        </div>

                        <!-- Highlight Quick Kas Mini -->
                        <div class="mt-6 pt-5 border-t border-white/10 bg-cypress-950/60 -mx-6 -mb-6 p-6 rounded-b-3xl">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <span class="text-stone-300 font-medium">Saldo Kas Transparan (Terbuka)</span>
                                <span class="text-[10px] text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">Audit Terkini</span>
                            </div>
                            <div class="flex items-baseline justify-between">
                                <span class="text-2xl font-bold text-antique-300 font-classic">
                                    <?= format_rupiah($saldoKasPublik) ?>
                                </span>
                                <a href="home/transparansi.php" class="text-xs text-stone-300 hover:text-white flex items-center gap-1 underline underline-offset-4">
                                    <span>Detail</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>

        </div>
    </div>
</section>

<!-- ==============================================================================
     2. QUICK STATS STRIP
     ============================================================================== -->
<section class="relative z-20 -mt-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-antique-300/60 shadow-xl grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-center sm:text-left">
        
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-cypress-50 border border-cypress-100 flex items-center justify-center text-cypress-700 shrink-0">
                <i class="fa-solid fa-wallet text-xl"></i>
            </div>
            <div>
                <span class="text-[11px] uppercase tracking-wider font-semibold text-warm-800/60 block">Saldo Kas Terbuka</span>
                <span class="text-lg sm:text-xl font-bold text-warm-900 font-classic"><?= format_rupiah($saldoKasPublik) ?></span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                <i class="fa-solid fa-hand-holding-heart text-xl"></i>
            </div>
            <div>
                <span class="text-[11px] uppercase tracking-wider font-semibold text-warm-800/60 block">Program Terlaksana</span>
                <span class="text-lg sm:text-xl font-bold text-warm-900"><?= count($programs) + 1 ?> Program Aktif</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                <i class="fa-solid fa-users text-xl"></i>
            </div>
            <div>
                <span class="text-[11px] uppercase tracking-wider font-semibold text-warm-800/60 block">Kapasitas Jamaah</span>
                <span class="text-lg sm:text-xl font-bold text-warm-900">± 1.500 Jamaah</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                <i class="fa-solid fa-qrcode text-xl"></i>
            </div>
            <div>
                <span class="text-[11px] uppercase tracking-wider font-semibold text-warm-800/60 block">Metode Donasi</span>
                <span class="text-lg sm:text-xl font-bold text-warm-900">QRIS &amp; Transfer</span>
            </div>
        </div>

    </div>
</section>

<!-- ==============================================================================
     3. KATALOG PROGRAM & DONASI UNGGULAN
     ============================================================================== -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-antique-50 border border-antique-300/50 text-antique-700 text-xs font-semibold uppercase tracking-wider mb-2">
                <i class="fa-solid fa-hand-holding-dollar text-cypress-700"></i>
                <span>Wakaf, Infaq &amp; Sedekah</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 tracking-tight font-classic">
                Program Donasi &amp; Pembangunan Masjid
            </h2>
            <p class="text-xs sm:text-sm text-warm-800/70 mt-1 max-w-xl">
                Dukung berbagai program kebaikan dan pemeliharaan rumah Allah. Setiap rupiah yang disalurkan menjadi amal jariyah yang pahalanya mengalir tiada henti.
            </p>
        </div>

        <a href="home/program.php" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-cypress-700 hover:text-cypress-900 transition underline underline-offset-4 shrink-0">
            <span>Lihat Semua Program</span>
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <!-- Cards Grid Program -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
        <?php foreach ($programs as $prog): 
            $persen = $prog['target_donasi'] > 0 ? min(100, round(($prog['dana_terkumpul'] / $prog['target_donasi']) * 100)) : 100;
        ?>
            <div class="bg-white rounded-3xl border border-antique-300/50 shadow-sm hover:shadow-xl transition-luxury overflow-hidden flex flex-col justify-between group">
                <div>
                    <!-- Header Card / Gambar Program -->
                    <div class="h-48 bg-gradient-to-br from-cypress-900 to-cypress-950 relative overflow-hidden flex items-center justify-center p-6 text-center">
                        <?php if (!empty($prog['gambar'])): ?>
                            <img src="<?= e(upload_url($prog['gambar'])) ?>" alt="<?= e($prog['nama_program']) ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-cypress-950/90 via-cypress-950/40 to-cypress-950/20"></div>
                        <?php else: ?>
                            <div class="absolute inset-0 pattern-arabesque-dark opacity-40"></div>
                        <?php endif; ?>
                        <div class="relative z-10">
                            <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-antique-500/20 text-antique-300 border border-antique-500/30 mb-2">
                                <?= strtoupper(e($prog['kategori'])) ?>
                            </span>
                            <h3 class="text-white font-bold text-lg font-classic line-clamp-2">
                                <?= e($prog['nama_program']) ?>
                            </h3>
                        </div>
                    </div>

                    <!-- Body Card -->
                    <div class="p-6 space-y-4">
                        <p class="text-xs text-warm-800/70 line-clamp-3 leading-relaxed">
                            <?= e($prog['deskripsi']) ?>
                        </p>

                        <!-- Progress Bar Capaian Dana -->
                        <div class="space-y-1.5 pt-2">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="text-warm-800/60">Terkumpul</span>
                                <span class="text-cypress-700 font-bold"><?= $persen ?>%</span>
                            </div>
                            <div class="w-full h-2.5 rounded-full bg-warm-100 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-antique-500 to-cypress-700 rounded-full transition-all duration-500" style="width: <?= $persen ?>%"></div>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-1">
                                <span class="font-bold text-warm-900"><?= format_rupiah($prog['dana_terkumpul']) ?></span>
                                <span class="text-warm-800/60 text-[11px]">Target: <?= format_rupiah($prog['target_donasi']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Card Action -->
                <div class="p-6 pt-0">
                    <div class="pt-4 border-t border-antique-100 flex items-center gap-3">
                        <a href="home/program-detail.php?id=<?= $prog['id'] ?>" class="flex-1 text-center py-2.5 px-3 rounded-xl bg-warm-50 hover:bg-warm-100 border border-antique-300/50 text-warm-800 text-xs font-semibold transition">
                            Rincian Program
                        </a>
                        <a href="home/donasi-online.php?program_id=<?= $prog['id'] ?>" class="flex-1 text-center py-2.5 px-3 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white text-xs font-bold shadow-md shadow-cypress-900/10 transition">
                            Donasi
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ==============================================================================
     4. TRANSPARANSI KEUANGAN MASJID (INTEGRASI PROJECT 2 KE PROJECT 1)
     ============================================================================== -->
<section class="bg-cypress-950 text-white py-16 sm:py-20 pattern-arabesque-dark border-y border-antique-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cypress-900 border border-antique-500/40 text-antique-300 text-xs font-semibold uppercase tracking-wider">
                    <i class="fa-solid fa-shield-halved text-emerald-400"></i>
                    <span>Akuntabilitas &amp; Transparansi Publik</span>
                </div>

                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight">
                    Pengelolaan Dana Kas Terbuka, <br class="hidden sm:inline">
                    <span class="gold-gradient-text font-classic">Amanah &amp; Dapat Dipertanggungjawabkan</span>
                </h2>

                <p class="text-stone-300 text-xs sm:text-sm leading-relaxed">
                    Setiap rupiah infaq dan sedekah dari jamaah dicatat secara rapi melalui Aplikasi Pembukuan Kas Masjid. Laporan keuangan yang telah disetujui ditampilkan secara terbuka demi kenyamanan dan kepercayaan segenap kaum muslimin.
                </p>

                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-[10px] text-emerald-400 uppercase font-bold tracking-wider block">Total Penerimaan Kas</span>
                        <span class="text-xl sm:text-2xl font-bold text-white font-classic mt-1 block">
                            <?= format_rupiah($totalPemasukanPublik) ?>
                        </span>
                        <span class="text-[10px] text-stone-400 block mt-1">Infaq, sedekah, dan wakaf publik</span>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-[10px] text-amber-400 uppercase font-bold tracking-wider block">Total Penyaluran Kas</span>
                        <span class="text-xl sm:text-2xl font-bold text-white font-classic mt-1 block">
                            <?= format_rupiah($totalPengeluaranPublik) ?>
                        </span>
                        <span class="text-[10px] text-stone-400 block mt-1">Operasional, fisik &amp; sosial dhuafa</span>
                    </div>
                </div>

                <div>
                    <a href="home/transparansi.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-antique-500 to-antique-600 text-cypress-950 font-bold text-xs shadow-lg hover:brightness-110 transition">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>Buka Halaman Laporan Keuangan Lengkap</span>
                    </a>
                </div>
            </div>

            <!-- Rekap Rekening & QRIS Cepat -->
            <div class="lg:col-span-6 bg-cypress-900/90 rounded-3xl p-6 sm:p-8 border border-antique-500/40 shadow-2xl space-y-6">
                <div class="flex items-center justify-between border-b border-white/10 pb-4">
                    <h3 class="font-classic text-base font-bold text-white">Salurkan Infaq Melalui Rekening Resmi</h3>
                    <span class="text-[10px] text-antique-300 font-semibold bg-white/5 px-2.5 py-1 rounded-full border border-white/10">100% Amanah</span>
                </div>

                <div class="space-y-3">
                    <?php foreach ($rekeningList as $rek): ?>
                        <div class="p-3.5 rounded-2xl bg-cypress-950/80 border border-white/10 flex items-center justify-between hover:border-antique-500/40 transition">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-white"><?= e($rek['nama_bank']) ?></span>
                                    <span class="text-[9px] bg-antique-500/20 text-antique-300 px-2 py-0.5 rounded"><?= e($rek['kategori_donasi']) ?></span>
                                </div>
                                <span class="font-mono text-sm text-antique-200 tracking-wider font-bold block"><?= e($rek['nomor_rekening']) ?></span>
                                <span class="text-[10px] text-stone-400 block">a.n <?= e($rek['atas_nama']) ?></span>
                            </div>
                            <button type="button" id="copyBtnIndex-<?= htmlspecialchars(md5($rek['id'])) ?>" onclick="copyToClipboardBtn('<?= e($rek['nomor_rekening']) ?>', '<?= htmlspecialchars(md5($rek['id'])) ?>', 'index');" class="px-3 py-1.5 rounded-lg bg-antique-500/10 hover:bg-antique-500 text-antique-300 hover:text-cypress-950 border border-antique-500/40 text-xs font-semibold transition shrink-0">
                                <i class="fa-regular fa-copy mr-1"></i> Salin
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="pt-2 text-center">
                    <a href="home/donasi-online.php" class="inline-flex items-center gap-2 text-xs text-antique-300 hover:text-white font-semibold">
                        <i class="fa-solid fa-qrcode text-antique-500"></i>
                        <span>Ingin Donasi dengan QRIS Instan? Klik di Sini</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ==============================================================================
     5. INTEGRASI KAJIAN YOUTUBE & LIVE STREAMING
     ============================================================================== -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 border border-red-200 text-red-700 text-xs font-semibold uppercase tracking-wider mb-2">
                <i class="fa-brands fa-youtube text-red-600"></i>
                <span>Syiar Dakwah Digital</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 tracking-tight font-classic">
                Video Kajian &amp; Live Streaming Masjid
            </h2>
            <p class="text-xs sm:text-sm text-warm-800/70 mt-1 max-w-xl">
                Saksikan siaran kajian rutin, khutbah Jumat, dan dokumentasi kegiatan masjid kapan saja dan di mana saja.
            </p>
        </div>

        <a href="home/kajian.php" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-cypress-700 hover:text-cypress-900 transition underline underline-offset-4 shrink-0">
            <span>Lihat Semua Video</span>
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <!-- Video Embed & Card -->
    <?php if ($videoUtama): ?>
        <div class="bg-white rounded-3xl border border-antique-300/50 shadow-lg overflow-hidden grid grid-cols-1 lg:grid-cols-12 gap-0">
            <div class="lg:col-span-7 bg-black relative aspect-video flex items-center justify-center">
                <iframe class="w-full h-full" 
                        src="<?= e($videoUtama['url_embed']) ?>" 
                        title="<?= e($videoUtama['judul']) ?>" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                </iframe>
            </div>
            <div class="lg:col-span-5 p-6 sm:p-8 flex flex-col justify-between space-y-6">
                <div class="space-y-3">
                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-red-50 text-red-600 border border-red-200">
                        <i class="fa-solid fa-play mr-1"></i> <?= strtoupper(e($videoUtama['kategori'])) ?>
                    </span>
                    <h3 class="text-xl sm:text-2xl font-bold text-warm-900 font-classic leading-tight">
                        <?= e($videoUtama['judul']) ?>
                    </h3>
                    <p class="text-xs sm:text-sm text-warm-800/70 leading-relaxed">
                        <?= e($videoUtama['deskripsi']) ?>
                    </p>
                </div>

                <div class="pt-4 border-t border-antique-100 flex items-center justify-between">
                    <span class="text-xs text-warm-800/60 font-medium">Saluran Resmi TV Masjid</span>
                    <a href="https://youtube.com/@<?= e($profil['youtube']) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-md transition">
                        <i class="fa-brands fa-youtube"></i>
                        <span>Subscribe</span>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<!-- ==============================================================================
     6. BERITA & WARTA KEGIATAN MASJID
     ============================================================================== -->
<section class="bg-warm-100/60 border-t border-antique-300/40 py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-antique-50 border border-antique-300/50 text-antique-700 text-xs font-semibold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-newspaper text-cypress-700"></i>
                    <span>Warta &amp; Informasi</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 tracking-tight font-classic">
                    Berita &amp; Agenda Kegiatan Terkini
                </h2>
                <p class="text-xs sm:text-sm text-warm-800/70 mt-1 max-w-xl">
                    Ikuti kabar terbaru seputar agenda shalat berjamaah, taklim, dan kegiatan sosial kemasyarakatan.
                </p>
            </div>

            <a href="home/berita.php" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-cypress-700 hover:text-cypress-900 transition underline underline-offset-4 shrink-0">
                <span>Lihat Semua Berita</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php foreach ($beritaTerbaru as $b): ?>
                <article class="bg-white rounded-3xl border border-antique-300/50 shadow-sm hover:shadow-lg transition-luxury overflow-hidden flex flex-col justify-between group">
                    <div>
                        <div class="h-44 bg-cypress-900 relative overflow-hidden flex items-center justify-center p-6 text-center">
                            <?php if (!empty($b['thumbnail'])): ?>
                                <img src="<?= e(upload_url($b['thumbnail'])) ?>" alt="<?= e($b['judul']) ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-cypress-950/85 via-cypress-950/20 to-cypress-950/10"></div>
                            <?php else: ?>
                                <div class="absolute inset-0 pattern-arabesque-dark opacity-30"></div>
                                <i class="fa-solid fa-newspaper text-4xl text-antique-400/40 group-hover:scale-110 transition-transform"></i>
                            <?php endif; ?>
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase bg-cypress-950/80 text-antique-300 border border-antique-500/30 z-10">
                                <?= e($b['kategori']) ?>
                            </span>
                        </div>
                        <div class="p-6 space-y-2.5">
                            <span class="text-[11px] text-warm-800/60 block">
                                <i class="fa-regular fa-calendar mr-1"></i> <?= tanggal_indo($b['tanggal_publikasi']) ?>
                            </span>
                            <h3 class="font-bold text-base text-warm-900 font-classic group-hover:text-cypress-700 transition line-clamp-2">
                                <a href="home/berita-detail.php?id=<?= $b['id'] ?>"><?= e($b['judul']) ?></a>
                            </h3>
                            <p class="text-xs text-warm-800/70 line-clamp-3 leading-relaxed">
                                <?= e($b['ringkasan']) ?>
                            </p>
                        </div>
                    </div>
                    <div class="p-6 pt-0">
                        <a href="home/berita-detail.php?id=<?= $b['id'] ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-cypress-700 hover:text-antique-600 transition">
                            <span>Baca Selengkapnya</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/layouts/public_footer.php'; ?>
