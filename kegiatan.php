<?php
require_once __DIR__ . '/config/database.php';
cek_login();
$user = $_SESSION['user'];

$pesan = '';
$tipe  = '';

// ==============================================================================
// 1. TAMBAH KEGIATAN (CREATE)
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $judul   = trim($_POST['judul'] ?? '');
    $isi     = trim($_POST['isi'] ?? '');
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');

    if ($judul === '' || $isi === '') {
        $pesan = 'Judul dan rincian kegiatan wajib diisi.';
        $tipe  = 'error';
    } else {
        $stmt = $pdo->prepare('INSERT INTO kegiatan (judul, isi, tanggal) VALUES (?,?,?)');
        $stmt->execute([$judul, $isi, $tanggal]);
        $pesan = 'Alhamdulillah, agenda kegiatan baru berhasil ditambahkan.';
        $tipe  = 'success';
    }
}

// ==============================================================================
// 2. EDIT KEGIATAN (UPDATE)
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $idEdit  = (int)($_POST['id'] ?? 0);
    $judul   = trim($_POST['judul'] ?? '');
    $isi     = trim($_POST['isi'] ?? '');
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');

    if ($idEdit <= 0 || $judul === '' || $isi === '') {
        $pesan = 'Data kegiatan tidak lengkap.';
        $tipe  = 'error';
    } else {
        $stmt = $pdo->prepare('UPDATE kegiatan SET judul = ?, isi = ?, tanggal = ? WHERE id = ?');
        $stmt->execute([$judul, $isi, $tanggal, $idEdit]);
        $pesan = 'Agenda kegiatan #' . $idEdit . ' berhasil diperbarui.';
        $tipe  = 'success';
    }
}

// ==============================================================================
// 3. HAPUS KEGIATAN (DELETE)
// ==============================================================================
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    if ($idHapus > 0) {
        $stmt = $pdo->prepare('DELETE FROM kegiatan WHERE id = ?');
        $stmt->execute([$idHapus]);
        header('Location: kegiatan.php?deleted=' . $idHapus);
        exit;
    }
}

if (isset($_GET['deleted'])) {
    $pesan = 'Agenda kegiatan telah berhasil dihapus.';
    $tipe  = 'success';
}

// ==============================================================================
// 4. AMBIL DATA & HITUNG STATISTIK (READ)
// ==============================================================================
$daftar = $pdo->query('SELECT * FROM kegiatan ORDER BY tanggal DESC, id DESC')->fetchAll();
$totalKegiatan = count($daftar);

$hariIni = date('Y-m-d');
$kegiatanMendatang = 0;
$kegiatanSelesai = 0;

foreach ($daftar as $k) {
    if ($k['tanggal'] >= $hariIni) {
        $kegiatanMendatang++;
    } else {
        $kegiatanSelesai++;
    }
}

// Konfigurasi Halaman & Navigasi
$pageTitle    = 'Agenda & Kegiatan · Masjid Nurul Iman';
$activeMenu   = 'kegiatan';
$pageSubtitle = 'Manajemen Agenda & Kajian Masjid';

// Panggil Header & Sidebar Bercabang
require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

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
            Agenda Takmir &amp; Jamaah
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-warm-900 tracking-tight">
            Agenda &amp; Kegiatan Masjid
        </h1>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">
            Kelola jadwal pengajian, kajian kitab, sholat Jumat, dan kegiatan sosial kemakmuran masjid.
        </p>
    </div>

    <div class="flex items-center gap-3">
        <a href="#formTambah" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-semibold text-xs tracking-wide shadow-md shadow-cypress-900/15 transition-luxury">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Agenda Baru</span>
        </a>
    </div>
</div>

