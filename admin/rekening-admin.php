<?php
// rekening-admin.php - Pengelolaan Rekening Donasi & QRIS Masjid (Scope 6)
require_once __DIR__ . '/../config/database.php';
cek_role(['admin', 'bendahara', 'content_admin']);

$profil = get_profil_masjid();
$user   = $_SESSION['user'];
$pesan  = '';
$tipe   = '';

// 1. TAMBAH REKENING
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $bank     = trim($_POST['nama_bank'] ?? '');
    $noRek    = trim($_POST['nomor_rekening'] ?? '');
    $atasNama = trim($_POST['atas_nama'] ?? '');
    $kategori = trim($_POST['kategori_donasi'] ?? 'Kas Umum & Infaq');
    $urutan   = (int)($_POST['urutan'] ?? 1);
    $active   = isset($_POST['is_active']) ? 1 : 0;

    if (empty($bank) || empty($noRek) || empty($atasNama)) {
        $pesan = 'Nama bank, nomor rekening, dan atas nama wajib diisi.';
        $tipe  = 'error';
    } else {
        $stmt = $pdo->prepare("INSERT INTO rekening_donasi (nama_bank, nomor_rekening, atas_nama, kategori_donasi, urutan, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$bank, $noRek, $atasNama, $kategori, $urutan, $active]);

        $pesan = "Rekening {$bank} berhasil ditambahkan!";
        $tipe  = 'success';
    }
}

// 2. EDIT REKENING
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id       = (int)($_POST['id'] ?? 0);
    $bank     = trim($_POST['nama_bank'] ?? '');
    $noRek    = trim($_POST['nomor_rekening'] ?? '');
    $atasNama = trim($_POST['atas_nama'] ?? '');
    $kategori = trim($_POST['kategori_donasi'] ?? 'Kas Umum & Infaq');
    $urutan   = (int)($_POST['urutan'] ?? 1);
    $active   = isset($_POST['is_active']) ? 1 : 0;

    if ($id > 0 && !empty($bank) && !empty($noRek)) {
        $stmt = $pdo->prepare("UPDATE rekening_donasi SET nama_bank = ?, nomor_rekening = ?, atas_nama = ?, kategori_donasi = ?, urutan = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$bank, $noRek, $atasNama, $kategori, $urutan, $active, $id]);

        $pesan = "Rekening {$bank} berhasil diperbarui!";
        $tipe  = 'success';
    }
}

// 3. HAPUS REKENING
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM rekening_donasi WHERE id = ?")->execute([$idHapus]);
    header('Location: rekening-admin.php?msg=deleted');
    exit;
}

$rekening = $pdo->query("SELECT * FROM rekening_donasi ORDER BY urutan ASC, id ASC")->fetchAll();

$pageTitle    = 'Kelola Rekening Donasi · ' . $profil['nama_masjid'];
$activeMenu   = 'rekening-admin';
$pageSubtitle = 'Master Rekening Bank & QRIS Donasi Resmi';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">
            Pengelolaan Rekening Donasi &amp; QRIS
        </h2>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">
            Data rekening resmi masjid dikelola dinamis tanpa mengubah source code aplikasi.
        </p>
    </div>

    <button type="button" onclick="bukaModalRekening()" class="px-4 py-2.5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs shadow-xs transition flex items-center gap-2">
        <i class="fa-solid fa-plus"></i>
        <span>Tambah Rekening Baru</span>
    </button>
</div>

<?php if ($pesan): ?>
    <div class="p-4 rounded-2xl <?= $tipe === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?> text-xs font-semibold flex items-center gap-2">
        <i class="fa-solid <?= $tipe === 'success' ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-red-600' ?>"></i>
        <span><?= e($pesan) ?></span>
    </div>
<?php endif; ?>

