<?php
// banner-admin.php - Pengelolaan Slider & Banner Promosi Beranda (Scope 9)
require_once __DIR__ . '/../config/database.php';
cek_role(['admin', 'content_admin']);

$profil = get_profil_masjid();
$user   = $_SESSION['user'];
$pesan  = '';
$tipe   = '';

// 1. TAMBAH BANNER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $judul    = trim($_POST['judul'] ?? '');
    $subjudul = trim($_POST['subjudul'] ?? '');
    $link_url = trim($_POST['link_url'] ?? '');
    $tipeB    = $_POST['tipe'] ?? 'slider';
    $urutan   = (int)($_POST['urutan'] ?? 1);
    $active   = isset($_POST['is_active']) ? 1 : 0;

    if (empty($judul)) {
        $pesan = 'Judul banner wajib diisi.';
        $tipe  = 'error';
    } else {
        $gambar = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $gambar = upload_berkas('gambar', 'banner');
        }

        $stmt = $pdo->prepare("INSERT INTO banners (judul, subjudul, gambar, link_url, tipe, urutan, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$judul, $subjudul, $gambar ?: '', $link_url, $tipeB, $urutan, $active]);

        $pesan = "Banner '{$judul}' berhasil ditambahkan!";
        $tipe  = 'success';
    }
}

// 2. EDIT BANNER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id       = (int)($_POST['id'] ?? 0);
    $judul    = trim($_POST['judul'] ?? '');
    $subjudul = trim($_POST['subjudul'] ?? '');
    $link_url = trim($_POST['link_url'] ?? '');
    $tipeB    = $_POST['tipe'] ?? 'slider';
    $urutan   = (int)($_POST['urutan'] ?? 1);
    $active   = isset($_POST['is_active']) ? 1 : 0;

    if ($id > 0 && !empty($judul)) {
        $stmt = $pdo->prepare("UPDATE banners SET judul = ?, subjudul = ?, link_url = ?, tipe = ?, urutan = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$judul, $subjudul, $link_url, $tipeB, $urutan, $active, $id]);

        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $gambar = upload_berkas('gambar', 'banner');
            if ($gambar) {
                $pdo->prepare("UPDATE banners SET gambar = ? WHERE id = ?")->execute([$gambar, $id]);
            }
        }

        $pesan = "Banner '{$judul}' berhasil diperbarui!";
        $tipe  = 'success';
    }
}

// 3. HAPUS BANNER
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM banners WHERE id = ?")->execute([$idHapus]);
    header('Location: banner-admin.php?msg=deleted');
    exit;
}

$pag = paginate_data($pdo, "SELECT * FROM banners ORDER BY urutan ASC, id DESC", [], 10);
$banners = $pag['items'];

