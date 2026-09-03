<?php
require_once __DIR__ . '/config/database.php';
cek_login();
$user = $_SESSION['user'];

// ==============================================================================
// 1. FITUR EKSPOR CSV / EXCEL
// ==============================================================================
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Laporan_Kas_Infaq_Masjid_Nurul_Iman_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    // Tambahkan BOM agar karakter terbaca rapi di Microsoft Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header Kolom CSV
    fputcsv($output, ['No', 'No. Kwitansi', 'Nama Donatur', 'Nominal (Rp)', 'Keterangan / Alokasi', 'Tanggal Donasi', 'Waktu Dicatat']);
    
    $rows = $pdo->query("SELECT * FROM donasi WHERE jenis_donasi = 'uang' ORDER BY tanggal_donasi DESC, id DESC")->fetchAll();
    $no = 1;
    foreach ($rows as $r) {
        fputcsv($output, [
            $no++,
            'DON-' . str_pad($r['id'], 5, '0', STR_PAD_LEFT),
            $r['nama_donatur'],
            (float)$r['jumlah'],
            $r['keterangan'] ?: 'Infaq / Sedekah',
            $r['tanggal_donasi'],
            $r['created_at'] ?? '-'
        ]);
    }
    fclose($output);
    exit;
}

$pesan = '';
$tipe  = '';

// ==============================================================================
// 2. TAMBAH CATATAN INFAQ (CREATE)
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama       = trim($_POST['nama_donatur'] ?? '');
    $jenis      = 'uang';
    $jumlah     = (float)($_POST['jumlah'] ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? '');
    $tanggal    = $_POST['tanggal'] ?? date('Y-m-d');

    if ($nama === '') {
        $pesan = 'Nama donatur wajib diisi.';
        $tipe  = 'error';
    } elseif ($jumlah <= 0) {
        $pesan = 'Nominal infaq harus lebih dari Rp 0.';
        $tipe  = 'error';
    } else {
        $stmt = $pdo->prepare('INSERT INTO donasi (nama_donatur, jenis_donasi, jumlah, keterangan, tanggal_donasi) VALUES (?,?,?,?,?)');
        $stmt->execute([$nama, $jenis, $jumlah, $keterangan, $tanggal]);
        $pesan = 'Alhamdulillah, catatan infaq baru berhasil disimpan.';
        $tipe  = 'success';
    }
}

// ==============================================================================
// 3. EDIT CATATAN INFAQ (UPDATE)
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $idEdit     = (int)($_POST['id'] ?? 0);
    $nama       = trim($_POST['nama_donatur'] ?? '');
    $jenis      = 'uang';
    $jumlah     = (float)($_POST['jumlah'] ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? '');
    $tanggal    = $_POST['tanggal'] ?? date('Y-m-d');

    if ($idEdit <= 0 || $nama === '') {
        $pesan = 'Data yang dipilih tidak valid.';
        $tipe  = 'error';
    } elseif ($jumlah <= 0) {
        $pesan = 'Nominal infaq harus lebih dari Rp 0.';
        $tipe  = 'error';
    } else {
        $stmt = $pdo->prepare('UPDATE donasi SET nama_donatur = ?, jenis_donasi = ?, jumlah = ?, keterangan = ?, tanggal_donasi = ? WHERE id = ?');
        $stmt->execute([$nama, $jenis, $jumlah, $keterangan, $tanggal, $idEdit]);
        $pesan = 'Catatan infaq #' . str_pad($idEdit, 5, '0', STR_PAD_LEFT) . ' berhasil diperbarui.';
        $tipe  = 'success';
    }
}

// ==============================================================================
// 4. HAPUS CATATAN INFAQ (DELETE)
// ==============================================================================
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    if ($idHapus > 0) {
        $stmt = $pdo->prepare('DELETE FROM donasi WHERE id = ?');
        $stmt->execute([$idHapus]);
        header('Location: donasi.php?deleted=' . $idHapus);
        exit;
    }
}

