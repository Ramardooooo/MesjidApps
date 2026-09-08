<?php
// laporan.php - Pusat Laporan Keuangan Masjid
require_once __DIR__ . '/../config/database.php';
cek_role(['admin', 'bendahara']);

$profil = get_profil_masjid();
$user   = $_SESSION['user'];

// Mode Tampilan Laporan: mingguan, bulanan, aktivitas_dana, custom
$tipeLaporan = $_GET['tipe'] ?? 'bulanan';

// Filter Parameter
$bulan   = (int)($_GET['bulan'] ?? date('n'));
$tahun   = (int)($_GET['tahun'] ?? date('Y'));
$minggu  = (int)($_GET['minggu'] ?? 1);
$tglDari = $_GET['tgl_dari'] ?? date('Y-m-01');
$tglSampai = $_GET['tgl_sampai'] ?? date('Y-m-t');

$namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

// Penentuan Range Tanggal Berdasarkan Tipe Laporan
switch ($tipeLaporan) {
    case 'mingguan':
        // Hitung range tanggal minggu ke-N pada bulan terpilih
        $startOfMonth = strtotime(sprintf('%04d-%02d-01', $tahun, $bulan));
        $daysInMonth  = date('t', $startOfMonth);
        
        $startDay = ($minggu - 1) * 7 + 1;
        $endDay   = min($minggu * 7, $daysInMonth);
        
        $tglMulai   = sprintf('%04d-%02d-%02d', $tahun, $bulan, $startDay);
        $tglSelesai = sprintf('%04d-%02d-%02d', $tahun, $bulan, $endDay);
        $judulPeriode = "Pekan ke-{$minggu} ({$startDay} - {$endDay} {$namaBulan[$bulan]} {$tahun})";
        break;

    case 'aktivitas_dana':
        $tglMulai   = sprintf('%04d-%02d-01', $tahun, $bulan);
        $tglSelesai = date('Y-m-t', strtotime($tglMulai));
        $judulPeriode = "Laporan Aktivitas Dana Periode {$namaBulan[$bulan]} {$tahun}";
        break;

    case 'custom':
        $tglMulai   = $tglDari;
        $tglSelesai = $tglSampai;
        $judulPeriode = "Periode " . tanggal_indo($tglMulai) . " s/d " . tanggal_indo($tglSelesai);
        break;

    case 'bulanan':
    default:
        $tipeLaporan = 'bulanan';
        $tglMulai   = sprintf('%04d-%02d-01', $tahun, $bulan);
        $tglSelesai = date('Y-m-t', strtotime($tglMulai));
        $judulPeriode = "Bulan {$namaBulan[$bulan]} {$tahun}";
        break;
}

// 1. Hitung Saldo Awal Periode (Saldo awal master + Semua transaksi sebelum $tglMulai)
$stmtInPrev = $pdo->prepare("SELECT COALESCE(SUM(nominal),0) FROM transaksi_keuangan WHERE jenis = 'pemasukan' AND tanggal_transaksi < ?");
$stmtInPrev->execute([$tglMulai]);
$inPrev = (float)$stmtInPrev->fetchColumn();

$stmtOutPrev = $pdo->prepare("SELECT COALESCE(SUM(nominal),0) FROM transaksi_keuangan WHERE jenis = 'pengeluaran' AND tanggal_transaksi < ?");
$stmtOutPrev->execute([$tglMulai]);
$outPrev = (float)$stmtOutPrev->fetchColumn();

$saldoAwalPeriode = (float)$profil['saldo_awal_kas'] + $inPrev - $outPrev;

// 2. Query Transaksi Dalam Periode Terpilih (dengan saldo berjalan via window function)
$sqlTrx = "SELECT t.*, k.nama_kategori, u.nama_lengkap as pencatat,
                  SUM(CASE WHEN t.jenis = 'pemasukan' THEN t.nominal ELSE -t.nominal END)
                      OVER (ORDER BY t.tanggal_transaksi ASC, t.id ASC) AS saldo_dasar
           FROM transaksi_keuangan t 
           JOIN kategori_transaksi k ON t.kategori_id = k.id 
           LEFT JOIN users u ON t.user_id = u.id 
           WHERE t.tanggal_transaksi BETWEEN ? AND ? 
           ORDER BY t.tanggal_transaksi ASC, t.id ASC";