<!-- 3 Kartu Statistik Kegiatan -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    
    <!-- Stat 1: Total Agenda -->
    <div class="bg-white rounded-2xl p-5 border border-antique-300/40 shadow-sm flex items-start justify-between">
        <div>
            <span class="text-[10px] uppercase font-bold tracking-widest text-warm-800/60 block mb-1">Total Seluruh Agenda</span>
            <h3 class="text-xl sm:text-2xl font-bold text-warm-900 tracking-tight">
                <?= (int)$totalKegiatan ?> <span class="text-xs font-normal text-warm-800/60">Program</span>
            </h3>
            <span class="text-[10px] text-cypress-700 block mt-1">Tersimpan dalam jadwal</span>
        </div>
        <div class="w-10 h-10 rounded-xl bg-cypress-50 border border-cypress-200 flex items-center justify-center text-cypress-700 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
    </div>

    <!-- Stat 2: Agenda Mendatang -->
    <div class="bg-white rounded-2xl p-5 border border-antique-300/40 shadow-sm flex items-start justify-between">
        <div>
            <span class="text-[10px] uppercase font-bold tracking-widest text-warm-800/60 block mb-1">Agenda Akan Datang</span>
            <h3 class="text-xl sm:text-2xl font-bold text-cypress-700 tracking-tight">
                <?= (int)$kegiatanMendatang ?> <span class="text-xs font-normal text-warm-800/60">Jadwal Aktif</span>
            </h3>
            <span class="text-[10px] text-antique-600 block mt-1">Siap diselenggarakan</span>
        </div>
        <div class="w-10 h-10 rounded-xl bg-antique-50 border border-antique-200 flex items-center justify-center text-antique-600 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>

    <!-- Stat 3: Agenda Terlaksana -->
    <div class="bg-white rounded-2xl p-5 border border-antique-300/40 shadow-sm flex items-start justify-between">
        <div>
            <span class="text-[10px] uppercase font-bold tracking-widest text-warm-800/60 block mb-1">Kegiatan Terlaksana</span>
            <h3 class="text-xl sm:text-2xl font-bold text-warm-800/80 tracking-tight">
                <?= (int)$kegiatanSelesai ?> <span class="text-xs font-normal text-warm-800/60">Selesai</span>
            </h3>
            <span class="text-[10px] text-warm-800/60 block mt-1">Arsip riwayat kegiatan</span>
        </div>
        <div class="w-10 h-10 rounded-xl bg-warm-100 border border-warm-200 flex items-center justify-center text-warm-700 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
    </div>

</div>

<!-- Formulir Tambah Kegiatan Baru (CREATE) -->
<div id="formTambah" class="bg-white rounded-2xl p-6 sm:p-8 border border-antique-300/40 shadow-sm">
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-warm-100">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-cypress-50 text-cypress-700 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </span>
            <div>
                <h2 class="text-base font-bold text-warm-900">Input Agenda / Pengajian Baru</h2>
                <p class="text-[11px] text-warm-800/60">Jadwal kegiatan akan otomatis tersimpan dalam sistem takmir</p>
            </div>
        </div>
        <span class="text-xs text-warm-800/60">* Wajib diisi</span>
    </div>

    <form method="POST" action="kegiatan.php" class="space-y-4">
        <input type="hidden" name="aksi" value="tambah">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <!-- Judul Kegiatan -->
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-1.5">
                    Nama Agenda / Kajian *
                </label>
                <input type="text" name="judul" required placeholder="Contoh: Kajian Rutin Malam Ahad / Sholat Tarawih Bersama"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 bg-warm-50/50 text-warm-900 text-xs focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
            </div>

            <!-- Tanggal Pelaksanaan -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-1.5">
                    Tanggal Pelaksanaan *
                </label>
                <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 bg-warm-50/50 text-warm-900 text-xs focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
            </div>

        </div>

        <!-- Rincian Acara & Penceramah -->
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-1.5">
                Rincian Acara / Penceramah / Waktu &amp; Tempat *
            </label>
            <textarea name="isi" rows="3" required placeholder="Contoh: Ba'da Maghrib s/d Isya di Ruang Utama Masjid. Pembicara: Ustadz Ahmad Fulan, Lc. Tema: Fiqih Ibadah Sehari-hari. Terbuka untuk umum muslimin &amp; muslimah."
                      class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 bg-warm-50/50 text-warm-900 text-xs focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury"></textarea>
        </div>

        <!-- Tombol Simpan -->
        <div class="pt-2 flex justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-semibold text-xs tracking-wide shadow-md shadow-cypress-900/15 active:scale-[0.99] transition-luxury">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Simpan Jadwal Kegiatan</span>
            </button>
        </div>
    </form>
</div>

