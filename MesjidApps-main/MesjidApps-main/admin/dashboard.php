<?php
// dashboard.php - Dashboard Keuangan & Eksekutif Terpadu (Scope 22 & 23)
require_once __DIR__ . '/../config/database.php';
cek_login();

$user = $_SESSION['user'];
if ($user['role'] === 'donatur') {
    header('Location: ../donatur/portal-donatur.php');
    exit;
}

$profil = get_profil_masjid();

// Konfigurasi Filter Periode (Scope 23: Hari ini, Minggu ini, Bulan ini, Custom date range)
$periode = $_GET['periode'] ?? 'bulan_ini';
$startDate = $_GET['start_date'] ?? '';
$endDate   = $_GET['end_date'] ?? '';

$tglMulai = '';
$tglSelesai = '';
$labelPeriode = '';

switch ($periode) {
    case 'hari_ini':
        $tglMulai = date('Y-m-d');
        $tglSelesai = date('Y-m-d');
        $labelPeriode = 'Hari Ini (' . tanggal_indo($tglMulai) . ')';
        break;
    case 'minggu_ini':
        $tglMulai = date('Y-m-d', strtotime('monday this week'));
        $tglSelesai = date('Y-m-d', strtotime('sunday this week'));
        $labelPeriode = 'Minggu Ini (' . tanggal_indo($tglMulai) . ' s/d ' . tanggal_indo($tglSelesai) . ')';
        break;
    case 'custom':
        if (!empty($startDate) && !empty($endDate)) {
            $tglMulai = $startDate;
            $tglSelesai = $endDate;
            $labelPeriode = tanggal_indo($tglMulai) . ' s/d ' . tanggal_indo($tglSelesai);
        } else {
            $tglMulai = date('Y-m-01');
            $tglSelesai = date('Y-m-t');
            $labelPeriode = 'Bulan Ini (' . date('F Y') . ')';
        }
        break;
    case 'bulan_ini':
    default:
        $periode = 'bulan_ini';
        $tglMulai = date('Y-m-01');
        $tglSelesai = date('Y-m-t');
        $labelPeriode = 'Bulan Ini (' . tanggal_indo(date('Y-m-01')) . ' s/d ' . tanggal_indo(date('Y-m-t')) . ')';
        break;
}

// 1. Total Saldo Kas Saat Ini (Kumulatif Sepanjang Masa)
$totalInAll  = (float)$pdo->query("SELECT COALESCE(SUM(nominal),0) FROM transaksi_keuangan WHERE jenis = 'pemasukan'")->fetchColumn();
$totalOutAll = (float)$pdo->query("SELECT COALESCE(SUM(nominal),0) FROM transaksi_keuangan WHERE jenis = 'pengeluaran'")->fetchColumn();
$saldoKasSaatIni = (float)$profil['saldo_awal_kas'] + $totalInAll - $totalOutAll;

// 2. Statistik Periode Terpilih
$stmtIn = $pdo->prepare("SELECT COALESCE(SUM(nominal),0) AS total, COUNT(*) AS jml FROM transaksi_keuangan WHERE jenis = 'pemasukan' AND tanggal_transaksi BETWEEN ? AND ?");
$stmtIn->execute([$tglMulai, $tglSelesai]);
$statIn = $stmtIn->fetch();

$stmtOut = $pdo->prepare("SELECT COALESCE(SUM(nominal),0) AS total, COUNT(*) AS jml FROM transaksi_keuangan WHERE jenis = 'pengeluaran' AND tanggal_transaksi BETWEEN ? AND ?");
$stmtOut->execute([$tglMulai, $tglSelesai]);
$statOut = $stmtOut->fetch();

$totalPemasukanPeriode = (float)$statIn['total'];
$totalPengeluaranPeriode = (float)$statOut['total'];
$surplusDefisitPeriode = $totalPemasukanPeriode - $totalPengeluaranPeriode;
$totalJumlahTransaksi = (int)$statIn['jml'] + (int)$statOut['jml'];

// 3. Donasi Online Masuk Menunggu Approval
$countPendingDonasi = (int)$pdo->query("SELECT COUNT(*) FROM donasi_online WHERE status = 'pending'")->fetchColumn();
$donasiPending = $pdo->query("SELECT d.*, p.nama_program FROM donasi_online d JOIN program_donasi p ON d.program_id = p.id WHERE d.status = 'pending' ORDER BY d.id DESC LIMIT 5")->fetchAll();

// 4. 5 Transaksi Kas Terbaru
$transaksiTerbaru = $pdo->query("SELECT t.*, k.nama_kategori FROM transaksi_keuangan t JOIN kategori_transaksi k ON t.kategori_id = k.id ORDER BY t.tanggal_transaksi DESC, t.id DESC LIMIT 5")->fetchAll();