if (isset($_GET['deleted'])) {
    $pesan = 'Catatan infaq #' . str_pad((int)$_GET['deleted'], 5, '0', STR_PAD_LEFT) . ' telah berhasil dihapus.';
    $tipe  = 'success';
}

// ==============================================================================
// 5. AMBIL DATA INFAQ UANG & STATISTIK (READ)
// ==============================================================================
$daftar = $pdo->query("SELECT * FROM donasi WHERE jenis_donasi = 'uang' ORDER BY tanggal_donasi DESC, id DESC")->fetchAll();
$totalUang = $pdo->query("SELECT COALESCE(SUM(jumlah), 0) AS total FROM donasi WHERE jenis_donasi = 'uang'")->fetch()['total'];

$bulanIni = date('Y-m');
$stmtBulan = $pdo->prepare("SELECT COALESCE(SUM(jumlah), 0) AS total FROM donasi WHERE jenis_donasi = 'uang' AND DATE_FORMAT(tanggal_donasi, '%Y-%m') = ?");
$stmtBulan->execute([$bulanIni]);
$totalUangBulanIni = $stmtBulan->fetch()['total'];

$totalDonatur = count($daftar);
$rataRata = $totalDonatur > 0 ? ($totalUang / $totalDonatur) : 0;

// Konfigurasi Halaman & Navigasi
$pageTitle    = 'Buku Kas & Donasi · Masjid Nurul Iman';
$activeMenu   = 'donasi';
$pageSubtitle = 'Manajemen Kas & Infaq Masjid';

// Panggil Header & Sidebar Bercabang
require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

<!-- Header Cetak Dokumen Resmi -->
<div class="hidden print-header p-4 text-center border-b-2 border-black pb-4 mb-6">
    <h1 class="text-2xl font-bold uppercase tracking-wider">Laporan Penerimaan Kas &amp; Infaq Masjid Nurul Iman</h1>
    <p class="text-sm text-gray-600">Dicetak pada: <?= date('d F Y') ?> | Oleh: <?= e($user['nama']) ?></p>
</div>

<!-- Pesan Pemberitahuan -->
<?php if ($pesan): ?>
<div class="p-4 rounded-xl <?= $tipe === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-900' : 'bg-amber-50 border border-amber-200 text-amber-900' ?> flex items-center justify-between text-sm shadow-sm transition">
    <div class="flex items-center gap-3">
        <?php if ($tipe === 'success'): ?>
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <?php else: ?>
        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <?php endif; ?>
        <span class="font-medium"><?= e($pesan) ?></span>
    </div>
    <button type="button" onclick="this.parentElement.remove()" class="text-xs text-warm-800/40 hover:text-warm-800">Tutup</button>
</div>
<?php endif; ?>

<!-- Header Halaman & Tombol Aksi -->
<div class="bg-white rounded-2xl p-6 sm:p-8 border border-antique-300/40 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-antique-50 border border-antique-300/50 text-antique-700 text-xs font-semibold uppercase tracking-wider mb-2">
            <span class="w-1.5 h-1.5 rounded-full bg-cypress-700"></span>
            Buku Kas &amp; Infaq Masjid
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-warm-900 tracking-tight">
            Pencatatan Infaq &amp; Sedekah
        </h1>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">
            Pencatatan uang masuk, sedekah jamaah, kwitansi resmi, dan rekapitulasi kas.
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-3 no-print">
        <!-- Tombol Unduh Laporan Excel / CSV -->
        <a href="?export=csv" class="inline-flex items-center gap-2 px-4 py-3 rounded-xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-semibold shadow-xs transition-luxury">
            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>Unduh File Excel / CSV</span>
        </a>

        <!-- Tombol Cetak Laporan -->
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-3 rounded-xl border border-antique-300/60 bg-white hover:bg-antique-50 text-antique-700 text-xs font-semibold shadow-xs transition-luxury">
            <svg class="w-4 h-4 text-antique-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Cetak Laporan</span>
        </button>
    </div>
