<?php
// program-admin.php - Manajemen Program & Donasi Masjid (Scope 4.b)
require_once __DIR__ . '/../config/database.php';
cek_role(['admin', 'content_admin']);

$profil = get_profil_masjid();
$user   = $_SESSION['user'];
$pesan  = '';
$tipe   = '';

// 1. TAMBAH PROGRAM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama      = trim($_POST['nama_program'] ?? '');
    $kategori  = $_POST['kategori'] ?? 'donasi';
    $target    = (float)str_replace(['Rp', '.', ' ', ','], '', $_POST['target_donasi'] ?? '0');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $lengkap   = trim($_POST['deskripsi_lengkap'] ?? '');
    $tglMulai  = $_POST['tanggal_mulai'] ?? date('Y-m-d');
    $tglAkhir  = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
    $status    = $_POST['status'] ?? 'aktif';

    if (empty($nama)) {
        $pesan = 'Nama program wajib diisi.';
        $tipe  = 'error';
    } else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nama))) . '-' . rand(100, 999);
        $gambar = null;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $gambar = upload_berkas('gambar', 'program');
        }

        $stmt = $pdo->prepare("INSERT INTO program_donasi (nama_program, slug, kategori, deskripsi, deskripsi_lengkap, gambar, target_donasi, tanggal_mulai, tanggal_selesai, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $slug, $kategori, $deskripsi, $lengkap, $gambar, $target, $tglMulai, $tglAkhir, $status]);

        $pesan = "Program '{$nama}' berhasil ditambahkan!";
        $tipe  = 'success';
    }
}

// 2. EDIT PROGRAM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id        = (int)($_POST['id'] ?? 0);
    $nama      = trim($_POST['nama_program'] ?? '');
    $kategori  = $_POST['kategori'] ?? 'donasi';
    $target    = (float)str_replace(['Rp', '.', ' ', ','], '', $_POST['target_donasi'] ?? '0');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $lengkap   = trim($_POST['deskripsi_lengkap'] ?? '');
    $tglMulai  = $_POST['tanggal_mulai'] ?? date('Y-m-d');
    $tglAkhir  = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
    $status    = $_POST['status'] ?? 'aktif';

    if ($id > 0 && !empty($nama)) {
        $stmt = $pdo->prepare("UPDATE program_donasi SET nama_program = ?, kategori = ?, deskripsi = ?, deskripsi_lengkap = ?, target_donasi = ?, tanggal_mulai = ?, tanggal_selesai = ?, status = ? WHERE id = ?");
        $stmt->execute([$nama, $kategori, $deskripsi, $lengkap, $target, $tglMulai, $tglAkhir, $status, $id]);

        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $gambar = upload_berkas('gambar', 'program');
            if ($gambar) {
                $pdo->prepare("UPDATE program_donasi SET gambar = ? WHERE id = ?")->execute([$gambar, $id]);
            }
        }

        $pesan = "Program '{$nama}' berhasil diperbarui!";
        $tipe  = 'success';
    }
}

// 3. HAPUS PROGRAM
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM program_donasi WHERE id = ?")->execute([$idHapus]);
    header('Location: program-admin.php?msg=deleted');
    exit;
}

$pag = paginate_data($pdo, "SELECT * FROM program_donasi ORDER BY id DESC", [], 10);
$programs = $pag['items'];

$pageTitle    = 'Kelola Program Donasi · ' . $profil['nama_masjid'];
$activeMenu   = 'program-admin';
$pageSubtitle = 'Katalog Program, Donasi & Kegiatan';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">
            Kelola Program &amp; Donasi Masjid
        </h2>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">
            Tambah, edit, dan kelola target dana serta status program kemaslahatan masjid.
        </p>
    </div>

    <button type="button" onclick="bukaModalProgram()" class="px-4 py-2.5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs shadow-xs transition flex items-center gap-2">
        <i class="fa-solid fa-plus"></i>
        <span>Tambah Program Baru</span>
    </button>
</div>

<?php if ($pesan): ?>
    <div class="p-4 rounded-2xl <?= $tipe === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?> text-xs font-semibold flex items-center gap-2">
        <i class="fa-solid <?= $tipe === 'success' ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-red-600' ?>"></i>
        <span><?= e($pesan) ?></span>
    </div>
<?php endif; ?>

