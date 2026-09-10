<?php
// profil.php - Profil Masjid, Sejarah Singkat, Visi Misi, Struktur DKM, dan Lokasi
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pageTitle = 'Profil & Struktur DKM · ' . $profil['nama_masjid'];
$activeNav = 'profil';

// Ambil Struktur Pengurus DKM
$pengurus = $pdo->query("SELECT * FROM pengurus_masjid ORDER BY urutan ASC, id ASC")->fetchAll();

require_once __DIR__ . '/../layouts/public_header.php';
?>

<!-- Header Banner Halaman -->
<div class="bg-cypress-950 text-white py-14 relative pattern-arabesque-dark border-b border-antique-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3 relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cypress-900 border border-antique-500/40 text-antique-300 text-xs font-semibold uppercase tracking-wider">
            <i class="fa-solid fa-mosque text-emerald-400"></i>
            <span>Mengenal Lebih Dekat</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight font-classic text-white">
            Profil &amp; Kepengurusan DKM
        </h1>
        <p class="text-stone-300 text-xs sm:text-sm max-w-xl mx-auto">
            <?= e($profil['nama_masjid']) ?> — <?= e($profil['slogan']) ?>
        </p>
    </div>
</div>

<!-- Konten Utama Profil -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
    
    <!-- Bagian 1: Tentang & Sejarah Singkat -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
        <div class="lg:col-span-7 space-y-5">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-antique-50 border border-antique-300/50 text-antique-700 text-xs font-semibold uppercase tracking-wider">
                <i class="fa-solid fa-landmark text-cypress-700"></i>
                <span>Kilasan Sejarah</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 tracking-tight font-classic">
                Sejarah Singkat <?= e($profil['nama_masjid']) ?>
            </h2>
            <div class="prose prose-sm text-warm-800/80 leading-relaxed space-y-4">
                <p><?= nl2br(e($profil['sejarah'])) ?></p>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4">
                <div class="p-4 rounded-2xl bg-white border border-antique-300/40 shadow-xs">
                    <span class="text-xs text-warm-800/60 block">Tahun Berdiri</span>
                    <span class="text-xl font-bold text-cypress-700 font-classic">1982 M</span>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-antique-300/40 shadow-xs">
                    <span class="text-xs text-warm-800/60 block">Luas Tanah Wakaf</span>
                    <span class="text-xl font-bold text-cypress-700 font-classic">± 3.200 m²</span>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-antique-300/40 shadow-xs col-span-2 sm:col-span-1">
                    <span class="text-xs text-warm-800/60 block">Status Legal</span>
                    <span class="text-sm font-bold text-cypress-700">Kemenag RI</span>
                </div>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="rounded-3xl bg-cypress-900 border-2 border-antique-500/40 p-6 text-white shadow-2xl pattern-arabesque-dark relative overflow-hidden">
                <div class="text-center py-8 space-y-3">
                    <div class="w-20 h-20 mx-auto rounded-3xl bg-cypress-950 border border-antique-500/50 flex items-center justify-center text-antique-400 shadow-inner">
                        <i class="fa-solid fa-kaaba text-4xl"></i>
                    </div>
                    <h3 class="font-classic text-xl font-bold text-white"><?= e($profil['nama_masjid']) ?></h3>
                    <p class="text-xs text-antique-300 max-w-xs mx-auto"><?= e($profil['sebutan']) ?></p>
                    <div class="pt-4 border-t border-white/10 text-xs text-stone-300">
                        <p><i class="fa-solid fa-location-dot text-antique-500 mr-1.5"></i> <?= e($profil['alamat']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian 2: Visi & Misi -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white rounded-3xl p-8 border border-antique-300/50 shadow-sm space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-antique-50 border border-antique-300/40 flex items-center justify-center text-antique-700">
                <i class="fa-solid fa-eye text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-warm-900 font-classic">Visi Masjid</h3>
            <p class="text-sm text-warm-800/80 leading-relaxed">
                <?= nl2br(e($profil['visi'])) ?>
            </p>
        </div>

        <div class="bg-white rounded-3xl p-8 border border-antique-300/50 shadow-sm space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-cypress-50 border border-cypress-100 flex items-center justify-center text-cypress-700">
                <i class="fa-solid fa-bullseye text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-warm-900 font-classic">Misi Utama</h3>
            <div class="text-sm text-warm-800/80 leading-relaxed whitespace-pre-line">
                <?= e($profil['misi']) ?>
            </div>
        </div>
    </div>

    <!-- Bagian 3: Struktur Pengurus DKM -->
    <div class="space-y-8">
        <div class="text-center max-w-2xl mx-auto space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-antique-50 border border-antique-300/50 text-antique-700 text-xs font-semibold uppercase tracking-wider">
                <i class="fa-solid fa-users text-cypress-700"></i>
                <span>Dewan Kemakmuran Masjid</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">
                Susunan Pengurus DKM Periode 2024–2028
            </h2>
            <p class="text-xs sm:text-sm text-warm-800/70">
                Para asatidz dan pengurus yang diamanahi mengemban pelayanan jamaah dan memakmurkan kegiatan masjid.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($pengurus as $p): ?>
                <div class="bg-white rounded-2xl p-6 border border-antique-300/40 shadow-xs hover:shadow-md transition flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-cypress-900 border border-antique-500/40 flex items-center justify-center text-antique-300 font-bold text-lg shrink-0">
                        <?= strtoupper(substr($p['nama'], 0, 1)) ?>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-antique-700 bg-antique-50 px-2 py-0.5 rounded border border-antique-200 block w-fit mb-1">
                            <?= e($p['jabatan']) ?>
                        </span>
                        <h4 class="font-bold text-sm text-warm-900 leading-snug"><?= e($p['nama']) ?></h4>
                        <span class="text-xs text-warm-800/60 block mt-0.5"><?= e($p['bidang'] ?: 'Pengurus Harian') ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bagian 4: Peta Lokasi Google Maps -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-bold text-warm-900 font-classic">Lokasi &amp; Akses Masjid</h3>
                <p class="text-xs sm:text-sm text-warm-800/70 mt-1"><?= e($profil['alamat']) ?>, <?= e($profil['kota']) ?></p>
            </div>
            <a href="https://maps.google.com/?q=<?= urlencode($profil['nama_masjid'] . ' ' . $profil['alamat']) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white text-xs font-semibold transition shrink-0">
                <i class="fa-solid fa-diamond-turn-right"></i>
                <span>Petunjuk Arah Google Maps</span>
            </a>
        </div>

        <div class="rounded-2xl overflow-hidden border border-antique-200 aspect-video sm:aspect-[21/9] w-full">
            <iframe class="w-full h-full" 
                    src="<?= e($profil['google_maps_embed']) ?>" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
