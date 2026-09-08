<?php
// berita-detail.php - Detail Artikel Berita & Kegiatan Masjid
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT b.*, u.nama_lengkap as penulis 
                       FROM berita b 
                       LEFT JOIN users u ON b.penulis_id = u.id 
                       WHERE b.id = ? AND b.status = 'published'");
$stmt->execute([$id]);
$berita = $stmt->fetch();

if (!$berita) {
    header('Location: berita.php');
    exit;
}

// Update views
$pdo->prepare("UPDATE berita SET views = views + 1 WHERE id = ?")->execute([$id]);

$pageTitle = e($berita['judul']) . ' · ' . $profil['nama_masjid'];
$activeNav = 'berita';

// Berita Lainnya
$stmtLain = $pdo->prepare("SELECT id, judul, tanggal_publikasi, kategori FROM berita WHERE id != ? AND status = 'published' ORDER BY tanggal_publikasi DESC LIMIT 4");
$stmtLain->execute([$id]);
$beritaLainnya = $stmtLain->fetchAll();

require_once __DIR__ . '/../layouts/public_header.php';
?>

<!-- Header Breadcrumb -->
<div class="bg-cypress-950 text-white py-12 relative pattern-arabesque-dark border-b border-antique-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-3 relative z-10">
        <div class="flex items-center gap-2 text-xs text-antique-300">
            <a href="../index.php" class="hover:underline">Beranda</a>
            <span>/</span>
            <a href="berita.php" class="hover:underline">Warta &amp; Berita</a>
            <span>/</span>
            <span class="text-stone-300 truncate max-w-xs sm:max-w-md"><?= e($berita['judul']) ?></span>
        </div>
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight font-classic text-white leading-tight">
            <?= e($berita['judul']) ?>
        </h1>
        <div class="flex items-center gap-4 text-xs text-stone-300 pt-1">
            <span class="px-2.5 py-0.5 rounded bg-antique-500/20 text-antique-300 border border-antique-500/30 font-bold uppercase text-[10px]">
                <?= e($berita['kategori']) ?>
            </span>
            <span><i class="fa-regular fa-calendar mr-1"></i> <?= tanggal_indo($berita['tanggal_publikasi'], true) ?></span>
            <span><i class="fa-regular fa-user mr-1"></i> <?= e($berita['penulis'] ?: 'Redaksi Media DKM') ?></span>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        
        <!-- Konten Berita Utama -->
        <article class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-10 border border-antique-300/50 shadow-sm space-y-6">
            
            <?php if (!empty($berita['thumbnail'])): ?>
                <img src="<?= e(upload_url($berita['thumbnail'])) ?>" alt="<?= e($berita['judul']) ?>" class="w-full rounded-2xl object-cover max-h-[420px] border border-antique-200">
            <?php endif; ?>

            <div class="prose prose-stone max-w-none text-warm-900 text-sm sm:text-base leading-relaxed space-y-4">
                <?= $berita['isi'] ?>
            </div>

            <!-- Tombol Bagikan -->
            <div class="pt-6 border-t border-antique-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <span class="text-xs font-semibold text-warm-800/80">Bagikan Warta Kebaikan Ini:</span>
                <div class="flex items-center gap-2">
                    <a href="https://api.whatsapp.com/send?text=<?= urlencode($berita['judul'] . ' - Baca selengkapnya di ' . $profil['nama_masjid'] . ': ' . base_url() . '/home/berita-detail.php?id=' . $berita['id']) ?>" target="_blank" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition flex items-center gap-1.5">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>WhatsApp</span>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(base_url() . '/home/berita-detail.php?id=' . $berita['id']) ?>" target="_blank" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition flex items-center gap-1.5">
                        <i class="fa-brands fa-facebook"></i>
                        <span>Facebook</span>
                    </a>
                </div>
            </div>

        </article>

        <!-- Sidebar Berita Terbaru -->
        <aside class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-4">
                <h4 class="font-classic text-base font-bold text-warm-900 border-b border-antique-200 pb-2">
                    Warta Terbaru Lainnya
                </h4>

                <div class="space-y-3.5">
                    <?php foreach ($beritaLainnya as $bl): ?>
                        <div class="border-b border-antique-100 last:border-0 pb-3 last:pb-0 space-y-1">
                            <span class="text-[10px] text-antique-700 font-semibold uppercase tracking-wider block">
                                <?= e($bl['kategori']) ?> • <?= tanggal_indo($bl['tanggal_publikasi']) ?>
                            </span>
                            <h5 class="text-xs font-bold text-warm-900 hover:text-cypress-700 transition leading-snug">
                                <a href="berita-detail.php?id=<?= $bl['id'] ?>"><?= e($bl['judul']) ?></a>
                            </h5>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Call to Action Donasi -->
            <div class="bg-cypress-900 text-white rounded-3xl p-6 border border-antique-500/40 pattern-arabesque-dark space-y-3">
                <h4 class="font-classic text-base font-bold text-white">Ingin Berkontribusi?</h4>
                <p class="text-xs text-stone-300 leading-relaxed">
                    Salurkan donasi infaq terbaik Anda untuk mendukung seluruh kegiatan dakwah dan program sosial masjid.
                </p>
                <a href="donasi-online.php" class="inline-block w-full text-center py-2.5 px-4 rounded-xl bg-gradient-to-r from-antique-500 to-antique-600 text-cypress-950 font-bold text-xs shadow-md">
                    Infaq Sekarang
                </a>
            </div>
        </aside>

    </div>
</div>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