<!-- Tabel Program -->
<div class="bg-white rounded-3xl border border-antique-300/50 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-antique-200 flex items-center justify-between">
        <h3 class="font-classic text-base font-bold text-warm-900">
            Daftar Program Terdaftar
        </h3>
        <span class="text-xs text-warm-800/60 font-semibold"><?= number_format($pag['total']) ?> Program</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-antique-200 bg-warm-50/70 text-warm-800/70 uppercase text-[10px] font-bold">
                    <th class="py-3 px-4">Nama Program &amp; Kategori</th>
                    <th class="py-3 px-4">Target Donasi</th>
                    <th class="py-3 px-4">Terkumpul</th>
                    <th class="py-3 px-4">Progress</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-antique-100">
                <?php foreach ($programs as $p): 
                    $persen = $p['target_donasi'] > 0 ? min(100, round(($p['dana_terkumpul'] / $p['target_donasi']) * 100)) : 100;
                ?>
                    <tr class="hover:bg-warm-50/60 transition">
                        <td class="py-3.5 px-4 align-top">
                            <div class="flex items-center gap-3">
                                <?php if (!empty($p['gambar'])): ?>
                                    <img src="<?= e(upload_url($p['gambar'])) ?>" alt="Foto Program" class="w-16 h-12 rounded-lg object-cover shrink-0 border border-antique-200">
                                <?php else: ?>
                                    <div class="w-16 h-12 rounded-lg bg-warm-100 border border-antique-200 flex items-center justify-center shrink-0 text-antique-400">
                                        <i class="fa-solid fa-hand-holding-heart text-lg"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="min-w-0">
                                    <span class="font-bold text-sm text-warm-900 block"><?= e($p['nama_program']) ?></span>
                                    <span class="text-[10px] text-antique-700 font-bold uppercase bg-antique-50 px-2 py-0.5 rounded border border-antique-200 inline-block mt-1">
                                        <?= e($p['kategori']) ?>
                                    </span>
                                    <span class="text-[10px] text-warm-800/50 block mt-1">Mulai: <?= tanggal_indo($p['tanggal_mulai']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 align-top whitespace-nowrap font-classic font-bold text-warm-900">
                            <?= format_rupiah($p['target_donasi']) ?>
                        </td>
                        <td class="py-3.5 px-4 align-top whitespace-nowrap font-classic font-bold text-emerald-700">
                            <?= format_rupiah($p['dana_terkumpul']) ?>
                        </td>
                        <td class="py-3.5 px-4 align-top whitespace-nowrap">
                            <div class="w-24">
                                <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                                    <span><?= $persen ?>%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-warm-100 overflow-hidden">
                                    <div class="h-full bg-cypress-700 rounded-full" style="width: <?= $persen ?>%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 align-top text-center whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $p['status'] === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-700' ?>">
                                <?= e($p['status']) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <a href="../home/program-detail.php?id=<?= $p['id'] ?>" target="_blank" class="p-1.5 rounded-lg text-cypress-700 hover:bg-cypress-50" title="Pratinjau">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                                <button type="button" onclick='editProgram(<?= json_encode($p) ?>)' class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <a href="program-admin.php?hapus=<?= $p['id'] ?>" data-hapus data-judul="Hapus Program" data-pesan="Program '<?= e($p['nama_program'] ?? '') ?>' beserta progres donasinya akan dihapus. Lanjutkan?" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50" title="Hapus">
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
</div>

<!-- Modal Tambah/Edit Program -->
<div id="modalProgram" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 border border-antique-300 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto text-xs">
        <div class="flex items-center justify-between border-b border-antique-200 pb-3">
            <h3 id="modalProgTitle" class="font-classic text-lg font-bold text-warm-900">Tambah Program Baru</h3>
            <button type="button" onclick="tutupModalProgram()" class="text-stone-400 hover:text-stone-700 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="program-admin.php" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" id="progAksi" name="aksi" value="tambah">
            <input type="hidden" id="progId" name="id" value="0">

            <div>
                <label class="block font-bold text-warm-800 mb-1">Nama Program <span class="text-red-500">*</span></label>
                <input type="text" id="progNama" name="nama_program" required placeholder="Contoh: Pembangunan Menara & Wudhu..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-semibold focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Kategori Program</label>
                    <select id="progKategori" name="kategori" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                        <option value="sosial">Sosial &amp; Yatim</option>
                        <option value="pendidikan">Pendidikan</option>
                        <option value="keagamaan">Keagamaan &amp; Al-Qur'an</option>
                        <option value="operasional">Operasional &amp; Fisik</option>
                        <option value="donasi">Donasi Terikat</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Target Donasi (Rp)</label>
                    <input type="number" id="progTarget" name="target_donasi" min="0" step="10000" placeholder="0" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-bold focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Tanggal Mulai</label>
                    <input type="date" id="progMulai" name="tanggal_mulai" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Tanggal Selesai</label>
                    <input type="date" id="progSelesai" name="tanggal_selesai" class="w-full px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Status</label>
                    <select id="progStatus" name="status" class="w-full px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai</option>
                        <option value="terpenuhi">Terpenuhi</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Deskripsi Singkat</label>
                <textarea id="progDesk" name="deskripsi" rows="2" placeholder="Ringkasan 1-2 kalimat program..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Deskripsi Lengkap / Rincian Kebutuhan</label>
                <textarea id="progLengkap" name="deskripsi_lengkap" rows="4" placeholder="Rincian lengkap pengadaan, tujuan program, dll..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Foto / Gambar Banner Program</label>
                <input type="file" name="gambar" accept="image/*" class="w-full px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300 text-[11px] file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-cypress-800 file:text-white">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalProgram()" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-700 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold shadow-xs">Simpan Program</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalProgram() {
    document.getElementById('progAksi').value = 'tambah';
    document.getElementById('progId').value = '0';
    document.getElementById('modalProgTitle').textContent = 'Tambah Program Baru';
    document.getElementById('progNama').value = '';
    document.getElementById('progTarget').value = '';
    document.getElementById('progDesk').value = '';
    document.getElementById('progLengkap').value = '';
    document.getElementById('modalProgram').classList.remove('hidden');
}

function editProgram(p) {
    document.getElementById('progAksi').value = 'edit';
    document.getElementById('progId').value = p.id;
    document.getElementById('modalProgTitle').textContent = 'Edit Program #' + p.id;
    document.getElementById('progNama').value = p.nama_program;
    document.getElementById('progKategori').value = p.kategori;
    document.getElementById('progTarget').value = p.target_donasi;
    document.getElementById('progMulai').value = p.tanggal_mulai;
    document.getElementById('progSelesai').value = p.tanggal_selesai || '';
    document.getElementById('progStatus').value = p.status;
    document.getElementById('progDesk').value = p.deskripsi;
    document.getElementById('progLengkap').value = p.deskripsi_lengkap || '';
    document.getElementById('modalProgram').classList.remove('hidden');
}

function tutupModalProgram() {
    document.getElementById('modalProgram').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
