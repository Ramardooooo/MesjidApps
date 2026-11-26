<?php
// program-detail.php - Rincian Lengkap Program Donasi & Riwayat Donatur
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();

// Slug-based URL: /program/nama-slug  ATAU  ?id=X (backward compat)
$_requestUri = $_SERVER['REQUEST_URI'] ?? '';
$prog = null;

if (preg_match('#/program/([a-z0-9\-]+)#i', $_requestUri, $_m)) {
    $_slug = $_m[1];
    $stmt  = $pdo->prepare("SELECT * FROM program_donasi WHERE slug = ?");
    $stmt->execute([$_slug]);
    $prog  = $stmt->fetch();
} elseif (!empty($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM program_donasi WHERE id = ?");
    $stmt->execute([$id]);
    $prog = $stmt->fetch();
    // Redirect ke slug URL jika program ditemukan
    if ($prog) {
        header('Location: ' . base_url() . '/program/' . $prog['slug'], true, 301);
        exit;
    }
}

if (!$prog) {
    header('Location: program');
    exit;
}

$pageTitle = e($prog['nama_program']) . ' · ' . $profil['nama_masjid'];
$activeNav = 'program';

$persen = $prog['target_donasi'] > 0 ? min(100, round(($prog['dana_terkumpul'] / $prog['target_donasi']) * 100)) : 100;
$sisaTarget = max(0, (float)$prog['target_donasi'] - (float)$prog['dana_terkumpul']);

// Ambil Donatur Terverifikasi untuk Program Ini
        $stmtDonatur = $pdo->prepare("SELECT d.*, u.avatar FROM donasi_online d LEFT JOIN users u ON d.user_id = u.id WHERE d.program_id = ? AND d.status = 'diverifikasi' ORDER BY d.tanggal_donasi DESC, d.id DESC LIMIT 10");
$stmtDonatur->execute([$prog['id']]);
$id = (int)$prog['id']; // backward compat untuk sisa kode di bawah
$donaturList = $stmtDonatur->fetchAll();
$totalDonatur = count($donaturList);

// Ambil Kabar Penyaluran Dana (Milestones)
$stmtUpdates = $pdo->prepare("SELECT * FROM program_updates WHERE program_id = ? ORDER BY tanggal_update DESC, id DESC");
$stmtUpdates->execute([$prog['id']]);
$updatesList = $stmtUpdates->fetchAll();

$totalDisalurkan = 0;
foreach ($updatesList as $u) {
    $totalDisalurkan += (float)$u['dana_disalurkan'];
}
$sisaKasProgram = max(0, (float)$prog['dana_terkumpul'] - $totalDisalurkan);

require_once __DIR__ . '/../layouts/public_header.php';
?>

<!-- Header Breadcrumb Banner -->
<div class="bg-cypress-950 text-white py-12 relative pattern-arabesque-dark border-b border-antique-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-3 relative z-10">
        <div class="flex items-center gap-2 text-xs text-antique-300">
            <a href="<?= base_url() ?>/" class="hover:underline">Beranda</a>
            <span>/</span>
            <a href="<?= base_url() ?>/program" class="hover:underline">Program Donasi</a>
            <span>/</span>
            <span class="text-stone-300 truncate max-w-xs sm:max-w-md"><?= e($prog['nama_program']) ?></span>
        </div>
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight font-classic text-white">
            <?= e($prog['nama_program']) ?>
        </h1>
        <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
            <span class="px-2.5 py-1 rounded bg-antique-500/20 text-antique-300 border border-antique-500/30 uppercase font-bold text-[10px]">
                <?= e($prog['kategori']) ?>
            </span>
            <span class="text-stone-300">Periode: <?= tanggal_indo($prog['tanggal_mulai']) ?> s/d <?= $prog['tanggal_selesai'] ? tanggal_indo($prog['tanggal_selesai']) : 'Tenggat Fleksibel' ?></span>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        
        <!-- Kolom Kiri: Detail & Deskripsi Lengkap -->
        <div class="lg:col-span-8 space-y-8">
            
            <?php if (!empty($prog['gambar'])): ?>
                <div class="rounded-3xl overflow-hidden border border-antique-300/50 shadow-sm">
                    <img src="<?= e(upload_url($prog['gambar'])) ?>" alt="<?= e($prog['nama_program']) ?>" class="w-full h-64 sm:h-80 object-cover">
                </div>
            <?php endif; ?>

            <!-- Hero Card Informasi Dana -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm space-y-6">
                <div>
                    <div class="flex items-baseline justify-between mb-2">
                        <div>
                            <span class="text-xs text-warm-800/60 block font-medium">Dana Terkumpul</span>
                            <span class="text-2xl sm:text-3xl font-bold text-cypress-700 font-classic">
                                <?= format_rupiah($prog['dana_terkumpul']) ?>
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-warm-800/60 block font-medium">Target Kebutuhan</span>
                            <span class="text-lg sm:text-xl font-bold text-warm-900 font-classic">
                                <?= format_rupiah($prog['target_donasi']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full h-3.5 rounded-full bg-warm-100 overflow-hidden relative">
                        <div class="h-full bg-gradient-to-r from-antique-500 via-emerald-600 to-cypress-700 rounded-full transition-all duration-700" style="width: <?= $persen ?>%"></div>
                    </div>

                    <div class="flex items-center justify-between text-xs font-semibold text-warm-800/70 pt-2">
                        <span>Pencapaian: <strong class="text-cypress-700"><?= $persen ?>%</strong></span>
                        <span>Sisa Kebutuhan: <strong class="text-antique-700"><?= format_rupiah($sisaTarget) ?></strong></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 pt-4 border-t border-antique-100 text-center">
                    <div class="p-3 rounded-2xl bg-warm-50 border border-antique-200">
                        <span class="text-[10px] uppercase font-bold text-warm-800/60 block">Status Program</span>
                        <span class="text-xs font-bold text-emerald-700 uppercase mt-0.5 block"><?= e($prog['status']) ?></span>
                    </div>
                    <div class="p-3 rounded-2xl bg-warm-50 border border-antique-200">
                        <span class="text-[10px] uppercase font-bold text-warm-800/60 block">Donatur Tercatat</span>
                        <span class="text-xs font-bold text-warm-900 mt-0.5 block"><?= $totalDonatur ?> Donatur</span>
                    </div>
                    <a href="#kabar-penyaluran" class="p-3 rounded-2xl bg-emerald-50/70 hover:bg-emerald-100/80 border border-emerald-200 text-emerald-900 transition block">
                        <span class="text-[10px] uppercase font-bold text-emerald-800/70 block">Penyaluran</span>
                        <span class="text-xs font-bold text-emerald-900 mt-0.5 block"><?= count($updatesList) ?> Kabar</span>
                    </a>
                    <div class="p-3 rounded-2xl bg-warm-50 border border-antique-200">
                        <span class="text-[10px] uppercase font-bold text-warm-800/60 block">Pengelola</span>
                        <span class="text-xs font-bold text-warm-900 mt-0.5 block">DKM Nurul Iman</span>
                    </div>
                </div>
            </div>

            <!-- Rincian Deskripsi Program -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm space-y-4">
                <h3 class="text-xl font-bold text-warm-900 font-classic border-b border-antique-200 pb-3">
                    Latar Belakang &amp; Tujuan Program
                </h3>
                
                <div class="prose max-w-none text-warm-800/80 text-sm leading-relaxed space-y-4">
                    <p class="font-medium text-base text-warm-900"><?= e($prog['deskripsi']) ?></p>
                    <?php if (!empty($prog['deskripsi_lengkap'])): ?>
                        <div class="pt-2">
                            <?= nl2br(e($prog['deskripsi_lengkap'])) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Jaminan Penyaluran Amanah -->
                <div class="mt-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-start gap-3 text-xs text-emerald-900">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg mt-0.5 shrink-0"></i>
                    <div>
                        <span class="font-bold block mb-0.5">Komitmen Transparansi DKM</span>
                        <p class="leading-relaxed">Setiap donasi yang masuk diverifikasi oleh bendahara dan langsung dibukukan ke Laporan Kas Masjid yang dapat dipantau di halaman transparansi publik.</p>
                    </div>
                </div>
            </div>

            <!-- KABAR PENYALURAN DANA & PROGRES TERKINI (MILESTONE TRACKER) -->
            <div id="kabar-penyaluran" class="bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm space-y-6 scroll-mt-24">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-antique-200 pb-4">
                    <div>
                        <div class="flex items-center gap-2 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-1">
                            <i class="fa-solid fa-bullhorn text-antique-500"></i>
                            <span>Transparansi Penyaluran</span>
                        </div>
                        <h3 class="text-xl font-bold text-warm-900 font-classic">
                            Kabar Terbaru &amp; Penyaluran Dana
                        </h3>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                        <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-stone-50 border border-antique-200 text-stone-800">
                            <i class="fa-solid fa-hand-holding-dollar text-emerald-600 text-sm"></i>
                            <div class="text-left">
                                <div class="text-[9px] uppercase font-bold text-stone-500">Tersalurkan</div>
                                <div class="text-xs sm:text-sm font-extrabold text-stone-900 font-classic"><?= format_rupiah($totalDisalurkan) ?></div>
                            </div>
                        </div>
                        <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                            <i class="fa-solid fa-wallet text-emerald-600 text-sm"></i>
                            <div class="text-left">
                                <div class="text-[9px] uppercase font-bold text-emerald-800/70">Sisa Kas Siap Salur</div>
                                <div class="text-xs sm:text-sm font-extrabold text-emerald-900 font-classic"><?= format_rupiah($sisaKasProgram) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (empty($updatesList)): ?>
                    <div class="text-center py-10 px-4 rounded-2xl bg-warm-50/60 border border-antique-200/70 space-y-2">
                        <div class="w-12 h-12 rounded-full bg-antique-100 text-antique-700 flex items-center justify-center mx-auto text-xl">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>
                        <h4 class="text-sm font-bold text-warm-900">Dana Sedang Dihimpun</h4>
                        <p class="text-xs text-warm-800/70 max-w-md mx-auto leading-relaxed">
                            Belum ada penyaluran dana yang dipublikasikan untuk program ini. Pengurus DKM akan mengunggah rincian pembelian, kuitansi belanja, dan foto dokumentasi kegiatan secara berkala di sini.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="relative pl-6 sm:pl-8 space-y-8 before:absolute before:left-2 sm:before:left-3 before:top-3 before:bottom-3 before:w-0.5 before:bg-antique-200">
                        <?php foreach ($updatesList as $uIdx => $u): ?>
                            <div class="relative group">
                                <div class="absolute -left-6 sm:-left-8 top-1 w-5 h-5 rounded-full bg-cypress-800 border-2 border-white shadow-xs flex items-center justify-center text-[10px] font-bold text-antique-300">
                                    <i class="fa-solid fa-check text-[8px]"></i>
                                </div>

                                <div class="bg-warm-50/70 rounded-2xl p-5 border border-antique-200/70 shadow-2xs space-y-3 transition hover:bg-warm-50">
                                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-antique-200/50 pb-2.5">
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="px-2.5 py-0.5 rounded-full bg-cypress-950 text-antique-300 text-[10px] font-bold uppercase tracking-wider">
                                                Update #<?= count($updatesList) - $uIdx ?>
                                            </span>
                                            <span class="text-stone-500 font-medium">
                                                <i class="fa-regular fa-calendar mr-1"></i> <?= tanggal_indo($u['tanggal_update'], true) ?>
                                            </span>
                                        </div>

                                        <?php if ((float)$u['dana_disalurkan'] > 0): ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-100 text-emerald-900 text-xs font-bold">
                                                <i class="fa-solid fa-coins text-emerald-600"></i>
                                                <span>Pencairan Dana: <?= format_rupiah($u['dana_disalurkan']) ?></span>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <h4 class="text-base font-bold text-warm-900 font-classic">
                                        <?= e($u['judul']) ?>
                                    </h4>

                                    <div class="text-xs sm:text-sm text-warm-800/80 leading-relaxed space-y-2">
                                        <?= nl2br(e($u['isi'])) ?>
                                    </div>

                                    <?php if (!empty($u['foto'])): ?>
                                        <div class="pt-2">
                                            <a href="<?= e(upload_url($u['foto'])) ?>" target="_blank" class="block relative group overflow-hidden rounded-xl border border-antique-300/50 max-w-md">
                                                <img src="<?= e(upload_url($u['foto'])) ?>" alt="Dokumentasi Penyaluran" class="w-full h-48 sm:h-56 object-cover group-hover:scale-105 transition duration-300">
                                                <div class="absolute inset-0 bg-cypress-950/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1.5">
                                                    <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
                                                    <span>Buka Foto Dokumentasi</span>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- List Donatur Terkini -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-antique-200 pb-3">
                    <h3 class="text-xl font-bold text-warm-900 font-classic">
                        Sahabat Kebaikan (Donatur Terkini)
                    </h3>
                    <span class="text-xs text-warm-800/60 font-semibold"><?= count($donaturList) ?> Donasi Terbaru</span>
                </div>

                <?php if (empty($donaturList)): ?>
                    <p class="text-xs text-warm-800/60 italic py-4 text-center">Belum ada catatan donasi online yang terverifikasi untuk program ini. Jadilah donatur pertama!</p>
                <?php else: ?>
                    <div class="divide-y divide-antique-100">
                        <?php foreach ($donaturList as $d): ?>
                            <div class="py-3.5 flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <?php
$avatarPath = trim((string)($d['avatar'] ?? ''));
$avatarUrl = $avatarPath !== '' ? upload_url($avatarPath) : '';
?>
<?php if ($avatarUrl): ?>
    <img src="<?= e($avatarUrl) ?>" alt="<?= e($d['nama_donatur']) ?>" class="w-10 h-10 rounded-full object-cover border border-cypress-100 shrink-0" draggable="false">
<?php else: ?>
    <div class="w-10 h-10 rounded-full bg-cypress-50 border border-cypress-100 flex items-center justify-center text-cypress-700 font-bold text-xs shrink-0">
        <?= $d['is_anonim'] ? '?' : strtoupper(substr($d['nama_donatur'], 0, 1)) ?>
    </div>
<?php endif; ?>
                                    <div>
                                        <h5 class="text-xs font-bold text-warm-900">
                                            <?= $d['is_anonim'] ? 'Hamba Allah (Anonim)' : e($d['nama_donatur']) ?>
                                        </h5>
                                        <span class="text-[11px] text-warm-800/50 block"><?= tanggal_indo($d['tanggal_donasi']) ?></span>
                                        <?php if (!empty($d['doa_donatur'])): ?>
                                            <p class="text-xs italic text-antique-800 bg-antique-50 p-2 rounded-lg mt-1 border border-antique-200">
                                                "<?= e($d['doa_donatur']) ?>"
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="font-bold text-xs text-cypress-700 shrink-0 font-classic">
                                    <?= format_rupiah($d['nominal']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Kolom Kanan: Card Aksi Donasi Sticky -->
        <div class="lg:col-span-4 sticky top-28 space-y-6">
            <div class="bg-cypress-900 text-white rounded-3xl p-6 sm:p-8 border border-antique-500/40 shadow-xl pattern-arabesque-dark space-y-6">
                <div>
                    <span class="text-[11px] uppercase font-bold text-antique-300 tracking-wider block mb-1">
                        Salurkan Kebaikan
                    </span>
                    <h3 class="text-xl font-bold font-classic text-white">
                        Infaq / Wakaf untuk Program Ini
                    </h3>
                    <p class="text-xs text-stone-300 mt-1">
                        Pilih metode donasi QRIS instan atau Transfer Bank resmi masjid.
                    </p>
                </div>

                <a href="<?= base_url() ?>/donasi-online/<?= e($prog['slug']) ?>" class="w-full text-center block py-3.5 px-4 rounded-xl bg-gradient-to-r from-antique-500 to-antique-600 hover:from-antique-400 hover:to-antique-500 text-cypress-950 font-bold text-sm tracking-wide shadow-lg shadow-antique-500/20 transition-luxury">
                    <i class="fa-solid fa-hand-holding-heart mr-2"></i> Donasi Sekarang
                </a>

                <div class="pt-4 border-t border-white/10 space-y-2 text-xs text-stone-300">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-qrcode text-antique-400"></i>
                        <span>Scan QRIS langsung dari HP</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-antique-400"></i>
                        <span>Dapatkan e-Kwitansi resmi tanda terima</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-antique-400"></i>
                        <span>100% tersalurkan secara amanah</span>
                    </div>
                </div>
            </div>

            <!-- Tombol Bagikan -->
            <div class="bg-white rounded-2xl p-4 border border-antique-300/40 shadow-xs flex items-center justify-between">
                <span class="text-xs font-semibold text-warm-800/80">Ajak Sahabat Berinfaq:</span>
                <a href="https://api.whatsapp.com/send?text=<?= urlencode('Mari bersama berinfaq untuk program ' . $prog['nama_program'] . ' di ' . $profil['nama_masjid'] . ': ' . base_url() . '/program/' . $prog['slug']) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>Share WA</span>
                </a>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