<!-- Tabel Daftar Kegiatan -->
<div class="bg-white rounded-2xl border border-antique-300/40 shadow-sm overflow-hidden">
    
    <!-- Toolbar Pencarian Realtime -->
    <div class="p-6 border-b border-warm-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-warm-900">Daftar Jadwal Kegiatan Masjid</h3>
            <p class="text-xs text-warm-800/60 mt-0.5">Total terdaftar: <?= $totalKegiatan ?> agenda takmir</p>
        </div>

        <div class="relative w-full sm:w-72">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-warm-800/40">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" id="filterKegiatan" onkeyup="cariKegiatan()" placeholder="Cari judul atau rincian..."
                   class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-warm-200 bg-warm-50/50 text-xs focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
        </div>
    </div>

    <!-- Kontainer Tabel -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs" id="tabelKegiatan">
            <thead class="bg-warm-50/80 text-warm-800/60 uppercase tracking-wider text-[10px] border-b border-warm-100">
                <tr>
                    <th class="py-3.5 px-5 font-semibold text-center w-12">No</th>
                    <th class="py-3.5 px-5 font-semibold">Tanggal</th>
                    <th class="py-3.5 px-5 font-semibold">Nama Kegiatan / Kajian</th>
                    <th class="py-3.5 px-5 font-semibold">Rincian &amp; Penceramah</th>
                    <th class="py-3.5 px-5 font-semibold">Status</th>
                    <th class="py-3.5 px-5 font-semibold text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-warm-100 text-warm-900" id="bodyKegiatan">
                <?php if (empty($daftar)): ?>
                <tr>
                    <td colspan="6" class="py-12 text-center text-warm-800/40">
                        Belum ada agenda kegiatan yang dicatat.
                    </td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($daftar as $k): ?>
                    <?php $isUpcoming = ($k['tanggal'] >= $hariIni); ?>
                    <tr class="hover:bg-warm-50/60 transition baris-kegiatan">
                        <td class="py-3.5 px-5 text-center text-warm-800/50 font-medium">
                            <?= $no++ ?>
                        </td>
                        <td class="py-3.5 px-5 font-semibold text-warm-900 whitespace-nowrap">
                            <?= e(date('d F Y', strtotime($k['tanggal']))) ?>
                        </td>
                        <td class="py-3.5 px-5 font-bold text-warm-900 item-judul">
                            <?= e($k['judul']) ?>
                        </td>
                        <td class="py-3.5 px-5 text-warm-800/70 item-isi max-w-xs sm:max-w-md">
                            <?= nl2br(e($k['isi'])) ?>
                        </td>
                        <td class="py-3.5 px-5 whitespace-nowrap">
                            <?php if ($isUpcoming): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                Akan Datang
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-warm-100 text-warm-800/70">
                                Terlaksana
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5 px-5 text-right whitespace-nowrap space-x-1.5">
                            
                            <!-- Tombol Edit -->
                            <button type="button" onclick='bukaModalEditKegiatan(<?= json_encode($k) ?>)'
                                    title="Ubah Agenda Kegiatan"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-warm-100 hover:bg-warm-200 text-warm-800 text-[11px] font-medium transition">
                                <svg class="w-3 h-3 text-warm-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>Edit</span>
                            </button>

                            <!-- Tombol Hapus (Pop Up) -->
                            <button type="button" onclick='bukaModalHapusKegiatan(<?= json_encode($k) ?>)'
                                    title="Hapus Agenda Kegiatan"
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

<!-- ==============================================================================
     MODAL EDIT AGENDA KEGIATAN
     ============================================================================== -->
