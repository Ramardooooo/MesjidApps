<?php
// program.php - Katalog Program & Donasi Masjid
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pageTitle = 'Katalog Program Donasi & Wakaf · ' . $profil['nama_masjid'];
$activeNav = 'program';

// Filter Kategori & Status
$filterKategori = trim($_GET['kategori'] ?? '');
$filterStatus   = trim($_GET['status'] ?? 'semua');
$search         = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM program_donasi WHERE 1=1";
$params = [];

if ($filterKategori !== '') {
    $sql .= " AND kategori = ?";
    $params[] = $filterKategori;
}

if ($filterStatus !== 'semua' && in_array($filterStatus, ['aktif', 'selesai', 'terpenuhi'])) {
    $sql .= " AND status = ?";
    $params[] = $filterStatus;
}

if ($search !== '') {
    $sql .= " AND (nama_program LIKE ? OR deskripsi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY (status = 'aktif') DESC, is_featured DESC, id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$programs = $stmt->fetchAll();

require_once __DIR__ . '/../layouts/public_header.php';
?>

<!-- Header Banner Halaman -->
<div class="bg-cypress-950 text-white py-14 relative pattern-arabesque-dark border-b border-antique-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3 relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cypress-900 border border-antique-500/40 text-antique-300 text-xs font-semibold uppercase tracking-wider">
            <i class="fa-solid fa-hand-holding-dollar text-emerald-400"></i>
            <span>Peluang Amal Jariyah</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight font-classic text-white">
            Katalog Program Donasi &amp; Pembangunan
        </h1>
        <p class="text-stone-300 text-xs sm:text-sm max-w-xl mx-auto">
            Salurkan infaq, sedekah, dan wakaf terbaik Anda untuk memakmurkan rumah Allah dan menebar kemaslahatan ummat.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
    
    <!-- Filter & Pencarian Bar -->
    <div class="bg-white p-6 rounded-2xl border border-antique-300/40 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        
        <!-- Tab Kategori -->
        <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0 text-xs font-semibold">
            <a href="program.php" class="px-3.5 py-2 rounded-xl whitespace-nowrap transition <?= $filterKategori === '' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
                Semua Program
            </a>
            <a href="program.php?kategori=sosial" class="px-3.5 py-2 rounded-xl whitespace-nowrap transition <?= $filterKategori === 'sosial' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
                Sosial &amp; Yatim
            </a>
            <a href="program.php?kategori=keagamaan" class="px-3.5 py-2 rounded-xl whitespace-nowrap transition <?= $filterKategori === 'keagamaan' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
                Dakwah &amp; Al-Qur'an
            </a>
            <a href="program.php?kategori=operasional" class="px-3.5 py-2 rounded-xl whitespace-nowrap transition <?= $filterKategori === 'operasional' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
                Fisik &amp; Operasional
            </a>
            <a href="program.php?kategori=pendidikan" class="px-3.5 py-2 rounded-xl whitespace-nowrap transition <?= $filterKategori === 'pendidikan' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
                Pendidikan
            </a>
        </div>

        <!-- Form Cari -->
        <form method="GET" action="program.php" class="w-full md:w-72 flex items-center gap-2">
            <?php if ($filterKategori): ?>
                <input type="hidden" name="kategori" value="<?= e($filterKategori) ?>">
            <?php endif; ?>
            <div class="relative w-full">
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama program..." class="w-full pl-9 pr-3 py-2 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:ring-2 focus:ring-antique-500 focus:outline-none">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-stone-400 text-xs"></i>
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white text-xs font-semibold">
                Cari
            </button>
        </form>

    </div>

    <!-- Grid Program -->
    <?php if (empty($programs)): ?>
        <div class="text-center py-16 bg-white rounded-3xl border border-antique-300/40 p-8 space-y-3">
            <i class="fa-solid fa-folder-open text-4xl text-stone-300"></i>
            <h3 class="text-lg font-bold text-warm-900 font-classic">Belum Ada Program Sesuai Pencarian</h3>
            <p class="text-xs text-warm-800/60 max-w-sm mx-auto">Silakan coba dengan kata kunci lain atau pilih kategori Semua Program.</p>
            <a href="program.php" class="inline-block mt-2 px-4 py-2 rounded-xl bg-cypress-700 text-white text-xs font-semibold">Reset Filter</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php foreach ($programs as $prog): 
                $persen = $prog['target_donasi'] > 0 ? min(100, round(($prog['dana_terkumpul'] / $prog['target_donasi']) * 100)) : 100;
            ?>
                <div class="bg-white rounded-3xl border border-antique-300/50 shadow-sm hover:shadow-xl transition-luxury overflow-hidden flex flex-col justify-between group">
                    <div>
                        <!-- Header Card -->
                        <div class="h-48 bg-gradient-to-br from-cypress-900 to-cypress-950 relative overflow-hidden flex items-center justify-center p-6 text-center">
                            <?php if (!empty($prog['gambar'])): ?>
                                <img src="<?= e(upload_url($prog['gambar'])) ?>" alt="<?= e($prog['nama_program']) ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-cypress-950/90 via-cypress-950/40 to-cypress-950/20"></div>
                            <?php else: ?>
                                <div class="absolute inset-0 pattern-arabesque-dark opacity-40"></div>
                            <?php endif; ?>
                            
                            <!-- Badges Status -->
                            <div class="absolute top-3 left-3 flex items-center gap-1.5 z-10">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase bg-cypress-950/90 text-antique-300 border border-antique-500/40">
                                    <?= strtoupper(e($prog['kategori'])) ?>
                                </span>
                                <?php if ($prog['status'] === 'terpenuhi'): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-600 text-white">
                                        Terpenuhi
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="relative z-10">
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

                            <!-- Progress Bar -->
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

                    <!-- Footer Action -->
                    <div class="p-6 pt-0">
                        <div class="pt-4 border-t border-antique-100 flex items-center gap-3">
                            <a href="program-detail.php?id=<?= $prog['id'] ?>" class="flex-1 text-center py-2.5 px-3 rounded-xl bg-warm-50 hover:bg-warm-100 border border-antique-300/50 text-warm-800 text-xs font-semibold transition">
                                Rincian
                            </a>
                            <?php if ($prog['status'] === 'aktif'): ?>
                                <a href="donasi-online.php?program_id=<?= $prog['id'] ?>" class="flex-1 text-center py-2.5 px-3 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white text-xs font-bold shadow-md shadow-cypress-900/10 transition">
                                    Donasi Sekarang
                                </a>
                            <?php else: ?>
                                <span class="flex-1 text-center py-2.5 px-3 rounded-xl bg-stone-100 text-stone-500 text-xs font-medium">
                                    Selesai
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