</div>

<!-- 4 Kartu Ringkasan Finansial Kas -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="kartuStatistik">
    
    <!-- Stat 1: Total Kas Terkumpul -->
    <div class="bg-white rounded-2xl p-5 border border-antique-300/40 shadow-sm">
        <span class="text-[10px] uppercase font-bold tracking-widest text-warm-800/60 block mb-1">Total Kas Terkumpul</span>
        <h3 class="text-xl sm:text-2xl font-bold text-cypress-700 tracking-tight">
            Rp <?= number_format($totalUang, 0, ',', '.') ?>
        </h3>
        <span class="text-[10px] text-cypress-700/80 block mt-1">Akumulasi seluruh infaq</span>
    </div>

    <!-- Stat 2: Infaq Bulan Ini -->
    <div class="bg-white rounded-2xl p-5 border border-antique-300/40 shadow-sm">
        <span class="text-[10px] uppercase font-bold tracking-widest text-warm-800/60 block mb-1">Masuk Bulan Ini (<?= date('M Y') ?>)</span>
        <h3 class="text-xl sm:text-2xl font-bold text-warm-900 tracking-tight">
            Rp <?= number_format($totalUangBulanIni, 0, ',', '.') ?>
        </h3>
        <span class="text-[10px] text-antique-600 block mt-1">Penerimaan kas aktif</span>
    </div>

    <!-- Stat 3: Total Donatur Tercatat -->
    <div class="bg-white rounded-2xl p-5 border border-antique-300/40 shadow-sm">
        <span class="text-[10px] uppercase font-bold tracking-widest text-warm-800/60 block mb-1">Jumlah Transaksi Infaq</span>
        <h3 class="text-xl sm:text-2xl font-bold text-warm-900 tracking-tight">
            <?= (int)$totalDonatur ?> <span class="text-xs font-normal text-warm-800/60">Catatan</span>
        </h3>
        <span class="text-[10px] text-warm-800/60 block mt-1">Donatur &amp; hamba Allah</span>
    </div>

    <!-- Stat 4: Rata-Rata Infaq -->
    <div class="bg-white rounded-2xl p-5 border border-antique-300/40 shadow-sm">
        <span class="text-[10px] uppercase font-bold tracking-widest text-warm-800/60 block mb-1">Rata-rata per Infaq</span>
        <h3 class="text-xl sm:text-2xl font-bold text-warm-900 tracking-tight">
            Rp <?= number_format($rataRata, 0, ',', '.') ?>
        </h3>
        <span class="text-[10px] text-cypress-700 block mt-1">Nilai rata-rata donasi</span>
    </div>

</div>

