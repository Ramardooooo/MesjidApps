<?php
// berita.php - Kabar & Warta Kegiatan Masjid (Scope 15)
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pageTitle = 'Berita & Warta Kegiatan · ' . $profil['nama_masjid'];
$activeNav = 'berita';

$kategori = trim($_GET['kategori'] ?? '');
$search   = trim($_GET['q'] ?? '');

$sql = "SELECT b.*, u.nama_lengkap as penulis 
        FROM berita b 
        LEFT JOIN users u ON b.penulis_id = u.id 
        WHERE b.status = 'published'";
$params = [];

if ($kategori !== '') {
    $sql .= " AND b.kategori = ?";
    $params[] = $kategori;
}

if ($search !== '') {
    $sql .= " AND (b.judul LIKE ? OR b.isi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY b.tanggal_publikasi DESC, b.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftarBerita = $stmt->fetchAll();

require_once __DIR__ . '/../layouts/public_header.php';
?>

<!-- Header Banner -->
<div class="bg-cypress-950 text-white py-14 relative pattern-arabesque-dark border-b border-antique-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3 relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cypress-900 border border-antique-500/40 text-antique-300 text-xs font-semibold uppercase tracking-wider">
            <i class="fa-solid fa-newspaper text-emerald-400"></i>
            <span>Warta &amp; Publikasi</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight font-classic text-white">
            Kabar &amp; Warta Kegiatan Masjid
        </h1>
        <p class="text-stone-300 text-xs sm:text-sm max-w-xl mx-auto">
            Informasi terkini seputar agenda shalat, taklim berkala, program sosial, dan pemberdayaan ummat.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
    
    <!-- Filter & Search Bar -->
    <div class="bg-white p-6 rounded-2xl border border-antique-300/40 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        
        <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0 text-xs font-semibold">
            <a href="berita.php" class="px-3.5 py-2 rounded-xl whitespace-nowrap transition <?= $kategori === '' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
                Semua Kategori
            </a>
            <a href="berita.php?kategori=<?= urlencode('Kajian & Kegiatan') ?>" class="px-3.5 py-2 rounded-xl whitespace-nowrap transition <?= $kategori === 'Kajian & Kegiatan' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
                Kajian &amp; Kegiatan
            </a>
            <a href="berita.php?kategori=<?= urlencode('Sosial Ummat') ?>" class="px-3.5 py-2 rounded-xl whitespace-nowrap transition <?= $kategori === 'Sosial Ummat' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
                Sosial Ummat
            </a>
            <a href="berita.php?kategori=<?= urlencode('Jadwal Kajian') ?>" class="px-3.5 py-2 rounded-xl whitespace-nowrap transition <?= $kategori === 'Jadwal Kajian' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
                Jadwal Kajian
            </a>
        </div>

        <form method="GET" action="berita.php" class="w-full md:w-72 flex items-center gap-2">
            <div class="relative w-full">
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari artikel berita..." class="w-full pl-9 pr-3 py-2 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:ring-2 focus:ring-antique-500 focus:outline-none">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-stone-400 text-xs"></i>
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white text-xs font-semibold">
                Cari
            </button>
        </form>

    </div>

    <!-- Grid Berita -->
    <?php if (empty($daftarBerita)): ?>
        <div class="text-center py-16 bg-white rounded-3xl border border-antique-300/40 p-8 space-y-3">
            <i class="fa-solid fa-newspaper text-4xl text-stone-300"></i>
            <h3 class="text-lg font-bold text-warm-900 font-classic">Tidak Ada Berita Sesuai Kriteria</h3>
            <p class="text-xs text-warm-800/60">Silakan kembali ke daftar lengkap berita.</p>
            <a href="berita.php" class="inline-block mt-2 px-4 py-2 rounded-xl bg-cypress-700 text-white text-xs font-semibold">Reset Filter</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php foreach ($daftarBerita as $b): ?>
                <article class="bg-white rounded-3xl border border-antique-300/50 shadow-sm hover:shadow-xl transition-luxury overflow-hidden flex flex-col justify-between group">
                    <div>
                        <div class="h-48 bg-cypress-900 relative overflow-hidden flex items-center justify-center p-6 text-center">
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
                                <a href="berita-detail.php?id=<?= $b['id'] ?>"><?= e($b['judul']) ?></a>
                            </h3>
                            <p class="text-xs text-warm-800/70 line-clamp-3 leading-relaxed">
                                <?= e($b['ringkasan']) ?>
                            </p>
                        </div>
                    </div>
                    <div class="p-6 pt-0">
                        <a href="berita-detail.php?id=<?= $b['id'] ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-cypress-700 hover:text-antique-600 transition">
                            <span>Baca Selengkapnya</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
