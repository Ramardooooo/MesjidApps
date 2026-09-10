<?php
// transparansi.php - Halaman Transparansi Kas Publik Masjid (Scope 8 & 25)
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pageTitle = 'Transparansi Keuangan Kas Masjid · ' . $profil['nama_masjid'];
$activeNav = 'transparansi';

// Filter Periode Bulan & Tahun
$bulanDipilih = (int)($_GET['bulan'] ?? date('n'));
$tahunDipilih = (int)($_GET['tahun'] ?? date('Y'));

// Query Saldo Awal Kumulatif (Saldo Awal Master + Transaksi Publik sebelum bulan berjalan)
$tanggalAwalBulan = sprintf('%04d-%02d-01', $tahunDipilih, $bulanDipilih);
$tanggalAkhirBulan = date('Y-m-t', strtotime($tanggalAwalBulan));

$pemasukanSebelumnya = $pdo->prepare("SELECT COALESCE(SUM(nominal),0) FROM transaksi_keuangan WHERE jenis = 'pemasukan' AND is_published = 1 AND tanggal_transaksi < ?");
$pemasukanSebelumnya->execute([$tanggalAwalBulan]);
$totalInSebelumnya = (float)$pemasukanSebelumnya->fetchColumn();

$pengeluaranSebelumnya = $pdo->prepare("SELECT COALESCE(SUM(nominal),0) FROM transaksi_keuangan WHERE jenis = 'pengeluaran' AND is_published = 1 AND tanggal_transaksi < ?");
$pengeluaranSebelumnya->execute([$tanggalAwalBulan]);
$totalOutSebelumnya = (float)$pengeluaranSebelumnya->fetchColumn();

$saldoAwalPeriode = (float)$profil['saldo_awal_kas'] + $totalInSebelumnya - $totalOutSebelumnya;

// Query Pemasukan & Pengeluaran Bulan Berjalan (Hanya is_published = 1)
$stmtIn = $pdo->prepare("SELECT COALESCE(SUM(nominal),0) FROM transaksi_keuangan WHERE jenis = 'pemasukan' AND is_published = 1 AND tanggal_transaksi BETWEEN ? AND ?");
$stmtIn->execute([$tanggalAwalBulan, $tanggalAkhirBulan]);
$pemasukanBulanIni = (float)$stmtIn->fetchColumn();

$stmtOut = $pdo->prepare("SELECT COALESCE(SUM(nominal),0) FROM transaksi_keuangan WHERE jenis = 'pengeluaran' AND is_published = 1 AND tanggal_transaksi BETWEEN ? AND ?");
$stmtOut->execute([$tanggalAwalBulan, $tanggalAkhirBulan]);
$pengeluaranBulanIni = (float)$stmtOut->fetchColumn();

$saldoAkhirPeriode = $saldoAwalPeriode + $pemasukanBulanIni - $pengeluaranBulanIni;
$surplusDefisit = $pemasukanBulanIni - $pengeluaranBulanIni;