$pageTitle    = 'Kelola Slider & Banner · ' . $profil['nama_masjid'];
$activeMenu   = 'banner-admin';
$pageSubtitle = 'Pengelolaan Slider Promosi & Banner Beranda';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">
            Slider Promosi &amp; Banner Beranda
        </h2>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">
            Kelola hero slider, banner kegiatan, program donasi, dan pengumuman di beranda utama.
        </p>
    </div>

    <button type="button" onclick="bukaModalBanner()" class="px-4 py-2.5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs shadow-xs transition flex items-center gap-2">
        <i class="fa-solid fa-plus"></i>
        <span>Tambah Banner Baru</span>
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
            Daftar Slider &amp; Banner
        </h3>
        <span class="text-xs text-warm-800/60 font-semibold"><?= number_format($pag['total']) ?> Banner</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-antique-200 bg-warm-50/70 text-warm-800/70 uppercase text-[10px] font-bold">
                    <th class="py-3 px-4 text-center">Urutan</th>
                    <th class="py-3 px-4">Judul &amp; Subjudul</th>
                    <th class="py-3 px-4">Tipe Banner</th>
                    <th class="py-3 px-4">Tautan Link</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-antique-100">
                <?php foreach ($banners as $b): ?>
                    <tr class="hover:bg-warm-50/60 transition">
                        <td class="py-3.5 px-4 text-center font-bold text-warm-900">
                            #<?= $b['urutan'] ?>
                        </td>
                        <td class="py-3.5 px-4 align-top max-w-sm">
                            <strong class="text-sm font-bold text-warm-900 block"><?= e($b['judul']) ?></strong>
                            <span class="text-[11px] text-warm-800/60 block mt-0.5"><?= e($b['subjudul']) ?></span>
                        </td>
                        <td class="py-3.5 px-4 align-top whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-warm-100 text-warm-800 uppercase">
                                <?= str_replace('_', ' ', e($b['tipe'])) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 align-top text-stone-600 font-mono text-[11px]">
                            <?= e($b['link_url'] ?: '-') ?>
                        </td>
                        <td class="py-3.5 px-4 align-top text-center whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $b['is_active'] ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-600' ?>">
                                <?= $b['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick='editBanner(<?= json_encode($b) ?>)' class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <a href="banner-admin.php?hapus=<?= $b['id'] ?>" data-hapus data-judul="Hapus Banner" data-pesan="Banner ini akan dihapus permanen. Lanjutkan?" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50" title="Hapus">
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

<!-- Modal Tambah/Edit Banner -->
<div id="modalBanner" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-cypress-950/50 veil-blur"></div>
    <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 border border-antique-300 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto text-xs">
        <div class="flex items-center justify-between border-b border-antique-200 pb-3">
            <h3 id="modalBannerTitle" class="font-classic text-lg font-bold text-warm-900">Tambah Banner Baru</h3>
            <button type="button" onclick="tutupModalBanner()" class="text-stone-400 hover:text-stone-700 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="banner-admin.php" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" id="bannerAksi" name="aksi" value="tambah">
            <input type="hidden" id="bannerId" name="id" value="0">

            <div>
                <label class="block font-bold text-warm-800 mb-1">Judul Utama <span class="text-red-500">*</span></label>
                <input type="text" id="bannerJudul" name="judul" required placeholder="Contoh: Selamat Datang di Masjid..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-semibold focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Subjudul / Deskripsi Pendek</label>
                <input type="text" id="bannerSub" name="subjudul" placeholder="Pesan inspiratif singkat..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Tipe Banner</label>
                    <select id="bannerTipe" name="tipe" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                        <option value="slider">Slider Hero Beranda</option>
                        <option value="banner_kegiatan">Banner Kegiatan</option>
                        <option value="banner_program">Banner Program Donasi</option>
                        <option value="banner_donasi">Banner Promosi Infaq</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Urutan Tampil (1, 2, ...)</label>
                    <input type="number" id="bannerUrutan" name="urutan" min="1" value="1" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-bold focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Tautan / Link Tujuan (Opsional)</label>
                <input type="text" id="bannerLink" name="link_url" placeholder="Contoh: home/program.php atau home/donasi-online.php" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Upload Gambar Banner</label>
                <input type="file" name="gambar" accept="image/*" class="w-full px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300 text-[11px] file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-cypress-800 file:text-white">
            </div>

            <div class="p-3 bg-warm-50 rounded-xl border border-antique-200">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-warm-900">
                    <input type="checkbox" id="bannerActive" name="is_active" value="1" checked class="accent-cypress-700 w-4 h-4 rounded">
                    <span>Status Aktif (Tampilkan di Beranda)</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalBanner()" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-700 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold shadow-xs">Simpan Banner</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalBanner() {
    document.getElementById('bannerAksi').value = 'tambah';
    document.getElementById('bannerId').value = '0';
    document.getElementById('modalBannerTitle').textContent = 'Tambah Banner Baru';
    document.getElementById('bannerJudul').value = '';
    document.getElementById('bannerSub').value = '';
    document.getElementById('bannerLink').value = '';
    document.getElementById('bannerUrutan').value = '1';
    document.getElementById('bannerActive').checked = true;
    document.getElementById('modalBanner').classList.remove('hidden');
}

function editBanner(b) {
    document.getElementById('bannerAksi').value = 'edit';
    document.getElementById('bannerId').value = b.id;
    document.getElementById('modalBannerTitle').textContent = 'Edit Banner #' + b.id;
    document.getElementById('bannerJudul').value = b.judul;
    document.getElementById('bannerSub').value = b.subjudul || '';
    document.getElementById('bannerTipe').value = b.tipe;
    document.getElementById('bannerLink').value = b.link_url || '';
    document.getElementById('bannerUrutan').value = b.urutan;
    document.getElementById('bannerActive').checked = b.is_active == 1;
    document.getElementById('modalBanner').classList.remove('hidden');
}

function tutupModalBanner() {
    document.getElementById('modalBanner').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
