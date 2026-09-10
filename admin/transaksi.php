<?php
// transaksi.php - Buku Kas & Pencatatan Transaksi Keuangan (Scope 17, 18, 25)
require_once __DIR__ . '/../config/database.php';
cek_role(['admin', 'bendahara']);

$user = $_SESSION['user'];
$profil = get_profil_masjid();
$pesan = '';
$tipe = '';

// ==============================================================================
// 1. EKSPOR CSV FILTER AKTIF
// ==============================================================================
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Buku_Kas_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM Excel

    fputcsv($output, ['No', 'No. Transaksi', 'Tanggal', 'Jenis', 'Kategori', 'Akun Kas', 'Keterangan', 'Metode', 'Debit / Pemasukan (Rp)', 'Kredit / Pengeluaran (Rp)', 'Status Publikasi']);

    $sqlExport = "SELECT t.*, k.nama_kategori 
                  FROM transaksi_keuangan t 
                  JOIN kategori_transaksi k ON t.kategori_id = k.id 
                  ORDER BY t.tanggal_transaksi DESC, t.id DESC";
    $rows = $pdo->query($sqlExport)->fetchAll();

    $no = 1;
    foreach ($rows as $r) {
        $debit = ($r['jenis'] === 'pemasukan') ? (float)$r['nominal'] : 0;
        $kredit = ($r['jenis'] === 'pengeluaran') ? (float)$r['nominal'] : 0;
        $statusPublikasi = $r['is_published'] ? 'Publik / Transparansi' : 'Internal / Private';

        fputcsv($output, [
            $no++,
            $r['no_transaksi'],
            $r['tanggal_transaksi'],
            ucfirst($r['jenis']),
            $r['nama_kategori'],
            $r['akun_kas'],
            $r['keterangan'],
            strtoupper($r['metode_pembayaran']),
            $debit,
            $kredit,
            $statusPublikasi
        ]);
    }
    fclose($output);
    exit;
}

// ==============================================================================
// 2. TOGGLE STATUS PUBLIKASI 1-KLIK (Scope 25: Integrasi Website dan Laporan)
// ==============================================================================
if (isset($_GET['toggle_publish'])) {
    $idToggle = (int)$_GET['toggle_publish'];
    $stmtToggle = $pdo->prepare("UPDATE transaksi_keuangan SET is_published = 1 - is_published WHERE id = ?");
    $stmtToggle->execute([$idToggle]);
    header('Location: transaksi.php?msg=toggled');
    exit;
}

// ==============================================================================
// 3. TAMBAH TRANSAKSI BARU (CREATE)
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $jenis        = $_POST['jenis'] ?? 'pemasukan';
    $tanggal      = $_POST['tanggal_transaksi'] ?? date('Y-m-d');
    $kategori_id  = (int)($_POST['kategori_id'] ?? 0);
    $program_id   = !empty($_POST['program_id']) ? (int)$_POST['program_id'] : null;
    $akun_kas     = trim($_POST['akun_kas'] ?? 'Kas Tunai Utama');
    $nominal      = (float)str_replace(['Rp', '.', ' ', ','], '', $_POST['nominal'] ?? '0');
    $metode       = $_POST['metode_pembayaran'] ?? 'tunai';
    $keterangan   = trim($_POST['keterangan'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    if ($kategori_id <= 0) {
        $pesan = 'Silakan pilih kategori transaksi.';
        $tipe  = 'error';
    } elseif ($nominal <= 0) {
        $pesan = 'Nominal transaksi harus lebih dari Rp 0.';
        $tipe  = 'error';
    } elseif (empty($keterangan)) {
        $pesan = 'Keterangan transaksi wajib diisi.';
        $tipe  = 'error';
    } else {
        // Upload Bukti Transaksi
        $buktiPath = null;
        if (isset($_FILES['bukti_transaksi']) && $_FILES['bukti_transaksi']['error'] === UPLOAD_ERR_OK) {
            $buktiPath = upload_berkas('bukti_transaksi', 'bukti');
        }

        // Auto-generate No Transaksi
        $prefix = ($jenis === 'pemasukan') ? 'TRX-IN' : 'TRX-OUT';
        $ym = date('Ym', strtotime($tanggal));
        $last = $pdo->query("SELECT no_transaksi FROM transaksi_keuangan WHERE no_transaksi LIKE '{$prefix}-{$ym}-%' ORDER BY id DESC LIMIT 1")->fetch();
        if ($last) {
            $num = (int)substr($last['no_transaksi'], -4);
            $next = str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $next = '0001';
        }
        $noTrx = "{$prefix}-{$ym}-{$next}";

        $stmt = $pdo->prepare("INSERT INTO transaksi_keuangan (
            no_transaksi, tanggal_transaksi, jenis, kategori_id, program_id, akun_kas, 
            nominal, keterangan, bukti_transaksi, user_id, metode_pembayaran, is_published
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $noTrx, $tanggal, $jenis, $kategori_id, $program_id, $akun_kas,
            $nominal, $keterangan, $buktiPath, $user['id'], $metode, $is_published
        ]);

        // Jika pemasukan dialokasikan ke program, perbarui nominal dana terkumpul program
        if ($jenis === 'pemasukan' && $program_id) {
            $pdo->prepare("UPDATE program_donasi SET dana_terkumpul = dana_terkumpul + ? WHERE id = ?")->execute([$nominal, $program_id]);
        }

        $pesan = "Transaksi kas {$noTrx} berhasil dicatat!";
        $tipe  = 'success';
    }
}