<!-- Formulir Tambah Donasi Baru (CREATE) -->
<div id="formTambah" class="bg-white rounded-2xl p-6 sm:p-8 border border-antique-300/40 shadow-sm no-print">
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-warm-100">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-cypress-50 text-cypress-700 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </span>
            <div>
                <h2 class="text-base font-bold text-warm-900">Catat Infaq Baru</h2>
                <p class="text-[11px] text-warm-800/60">Catatan akan otomatis tersimpan dalam sistem kas masjid</p>
            </div>
        </div>
        <span class="text-xs text-warm-800/60">* Wajib diisi</span>
    </div>

    <form method="POST" action="donasi.php" class="space-y-4">
        <input type="hidden" name="aksi" value="tambah">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <!-- Nama Donatur -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-1.5">
                    Nama Donatur / Hamba Allah *
                </label>
                <input type="text" name="nama_donatur" required placeholder="Contoh: Bpk. Ahmad / Hamba Allah"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 bg-warm-50/50 text-warm-900 text-xs focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
            </div>

            <!-- Jumlah Nominal Uang (Rp) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80">
                        Nominal Infaq (Rp) *
                    </label>
                    <span id="nominalPreview" class="text-[11px] font-semibold text-cypress-700">Rp 0</span>
                </div>
                <input type="number" id="inputJumlah" name="jumlah" min="1" step="any" required placeholder="Contoh: 100000"
                       oninput="updateNominalPreview(this.value, 'nominalPreview')"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 bg-warm-50/50 text-warm-900 text-xs focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
            </div>

            <!-- Tanggal Donasi -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-1.5">
                    Tanggal Donasi
                </label>
                <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 bg-warm-50/50 text-warm-900 text-xs focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
            </div>

        </div>

        <!-- Keterangan / Alokasi -->
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-1.5">
                Keterangan / Alokasi (Opsional)
            </label>
            <input type="text" name="keterangan" placeholder="Contoh: Infaq Jumat, Sedekah Pembangunan Kubah, Santunan Yatim, Zakat Maal"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 bg-warm-50/50 text-warm-900 text-xs focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
        </div>

        <!-- Tombol Simpan -->
        <div class="pt-2 flex justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-semibold text-xs tracking-wide shadow-md shadow-cypress-900/15 active:scale-[0.99] transition-luxury">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Simpan Catatan Infaq</span>
            </button>
        </div>
    </form>
</div>

<!-- Tabel Data Infaq -->
<div class="bg-white rounded-2xl border border-antique-300/40 shadow-sm overflow-hidden">
    
    <!-- Toolbar Pencarian -->
    <div class="p-6 border-b border-warm-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print" id="searchBox">
        <div>
            <h3 class="text-base font-bold text-warm-900">Riwayat Infaq &amp; Sedekah</h3>
            <p class="text-xs text-warm-800/60 mt-0.5">Menampilkan <?= $totalDonatur ?> catatan infaq jamaah</p>
        </div>

        <!-- Input Pencarian Realtime -->
        <div class="relative w-full sm:w-72">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-warm-800/40">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="filterInput" onkeyup="jalankanPencarian()" placeholder="Cari donatur atau keterangan..."
                   class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-warm-200 bg-warm-50/50 text-xs focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
        </div>
    </div>

    <!-- Kontainer Tabel -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs" id="tabelDonasi">
            <thead class="bg-warm-50/80 text-warm-800/60 uppercase tracking-wider text-[10px] border-b border-warm-100">
                <tr>
                    <th class="py-3.5 px-5 font-semibold text-center w-12">No</th>
                    <th class="py-3.5 px-5 font-semibold">No. Kwitansi</th>
                    <th class="py-3.5 px-5 font-semibold">Nama Donatur</th>
                    <th class="py-3.5 px-5 font-semibold">Nominal Infaq</th>
                    <th class="py-3.5 px-5 font-semibold">Keterangan / Alokasi</th>
                    <th class="py-3.5 px-5 font-semibold">Tanggal</th>
                    <th class="py-3.5 px-5 font-semibold text-right no-print">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-warm-100 text-warm-900" id="daftarBody">
                <?php if (empty($daftar)): ?>
                <tr>
                    <td colspan="7" class="py-12 text-center text-warm-800/40">
                        Belum ada data infaq yang tercatat.
                    </td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($daftar as $d): ?>
                    <tr class="hover:bg-warm-50/60 transition item-donasi">
                        <td class="py-3.5 px-5 text-center text-warm-800/50 font-medium">
                            <?= $no++ ?>
                        </td>
                        <td class="py-3.5 px-5 font-mono text-[11px] text-cypress-800 font-semibold">
                            #DON-<?= str_pad($d['id'], 5, '0', STR_PAD_LEFT) ?>
                        </td>
                        <td class="py-3.5 px-5 font-bold text-warm-900 item-nama">
                            <?= e($d['nama_donatur']) ?>
                        </td>
                        <td class="py-3.5 px-5 font-bold text-cypress-700">
                            Rp <?= number_format($d['jumlah'], 0, ',', '.') ?>
                        </td>
                        <td class="py-3.5 px-5 text-warm-800/70 item-ket">
                            <?= e($d['keterangan'] ?: 'Infaq Umum') ?>
                        </td>
                        <td class="py-3.5 px-5 text-warm-800/60 whitespace-nowrap">
                            <?= e(date('d F Y', strtotime($d['tanggal_donasi']))) ?>
                        </td>
                        
                        <!-- Aksi: Kwitansi, Edit, Hapus -->
                        <td class="py-3.5 px-5 text-right no-print whitespace-nowrap space-x-1.5">
                            
                            <!-- Tombol Kwitansi -->
                            <button type="button" onclick='bukaKwitansi(<?= json_encode($d) ?>)'
                                    title="Lihat &amp; Cetak Bukti Kwitansi"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-antique-50 hover:bg-antique-100 text-antique-700 border border-antique-300/60 text-[11px] font-medium transition">
                                <svg class="w-3 h-3 text-antique-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Kwitansi</span>
                            </button>

                            <!-- Tombol Edit -->
                            <button type="button" onclick='bukaModalEdit(<?= json_encode($d) ?>)'
                                    title="Ubah Catatan Infaq"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-warm-100 hover:bg-warm-200 text-warm-800 text-[11px] font-medium transition">
                                <svg class="w-3 h-3 text-warm-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>Edit</span>
                            </button>

                            <!-- Tombol Hapus (Membuka Popup Modal) -->
                            <button type="button" onclick='bukaModalHapus(<?= json_encode($d) ?>)'
                                    title="Hapus Catatan Infaq"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-medium transition">
                                <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Hapus</span>
                            </button>

                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Modal Edit Catatan Infaq -->
