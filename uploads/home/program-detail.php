<?php
// program-detail.php - Rincian Lengkap Program Donasi & Riwayat Donatur
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM program_donasi WHERE id = ?");
$stmt->execute([$id]);
$prog = $stmt->fetch();

if (!$prog) {
    header('Location: program.php');
    exit;
}

$pageTitle = e($prog['nama_program']) . ' · ' . $profil['nama_masjid'];
$activeNav = 'program';

$persen = $prog['target_donasi'] > 0 ? min(100, round(($prog['dana_terkumpul'] / $prog['target_donasi']) * 100)) : 100;
$sisaTarget = max(0, (float)$prog['target_donasi'] - (float)$prog['dana_terkumpul']);

// Ambil Donatur Terverifikasi untuk Program Ini
$stmtDonatur = $pdo->prepare("SELECT * FROM donasi_online WHERE program_id = ? AND status = 'diverifikasi' ORDER BY tanggal_donasi DESC, id DESC LIMIT 10");
$stmtDonatur->execute([$id]);
$donaturList = $stmtDonatur->fetchAll();
$totalDonatur = count($donaturList);

require_once __DIR__ . '/../layouts/public_header.php';
?>

<!-- Header Breadcrumb Banner -->
<div class="bg-cypress-950 text-white py-12 relative pattern-arabesque-dark border-b border-antique-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-3 relative z-10">
        <div class="flex items-center gap-2 text-xs text-antique-300">
            <a href="../index.php" class="hover:underline">Beranda</a>
            <span>/</span>
            <a href="program.php" class="hover:underline">Program Donasi</a>
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

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 border-t border-antique-100 text-center">
                    <div class="p-3 rounded-2xl bg-warm-50 border border-antique-200">
                        <span class="text-[10px] uppercase font-bold text-warm-800/60 block">Status Program</span>
                        <span class="text-xs font-bold text-emerald-700 uppercase mt-0.5 block"><?= e($prog['status']) ?></span>
                    </div>
                    <div class="p-3 rounded-2xl bg-warm-50 border border-antique-200">
                        <span class="text-[10px] uppercase font-bold text-warm-800/60 block">Donatur Tercatat</span>
                        <span class="text-xs font-bold text-warm-900 mt-0.5 block"><?= $totalDonatur ?> Donatur</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-warm-50 border border-antique-200 col-span-2 sm:col-span-1">
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
                                    <div class="w-10 h-10 rounded-full bg-cypress-50 border border-cypress-100 flex items-center justify-center text-cypress-700 font-bold text-xs shrink-0">
                                        <?= $d['is_anonim'] ? '?' : strtoupper(substr($d['nama_donatur'], 0, 1)) ?>
                                    </div>
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

                <a href="donasi-online.php?program_id=<?= $prog['id'] ?>" class="w-full text-center block py-3.5 px-4 rounded-xl bg-gradient-to-r from-antique-500 to-antique-600 hover:from-antique-400 hover:to-antique-500 text-cypress-950 font-bold text-sm tracking-wide shadow-lg shadow-antique-500/20 transition-luxury">
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
                <a href="https://api.whatsapp.com/send?text=<?= urlencode('Mari bersama berinfaq untuk program ' . $prog['nama_program'] . ' di ' . $profil['nama_masjid'] . ': ' . base_url() . '/home/program-detail.php?id=' . $prog['id']) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>Share WA</span>
                </a>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