// ==============================================================================
// 4. HAPUS TRANSAKSI (DELETE)
// ==============================================================================
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    $trx = $pdo->prepare("SELECT * FROM transaksi_keuangan WHERE id = ?");
    $trx->execute([$idHapus]);
    $dataTrx = $trx->fetch();

    if ($dataTrx) {
        // Rollback nominal program jika pemasukan
        if ($dataTrx['jenis'] === 'pemasukan' && $dataTrx['program_id']) {
            $pdo->prepare("UPDATE program_donasi SET dana_terkumpul = GREATEST(0, dana_terkumpul - ?) WHERE id = ?")->execute([$dataTrx['nominal'], $dataTrx['program_id']]);
        }

        $pdo->prepare("DELETE FROM transaksi_keuangan WHERE id = ?")->execute([$idHapus]);
        header('Location: transaksi.php?msg=deleted');
        exit;
    }
}

// ==============================================================================
// 5. QUERY FILTER TRANSAKSI
// ==============================================================================
$filterJenis    = $_GET['jenis'] ?? '';
$filterKategori = (int)($_GET['kategori_id'] ?? 0);
$filterPublish  = $_GET['status_publik'] ?? '';
$startDate      = $_GET['start_date'] ?? '';
$endDate        = $_GET['end_date'] ?? '';
$search         = trim($_GET['q'] ?? '');

$sql = "SELECT t.*, k.nama_kategori, u.nama_lengkap as pencatat, p.nama_program 
        FROM transaksi_keuangan t 
        JOIN kategori_transaksi k ON t.kategori_id = k.id 
        LEFT JOIN users u ON t.user_id = u.id 
        LEFT JOIN program_donasi p ON t.program_id = p.id 
        WHERE 1=1";
$params = [];

if ($filterJenis !== '' && in_array($filterJenis, ['pemasukan', 'pengeluaran'])) {
    $sql .= " AND t.jenis = ?";
    $params[] = $filterJenis;
}

if ($filterKategori > 0) {
    $sql .= " AND t.kategori_id = ?";
    $params[] = $filterKategori;
}

if ($filterPublish !== '' && in_array($filterPublish, ['0', '1'])) {
    $sql .= " AND t.is_published = ?";
    $params[] = (int)$filterPublish;
}

if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND t.tanggal_transaksi BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
}