<div id="modalEdit" class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs hidden items-center justify-center p-4 z-50 transition-opacity no-print">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-warm-200">
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-warm-100">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-antique-50 text-antique-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="font-bold text-base text-warm-900">Ubah Catatan Infaq</h3>
                    <p id="editIdLabel" class="text-[11px] text-warm-800/60 font-mono">#DON-00000</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalEdit()" class="text-warm-800/50 hover:text-warm-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="donasi.php" class="space-y-4">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" id="edit_id" value="">

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-1">Nama Donatur</label>
                <input type="text" name="nama_donatur" id="edit_nama" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 text-xs focus:outline-none focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80">Nominal Infaq (Rp)</label>
                    <span id="editNominalPreview" class="text-[10px] font-semibold text-cypress-700">Rp 0</span>
                </div>
                <input type="number" name="jumlah" id="edit_jumlah" min="1" step="any" required
                       oninput="updateNominalPreview(this.value, 'editNominalPreview')"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 text-xs focus:outline-none focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-1">Tanggal Donasi</label>
                <input type="date" name="tanggal" id="edit_tanggal" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 text-xs focus:outline-none focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-1">Keterangan / Alokasi</label>
                <input type="text" name="keterangan" id="edit_keterangan"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 text-xs focus:outline-none focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15">
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="tutupModalEdit()" class="px-4 py-2.5 rounded-xl border border-warm-200 text-xs font-medium text-warm-800 hover:bg-warm-50 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white text-xs font-semibold shadow transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Kwitansi Resmi Masjid -->
<div id="modalKwitansi" class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs hidden items-center justify-center p-4 z-50 transition-opacity">
    <div id="kwitansiCard" class="bg-white rounded-2xl max-w-xl w-full p-8 shadow-2xl border-2 border-antique-500/50 relative">
        
        <!-- Header Kwitansi -->
        <div class="flex items-center justify-between pb-4 mb-5 border-b-2 border-warm-200">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-cypress-800 text-antique-300 flex items-center justify-center">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 0c-3 3-5 6-5 9a5 5 0 0010 0c0-3-2-6-5-9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 21h16M7 21v-4m10 4v-4M9 13h6"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-classic text-lg font-bold text-cypress-800 leading-tight">MASJID NURUL IMAN</h3>
                    <p class="text-[11px] text-warm-800/60">Tanda Terima &amp; Kwitansi Infaq / Sedekah</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-warm-800/60 uppercase block">No. Kwitansi</span>
                <span id="kwitansi_no" class="font-mono font-bold text-sm text-cypress-800">#DON-00000</span>
            </div>
        </div>

        <!-- Rincian Kwitansi -->
        <div class="space-y-4 text-xs">
            <div class="flex border-b border-warm-100 pb-2">
                <span class="w-36 text-warm-800/60">Telah Diterima Dari</span>
                <span class="font-bold text-warm-900 flex-1 text-sm" id="kwitansi_nama">-</span>
            </div>
            <div class="flex border-b border-warm-100 pb-2">
                <span class="w-36 text-warm-800/60">Jumlah Infaq</span>
                <div class="flex-1">
                    <span class="font-bold text-base text-cypress-700 block" id="kwitansi_jumlah">-</span>
                </div>
            </div>
            <div class="flex border-b border-warm-100 pb-2">
                <span class="w-36 text-warm-800/60">Untuk Keperluan</span>
                <span class="text-warm-800 flex-1" id="kwitansi_ket">-</span>
            </div>
            <div class="flex border-b border-warm-100 pb-2">
                <span class="w-36 text-warm-800/60">Tanggal Penerimaan</span>
                <span class="font-medium text-warm-800 flex-1" id="kwitansi_tanggal">-</span>
            </div>
        </div>

        <!-- Tanda Tangan & Doa -->
        <div class="mt-6 pt-4 border-t border-warm-100 flex items-center justify-between text-xs">
            <div class="max-w-xs text-[11px] text-warm-800/70 italic leading-snug">
                "Jazakumullahu khairan katsiran. Semoga menjadi amal jariyah yang diberkahi oleh Allah SWT."
            </div>
            <div class="text-center">
                <span class="text-[10px] text-warm-800/60 block mb-8">Penerima / Pengurus</span>
                <span class="font-semibold text-warm-900 border-t border-warm-300 pt-1 block"><?= e($user['nama']) ?></span>
            </div>
        </div>

        <!-- Tombol Cetak / Tutup Kwitansi -->
        <div class="mt-6 pt-4 border-t border-warm-100 flex justify-end gap-2 no-print">
            <button type="button" onclick="tutupKwitansi()" class="px-4 py-2 rounded-xl border border-warm-200 text-xs font-medium text-warm-800 hover:bg-warm-50 transition">
                Tutup
            </button>
            <button type="button" onclick="window.print()" class="px-5 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white text-xs font-semibold shadow transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Kwitansi Ini</span>
            </button>
        </div>

    </div>