// 5. Data Grafik 6 Bulan Terakhir
$chartLabels = [];
$chartPemasukan = [];
$chartPengeluaran = [];

for ($i = 5; $i >= 0; $i--) {
    $m = date('n', strtotime("-$i month"));
    $y = date('Y', strtotime("-$i month"));
    $namaBln = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][$m] . ' ' . $y;
    $chartLabels[] = $namaBln;

    $startDateMonth = sprintf('%04d-%02d-01', $y, $m);
    $endDateMonth = date('Y-m-t', strtotime($startDateMonth));

    $inMonth = $pdo->prepare("SELECT COALESCE(SUM(nominal),0) FROM transaksi_keuangan WHERE jenis = 'pemasukan' AND tanggal_transaksi BETWEEN ? AND ?");
    $inMonth->execute([$startDateMonth, $endDateMonth]);
    $chartPemasukan[] = (float)$inMonth->fetchColumn();

    $outMonth = $pdo->prepare("SELECT COALESCE(SUM(nominal),0) FROM transaksi_keuangan WHERE jenis = 'pengeluaran' AND tanggal_transaksi BETWEEN ? AND ?");
    $outMonth->execute([$startDateMonth, $endDateMonth]);
    $chartPengeluaran[] = (float)$outMonth->fetchColumn();
}

// Konfigurasi Tampilan
$pageTitle    = 'Dashboard Keuangan & Eksekutif · ' . $profil['nama_masjid'];
$activeMenu   = 'dashboard';
$pageSubtitle = 'Dashboard Monitoring Kas & Pembukuan Keuangan';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<!-- Banner Sambutan Klasik -->
<div class="bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm relative overflow-hidden">
    <div class="absolute -right-10 -bottom-10 w-48 h-48 text-antique-500/10 pointer-events-none">
        <i class="fa-solid fa-mosque text-9xl"></i>
    </div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-antique-50 border border-antique-300/50 text-antique-800 text-xs font-semibold uppercase tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                <span>Portal Resmi Pengurus DKM</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-warm-900 tracking-tight font-classic">
                Assalamu’alaikum, <span class="text-cypress-700"><?= e($user['nama']) ?></span>
            </h1>
            <p class="text-xs sm:text-sm text-warm-800/70 mt-1 max-w-2xl leading-relaxed">
                Anda masuk sebagai <strong class="text-cypress-800 uppercase"><?= e(str_replace('_', ' ', $user['role'])) ?></strong>. Pantau kondisi kas masjid, mutasi transaksi keuangan, dan verifikasi amanah donasi jamaah secara transparan.
            </p>
        </div>

        <!-- Tombol Aksi Cepat -->
        <div class="shrink-0 flex flex-wrap items-center gap-2.5">
            <?php if (in_array($user['role'], ['admin', 'bendahara'])): ?>
                <a href="transaksi.php?action=tambah_masuk" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs shadow-sm transition">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>Catat Kas Masuk</span>
                </a>
                <a href="transaksi.php?action=tambah_keluar" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-700 hover:bg-amber-800 text-white font-bold text-xs shadow-sm transition">
                    <i class="fa-solid fa-minus-circle"></i>
                    <span>Catat Pengeluaran</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==============================================================================
     FILTER PERIODE DASHBOARD (Scope 23)
     ============================================================================== -->
<div class="bg-white p-4 sm:p-5 rounded-2xl border border-antique-300/40 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <div class="flex items-center gap-2">
        <i class="fa-regular fa-calendar-check text-antique-600"></i>
        <span class="text-xs font-semibold text-warm-900">Periode Aktif: <strong><?= e($labelPeriode) ?></strong></span>
    </div>

    <!-- Filter Buttons & Form -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="dashboard.php?periode=hari_ini" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition <?= $periode === 'hari_ini' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
            Hari Ini
        </a>
        <a href="dashboard.php?periode=minggu_ini" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition <?= $periode === 'minggu_ini' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
            Minggu Ini
        </a>
        <a href="dashboard.php?periode=bulan_ini" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition <?= $periode === 'bulan_ini' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
            Bulan Ini
        </a>

        <!-- Custom Date Range Form -->
        <form method="GET" action="dashboard.php" class="flex items-center gap-1.5 pl-2 border-l border-antique-200">
            <input type="hidden" name="periode" value="custom">
            <input type="date" name="start_date" value="<?= e($startDate ?: $tglMulai) ?>" class="px-2 py-1 rounded-lg bg-warm-50 border border-antique-300 text-[11px] focus:ring-1 focus:ring-antique-500">
            <span class="text-xs text-stone-400">-</span>
            <input type="date" name="end_date" value="<?= e($endDate ?: $tglSelesai) ?>" class="px-2 py-1 rounded-lg bg-warm-50 border border-antique-300 text-[11px] focus:ring-1 focus:ring-antique-500">
            <button type="submit" class="px-2.5 py-1 rounded-lg bg-cypress-700 hover:bg-cypress-800 text-white text-[11px] font-semibold transition">
                Filter
            </button>
        </form>
    </div>