<div class="bg-white rounded-3xl border border-antique-300/50 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-antique-200 flex items-center justify-between">
        <h3 class="font-classic text-base font-bold text-warm-900">
            Daftar Rekening Bank &amp; QRIS
        </h3>
        <span class="text-xs text-warm-800/60 font-semibold"><?= count($rekening) ?> Rekening</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-antique-200 bg-warm-50/70 text-warm-800/70 uppercase text-[10px] font-bold">
                    <th class="py-3 px-4 text-center">Urutan</th>
                    <th class="py-3 px-4">Nama Bank / Kanal</th>
                    <th class="py-3 px-4">Nomor Rekening</th>
                    <th class="py-3 px-4">Atas Nama</th>
                    <th class="py-3 px-4">Kategori Alokasi</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-antique-100">
                <?php foreach ($rekening as $r): ?>
                    <tr class="hover:bg-warm-50/60 transition">
                        <td class="py-3.5 px-4 text-center font-bold text-warm-900">
                            #<?= $r['urutan'] ?>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-warm-900">
                            <?= e($r['nama_bank']) ?>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-cypress-800 text-sm">
                            <?= e($r['nomor_rekening']) ?>
                        </td>
                        <td class="py-3.5 px-4 text-warm-900">
                            <?= e($r['atas_nama']) ?>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-antique-50 text-antique-800 border border-antique-200">
                                <?= e($r['kategori_donasi']) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $r['is_active'] ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-600' ?>">
                                <?= $r['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick='editRekening(<?= json_encode($r) ?>)' class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <a href="rekening-admin.php?hapus=<?= $r['id'] ?>" data-hapus data-judul="Hapus Rekening" data-pesan="Rekening <?= e($r['nama_bank'] ?? '') ?> a.n. <?= e($r['atas_nama'] ?? '') ?> akan dihapus. Lanjutkan?" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50" title="Hapus">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah/Edit Rekening -->
<div id="modalRekening" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 border border-antique-300 shadow-2xl space-y-4 text-xs">
        <div class="flex items-center justify-between border-b border-antique-200 pb-3">
            <h3 id="modalRekTitle" class="font-classic text-lg font-bold text-warm-900">Tambah Rekening Baru</h3>
            <button type="button" onclick="tutupModalRekening()" class="text-stone-400 hover:text-stone-700 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="rekening-admin.php" class="space-y-4">
            <input type="hidden" id="rekAksi" name="aksi" value="tambah">
            <input type="hidden" id="rekId" name="id" value="0">

            <div>
                <label class="block font-bold text-warm-800 mb-1">Nama Bank / Dompet Digital <span class="text-red-500">*</span></label>
                <input type="text" id="rekBank" name="nama_bank" required placeholder="Contoh: Bank Syariah Indonesia (BSI)" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-semibold focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Nomor Rekening / No. Virtual Account <span class="text-red-500">*</span></label>
                <input type="text" id="rekNo" name="nomor_rekening" required placeholder="Contoh: 7123-4567-89" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-mono font-bold focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Atas Nama Rekening <span class="text-red-500">*</span></label>
                <input type="text" id="rekNama" name="atas_nama" required placeholder="Contoh: MASJID JAMI NURUL IMAN" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Kategori / Peruntukan Donasi</label>
                    <input type="text" id="rekKat" name="kategori_donasi" placeholder="Contoh: Kas Umum / Yatim" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Urutan Tampil</label>
                    <input type="number" id="rekUrutan" name="urutan" min="1" value="1" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-bold focus:outline-none">
                </div>
            </div>

            <div class="p-3 bg-warm-50 rounded-xl border border-antique-200">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-warm-900">
                    <input type="checkbox" id="rekActive" name="is_active" value="1" checked class="accent-cypress-700 w-4 h-4 rounded">
                    <span>Status Aktif (Tampilkan di Pilihan Donasi)</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalRekening()" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-700 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold shadow-xs">Simpan Rekening</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalRekening() {
    document.getElementById('rekAksi').value = 'tambah';
    document.getElementById('rekId').value = '0';
    document.getElementById('modalRekTitle').textContent = 'Tambah Rekening Baru';
    document.getElementById('rekBank').value = '';
    document.getElementById('rekNo').value = '';
    document.getElementById('rekNama').value = '';
    document.getElementById('rekKat').value = 'Kas Operasional & Infaq Umum';
    document.getElementById('rekUrutan').value = '1';
    document.getElementById('rekActive').checked = true;
    document.getElementById('modalRekening').classList.remove('hidden');
}

function editRekening(r) {
    document.getElementById('rekAksi').value = 'edit';
    document.getElementById('rekId').value = r.id;
    document.getElementById('modalRekTitle').textContent = 'Edit Rekening #' + r.id;
    document.getElementById('rekBank').value = r.nama_bank;
    document.getElementById('rekNo').value = r.nomor_rekening;
    document.getElementById('rekNama').value = r.atas_nama;
    document.getElementById('rekKat').value = r.kategori_donasi;
    document.getElementById('rekUrutan').value = r.urutan;
    document.getElementById('rekActive').checked = r.is_active == 1;
    document.getElementById('modalRekening').classList.remove('hidden');
}

function tutupModalRekening() {
    document.getElementById('modalRekening').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