if ($search !== '') {
    $sql .= " AND (t.no_transaksi LIKE ? OR t.keterangan LIKE ? OR t.akun_kas LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY t.tanggal_transaksi DESC, t.id DESC";

$pag = paginate_data($pdo, $sql, $params, 10);
$daftarTransaksi = $pag['items'];

// Hitung Ringkasan dari Seluruh Transaksi yang Terfilter (independent dari halaman)
$stmtTotal = $pdo->prepare("SELECT 
        COALESCE(SUM(CASE WHEN sub_sum.jenis = 'pemasukan' THEN sub_sum.nominal ELSE 0 END), 0) AS debit,
        COALESCE(SUM(CASE WHEN sub_sum.jenis = 'pengeluaran' THEN sub_sum.nominal ELSE 0 END), 0) AS kredit
    FROM (" . $sql . ") AS sub_sum");
$stmtTotal->execute($params);
$rowTotal = $stmtTotal->fetch();
$totalDebitFilter  = (float)$rowTotal['debit'];
$totalKreditFilter = (float)$rowTotal['kredit'];

// Master Kategori & Program
$kategoriList = $pdo->query("SELECT * FROM kategori_transaksi ORDER BY jenis ASC, urutan ASC")->fetchAll();
$programList  = $pdo->query("SELECT id, nama_program FROM program_donasi WHERE status = 'aktif' ORDER BY id DESC")->fetchAll();

$pageTitle    = 'Buku Kas & Transaksi · ' . $profil['nama_masjid'];
$activeMenu   = 'transaksi';
$pageSubtitle = 'Pencatatan Buku Kas Pemasukan & Pengeluaran';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<!-- Action Bar Atas -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">
            Buku Kas Transaksi Keuangan
        </h2>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">
            Kelola mutasi pemasukan (debit) dan pengeluaran (kredit) dengan nomor transaksi akuntansi standar.
        </p>
    </div>

    <div class="flex items-center gap-2.5">
        <a href="transaksi.php?export=csv" class="px-3.5 py-2 rounded-xl bg-emerald-800 hover:bg-emerald-900 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-file-excel"></i>
            <span>Ekspor Excel/CSV</span>
        </a>
        <button type="button" onclick="bukaModalTransaksi('pemasukan')" class="px-4 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-plus"></i>
            <span>Catat Pemasukan</span>
        </button>
        <button type="button" onclick="bukaModalTransaksi('pengeluaran')" class="px-4 py-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-xs">
            <i class="fa-solid fa-minus"></i>
            <span>Catat Pengeluaran</span>
        </button>
    </div>
</div>

<?php if ($pesan): ?>
    <div class="p-4 rounded-2xl <?= $tipe === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?> text-xs font-semibold flex items-center gap-2">
        <i class="fa-solid <?= $tipe === 'success' ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-red-600' ?>"></i>
        <span><?= e($pesan) ?></span>
    </div>
<?php endif; ?>

<!-- 3 Ringkasan Angka Filter -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
    <div class="bg-white rounded-2xl p-5 border border-antique-300/40 shadow-xs">
        <span class="text-[10px] uppercase font-bold text-emerald-700 block">Total Pemasukan (Tercantum)</span>
        <h4 class="text-xl font-bold font-classic text-emerald-700 mt-1"><?= format_rupiah($totalDebitFilter) ?></h4>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-antique-300/40 shadow-xs">
        <span class="text-[10px] uppercase font-bold text-amber-700 block">Total Pengeluaran (Tercantum)</span>
        <h4 class="text-xl font-bold font-classic text-amber-700 mt-1"><?= format_rupiah($totalKreditFilter) ?></h4>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-antique-300/40 shadow-xs">
        <span class="text-[10px] uppercase font-bold text-warm-800/60 block">Net Saldo Periode Filter</span>
        <h4 class="text-xl font-bold font-classic mt-1 <?= ($totalDebitFilter - $totalKreditFilter) >= 0 ? 'text-cypress-800' : 'text-red-600' ?>">
            <?= format_rupiah($totalDebitFilter - $totalKreditFilter) ?>
        </h4>
    </div>
</div>

<!-- ==============================================================================
     FILTER & SEARCH FORM
     ============================================================================== -->
<div class="bg-white p-5 rounded-3xl border border-antique-300/50 shadow-sm">
    <form method="GET" action="transaksi.php" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
        
        <!-- Jenis -->
        <div>
            <label class="block font-bold text-warm-800 mb-1">Jenis Transaksi</label>
            <select name="jenis" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                <option value="">-- Semua Jenis --</option>
                <option value="pemasukan" <?= $filterJenis === 'pemasukan' ? 'selected' : '' ?>>Pemasukan (Debit)</option>
                <option value="pengeluaran" <?= $filterJenis === 'pengeluaran' ? 'selected' : '' ?>>Pengeluaran (Kredit)</option>
            </select>
        </div>

        <!-- Kategori -->
        <div>
            <label class="block font-bold text-warm-800 mb-1">Kategori Transaksi</label>
            <select name="kategori_id" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                <option value="0">-- Semua Kategori --</option>
                <?php foreach ($kategoriList as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $filterKategori === $k['id'] ? 'selected' : '' ?>>
                        [<?= ucfirst($k['jenis']) ?>] <?= e($k['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Status Publikasi (Scope 25) -->
        <div>
            <label class="block font-bold text-warm-800 mb-1">Status Publikasi</label>
            <select name="status_publik" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                <option value="">-- Semua Status --</option>
                <option value="1" <?= $filterPublish === '1' ? 'selected' : '' ?>>Publik / Published</option>
                <option value="0" <?= $filterPublish === '0' ? 'selected' : '' ?>>Internal / Private</option>
            </select>
        </div>

        <!-- Rentang Tanggal -->
        <div>
            <label class="block font-bold text-warm-800 mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" value="<?= e($startDate) ?>" class="w-full px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
        </div>

        <div>
            <label class="block font-bold text-warm-800 mb-1">Sampai Tanggal</label>
            <div class="flex items-center gap-1.5">
                <input type="date" name="end_date" value="<?= e($endDate) ?>" class="w-full px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold transition shrink-0">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </div>
        </div>

    </form>
</div>

<!-- ==============================================================================
     TABEL TRANSAKSI KAS
     ============================================================================== -->
<div class="bg-white rounded-3xl border border-antique-300/50 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-antique-200 flex items-center justify-between">
        <h3 class="font-classic text-base font-bold text-warm-900">
            Daftar Catatan Buku Kas
        </h3>
        <span class="text-xs text-warm-800/60 font-semibold"><?= number_format($pag['total']) ?> Baris Data</span>
    </div>

    <?php if (empty($daftarTransaksi)): ?>
        <div class="text-center py-12 space-y-2">
            <i class="fa-solid fa-book-open text-4xl text-stone-300"></i>
            <p class="text-xs text-warm-800/60 font-medium">Tidak ada data transaksi yang sesuai filter.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-antique-200 bg-warm-50/70 text-warm-800/70 uppercase text-[10px] font-bold">
                        <th class="py-3 px-4">No. Transaksi / Tgl</th>
                        <th class="py-3 px-4">Kategori &amp; Program</th>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-4">Akun Kas</th>
                        <th class="py-3 px-4 text-right">Debit (Masuk)</th>
                        <th class="py-3 px-4 text-right">Kredit (Keluar)</th>
                        <th class="py-3 px-4 text-center">Publikasi</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-antique-100">
                    <?php foreach ($daftarTransaksi as $t): ?>
                        <tr class="hover:bg-warm-50/60 transition">
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <span class="font-mono font-bold text-cypress-800 block"><?= e($t['no_transaksi']) ?></span>
                                <span class="text-[11px] text-warm-800/60 block"><?= tanggal_indo($t['tanggal_transaksi']) ?></span>
                            </td>
                            <td class="py-3.5 px-4 align-top">
                                <span class="font-bold text-warm-900 block"><?= e($t['nama_kategori']) ?></span>
                                <?php if (!empty($t['nama_program'])): ?>
                                    <span class="text-[10px] text-antique-700 bg-antique-50 px-1.5 py-0.5 rounded border border-antique-200 block mt-0.5">
                                        <?= e($t['nama_program']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 align-top max-w-xs">
                                <p class="text-xs text-warm-900 leading-relaxed"><?= e($t['keterangan']) ?></p>
                                <span class="text-[10px] text-warm-800/50 block mt-0.5">Oleh: <?= e($t['pencatat'] ?: 'Pengurus') ?> • <?= strtoupper(e($t['metode_pembayaran'])) ?></span>
                            </td>
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-lg bg-warm-100 text-warm-800 text-[11px] font-semibold">
                                    <?= e($t['akun_kas']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                <?php if ($t['jenis'] === 'pemasukan'): ?>
                                    <span class="font-bold font-classic text-emerald-700">
                                        <?= format_rupiah($t['nominal']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-stone-300">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                <?php if ($t['jenis'] === 'pengeluaran'): ?>
                                    <span class="font-bold font-classic text-amber-700">
                                        <?= format_rupiah($t['nominal']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-stone-300">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 align-top text-center whitespace-nowrap">
                                <a href="transaksi.php?toggle_publish=<?= $t['id'] ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold transition <?= $t['is_published'] ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-stone-200 text-stone-700 hover:bg-stone-300' ?>" title="Klik untuk mengubah status publikasi">
                                    <i class="fa-solid <?= $t['is_published'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                                    <span><?= $t['is_published'] ? 'Publik' : 'Private' ?></span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <?php if (!empty($t['bukti_transaksi'])): ?>
                                        <a href="<?= e(upload_url($t['bukti_transaksi'])) ?>" target="_blank" class="p-1.5 rounded-lg text-cypress-700 hover:bg-cypress-50" title="Lihat Bukti Nota">
                                            <i class="fa-solid fa-file-invoice"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="transaksi.php?hapus=<?= $t['id'] ?>" data-hapus data-judul="Hapus Transaksi" data-pesan="Catatan transaksi '<?= e($t['keterangan']) ?>' akan dihapus permanen dari buku kas. Lanjutkan?" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50" title="Hapus Transaksi">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php render_pagination($pag['totalHalaman'], $pag['halaman'], $pag['total'], $pag['dari'], $pag['sampai']); ?>
    <?php endif; ?>
</div>

<!-- ==============================================================================

     MODAL TAMBAH TRANSAKSI KAS (DEBIT / KREDIT)
     ============================================================================== -->
<div id="modalTransaksi" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-cypress-950/50 veil-blur"></div>
    <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 border border-antique-300 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-antique-200 pb-3">
            <h3 id="modalTitle" class="font-classic text-lg font-bold text-warm-900">
                Catat Transaksi Kas Baru
            </h3>
            <button type="button" onclick="tutupModalTransaksi()" class="text-stone-400 hover:text-stone-700 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="transaksi.php" enctype="multipart/form-data" class="space-y-4 text-xs">
            <input type="hidden" name="aksi" value="tambah">
            <input type="hidden" id="formJenis" name="jenis" value="pemasukan">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Tanggal Transaksi <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_transaksi" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Kategori Transaksi <span class="text-red-500">*</span></label>
                    <select id="selectKategori" name="kategori_id" required class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                        <!-- Populated by JavaScript according to jenis -->
                    </select>   
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="nominal" min="1000" step="500" required placeholder="Contoh: 500000" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-bold text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Metode Pembayaran</label>
                    <select name="metode_pembayaran" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                        <option value="tunai">Tunai / Kas Langsung</option>
                        <option value="transfer">Transfer Rekening Bank</option>
                        <option value="qris">QRIS Digital</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Akun Kas / Sumber Dana</label>
                    <select name="akun_kas" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                        <option value="Kas Tunai Utama">Kas Tunai Utama</option>
                        <option value="Kas Kotak Amal">Kas Kotak Amal</option>
                        <option value="Bank BSI">Bank Syariah Indonesia (BSI)</option>
                        <option value="Bank Muamalat">Bank Muamalat</option>
                        <option value="Bank BRI">Bank BRI</option>
                        <option value="Bank BCA">Bank BCA</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Alokasi Program (Opsional)</label>
                    <select name="program_id" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                        <option value="">-- Tanpa Alokasi Program --</option>
                        <?php foreach ($programList as $pr): ?>
                            <option value="<?= $pr['id'] ?>"><?= e($pr['nama_program']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Keterangan Rinci <span class="text-red-500">*</span></label>
                <textarea name="keterangan" rows="2" required placeholder="Contoh: Penerimaan Infaq Jumat Pekan I September..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Upload Bukti Nota / Kwitansi Fisik (Opsional)</label>
                <input type="file" name="bukti_transaksi" accept="image/*,application/pdf" class="w-full px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300 text-[11px] file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-cypress-800 file:text-white">
            </div>

            <div class="p-3 rounded-xl bg-warm-50 border border-antique-200">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-warm-900">
                    <input type="checkbox" name="is_published" value="1" checked class="accent-cypress-700 w-4 h-4 rounded">
                    <span>Publikasikan ke Website (Halaman Transparansi Kas Terbuka)</span>
                </label>
                <p class="text-[10px] text-warm-800/60 mt-1 pl-6">Centang jika transaksi ini boleh dilihat publik. Hapus centang untuk transaksi rahasia/internal pengurus.</p>
            </div>

            <div class="pt-2 flex items-center justify-end gap-3">
                <button type="button" onclick="tutupModalTransaksi()" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-700 font-semibold hover:bg-stone-200 transition">
                    Batal
                </button>
                <button type="submit" id="submitBtnModal" class="px-5 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold shadow-sm transition">
                    Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const allKategori = <?= json_encode($kategoriList) ?>;

function bukaModalTransaksi(jenis) {
    document.getElementById('formJenis').value = jenis;
    const title = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('submitBtnModal');
    const selectKat = document.getElementById('selectKategori');
    selectKat.innerHTML = '';

    if (jenis === 'pemasukan') {
        title.innerHTML = '<i class="fa-solid fa-plus-circle text-emerald-600 mr-1.5"></i> Catat Pemasukan Kas (Debit)';
        submitBtn.className = 'px-5 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold shadow-sm transition';
    } else {
        title.innerHTML = '<i class="fa-solid fa-minus-circle text-amber-600 mr-1.5"></i> Catat Pengeluaran Kas (Kredit)';
        submitBtn.className = 'px-5 py-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white font-bold shadow-sm transition';
    }

    // Filter kategori sesuai jenis
    allKategori.filter(k => k.jenis === jenis).forEach(k => {
        const opt = document.createElement('option');
        opt.value = k.id;
        opt.textContent = k.nama_kategori;
        selectKat.appendChild(opt);
    });

    document.getElementById('modalTransaksi').classList.remove('hidden');
}

function tutupModalTransaksi() {
    document.getElementById('modalTransaksi').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
