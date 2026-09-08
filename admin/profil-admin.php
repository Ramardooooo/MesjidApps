<?php
// profil-admin.php - Pengaturan Profil Masjid & Struktur DKM (Scope 4.a & 7)
require_once __DIR__ . '/../config/database.php';
cek_role(['admin', 'content_admin']);

$profil = get_profil_masjid();
$user   = $_SESSION['user'];
$pesan  = '';
$tipe   = '';

// 1. UPDATE PROFIL MASJID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'update_profil') {
    $nama   = trim($_POST['nama_masjid'] ?? '');
    $sebut  = trim($_POST['sebutan'] ?? '');
    $slogan = trim($_POST['slogan'] ?? '');
    $sejarah= trim($_POST['sejarah'] ?? '');
    $visi   = trim($_POST['visi'] ?? '');
    $misi   = trim($_POST['misi'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $kota   = trim($_POST['kota'] ?? '');
    $maps   = trim($_POST['google_maps_embed'] ?? '');
    $wa     = trim($_POST['whatsapp'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $ig     = trim($_POST['instagram'] ?? '');
    $yt     = trim($_POST['youtube'] ?? '');
    $fb     = trim($_POST['facebook'] ?? '');
    $tt     = trim($_POST['tiktok'] ?? '');
    $tw     = trim($_POST['twitter'] ?? '');
    $tg     = trim($_POST['telegram'] ?? '');
    $saldo  = (float)str_replace(['Rp', '.', ' ', ','], '', $_POST['saldo_awal_kas'] ?? '0');

    // Cek column mana saja yang ada di database (MySQL)
    $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'profil_masjid' AND TABLE_SCHEMA = DATABASE()")->fetchAll();
    $colNames = array_column($cols, 'COLUMN_NAME');
    
    // Build dynamic UPDATE based on available columns
    $setClause = [
        'nama_masjid = ?', 'sebutan = ?', 'slogan = ?', 'sejarah = ?', 'visi = ?', 'misi = ?',
        'alamat = ?', 'kota = ?', 'google_maps_embed = ?', 'whatsapp = ?', 'email = ?',
        'instagram = ?', 'youtube = ?', 'facebook = ?', 'saldo_awal_kas = ?'
    ];
    $values = [
        $nama, $sebut, $slogan, $sejarah, $visi, $misi,
        $alamat, $kota, $maps, $wa, $email,
        $ig, $yt, $fb, $saldo
    ];
    
    // Tambah extra socmed columns jika ada
    if (in_array('tiktok', $colNames)) {
        $setClause[] = 'tiktok = ?';
        $values[] = $tt;
    }
    if (in_array('twitter', $colNames)) {
        $setClause[] = 'twitter = ?';
        $values[] = $tw;
    }
    if (in_array('telegram', $colNames)) {
        $setClause[] = 'telegram = ?';
        $values[] = $tg;
    }

    $updateSql = "UPDATE profil_masjid SET " . implode(', ', $setClause) . " WHERE id = 1";
    $stmt = $pdo->prepare($updateSql);
    $stmt->execute($values);

    $pesan = "Profil masjid berhasil diperbarui!";
    $tipe  = 'success';
    $profil = get_profil_masjid(); // Refresh data
}

// 2. TAMBAH PENGURUS DKM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah_pengurus') {
    $namaPengurus = trim($_POST['nama'] ?? '');
    $jabatan      = trim($_POST['jabatan'] ?? '');
    $bidang       = trim($_POST['bidang'] ?? '');
    $noHp         = trim($_POST['no_hp'] ?? '');
    $urutan       = (int)($_POST['urutan'] ?? 1);

    if (!empty($namaPengurus) && !empty($jabatan)) {
        $stmt = $pdo->prepare("INSERT INTO pengurus_masjid (nama, jabatan, bidang, no_hp, urutan) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$namaPengurus, $jabatan, $bidang, $noHp, $urutan]);
        $pesan = "Pengurus DKM '{$namaPengurus}' berhasil ditambahkan!";
        $tipe  = 'success';
    }
}

// 3. HAPUS PENGURUS DKM
if (isset($_GET['hapus_pengurus'])) {
    $idHapus = (int)$_GET['hapus_pengurus'];
    $pdo->prepare("DELETE FROM pengurus_masjid WHERE id = ?")->execute([$idHapus]);
    header('Location: profil-admin.php?msg=deleted');
    exit;
}

$pengurusList = $pdo->query("SELECT * FROM pengurus_masjid ORDER BY urutan ASC, id ASC")->fetchAll();

$pageTitle    = 'Pengaturan Profil Masjid & DKM · ' . $profil['nama_masjid'];
$activeMenu   = 'profil-admin';
$pageSubtitle = 'Konfigurasi Profil, Sejarah, Visi Misi & Pengurus';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="space-y-8">
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">
            Pengaturan Profil Masjid &amp; DKM
        </h2>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">
            Sesuaikan identitas resmi masjid, sejarah, visi misi, kontak, serta susunan kepengurusan DKM.
        </p>
    </div>

    <?php if ($pesan): ?>
        <div class="p-4 rounded-2xl <?= $tipe === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?> text-xs font-semibold flex items-center gap-2">
            <i class="fa-solid <?= $tipe === 'success' ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-red-600' ?>"></i>
            <span><?= e($pesan) ?></span>
        </div>
    <?php endif; ?>

    <!-- Formulir Profil Masjid -->
    <form method="POST" action="profil-admin.php" class="bg-white rounded-3xl p-6 sm:p-10 border border-antique-300/50 shadow-sm space-y-6 text-xs">
        <input type="hidden" name="aksi" value="update_profil">

        <h3 class="font-classic text-base font-bold text-warm-900 border-b border-antique-200 pb-3">
            Identitas &amp; Sambutan Masjid
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold text-warm-800 mb-1">Nama Resmi Masjid <span class="text-red-500">*</span></label>
                <input type="text" name="nama_masjid" value="<?= e($profil['nama_masjid']) ?>" required class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-semibold focus:outline-none">
            </div>
            <div>
                <label class="block font-bold text-warm-800 mb-1">Sebutan / Tagline Pendek</label>
                <input type="text" name="sebutan" value="<?= e($profil['sebutan']) ?>" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block font-bold text-warm-800 mb-1">Slogan Masjid</label>
            <input type="text" name="slogan" value="<?= e($profil['slogan']) ?>" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
        </div>

        <div>
            <label class="block font-bold text-warm-800 mb-1">Sejarah Singkat Pendirian Masjid</label>
            <textarea name="sejarah" rows="4" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none"><?= e($profil['sejarah']) ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold text-warm-800 mb-1">Visi Masjid</label>
                <textarea name="visi" rows="3" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none"><?= e($profil['visi']) ?></textarea>
            </div>
            <div>
                <label class="block font-bold text-warm-800 mb-1">Misi Masjid</label>
                <textarea name="misi" rows="3" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none"><?= e($profil['misi']) ?></textarea>
            </div>
        </div>

        <h3 class="font-classic text-base font-bold text-warm-900 border-b border-antique-200 pb-3 pt-4">
            Kontak, Alamat &amp; Saldo Awal Pembukuan
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label class="block font-bold text-warm-800 mb-1">Alamat Lengkap Masjid</label>
                <input type="text" name="alamat" value="<?= e($profil['alamat']) ?>" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>
            <div>
                <label class="block font-bold text-warm-800 mb-1">Kota / Kabupaten</label>
                <input type="text" name="kota" value="<?= e($profil['kota']) ?>" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block font-bold text-warm-800 mb-1">Nomor WhatsApp Layanan</label>
                <input type="text" name="whatsapp" value="<?= e($profil['whatsapp']) ?>" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>
            <div>
                <label class="block font-bold text-warm-800 mb-1">Email Resmi</label>
                <input type="email" name="email" value="<?= e($profil['email']) ?>" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>
            <div>
                <label class="block font-bold text-warm-800 mb-1">Saldo Awal Kas Master (Rp)</label>
                <input type="number" name="saldo_awal_kas" value="<?= (float)$profil['saldo_awal_kas'] ?>" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-bold focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block font-bold text-warm-800 mb-1">URL Google Maps Embed</label>
            <input type="text" name="google_maps_embed" value="<?= e($profil['google_maps_embed']) ?>" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-mono text-[11px] focus:outline-none">
        </div>

        <h3 class="font-classic text-base font-bold text-warm-900 border-b border-antique-200 pb-3 pt-4">
            Media Sosial
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block font-bold text-warm-800 mb-1">Instagram <i class="fa-brands fa-instagram text-pink-500"></i></label>
                <input type="text" name="instagram" value="<?= e($profil['instagram'] ?? '') ?>" placeholder="username" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>
            <div>
                <label class="block font-bold text-warm-800 mb-1">YouTube <i class="fa-brands fa-youtube text-red-600"></i></label>
                <input type="text" name="youtube" value="<?= e($profil['youtube'] ?? '') ?>" placeholder="@channelname" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>
            <div>
                <label class="block font-bold text-warm-800 mb-1">Facebook <i class="fa-brands fa-facebook text-blue-600"></i></label>
                <input type="text" name="facebook" value="<?= e($profil['facebook'] ?? '') ?>" placeholder="username" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>
            <div>
                <label class="block font-bold text-warm-800 mb-1">TikTok <i class="fa-brands fa-tiktok text-black"></i></label>
                <input type="text" name="tiktok" value="<?= e($profil['tiktok'] ?? '') ?>" placeholder="@username" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>
            <div>
                <label class="block font-bold text-warm-800 mb-1">Twitter/X <i class="fa-brands fa-x-twitter text-black"></i></label>
                <input type="text" name="twitter" value="<?= e($profil['twitter'] ?? '') ?>" placeholder="username" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>
            <div>
                <label class="block font-bold text-warm-800 mb-1">Telegram <i class="fa-brands fa-telegram text-blue-500"></i></label>
                <input type="text" name="telegram" value="<?= e($profil['telegram'] ?? '') ?>" placeholder="username" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>
        </div>

        <div class="pt-4 border-t border-antique-200 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs shadow-xs transition">
                Simpan Perubahan Profil
            </button>
        </div>
    </form>

    <!-- Manajemen Susunan Pengurus DKM -->
    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-antique-300/50 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-antique-200 pb-4">
            <div>
                <h3 class="font-classic text-base font-bold text-warm-900">
                    Susunan Pengurus DKM Masjid
                </h3>
                <p class="text-xs text-warm-800/60 mt-0.5">Daftar asatidz dan pengurus DKM yang ditampilkan pada halaman profil.</p>
            </div>

            <!-- Form Tambah Pengurus Langsung -->
            <form method="POST" action="profil-admin.php" class="flex flex-wrap items-center gap-2 text-xs">
                <input type="hidden" name="aksi" value="tambah_pengurus">
                <input type="text" name="nama" required placeholder="Nama Pengurus..." class="px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300">
                <input type="text" name="jabatan" required placeholder="Jabatan (Ketua, Bendahara)..." class="px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300">
                <input type="number" name="urutan" value="1" min="1" class="w-16 px-2 py-1.5 rounded-xl bg-warm-50 border border-antique-300 font-bold">
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold">
                    + Tambah
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($pengurusList as $peng): ?>
                <div class="p-4 rounded-2xl border border-antique-200 bg-warm-50/50 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-[10px] font-bold text-antique-700 uppercase bg-antique-100 px-2 py-0.5 rounded">
                            #<?= $peng['urutan'] ?> <?= e($peng['jabatan']) ?>
                        </span>
                        <h4 class="font-bold text-warm-900 text-sm mt-1"><?= e($peng['nama']) ?></h4>
                        <span class="text-[11px] text-warm-800/60"><?= e($peng['bidang'] ?: 'Pengurus') ?></span>
                    </div>
                    <a href="profil-admin.php?hapus_pengurus=<?= $peng['id'] ?>" data-hapus data-judul="Hapus Pengurus" data-pesan="Pengurus '<?= e($peng['nama']) ?>' akan dihapus dari struktur kepengurusan. Lanjutkan?" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50">
                        <i class="fa-solid fa-trash-can"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