$paramsTrx = [$tglMulai, $tglSelesai];

// 3. Hitung Pemasukan, Pengeluaran, Saldo Akhir & Surplus/Defisit (via SUM, bukan loop)
$stmtTot = $pdo->prepare("SELECT 
        COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN nominal ELSE 0 END), 0) AS masuk,
        COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN nominal ELSE 0 END), 0) AS keluar
    FROM transaksi_keuangan WHERE tanggal_transaksi BETWEEN ? AND ?");
$stmtTot->execute($paramsTrx);
$rowTot = $stmtTot->fetch();
$totalPemasukanPeriode   = (float)$rowTot['masuk'];
$totalPengeluaranPeriode = (float)$rowTot['keluar'];
$saldoAkhirPeriode = $saldoAwalPeriode + $totalPemasukanPeriode - $totalPengeluaranPeriode;
$surplusDefisit    = $totalPemasukanPeriode - $totalPengeluaranPeriode;

// 4. Rekap Berdasarkan Kategori
$stmtRekapIn = $pdo->prepare("SELECT k.nama_kategori, SUM(t.nominal) as total 
                              FROM transaksi_keuangan t 
                              JOIN kategori_transaksi k ON t.kategori_id = k.id 
                              WHERE t.jenis = 'pemasukan' AND t.tanggal_transaksi BETWEEN ? AND ? 
                              GROUP BY k.id ORDER BY total DESC");
$stmtRekapIn->execute([$tglMulai, $tglSelesai]);
$rekapPemasukan = $stmtRekapIn->fetchAll();

$stmtRekapOut = $pdo->prepare("SELECT k.nama_kategori, SUM(t.nominal) as total 
                               FROM transaksi_keuangan t 
                               JOIN kategori_transaksi k ON t.kategori_id = k.id 
                               WHERE t.jenis = 'pengeluaran' AND t.tanggal_transaksi BETWEEN ? AND ? 
                               GROUP BY k.id ORDER BY total DESC");
$stmtRekapOut->execute([$tglMulai, $tglSelesai]);
$rekapPengeluaran = $stmtRekapOut->fetchAll();

// ==============================================================================
// 5. FITUR EKSPOR EXCEL (CSV STANDAR EXCEL)
// ==============================================================================
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Laporan_Keuangan_' . str_replace(' ', '_', $judulPeriode) . '.csv');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM Excel

    fputcsv($out, [strtoupper($profil['nama_masjid'])]);
    fputcsv($out, ['LAPORAN KEUANGAN KAS: ' . strtoupper($judulPeriode)]);
    fputcsv($out, ['Tanggal Cetak: ' . date('d-m-Y H:i')]);
    fputcsv($out, []);
    fputcsv($out, ['RINGKASAN POSISI KAS']);
    fputcsv($out, ['Saldo Awal Periode', (float)$saldoAwalPeriode]);
    fputcsv($out, ['Total Penerimaan / Pemasukan', (float)$totalPemasukanPeriode]);
    fputcsv($out, ['Total Penyaluran / Pengeluaran', (float)$totalPengeluaranPeriode]);
    fputcsv($out, ['Surplus / (Defisit) Periode', (float)$surplusDefisit]);
    fputcsv($out, ['Saldo Akhir Kas', (float)$saldoAkhirPeriode]);
    fputcsv($out, []);
    fputcsv($out, ['RINCIAN TRANSAKSI']);
    fputcsv($out, ['No', 'Tanggal', 'No. Transaksi', 'Kategori', 'Keterangan', 'Akun Kas', 'Penerimaan / Debit (Rp)', 'Pengeluaran / Kredit (Rp)', 'Saldo Berjalan (Rp)']);

    $no = 1;
    $saldoJalan = $saldoAwalPeriode;
    $stmtExport = $pdo->prepare($sqlTrx);
    $stmtExport->execute($paramsTrx);
    foreach ($stmtExport->fetchAll() as $t) {
        $debit = ($t['jenis'] === 'pemasukan') ? (float)$t['nominal'] : 0;
        $kredit = ($t['jenis'] === 'pengeluaran') ? (float)$t['nominal'] : 0;
        $saldoJalan += ($debit - $kredit);

        fputcsv($out, [
            $no++,
            $t['tanggal_transaksi'],
            $t['no_transaksi'],
            $t['nama_kategori'],
            $t['keterangan'],
            $t['akun_kas'],
            $debit,
            $kredit,
            $saldoJalan
        ]);
    }
    fclose($out);
    exit;
}

// Mode Cetak PDF / Print Ready
$isPrint = isset($_GET['print']) && $_GET['print'] === '1';

if ($isPrint) {
    // Tampilan Formal Print A4 / PDF Export
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Cetak Laporan Keuangan - <?= e($judulPeriode) ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="../assets/css/classic-theme.css">
        <style>
            @media print {
                .no-print { display: none !important; }
                body { background: white !important; font-size: 11pt; }
                @page { size: A4 portrait; margin: 1.5cm; }
            }
        </style>
    </head>
    <body class="bg-white text-stone-900 p-8 antialiased max-w-4xl mx-auto">
        
        <div class="no-print mb-6 flex items-center justify-between pb-4 border-b">
            <button onclick="window.history.back()" class="text-xs font-bold text-stone-600 hover:text-black">
                &larr; Kembali ke Aplikasi
            </button>
            <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-emerald-800 text-white text-xs font-bold shadow">
                Cetak Sekarang / Simpan PDF
            </button>
        </div>

        <!-- Kop Surat Resmi Masjid -->
        <div class="border-b-2 border-stone-800 pb-4 mb-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="font-classic text-xl font-bold text-stone-900 leading-tight"><?= strtoupper(e($profil['nama_masjid'])) ?></h1>
                <p class="text-xs font-semibold uppercase tracking-wider text-stone-700">DEWAN KEMAKMURAN MASJID (DKM) - BIDANG KEUANGAN</p>
                <p class="text-xs text-stone-600"><?= e($profil['alamat']) ?>, <?= e($profil['kota']) ?> • Telp/WA: <?= e($profil['whatsapp']) ?></p>
            </div>
            <div class="text-right text-xs">
                <span class="font-bold block">DOKUMEN KEUANGAN</span>
                <span class="text-stone-500 block">Dicetak: <?= date('d/m/Y H:i') ?></span>
            </div>
        </div>

        <div class="text-center mb-6">
            <h2 class="font-classic text-lg font-bold uppercase tracking-wide">
                LAPORAN KEUANGAN KAS MASJID
            </h2>
            <p class="text-xs font-semibold text-stone-700 uppercase"><?= e($judulPeriode) ?></p>
        </div>

        <!-- Ringkasan Saldo -->
        <div class="grid grid-cols-4 gap-3 mb-6 text-xs text-center border p-3 rounded-lg bg-stone-50">
            <div>
                <span class="text-[10px] text-stone-500 uppercase block">Saldo Awal</span>
                <strong class="text-sm block mt-0.5"><?= format_rupiah($saldoAwalPeriode) ?></strong>
            </div>
            <div>
                <span class="text-[10px] text-stone-500 uppercase block">Total Penerimaan</span>
                <strong class="text-sm text-emerald-800 block mt-0.5"><?= format_rupiah($totalPemasukanPeriode) ?></strong>
            </div>
            <div>
                <span class="text-[10px] text-stone-500 uppercase block">Total Pengeluaran</span>
                <strong class="text-sm text-amber-800 block mt-0.5"><?= format_rupiah($totalPengeluaranPeriode) ?></strong>
            </div>
            <div>
                <span class="text-[10px] text-stone-500 uppercase block">Saldo Akhir</span>
                <strong class="text-sm text-stone-900 block mt-0.5"><?= format_rupiah($saldoAkhirPeriode) ?></strong>
            </div>
        </div>

        <!-- Tabel Rincian Mutasi -->
        <table class="w-full text-left text-xs border border-stone-300 mb-8">
            <thead class="bg-stone-100 uppercase text-[10px] border-b border-stone-300">
                <tr>
                    <th class="p-2 border-r border-stone-300">Tgl / No</th>
                    <th class="p-2 border-r border-stone-300">Kategori &amp; Keterangan</th>
                    <th class="p-2 border-r border-stone-300 text-right">Debit (Rp)</th>
                    <th class="p-2 border-r border-stone-300 text-right">Kredit (Rp)</th>
                    <th class="p-2 text-right">Saldo (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
                <?php 
                $stmtPrint = $pdo->prepare($sqlTrx);
                $stmtPrint->execute($paramsTrx);
                $saldoRun = $saldoAwalPeriode;
                foreach ($stmtPrint->fetchAll() as $tr): 
                    $deb = ($tr['jenis'] === 'pemasukan') ? (float)$tr['nominal'] : 0;
                    $kre = ($tr['jenis'] === 'pengeluaran') ? (float)$tr['nominal'] : 0;
                    $saldoRun += ($deb - $kre);
                ?>
                    <tr>
                        <td class="p-2 align-top border-r border-stone-300 whitespace-nowrap">
                            <span class="font-bold block"><?= tanggal_indo($tr['tanggal_transaksi']) ?></span>
                            <span class="font-mono text-[9px] text-stone-500 block"><?= e($tr['no_transaksi']) ?></span>
                        </td>
                        <td class="p-2 align-top border-r border-stone-300">
                            <strong>[<?= e($tr['nama_kategori']) ?>]</strong> <?= e($tr['keterangan']) ?>
                        </td>
                        <td class="p-2 align-top border-r border-stone-300 text-right whitespace-nowrap">
                            <?= $deb > 0 ? number_format($deb, 0, ',', '.') : '-' ?>
                        </td>
                        <td class="p-2 align-top border-r border-stone-300 text-right whitespace-nowrap">
                            <?= $kre > 0 ? number_format($kre, 0, ',', '.') : '-' ?>
                        </td>
                        <td class="p-2 align-top text-right font-semibold whitespace-nowrap">
                            <?= number_format($saldoRun, 0, ',', '.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Kolom Pengesahan Tanda Tangan -->
        <div class="grid grid-cols-2 text-center text-xs pt-4">
            <div>
                <p>Mengetahui,</p>
                <p class="font-bold mt-1">Ketua Umum DKM</p>
                <div class="h-16"></div>
                <p class="font-bold underline">H. Ahmad Syukron, S.Ag</p>
            </div>
            <div>
                <p><?= e($profil['kota']) ?>, <?= tanggal_indo(date('Y-m-d')) ?></p>
                <p class="font-bold mt-1">Bendahara Umum</p>
                <div class="h-16"></div>
                <p class="font-bold underline">H. Muhammad Ridwan, SE</p>
            </div>
        </div>

    </body>
    </html>
    <?php
    exit;
}

$pageTitle    = 'Laporan Keuangan Masjid · ' . $profil['nama_masjid'];
$activeMenu   = 'laporan';
$pageSubtitle = 'Laporan Mingguan, Bulanan & Aktivitas Dana Kas';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<!-- Header Section & Tombol Cetak/Export -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">
            Pusat Laporan Keuangan Masjid
        </h2>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">
            Menghasilkan laporan mingguan, bulanan, dan perbandingan aktivitas dana (surplus/defisit).
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="laporan.php?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" class="px-3.5 py-2 rounded-xl bg-emerald-800 hover:bg-emerald-900 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-file-excel"></i>
            <span>Export Excel</span>
        </a>
        <a href="laporan.php?<?= http_build_query(array_merge($_GET, ['print' => '1'])) ?>" target="_blank" class="px-4 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-print"></i>
            <span>Cetak Laporan PDF</span>
        </a>
    </div>
</div>

<!-- ==============================================================================
     TABS MODE LAPORAN
     ============================================================================== -->
<div class="bg-white p-3 rounded-2xl border border-antique-300/40 shadow-xs flex items-center gap-2 overflow-x-auto text-xs font-semibold">
    <a href="laporan.php?tipe=bulanan&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= $tipeLaporan === 'bulanan' ? 'bg-cypress-800 text-white shadow-xs' : 'text-warm-800 hover:bg-warm-50' ?>">
        <i class="fa-solid fa-calendar-days mr-1.5"></i> Laporan Bulanan
    </a>
    <a href="laporan.php?tipe=mingguan&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&minggu=<?= $minggu ?>" class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= $tipeLaporan === 'mingguan' ? 'bg-cypress-800 text-white shadow-xs' : 'text-warm-800 hover:bg-warm-50' ?>">
        <i class="fa-solid fa-calendar-week mr-1.5"></i> Laporan Mingguan
    </a>
    <a href="laporan.php?tipe=aktivitas_dana&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= $tipeLaporan === 'aktivitas_dana' ? 'bg-cypress-800 text-white shadow-xs' : 'text-warm-800 hover:bg-warm-50' ?>">
        <i class="fa-solid fa-scale-balanced mr-1.5"></i> Penerimaan &amp; Pengeluaran
    </a>
    <a href="laporan.php?tipe=custom" class="px-4 py-2 rounded-xl transition whitespace-nowrap <?= $tipeLaporan === 'custom' ? 'bg-cypress-800 text-white shadow-xs' : 'text-warm-800 hover:bg-warm-50' ?>">
        <i class="fa-solid fa-sliders mr-1.5"></i> Filter Kustom
    </a>
</div>

<!-- ==============================================================================
     FORM FILTER BERDASARKAN MODE
     ============================================================================== -->
<div class="bg-white p-5 rounded-3xl border border-antique-300/50 shadow-sm">
    <form method="GET" action="laporan.php" class="flex flex-wrap items-center gap-3 text-xs">
        <input type="hidden" name="tipe" value="<?= e($tipeLaporan) ?>">

        <?php if ($tipeLaporan === 'mingguan'): ?>
            <div>
                <label class="block font-bold text-warm-800 mb-1">Pilih Minggu / Pekan</label>
                <select name="minggu" class="px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-semibold focus:outline-none">
                    <?php for ($w = 1; $w <= 5; $w++): ?>
                        <option value="<?= $w ?>" <?= ($minggu == $w) ? 'selected' : '' ?>>Pekan Ke-<?= $w ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if (in_array($tipeLaporan, ['bulanan', 'mingguan', 'aktivitas_dana'])): ?>
            <div>
                <label class="block font-bold text-warm-800 mb-1">Bulan</label>
                <select name="bulan" class="px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-semibold focus:outline-none">
                    <option value="">-- Pilih Bulan --</option>
                    <?php 
                    $bulanList = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    for ($m = 1; $m <= 12; $m++): 
                    ?>
                        <option value="<?= $m ?>" <?= ($bulan == $m) ? 'selected' : '' ?>>
                            <?= $bulanList[$m] ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Tahun</label>
                <select name="tahun" class="px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-semibold focus:outline-none">
                    <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                        <option value="<?= $y ?>" <?= ($tahun == $y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        <?php else: ?>
            <div>
                <label class="block font-bold text-warm-800 mb-1">Dari Tanggal</label>
                <input type="date" name="tgl_dari" value="<?= e($tglDari) ?>" class="px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>
            <div>
                <label class="block font-bold text-warm-800 mb-1">Sampai Tanggal</label>
                <input type="date" name="tgl_sampai" value="<?= e($tglSampai) ?>" class="px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>
        <?php endif; ?>

        <div class="pt-5">
            <button type="submit" class="px-5 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold transition">
                Tampilkan Laporan
            </button>
        </div>
    </form>
</div>

<!-- ==============================================================================
     4 KARTU POSISI KAS PERIODE
     ============================================================================== -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-1">
        <span class="text-[10px] uppercase font-bold text-warm-800/60 block">1. Saldo Awal Periode</span>
        <h3 class="text-2xl font-bold font-classic text-warm-900"><?= format_rupiah($saldoAwalPeriode) ?></h3>
        <span class="text-[10px] text-warm-800/50 block">Posisi awal buku kas</span>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-1">
        <span class="text-[10px] uppercase font-bold text-emerald-700 block">2. Total Penerimaan (Debit)</span>
        <h3 class="text-2xl font-bold font-classic text-emerald-700"><?= format_rupiah($totalPemasukanPeriode) ?></h3>
        <span class="text-[10px] text-emerald-600 block">+ Infaq kotak &amp; transfer</span>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-1">
        <span class="text-[10px] uppercase font-bold text-amber-700 block">3. Total Penyaluran (Kredit)</span>
        <h3 class="text-2xl font-bold font-classic text-amber-700"><?= format_rupiah($totalPengeluaranPeriode) ?></h3>
        <span class="text-[10px] text-amber-600 block">- Operasional &amp; sarana</span>
    </div>

    <div class="bg-cypress-900 text-white rounded-3xl p-6 border border-antique-500/40 shadow-md space-y-1 pattern-arabesque-dark">
        <span class="text-[10px] uppercase font-bold text-antique-300 block">4. Saldo Akhir Kas</span>
        <h3 class="text-2xl font-bold font-classic text-white"><?= format_rupiah($saldoAkhirPeriode) ?></h3>
        <span class="text-[10px] text-antique-300 block">Surplus: <?= format_rupiah($surplusDefisit) ?></span>
    </div>
</div>

<?php if ($tipeLaporan === 'aktivitas_dana'): ?>
    <!-- ==============================================================================
         FORMAT LAPORAN PENERIMAAN & PENGELUARAN / AKTIVITAS DANA
         ============================================================================== -->
    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-antique-300/50 shadow-sm space-y-8">
        <div class="border-b border-antique-200 pb-4 text-center">
            <span class="text-xs uppercase font-bold text-antique-700 tracking-wider">Format Standar Akuntansi Nirlaba Masjid</span>
            <h3 class="font-classic text-xl font-bold text-warm-900 mt-1">
                Laporan Penerimaan dan Pengeluaran (Aktivitas Dana)
            </h3>
            <p class="text-xs text-warm-800/60 mt-0.5"><?= e($judulPeriode) ?></p>
        </div>

        <div class="space-y-6 text-xs sm:text-sm">
            <!-- Bagian Penerimaan -->
            <div>
                <div class="p-3 bg-emerald-50 rounded-xl font-bold text-emerald-900 flex items-center justify-between">
                    <span>A. PENERIMAAN DANA (INFAQ, SEDEKAH, WAKAF)</span>
                    <span>NOMINAL (RP)</span>
                </div>
                <div class="divide-y divide-antique-100 mt-2 px-2">
                    <?php foreach ($rekapPemasukan as $rin): ?>
                        <div class="py-2.5 flex items-center justify-between">
                            <span><?= e($rin['nama_kategori']) ?></span>
                            <span class="font-bold text-emerald-800"><?= format_rupiah($rin['total']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-2 p-3 bg-emerald-100/70 rounded-xl font-bold text-emerald-950 flex items-center justify-between">
                    <span>TOTAL PENERIMAAN (A)</span>
                    <span class="text-base font-classic"><?= format_rupiah($totalPemasukanPeriode) ?></span>
                </div>
            </div>

            <!-- Bagian Pengeluaran -->
            <div>
                <div class="p-3 bg-amber-50 rounded-xl font-bold text-amber-900 flex items-center justify-between">
                    <span>B. PENYALURAN &amp; AKTIVITAS DANA (PENGELUARAN)</span>
                    <span>NOMINAL (RP)</span>
                </div>
                <div class="divide-y divide-antique-100 mt-2 px-2">
                    <?php foreach ($rekapPengeluaran as $rout): ?>
                        <div class="py-2.5 flex items-center justify-between">
                            <span><?= e($rout['nama_kategori']) ?></span>
                            <span class="font-bold text-amber-800"><?= format_rupiah($rout['total']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-2 p-3 bg-amber-100/70 rounded-xl font-bold text-amber-950 flex items-center justify-between">
                    <span>TOTAL PENGELUARAN (B)</span>
                    <span class="text-base font-classic"><?= format_rupiah($totalPengeluaranPeriode) ?></span>
                </div>
            </div>

            <!-- Surplus / Defisit -->
            <div class="p-5 rounded-2xl bg-cypress-900 text-white pattern-arabesque-dark border border-antique-500/40 flex items-center justify-between">
                <div>
                    <span class="font-classic text-base font-bold block">SURPLUS / (DEFISIT) DANA PERIODE (A - B)</span>
                    <span class="text-xs text-antique-300">Hasil bersih penerimaan setelah dikurangi penyaluran</span>
                </div>
                <span class="text-2xl font-bold font-classic text-antique-300">
                    <?= format_rupiah($surplusDefisit) ?>
                </span>
            </div>
        </div>
    </div>

<?php else: ?>

    <!-- ==============================================================================
         REKAPITULASI KATEGORI & TABEL DETAIL TRANSAKSI
         ============================================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Rekap Penerimaan -->
        <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-4">
            <h4 class="font-classic text-sm font-bold text-emerald-800 border-b border-antique-200 pb-2 flex items-center justify-between">
                <span>Rekapitulasi Penerimaan</span>
                <span><?= format_rupiah($totalPemasukanPeriode) ?></span>
            </h4>
            <div class="space-y-2 text-xs">
                <?php foreach ($rekapPemasukan as $rp): ?>
                    <div class="p-2.5 rounded-xl bg-warm-50 border border-antique-100 flex items-center justify-between">
                        <span><?= e($rp['nama_kategori']) ?></span>
                        <strong class="font-classic text-emerald-700"><?= format_rupiah($rp['total']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Rekap Pengeluaran -->
        <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-4">
            <h4 class="font-classic text-sm font-bold text-amber-800 border-b border-antique-200 pb-2 flex items-center justify-between">
                <span>Rekapitulasi Pengeluaran</span>
                <span><?= format_rupiah($totalPengeluaranPeriode) ?></span>
            </h4>
            <div class="space-y-2 text-xs">
                <?php foreach ($rekapPengeluaran as $rq): ?>
                    <div class="p-2.5 rounded-xl bg-warm-50 border border-antique-100 flex items-center justify-between">
                        <span><?= e($rq['nama_kategori']) ?></span>
                        <strong class="font-classic text-amber-700"><?= format_rupiah($rq['total']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- Tabel Rincian Mutasi Transaksi Detail -->
    <?php $pag = paginate_data($pdo, $sqlTrx, $paramsTrx, 10); ?>
    <div class="bg-white rounded-3xl border border-antique-300/50 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-antique-200 flex items-center justify-between">
            <h3 class="font-classic text-base font-bold text-warm-900">
                Detail Transaksi: <?= e($judulPeriode) ?>
            </h3>
            <span class="text-xs text-warm-800/60 font-semibold"><?= number_format($pag['total']) ?> Transaksi</span>
        </div>

        <?php if (empty($pag['items'])): ?>
            <div class="text-center py-12 space-y-2">
                <i class="fa-solid fa-folder-open text-4xl text-stone-300"></i>
                <p class="text-xs text-warm-800/60 font-medium">Tidak ada transaksi pada periode ini.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-antique-200 bg-warm-50/70 text-warm-800/70 uppercase text-[10px] font-bold">
                            <th class="py-3 px-4">Tgl / No. Transaksi</th>
                            <th class="py-3 px-4">Kategori &amp; Keterangan</th>
                            <th class="py-3 px-4">Akun Kas</th>
                            <th class="py-3 px-4 text-right">Debit (Rp)</th>
                            <th class="py-3 px-4 text-right">Kredit (Rp)</th>
                            <th class="py-3 px-4 text-right">Saldo Berjalan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-antique-100">
                        <?php 
                        $saldoAwalHalaman = $saldoAwalPeriode;
                        foreach ($pag['items'] as $tp): 
                            $deb = ($tp['jenis'] === 'pemasukan') ? (float)$tp['nominal'] : 0;
                            $kre = ($tp['jenis'] === 'pengeluaran') ? (float)$tp['nominal'] : 0;
                            $saldoBaris = $saldoAwalPeriode + (float)$tp['saldo_dasar'];
                        ?>
                            <tr class="hover:bg-warm-50/60 transition">
                                <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                    <span class="font-bold text-warm-900 block"><?= tanggal_indo($tp['tanggal_transaksi']) ?></span>
                                    <span class="font-mono text-[10px] text-stone-400 block"><?= e($tp['no_transaksi']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 align-top">
                                    <span class="font-bold text-warm-900 block">[<?= e($tp['nama_kategori']) ?>]</span>
                                    <p class="text-xs text-warm-800/80 leading-relaxed"><?= e($tp['keterangan']) ?></p>
                                </td>
                                <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded bg-warm-100 text-[10px] font-semibold">
                                        <?= e($tp['akun_kas']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                    <?php if ($deb > 0): ?>
                                        <span class="font-bold font-classic text-emerald-700"><?= format_rupiah($deb) ?></span>
                                    <?php else: ?>
                                        <span class="text-stone-300">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                    <?php if ($kre > 0): ?>
                                        <span class="font-bold font-classic text-amber-700"><?= format_rupiah($kre) ?></span>
                                    <?php else: ?>
                                        <span class="text-stone-300">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                    <span class="font-bold font-classic text-cypress-900">
                                        <?= format_rupiah($saldoBaris) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php render_pagination($pag['totalHalaman'], $pag['halaman'], $pag['total'], $pag['dari'], $pag['sampai']); ?>
        <?php endif; ?>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
