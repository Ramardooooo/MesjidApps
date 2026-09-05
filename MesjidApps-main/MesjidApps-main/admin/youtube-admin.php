<?php
// youtube-admin.php - Integrasi Video YouTube & Live Streaming (Scope 10)
require_once __DIR__ . '/../config/database.php';
cek_role(['admin', 'content_admin']);

$profil = get_profil_masjid();
$user   = $_SESSION['user'];
$pesan  = '';
$tipe   = '';

// Helper extract YouTube ID
function extractYouTubeId($url) {
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
        return $match[1];
    }
    return trim($url);
}

// 1. TAMBAH VIDEO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $judul     = trim($_POST['judul'] ?? '');
    $urlInput  = trim($_POST['url_youtube'] ?? '');
    $kategori  = $_POST['kategori'] ?? 'kajian';
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $active    = isset($_POST['is_active']) ? 1 : 0;

    $videoId = extractYouTubeId($urlInput);

    if (empty($judul) || empty($videoId)) {
        $pesan = 'Judul dan URL / ID video YouTube wajib diisi.';
        $tipe  = 'error';
    } else {
        $urlEmbed = 'https://www.youtube.com/embed/' . $videoId;
        $stmt = $pdo->prepare("INSERT INTO youtube_videos (judul, video_id, url_embed, kategori, deskripsi, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$judul, $videoId, $urlEmbed, $kategori, $deskripsi, $active]);

        $pesan = "Video '{$judul}' berhasil ditambahkan!";
        $tipe  = 'success';
    }
}

// 2. EDIT VIDEO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id        = (int)($_POST['id'] ?? 0);
    $judul     = trim($_POST['judul'] ?? '');
    $urlInput  = trim($_POST['url_youtube'] ?? '');
    $kategori  = $_POST['kategori'] ?? 'kajian';
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $active    = isset($_POST['is_active']) ? 1 : 0;

    $videoId = extractYouTubeId($urlInput);

    if ($id > 0 && !empty($judul) && !empty($videoId)) {
        $urlEmbed = 'https://www.youtube.com/embed/' . $videoId;
        $stmt = $pdo->prepare("UPDATE youtube_videos SET judul = ?, video_id = ?, url_embed = ?, kategori = ?, deskripsi = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$judul, $videoId, $urlEmbed, $kategori, $deskripsi, $active, $id]);

        $pesan = "Video '{$judul}' berhasil diperbarui!";
        $tipe  = 'success';
    }
}

// 3. HAPUS VIDEO
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM youtube_videos WHERE id = ?")->execute([$idHapus]);
    header('Location: youtube-admin.php?msg=deleted');
    exit;
}

$videos = $pdo->query("SELECT * FROM youtube_videos ORDER BY id DESC")->fetchAll();

