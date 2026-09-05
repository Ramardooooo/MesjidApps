<?php
// kajian.php - Integrasi Video Kajian & Live Streaming YouTube (Scope 10)
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pageTitle = 'Video Kajian & Live Streaming · ' . $profil['nama_masjid'];
$activeNav = 'kajian';

$kategori = trim($_GET['kategori'] ?? '');

$sql = "SELECT * FROM youtube_videos WHERE is_active = 1";
$params = [];
if ($kategori !== '' && in_array($kategori, ['live_streaming', 'kajian', 'dokumentasi', 'profil'])) {
    $sql .= " AND kategori = ?";
    $params[] = $kategori;
}
$sql .= " ORDER BY id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$videos = $stmt->fetchAll();

require_once __DIR__ . '/../layouts/public_header.php';
?>

<!-- Header Banner -->
<div class="bg-cypress-950 text-white py-14 relative pattern-arabesque-dark border-b border-antique-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3 relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-950/80 border border-red-500/40 text-red-300 text-xs font-semibold uppercase tracking-wider">
            <i class="fa-brands fa-youtube text-red-500"></i>
            <span>Saluran Resmi TV Masjid</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight font-classic text-white">
            Video Kajian &amp; Siaran Langsung
        </h1>
        <p class="text-stone-300 text-xs sm:text-sm max-w-xl mx-auto">
            Saksikan rekaman majelis taklim, khutbah Jumat, live streaming, dan dokumentasi syiar Islam.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
    
    <!-- Filter Kategori Video -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 text-xs font-semibold">
        <a href="kajian.php" class="px-4 py-2 rounded-xl whitespace-nowrap transition <?= $kategori === '' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-white text-warm-800 border border-antique-200 hover:bg-warm-50' ?>">
            Semua Video
        </a>
        <a href="kajian.php?kategori=live_streaming" class="px-4 py-2 rounded-xl whitespace-nowrap transition <?= $kategori === 'live_streaming' ? 'bg-red-700 text-white shadow-xs' : 'bg-white text-warm-800 border border-antique-200 hover:bg-warm-50' ?>">
            <i class="fa-solid fa-broadcast-tower mr-1.5"></i> Live Streaming
        </a>
        <a href="kajian.php?kategori=kajian" class="px-4 py-2 rounded-xl whitespace-nowrap transition <?= $kategori === 'kajian' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-white text-warm-800 border border-antique-200 hover:bg-warm-50' ?>">
            Kajian Tematik
        </a>
        <a href="kajian.php?kategori=dokumentasi" class="px-4 py-2 rounded-xl whitespace-nowrap transition <?= $kategori === 'dokumentasi' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-white text-warm-800 border border-antique-200 hover:bg-warm-50' ?>">
            Dokumentasi Kegiatan
        </a>
    </div>

    <!-- Grid Video YouTube Embed -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
        <?php foreach ($videos as $v): ?>
            <div class="bg-white rounded-3xl border border-antique-300/50 shadow-sm hover:shadow-lg transition-luxury overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="relative aspect-video bg-black">
                        <iframe class="w-full h-full" 
                                src="<?= e($v['url_embed']) ?>" 
                                title="<?= e($v['judul']) ?>" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                        </iframe>
                    </div>
                    <div class="p-6 space-y-2">
                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded <?= $v['kategori'] === 'live_streaming' ? 'bg-red-100 text-red-800' : 'bg-cypress-50 text-cypress-800' ?>">
                            <?= str_replace('_', ' ', strtoupper(e($v['kategori']))) ?>
                        </span>
                        <h3 class="font-bold text-base text-warm-900 font-classic line-clamp-2">
                            <?= e($v['judul']) ?>
                        </h3>
                        <p class="text-xs text-warm-800/70 line-clamp-2 leading-relaxed">
                            <?= e($v['deskripsi']) ?>
                        </p>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <a href="https://youtube.com/watch?v=<?= e($v['video_id']) ?>" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 hover:text-red-700 transition">
                        <i class="fa-brands fa-youtube"></i>
                        <span>Tonton di Aplikasi YouTube</span>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