</div>

<!-- ==============================================================================
     KARTU INDIKATOR KEUANGAN (Scope 22)
     ============================================================================== -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    
    <!-- 1. Saldo Kas Saat Ini -->
    <div class="bg-cypress-900 text-white rounded-3xl p-6 border border-antique-500/40 shadow-md pattern-arabesque-dark space-y-2 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-bold text-antique-300 tracking-wider">Saldo Kas Saat Ini</span>
                <i class="fa-solid fa-vault text-antique-400"></i>
            </div>
            <h3 class="text-2xl font-bold font-classic mt-2 text-white">
                <?= format_rupiah($saldoKasSaatIni) ?>
            </h3>
        </div>
        <div class="pt-2 border-t border-white/10 text-[11px] text-stone-300 flex items-center justify-between">
            <span>Saldo Riil Kumulatif</span>
            <span class="text-antique-300 font-semibold">Semua Akun</span>
        </div>
    </div>

    <!-- 2. Pemasukan Periode -->
    <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-2 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-bold text-emerald-700 tracking-wider">Pemasukan Kas (Debit)</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-arrow-down"></i>
                </span>
            </div>
            <h3 class="text-2xl font-bold font-classic mt-2 text-emerald-700">
                <?= format_rupiah($totalPemasukanPeriode) ?>
            </h3>
        </div>
        <div class="pt-2 border-t border-antique-100 text-[11px] text-warm-800/60 flex items-center justify-between">
            <span><?= (int)$statIn['jml'] ?> Transaksi Masuk</span>
            <span class="font-semibold text-emerald-700">Periode Aktif</span>
        </div>
    </div>

    <!-- 3. Pengeluaran Periode -->
    <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-2 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-bold text-amber-700 tracking-wider">Pengeluaran Kas (Kredit)</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-arrow-up"></i>
                </span>
            </div>
            <h3 class="text-2xl font-bold font-classic mt-2 text-amber-700">
                <?= format_rupiah($totalPengeluaranPeriode) ?>
            </h3>
        </div>
        <div class="pt-2 border-t border-antique-100 text-[11px] text-warm-800/60 flex items-center justify-between">
            <span><?= (int)$statOut['jml'] ?> Transaksi Keluar</span>
            <span class="font-semibold text-amber-700">Periode Aktif</span>
        </div>
    </div>

    <!-- 4. Surplus / Defisit Periode -->
    <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-2 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-bold text-warm-800/60 tracking-wider">Surplus / (Defisit)</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-scale-balanced"></i>
                </span>
            </div>
            <h3 class="text-2xl font-bold font-classic mt-2 <?= $surplusDefisitPeriode >= 0 ? 'text-emerald-700' : 'text-red-600' ?>">
                <?= format_rupiah($surplusDefisitPeriode) ?>
            </h3>
        </div>
        <div class="pt-2 border-t border-antique-100 text-[11px] text-warm-800/60 flex items-center justify-between">
            <span>Total Mutasi</span>
            <span class="font-semibold text-warm-900"><?= $totalJumlahTransaksi ?> Transaksi</span>
        </div>
    </div>

</div>

<!-- ==============================================================================
     GRAFIK TREN KEUANGAN PEMASUKAN VS PENGELUARAN (Chart.js)
     ============================================================================== -->
<div class="bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-antique-200 pb-4">
        <div>
            <h3 class="font-classic text-lg font-bold text-warm-900">
                Grafik Tren Pemasukan &amp; Pengeluaran Kas (6 Bulan Terakhir)
            </h3>
            <p class="text-xs text-warm-800/60 mt-0.5">Perbandingan arus kas masuk dan penyaluran operasional/program masjid.</p>
        </div>
        <div class="flex items-center gap-4 text-xs font-semibold">
            <span class="flex items-center gap-1.5 text-emerald-700">
                <span class="w-3 h-3 rounded-full bg-emerald-600"></span> Pemasukan
            </span>
            <span class="flex items-center gap-1.5 text-amber-700">
                <span class="w-3 h-3 rounded-full bg-amber-500"></span> Pengeluaran
            </span>
        </div>
    </div>

    <div class="w-full aspect-[21/9] max-h-72">
        <canvas id="trenKeuanganChart"></canvas>
    </div>
</div>

