<?php
// berita-admin.php - Pengelolaan Berita & Warta Masjid (Scope 15)
require_once __DIR__ . '/../config/database.php';
cek_role(['admin', 'content_admin']);

$profil = get_profil_masjid();
$user   = $_SESSION['user'];
$pesan  = '';
$tipe   = '';

// HANDLE AJAX REQUEST UNTUK GET BERITA
if (isset($_GET['get_berita'])) {
    header('Content-Type: application/json');
    $id = (int)$_GET['get_berita'];
    $stmt = $pdo->prepare("SELECT * FROM berita WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($result ?: []);
    exit;
}

// 1. TAMBAH BERITA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $judul     = trim($_POST['judul'] ?? '');
    $kategori  = $_POST['kategori'] ?? 'Kajian & Kegiatan';
    $tglPublik = $_POST['tanggal_publikasi'] ?? date('Y-m-d');
    $status    = $_POST['status'] ?? 'published';
    $ringkasan = trim($_POST['ringkasan'] ?? '');
    $isi       = trim($_POST['isi'] ?? '');

    if (empty($judul) || empty($isi)) {
        $pesan = 'Judul dan isi berita wajib diisi.';
        $tipe  = 'error';
    } else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $judul))) . '-' . rand(100, 999);
        $thumb = null;
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $thumb = upload_berkas('thumbnail', 'berita');
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO berita (judul, slug, isi, ringkasan, thumbnail, kategori, penulis_id, tanggal_publikasi, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$judul, $slug, $isi, $ringkasan, $thumb, $kategori, $user['id'], $tglPublik, $status])) {
                $pesan = "Berita '{$judul}' berhasil disimpan!";
                $tipe  = 'success';
            }
        } catch (Exception $e) {
            $pesan = 'Gagal menyimpan berita: ' . $e->getMessage();
            $tipe  = 'error';
        }
    }
}

// 2. EDIT BERITA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id        = (int)($_POST['id'] ?? 0);
    $judul     = trim($_POST['judul'] ?? '');
    $kategori  = $_POST['kategori'] ?? 'Kajian & Kegiatan';
    $tglPublik = $_POST['tanggal_publikasi'] ?? date('Y-m-d');
    $status    = $_POST['status'] ?? 'published';
    $ringkasan = trim($_POST['ringkasan'] ?? '');
    $isi       = trim($_POST['isi'] ?? '');

    if ($id <= 0) {
        $pesan = 'ID berita tidak valid.';
        $tipe  = 'error';
    } elseif (empty($judul) || empty($isi)) {
        $pesan = 'Judul dan isi berita wajib diisi.';
        $tipe  = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE berita SET judul = ?, kategori = ?, tanggal_publikasi = ?, status = ?, ringkasan = ?, isi = ? WHERE id = ?");
            if ($stmt->execute([$judul, $kategori, $tglPublik, $status, $ringkasan, $isi, $id])) {
                if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                    $thumb = upload_berkas('thumbnail', 'berita');
                    if ($thumb) {
                        $pdo->prepare("UPDATE berita SET thumbnail = ? WHERE id = ?")->execute([$thumb, $id]);
                    }
                }
                $pesan = "Berita '{$judul}' berhasil diperbarui!";
                $tipe  = 'success';
            }
        } catch (Exception $e) {
            $pesan = 'Gagal memperbarui berita: ' . $e->getMessage();
            $tipe  = 'error';
        }
    }
}

// 3. HAPUS BERITA
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    if ($idHapus > 0) {
        try {
            $pdo->prepare("DELETE FROM berita WHERE id = ?")->execute([$idHapus]);
            header('Location: berita-admin.php?msg=deleted');
            exit;
        } catch (Exception $e) {
            die("Gagal menghapus berita: " . $e->getMessage());
        }
    }
}

$pag = paginate_data($pdo, "SELECT b.id, b.judul, b.kategori, b.tanggal_publikasi, b.status, b.views, b.thumbnail, b.isi, b.ringkasan, u.nama_lengkap as penulis FROM berita b LEFT JOIN users u ON b.penulis_id = u.id ORDER BY b.id DESC", [], 10);
$beritaList = $pag['items'];