$pageTitle    = 'Kelola Video YouTube · ' . $profil['nama_masjid'];
$activeMenu   = 'youtube-admin';
$pageSubtitle = 'Pengelolaan Siaran Live Streaming & Video Kajian';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">
            Integrasi Video &amp; Live Streaming YouTube
        </h2>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">
            Kelola tautan video siaran shalat Jumat, majelis kajian, dan dokumentasi kegiatan.
        </p>
    </div>

    <button type="button" onclick="bukaModalVideo()" class="px-4 py-2.5 rounded-xl bg-red-700 hover:bg-red-800 text-white font-bold text-xs shadow-xs transition flex items-center gap-2">
        <i class="fa-brands fa-youtube"></i>
        <span>Tambah Video Baru</span>
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
            Daftar Video YouTube Aktif
        </h3>
        <span class="text-xs text-warm-800/60 font-semibold"><?= count($videos) ?> Video</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-antique-200 bg-warm-50/70 text-warm-800/70 uppercase text-[10px] font-bold">
                    <th class="py-3 px-4">Judul &amp; Deskripsi</th>
                    <th class="py-3 px-4">Kategori</th>
                    <th class="py-3 px-4">Video ID / Link</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-antique-100">
                <?php foreach ($videos as $v): ?>
                    <tr class="hover:bg-warm-50/60 transition">
                        <td class="py-3.5 px-4 align-top max-w-sm">
                            <strong class="text-sm font-bold text-warm-900 block"><?= e($v['judul']) ?></strong>
                            <span class="text-[11px] text-warm-800/60 block mt-0.5 line-clamp-1"><?= e($v['deskripsi']) ?></span>
                        </td>
                        <td class="py-3.5 px-4 align-top whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $v['kategori'] === 'live_streaming' ? 'bg-red-100 text-red-800' : 'bg-warm-100 text-warm-800' ?>">
                                <?= str_replace('_', ' ', e($v['kategori'])) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 align-top font-mono text-[11px] text-stone-600">
                            <?= e($v['video_id']) ?>
                        </td>
                        <td class="py-3.5 px-4 align-top text-center whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $v['is_active'] ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-600' ?>">
                                <?= $v['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <a href="https://youtube.com/watch?v=<?= e($v['video_id']) ?>" target="_blank" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50" title="Buka di YouTube">
                                    <i class="fa-solid fa-play"></i>
                                </a>
                                <button type="button" onclick='editVideo(<?= json_encode($v) ?>)' class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <a href="youtube-admin.php?hapus=<?= $v['id'] ?>" onclick="return confirm('Hapus video ini?');" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50" title="Hapus">
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

<!-- Modal Tambah/Edit Video -->
<div id="modalVideo" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 border border-antique-300 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto text-xs">
        <div class="flex items-center justify-between border-b border-antique-200 pb-3">
            <h3 id="modalVideoTitle" class="font-classic text-lg font-bold text-warm-900">Tambah Video YouTube</h3>
            <button type="button" onclick="tutupModalVideo()" class="text-stone-400 hover:text-stone-700 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="youtube-admin.php" class="space-y-4">
            <input type="hidden" id="videoAksi" name="aksi" value="tambah">
            <input type="hidden" id="videoId" name="id" value="0">

            <div>
                <label class="block font-bold text-warm-800 mb-1">Judul Video / Kajian <span class="text-red-500">*</span></label>
                <input type="text" id="videoJudul" name="judul" required placeholder="Contoh: Kajian Tafsir Al-Qur'an..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-semibold focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-warm-800 mb-1">URL / Video ID YouTube <span class="text-red-500">*</span></label>
                    <input type="text" id="videoUrl" name="url_youtube" required placeholder="https://youtube.com/watch?v=..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Kategori Konten</label>
                    <select id="videoKategori" name="kategori" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                        <option value="kajian">Kajian Tematik</option>
                        <option value="live_streaming">Live Streaming</option>
                        <option value="dokumentasi">Dokumentasi Kegiatan</option>
                        <option value="profil">Video Profil</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Deskripsi Ringkas</label>
                <textarea id="videoDesk" name="deskripsi" rows="3" placeholder="Pemateri, tema kajian, dsb..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none"></textarea>
            </div>

            <div class="p-3 bg-warm-50 rounded-xl border border-antique-200">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-warm-900">
                    <input type="checkbox" id="videoActive" name="is_active" value="1" checked class="accent-cypress-700 w-4 h-4 rounded">
                    <span>Status Aktif (Tampilkan di Halaman Publik)</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalVideo()" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-700 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white font-bold shadow-xs">Simpan Video</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalVideo() {
    document.getElementById('videoAksi').value = 'tambah';
    document.getElementById('videoId').value = '0';
    document.getElementById('modalVideoTitle').textContent = 'Tambah Video YouTube';
    document.getElementById('videoJudul').value = '';
    document.getElementById('videoUrl').value = '';
    document.getElementById('videoDesk').value = '';
    document.getElementById('videoActive').checked = true;
    document.getElementById('modalVideo').classList.remove('hidden');
}

function editVideo(v) {
    document.getElementById('videoAksi').value = 'edit';
    document.getElementById('videoId').value = v.id;
    document.getElementById('modalVideoTitle').textContent = 'Edit Video #' + v.id;
    document.getElementById('videoJudul').value = v.judul;
    document.getElementById('videoUrl').value = 'https://www.youtube.com/watch?v=' + v.video_id;
    document.getElementById('videoKategori').value = v.kategori;
    document.getElementById('videoDesk').value = v.deskripsi || '';
    document.getElementById('videoActive').checked = v.is_active == 1;
    document.getElementById('modalVideo').classList.remove('hidden');
}

function tutupModalVideo() {
    document.getElementById('modalVideo').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