<!-- ==============================================================================
     GRID DUA KOLOM: TRANSAKSI TERBARU & DONASI PENDING
     ============================================================================== -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    
    <!-- Kolom Kiri: 5 Transaksi Kas Terbaru -->
    <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm space-y-5">
        <div class="flex items-center justify-between border-b border-antique-200 pb-3">
            <h3 class="font-classic text-base font-bold text-warm-900">
                Transaksi Kas Terbaru
            </h3>
            <a href="transaksi.php" class="text-xs text-cypress-700 hover:text-cypress-900 font-semibold underline">
                Buka Buku Kas &rarr;
            </a>
        </div>

        <?php if (empty($transaksiTerbaru)): ?>
            <p class="text-xs text-warm-800/60 italic py-6 text-center">Belum ada mutasi transaksi.</p>
        <?php else: ?>
            <div class="divide-y divide-antique-100">
                <?php foreach ($transaksiTerbaru as $t): ?>
                    <div class="py-3 flex items-center justify-between gap-3 text-xs">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-warm-900"><?= e($t['nama_kategori']) ?></span>
                                <span class="text-[9px] px-1.5 py-0.2 rounded font-bold uppercase <?= $t['is_published'] ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-stone-100 text-stone-600' ?>">
                                    <?= $t['is_published'] ? 'Publik' : 'Internal' ?>
                                </span>
                            </div>
                            <span class="text-[11px] text-warm-800/60 line-clamp-1"><?= e($t['keterangan']) ?></span>
                            <span class="text-[10px] text-stone-400 font-mono"><?= tanggal_indo($t['tanggal_transaksi']) ?> • <?= e($t['no_transaksi']) ?></span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="font-bold font-classic text-xs <?= $t['jenis'] === 'pemasukan' ? 'text-emerald-700' : 'text-amber-700' ?>">
                                <?= $t['jenis'] === 'pemasukan' ? '+' : '-' ?> <?= format_rupiah($t['nominal']) ?>
                            </span>
                            <span class="text-[10px] text-warm-800/50 block capitalize"><?= e($t['metode_pembayaran']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Kolom Kanan: Donasi Online Menunggu Approval -->
    <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm space-y-5">
        <div class="flex items-center justify-between border-b border-antique-200 pb-3">
            <div class="flex items-center gap-2">
                <h3 class="font-classic text-base font-bold text-warm-900">Donasi Menunggu</h3>
                <?php if ($countPendingDonasi > 0): ?>
                    <span class="px-2 py-0.5 rounded-full bg-amber-500 text-cypress-950 font-bold text-[10px]">
                        <?= $countPendingDonasi ?>
                    </span>
                <?php endif; ?>
            </div>
            <a href="verifikasi-donasi.php" class="text-xs text-cypress-700 hover:text-cypress-900 font-semibold underline">
                Verifikasi &rarr;
            </a>
        </div>

        <?php if (empty($donasiPending)): ?>
            <div class="text-center py-6 space-y-2">
                <i class="fa-solid fa-circle-check text-3xl text-emerald-500"></i>
                <p class="text-xs text-warm-800/70 font-semibold">Semua donasi online telah diverifikasi!</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($donasiPending as $dp): ?>
                    <div class="p-3 rounded-2xl bg-warm-50 border border-antique-200 flex items-center justify-between text-xs gap-3">
                        <div>
                            <span class="font-bold text-warm-900 block"><?= $dp['is_anonim'] ? 'Hamba Allah' : e($dp['nama_donatur']) ?></span>
                            <span class="text-[10px] text-warm-800/60 line-clamp-1"><?= e($dp['nama_program']) ?></span>
                            <span class="text-[10px] text-antique-700 font-mono"><?= e($dp['no_donasi']) ?></span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="font-bold text-cypress-800 font-classic block"><?= format_rupiah($dp['nominal']) ?></span>
                            <a href="verifikasi-donasi.php" class="text-[10px] font-bold text-emerald-700 hover:underline">
                                Periksa &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Script Chart.js -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('trenKeuanganChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [
                {
                    label: 'Pemasukan (Infaq)',
                    data: <?= json_encode($chartPemasukan) ?>,
                    backgroundColor: 'rgba(35, 75, 56, 0.85)',
                    borderColor: '#1b3a2b',
                    borderWidth: 1,
                    borderRadius: 8,
                },
                {
                    label: 'Pengeluaran (Kredit)',
                    data: <?= json_encode($chartPengeluaran) ?>,
                    backgroundColor: 'rgba(217, 119, 6, 0.85)',
                    borderColor: '#b45309',
                    borderWidth: 1,
                    borderRadius: 8,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) {
                            return 'Rp ' + val.toLocaleString('id-ID');
                        },
                        font: { size: 10 }
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    ticks: { font: { size: 11, weight: 'bold' } },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>