$pageTitle    = 'Kelola Berita & Warta · ' . $profil['nama_masjid'];
$activeMenu   = 'berita-admin';
$pageSubtitle = 'Pengelolaan Berita, Kajian & Artikel Masjid';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">
            Manajemen Berita &amp; Artikel
        </h2>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">
            Publikasikan artikel dakwah, liputan kegiatan, dan jadwal taklim berkala.
        </p>
    </div>

    <button type="button" onclick="bukaModalBerita()" class="px-4 py-2.5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs shadow-xs transition flex items-center gap-2">
        <i class="fa-solid fa-plus"></i>
        <span>Tulis Berita Baru</span>
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
            Daftar Artikel &amp; Berita
        </h3>
        <span class="text-xs text-warm-800/60 font-semibold"><?= number_format($pag['total']) ?> Artikel</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-antique-200 bg-warm-50/70 text-warm-800/70 uppercase text-[10px] font-bold">
                    <th class="py-3 px-4">Judul Artikel &amp; Penulis</th>
                    <th class="py-3 px-4">Kategori</th>
                    <th class="py-3 px-4">Tanggal Publikasi</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Views</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-antique-100">
                <?php foreach ($beritaList as $b): ?>
                    <tr class="hover:bg-warm-50/60 transition">
                        <td class="py-3 px-4 align-middle max-w-sm">
                            <div class="flex items-center gap-3">
                                <?php if (!empty($b['thumbnail'])): ?>
                                    <img src="<?= e(upload_url($b['thumbnail'])) ?>" alt="Thumbnail" class="w-16 h-12 rounded-lg object-cover shrink-0 border border-antique-200">
                                <?php else: ?>
                                    <div class="w-16 h-12 rounded-lg bg-warm-100 border border-antique-200 flex items-center justify-center shrink-0 text-antique-400">
                                        <i class="fa-solid fa-image text-lg"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="min-w-0">
                                    <span class="font-bold text-sm text-warm-900 block line-clamp-2"><?= e($b['judul']) ?></span>
                                    <span class="text-[11px] text-warm-800/50 block mt-0.5">Penulis: <?= e($b['penulis'] ?: 'Redaksi') ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 align-middle whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-warm-100 text-warm-800 uppercase">
                                <?= e($b['kategori']) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 align-middle whitespace-nowrap text-[11px]">
                            <?= tanggal_indo($b['tanggal_publikasi']) ?>
                        </td>
                        <td class="py-3.5 px-4 align-middle text-center whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase inline-block <?= $b['status'] === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-600' ?>">
                                <?= e($b['status']) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 align-middle text-center font-bold text-stone-600 whitespace-nowrap">
                            <?= $b['views'] ?>x
                        </td>
                        <td class="py-3.5 px-4 align-middle text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <a href="../home/berita-detail.php?id=<?= $b['id'] ?>" target="_blank" class="p-1.5 rounded-lg text-cypress-700 hover:bg-cypress-50 transition" title="Pratinjau">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                </a>
                                <button type="button" onclick="bukaModalEditBerita(<?= $b['id'] ?>)" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <a href="berita-admin.php?hapus=<?= $b['id'] ?>" data-hapus data-judul="Hapus Artikel" data-pesan="Artikel '<?= e($b['judul']) ?>' akan dihapus permanen dari daftar berita. Lanjutkan?" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition" title="Hapus">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
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

<!-- Modal Tambah/Edit Berita -->
<div id="modalBerita" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-cypress-950/50 veil-blur"></div>
    <div class="relative bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 border border-antique-300 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto text-xs">
        <div class="flex items-center justify-between border-b border-antique-200 pb-3">
            <h3 id="modalBeritaTitle" class="font-classic text-lg font-bold text-warm-900">Tulis Berita Baru</h3>
            <button type="button" onclick="tutupModalBerita()" class="text-stone-400 hover:text-stone-700 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="berita-admin.php" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" id="beritaAksi" name="aksi" value="tambah">
            <input type="hidden" id="beritaId" name="id" value="0">

            <div>
                <label class="block font-bold text-warm-800 mb-1">Judul Artikel / Berita <span class="text-red-500">*</span></label>
                <input type="text" id="beritaJudul" name="judul" required placeholder="Contoh: Semarak Maulid Nabi Muhammad SAW..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-semibold focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Kategori</label>
                    <select id="beritaKategori" name="kategori" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                        <option value="Kajian & Kegiatan">Kajian &amp; Kegiatan</option>
                        <option value="Sosial Ummat">Sosial Ummat</option>
                        <option value="Jadwal Kajian">Jadwal Kajian</option>
                        <option value="Pengumuman DKM">Pengumuman DKM</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Tanggal Publikasi</label>
                    <input type="date" id="beritaTgl" name="tanggal_publikasi" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Status Publikasi</label>
                    <select id="beritaStatus" name="status" class="w-full px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                        <option value="published">Published (Tayang)</option>
                        <option value="draft">Draft (Konsep)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Ringkasan Singkat (Lead Paragraph)</label>
                <textarea id="beritaRingkas" name="ringkasan" rows="2" placeholder="Ringkasan 1 kalimat yang memikat pembaca..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Isi Lengkap Berita (Mendukung HTML &amp; Paragraf) <span class="text-red-500">*</span></label>
                <textarea id="beritaIsi" name="isi" rows="8" required placeholder="Tuliskan isi berita di sini..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-mono text-xs focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Upload Thumbnail / Gambar Berita</label>
                <input type="file" name="thumbnail" accept="image/*" class="w-full px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300 text-[11px] file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-cypress-800 file:text-white">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalBerita()" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-700 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold shadow-xs">Simpan Berita</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalBerita() {
    document.getElementById('beritaAksi').value = 'tambah';
    document.getElementById('beritaId').value = '0';
    document.getElementById('modalBeritaTitle').textContent = 'Tulis Berita Baru';
    document.getElementById('beritaJudul').value = '';
    document.getElementById('beritaRingkas').value = '';
    document.getElementById('beritaIsi').value = '';
    document.getElementById('modalBerita').classList.remove('hidden');
}

function bukaModalEditBerita(id) {
    fetch(`berita-admin.php?get_berita=${id}`)
        .then(r => r.json())
        .then(b => {
            document.getElementById('beritaAksi').value = 'edit';
            document.getElementById('beritaId').value = b.id;
            document.getElementById('modalBeritaTitle').textContent = 'Edit Berita #' + b.id;
            document.getElementById('beritaJudul').value = b.judul;
            document.getElementById('beritaKategori').value = b.kategori;
            document.getElementById('beritaTgl').value = b.tanggal_publikasi;
            document.getElementById('beritaStatus').value = b.status;
            document.getElementById('beritaRingkas').value = b.ringkasan || '';
            document.getElementById('beritaIsi').value = b.isi;
            document.getElementById('modalBerita').classList.remove('hidden');
        });
}

function tutupModalBerita() {
    document.getElementById('modalBerita').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