</div>

<!-- ==============================================================================
     MODAL KONFIRMASI HAPUS DATA (POP UP ELEGAN)
     ============================================================================== -->
<div id="modalHapus" class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs hidden items-center justify-center p-4 z-50 transition-opacity no-print">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-warm-200">
        
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-warm-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-base text-warm-900">Hapus Catatan Infaq?</h3>
                    <p id="hapus_id_label" class="text-[11px] text-warm-800/60 font-mono">#DON-00000</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalHapus()" class="text-warm-800/50 hover:text-warm-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <p class="text-xs text-warm-800/80 mb-4 leading-relaxed">
            Apakah Anda yakin ingin menghapus data infaq berikut? Catatan yang telah dihapus tidak dapat dipulihkan kembali.
        </p>

        <!-- Rincian Data yang Akan Dihapus -->
        <div class="rounded-xl bg-warm-50/80 border border-warm-200/80 p-4 mb-5 space-y-2 text-xs">
            <div class="flex justify-between items-center">
                <span class="text-warm-800/60">Nama Donatur:</span>
                <span class="font-bold text-warm-900" id="hapus_nama">-</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-warm-800/60">Nominal Infaq:</span>
                <span class="font-bold text-rose-700 text-sm" id="hapus_jumlah">-</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-warm-800/60">Tanggal Donasi:</span>
                <span class="text-warm-800 font-medium" id="hapus_tanggal">-</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-warm-800/60">Keterangan:</span>
                <span class="text-warm-800 truncate max-w-[180px]" id="hapus_keterangan">-</span>
            </div>
        </div>

        <!-- Tombol Aksi Modal Hapus -->
        <div class="flex items-center justify-end gap-2.5">
            <button type="button" onclick="tutupModalHapus()" class="px-4 py-2.5 rounded-xl border border-warm-200 text-xs font-medium text-warm-800 hover:bg-warm-50 transition">
                Batal
            </button>
            <a id="btnKonfirmasiHapus" href="#" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-md shadow-rose-900/15 transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Ya, Hapus Data</span>
            </a>
        </div>

    </div>