<div id="modalEditKegiatan" class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs hidden items-center justify-center p-4 z-50 transition-opacity">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-warm-200">
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-warm-100">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-antique-50 text-antique-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="font-bold text-base text-warm-900">Ubah Agenda Kegiatan</h3>
                    <p class="text-[11px] text-warm-800/60">Perbarui rincian atau tanggal pelaksanaan</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalEditKegiatan()" class="text-warm-800/50 hover:text-warm-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="kegiatan.php" class="space-y-4">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" id="edit_kegiatan_id" value="">

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-1">Nama Agenda / Kajian</label>
                <input type="text" name="judul" id="edit_kegiatan_judul" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 text-xs focus:outline-none focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-1">Tanggal Pelaksanaan</label>
                <input type="date" name="tanggal" id="edit_kegiatan_tanggal" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 text-xs focus:outline-none focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-1">Rincian Acara &amp; Penceramah</label>
                <textarea name="isi" id="edit_kegiatan_isi" rows="3" required
                          class="w-full px-3.5 py-2.5 rounded-xl border border-warm-200 text-xs focus:outline-none focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="tutupModalEditKegiatan()" class="px-4 py-2.5 rounded-xl border border-warm-200 text-xs font-medium text-warm-800 hover:bg-warm-50 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white text-xs font-semibold shadow transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==============================================================================
     MODAL KONFIRMASI HAPUS KEGIATAN (POP UP ELEGAN)
     ============================================================================== -->
<div id="modalHapusKegiatan" class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs hidden items-center justify-center p-4 z-50 transition-opacity">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-warm-200">
        
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-warm-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-base text-warm-900">Hapus Agenda Kegiatan?</h3>
                    <p class="text-[11px] text-warm-800/60">Konfirmasi pembatalan agenda</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalHapusKegiatan()" class="text-warm-800/50 hover:text-warm-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <p class="text-xs text-warm-800/80 mb-4 leading-relaxed">
            Apakah Anda yakin ingin menghapus agenda kegiatan ini? Data kegiatan yang dihapus tidak dapat dipulihkan kembali.
        </p>

        <!-- Rincian Data yang Akan Dihapus -->
        <div class="rounded-xl bg-warm-50/80 border border-warm-200/80 p-4 mb-5 space-y-2 text-xs">
            <div class="flex justify-between items-start">
                <span class="text-warm-800/60 shrink-0 mr-2">Nama Agenda:</span>
                <span class="font-bold text-warm-900 text-right" id="hapus_kegiatan_judul">-</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-warm-800/60">Tanggal:</span>
                <span class="font-semibold text-cypress-700" id="hapus_kegiatan_tanggal">-</span>
            </div>
        </div>

        <!-- Tombol Aksi Modal Hapus -->
        <div class="flex items-center justify-end gap-2.5">
            <button type="button" onclick="tutupModalHapusKegiatan()" class="px-4 py-2.5 rounded-xl border border-warm-200 text-xs font-medium text-warm-800 hover:bg-warm-50 transition">
                Batal
            </button>
            <a id="btnKonfirmasiHapusKegiatan" href="#" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-md shadow-rose-900/15 transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Ya, Hapus Kegiatan</span>
            </a>
        </div>

    </div>
</div>

<!-- Script Halaman Kegiatan -->
<script>
    // Pencarian Realtime
    function cariKegiatan() {
        const query = document.getElementById('filterKegiatan').value.toLowerCase();
        const rows = document.querySelectorAll('.baris-kegiatan');

        rows.forEach(row => {
            const judul = row.querySelector('.item-judul').textContent.toLowerCase();
            const isi = row.querySelector('.item-isi').textContent.toLowerCase();

            if (judul.includes(query) || isi.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Modal Edit
    function bukaModalEditKegiatan(data) {
        document.getElementById('edit_kegiatan_id').value = data.id;
        document.getElementById('edit_kegiatan_judul').value = data.judul;
        document.getElementById('edit_kegiatan_tanggal').value = data.tanggal;
        document.getElementById('edit_kegiatan_isi').value = data.isi;

        const m = document.getElementById('modalEditKegiatan');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function tutupModalEditKegiatan() {
        const m = document.getElementById('modalEditKegiatan');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

    // Modal Hapus
    function bukaModalHapusKegiatan(data) {
        document.getElementById('hapus_kegiatan_judul').textContent = data.judul;
        document.getElementById('hapus_kegiatan_tanggal').textContent = data.tanggal;
        document.getElementById('btnKonfirmasiHapusKegiatan').href = '?hapus=' + data.id;

        const m = document.getElementById('modalHapusKegiatan');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function tutupModalHapusKegiatan() {
        const m = document.getElementById('modalHapusKegiatan');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
</script>

<?php
// Panggil Footer Bercabang
require_once __DIR__ . '/layouts/footer.php';
?>