// Query Rincian Mutasi Transaksi Publik Bulan Berjalan
$stmtTrx = $pdo->prepare("SELECT t.*, k.nama_kategori 
                          FROM transaksi_keuangan t 
                          JOIN kategori_transaksi k ON t.kategori_id = k.id 
                          WHERE t.is_published = 1 AND t.tanggal_transaksi BETWEEN ? AND ? 
                          ORDER BY t.tanggal_transaksi DESC, t.id DESC");
$stmtTrx->execute([$tanggalAwalBulan, $tanggalAkhirBulan]);
$daftarTransaksi = $stmtTrx->fetchAll();

// Rekapitulasi per Kategori Publik
$stmtRekap = $pdo->prepare("SELECT k.nama_kategori, t.jenis, SUM(t.nominal) as total 
                            FROM transaksi_keuangan t 
                            JOIN kategori_transaksi k ON t.kategori_id = k.id 
                            WHERE t.is_published = 1 AND t.tanggal_transaksi BETWEEN ? AND ? 
                            GROUP BY k.id, t.jenis 
                            ORDER BY total DESC");
$stmtRekap->execute([$tanggalAwalBulan, $tanggalAkhirBulan]);
$rekapKategori = $stmtRekap->fetchAll();

$namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

require_once __DIR__ . '/../layouts/public_header.php';
?>

<!-- Header Banner -->
<div class="bg-cypress-950 text-white py-14 relative pattern-arabesque-dark border-b border-antique-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3 relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cypress-900 border border-antique-500/40 text-antique-300 text-xs font-semibold uppercase tracking-wider">
            <i class="fa-solid fa-chart-line text-emerald-400"></i>
            <span>Akuntabilitas &amp; Keterbukaan Publik</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight font-classic text-white">
            Laporan Transparansi Keuangan Kas Masjid
        </h1>
        <p class="text-stone-300 text-xs sm:text-sm max-w-xl mx-auto">
            Informasi pembukuan kas yang diverifikasi dan disetujui untuk konsumsi jamaah dan donatur secara transparan.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
    
    <!-- Filter Bar Periode Bulan & Tahun -->
    <div class="bg-white p-6 rounded-2xl border border-antique-300/40 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-classic text-base font-bold text-warm-900">
                Periode Laporan: <?= $namaBulan[$bulanDipilih] ?> <?= $tahunDipilih ?>
            </h3>
            <span class="text-xs text-warm-800/60">Data terhubung langsung dengan Aplikasi Pembukuan DKM</span>
        </div>

        <form method="GET" action="transparansi.php" class="flex items-center gap-2.5 w-full sm:w-auto">
            <select name="bulan" class="px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 text-xs font-semibold focus:ring-2 focus:ring-antique-500 focus:outline-none">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($bulanDipilih === $m) ? 'selected' : '' ?>>
                        <?= $namaBulan[$m] ?>
                    </option>
                <?php endfor; ?>
            </select>

            <select name="tahun" class="px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 text-xs font-semibold focus:ring-2 focus:ring-antique-500 focus:outline-none">
                <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                    <option value="<?= $y ?>" <?= ($tahunDipilih === $y) ? 'selected' : '' ?>>
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white text-xs font-semibold transition">
                Tampilkan
            </button>
        </form>
    </div>

    <!-- 4 Kartu Metrik Transparansi Kas (Scope 8 & 21) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Saldo Awal -->
        <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-2">
            <span class="text-[11px] uppercase font-bold text-warm-800/60 tracking-wider block">Saldo Awal Periode</span>
            <h4 class="text-xl sm:text-2xl font-bold text-warm-900 font-classic">
                <?= format_rupiah($saldoAwalPeriode) ?>
            </h4>
            <span class="text-[11px] text-warm-800/60 block">Posisi per 1 <?= $namaBulan[$bulanDipilih] ?></span>
        </div>

        <!-- Total Pemasukan -->
        <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-2">
            <span class="text-[11px] uppercase font-bold text-emerald-700 tracking-wider block">Total Penerimaan (Infaq)</span>
            <h4 class="text-xl sm:text-2xl font-bold text-emerald-700 font-classic">
                <?= format_rupiah($pemasukanBulanIni) ?>
            </h4>
            <span class="text-[11px] text-emerald-600 block">+ Infaq kotak amal &amp; transfer</span>
        </div>

        <!-- Total Pengeluaran -->
        <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-2">
            <span class="text-[11px] uppercase font-bold text-amber-700 tracking-wider block">Total Penyaluran Kas</span>
            <h4 class="text-xl sm:text-2xl font-bold text-amber-700 font-classic">
                <?= format_rupiah($pengeluaranBulanIni) ?>
            </h4>
            <span class="text-[11px] text-amber-600 block">- Operasional &amp; program</span>
        </div>

        <!-- Saldo Akhir -->
        <div class="bg-cypress-900 text-white rounded-3xl p-6 border border-antique-500/40 shadow-md space-y-2 pattern-arabesque-dark">
            <span class="text-[11px] uppercase font-bold text-antique-300 tracking-wider block">Saldo Kas Saat Ini</span>
            <h4 class="text-xl sm:text-2xl font-bold text-white font-classic">
                <?= format_rupiah($saldoAkhirPeriode) ?>
            </h4>
            <span class="text-[11px] text-antique-300 block">Surplus: <?= format_rupiah($surplusDefisit) ?></span>
        </div>

    </div>

    <!-- Rekapitulasi per Kategori & Mutasi -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Kolom Kiri: Tabel Mutasi Transaksi Publik -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm space-y-6">
            <div class="flex items-center justify-between border-b border-antique-200 pb-3">
                <h3 class="font-classic text-lg font-bold text-warm-900">
                    Rincian Transaksi Publik Bulan Ini
                </h3>
                <span class="text-xs text-warm-800/60"><?= count($daftarTransaksi) ?> Transaksi</span>
            </div>

            <?php if (empty($daftarTransaksi)): ?>
                <p class="text-xs text-warm-800/60 italic py-8 text-center">Belum ada transaksi publik pada periode <?= $namaBulan[$bulanDipilih] ?> <?= $tahunDipilih ?>.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-antique-200 text-warm-800/70 uppercase text-[10px] font-bold">
                                <th class="pb-3 pr-4">Tanggal / No</th>
                                <th class="pb-3 pr-4">Kategori &amp; Keterangan</th>
                                <th class="pb-3 pr-4 text-center">Metode</th>
                                <th class="pb-3 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-antique-100">
                            <?php foreach ($daftarTransaksi as $t): ?>
                                <tr class="hover:bg-warm-50/60 transition">
                                    <td class="py-3 pr-4 align-top">
                                        <span class="font-bold text-warm-900 block"><?= tanggal_indo($t['tanggal_transaksi']) ?></span>
                                        <span class="font-mono text-[10px] text-warm-800/50 block"><?= e($t['no_transaksi']) ?></span>
                                    </td>
                                    <td class="py-3 pr-4 align-top">
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $t['jenis'] === 'pemasukan' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' ?> mb-1">
                                            <?= e($t['nama_kategori']) ?>
                                        </span>
                                        <p class="text-xs text-warm-900 leading-relaxed"><?= e($t['keterangan']) ?></p>
                                    </td>
                                    <td class="py-3 pr-4 align-top text-center">
                                        <span class="text-[10px] uppercase font-semibold text-warm-800/70 bg-warm-100 px-2 py-0.5 rounded">
                                            <?= e($t['metode_pembayaran']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 align-top text-right whitespace-nowrap">
                                        <span class="font-bold text-xs font-classic <?= $t['jenis'] === 'pemasukan' ? 'text-emerald-700' : 'text-amber-700' ?>">
                                            <?= $t['jenis'] === 'pemasukan' ? '+' : '-' ?> <?= format_rupiah($t['nominal']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kolom Kanan: Rekapitulasi Alokasi Kategori -->
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm space-y-6">
            <h3 class="font-classic text-lg font-bold text-warm-900 border-b border-antique-200 pb-3">
                Rekapitulasi Kategori
            </h3>

            <div class="space-y-3">
                <?php if (empty($rekapKategori)): ?>
                    <p class="text-xs text-warm-800/60 italic py-4 text-center">Belum ada data rekap.</p>
                <?php else: ?>
                    <?php foreach ($rekapKategori as $rk): ?>
                        <div class="p-3 rounded-xl bg-warm-50 border border-antique-200 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-warm-900 block"><?= e($rk['nama_kategori']) ?></span>
                                <span class="text-[10px] uppercase font-semibold <?= $rk['jenis'] === 'pemasukan' ? 'text-emerald-700' : 'text-amber-700' ?>">
                                    <?= e($rk['jenis']) ?>
                                </span>
                            </div>
                            <span class="font-bold font-classic <?= $rk['jenis'] === 'pemasukan' ? 'text-emerald-700' : 'text-amber-700' ?>">
                                <?= format_rupiah($rk['total']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="p-4 rounded-2xl bg-cypress-950 text-white pattern-arabesque-dark border border-antique-500/40 text-xs space-y-2">
                <span class="font-bold text-antique-300 block flex items-center gap-1.5">
                    <i class="fa-solid fa-lock"></i> Kebijakan Privasi
                </span>
                <p class="text-[11px] text-stone-300 leading-relaxed">
                    Data transaksi yang bersifat sensitif internal (seperti rincian honor mukafaah petugas atau data vendor pribadi) dijaga kerahasiaannya dan hanya dapat diakses oleh dewan pengurus dan pengawas keuangan DKM.
                </p>
            </div>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