</div>

<!-- Script Halaman Donasi -->
<script>
    // Update format nominal real-time saat mengetik
    function updateNominalPreview(val, targetId) {
        const num = parseFloat(val) || 0;
        const formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
        document.getElementById(targetId).textContent = formatted;
    }

    // Pencarian Realtime
    function jalankanPencarian() {
        const query = document.getElementById('filterInput').value.toLowerCase();
        const rows = document.querySelectorAll('.item-donasi');

        rows.forEach(row => {
            const nama = row.querySelector('.item-nama').textContent.toLowerCase();
            const ket = row.querySelector('.item-ket').textContent.toLowerCase();

            if (nama.includes(query) || ket.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Buka Modal Edit
    function bukaModalEdit(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('editIdLabel').textContent = '#DON-' + String(data.id).padStart(5, '0');
        document.getElementById('edit_nama').value = data.nama_donatur;
        document.getElementById('edit_jumlah').value = data.jumlah;
        document.getElementById('edit_tanggal').value = data.tanggal_donasi;
        document.getElementById('edit_keterangan').value = data.keterangan || '';
        
        updateNominalPreview(data.jumlah, 'editNominalPreview');

        const m = document.getElementById('modalEdit');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function tutupModalEdit() {
        const m = document.getElementById('modalEdit');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

    // Buka Modal Kwitansi
    function bukaKwitansi(data) {
        document.getElementById('kwitansi_no').textContent = '#DON-' + String(data.id).padStart(5, '0');
        document.getElementById('kwitansi_nama').textContent = data.nama_donatur;
        
        const nominal = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(data.jumlah);
        document.getElementById('kwitansi_jumlah').textContent = nominal;

        document.getElementById('kwitansi_ket').textContent = data.keterangan || 'Infaq / Sedekah untuk kemakmuran masjid';
        document.getElementById('kwitansi_tanggal').textContent = data.tanggal_donasi;

        const m = document.getElementById('modalKwitansi');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function tutupKwitansi() {
        const m = document.getElementById('modalKwitansi');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

    // Buka Modal Konfirmasi Hapus
    function bukaModalHapus(data) {
        document.getElementById('hapus_id_label').textContent = '#DON-' + String(data.id).padStart(5, '0');
        document.getElementById('hapus_nama').textContent = data.nama_donatur;
        
        const nominal = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(data.jumlah);
        document.getElementById('hapus_jumlah').textContent = nominal;
        
        document.getElementById('hapus_tanggal').textContent = data.tanggal_donasi;
        document.getElementById('hapus_keterangan').textContent = data.keterangan || 'Infaq Umum';
        
        document.getElementById('btnKonfirmasiHapus').href = '?hapus=' + data.id;

        const m = document.getElementById('modalHapus');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function tutupModalHapus() {
        const m = document.getElementById('modalHapus');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
</script>

<?php
// Panggil Footer Bercabang
require_once __DIR__ . '/layouts/footer.php';
?>
