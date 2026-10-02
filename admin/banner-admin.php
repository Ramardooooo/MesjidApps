<?php
// banner-admin.php - Pengelolaan Slider & Banner Promosi Beranda (Scope 9)
require_once __DIR__ . '/../config/database.php';
cek_role(['admin', 'content_admin']);

$profil = get_profil_masjid();
$user   = $_SESSION['user'];
$pesan  = '';
$tipe   = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') {
        $pesan = 'Banner berhasil dihapus dari sistem.';
        $tipe  = 'success';
    } elseif ($_GET['msg'] === 'added') {
        $pesan = 'Banner baru berhasil ditambahkan!';
        $tipe  = 'success';
    } elseif ($_GET['msg'] === 'updated') {
        $pesan = 'Banner berhasil diperbarui!';
        $tipe  = 'success';
    } elseif ($_GET['msg'] === 'toggled') {
        $pesan = 'Status aktif banner berhasil diperbarui!';
        $tipe  = 'success';
    }
}

// 0. TOGGLE STATUS AKTIF CEPAT
if (isset($_GET['toggle'])) {
    $idToggle = (int)$_GET['toggle'];
    if ($idToggle > 0) {
        try {
            $pdo->prepare("UPDATE banners SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?")->execute([$idToggle]);
            header('Location: banner-admin?msg=toggled');
            exit;
        } catch (Exception $e) {
            $pesan = 'Gagal mengubah status: ' . $e->getMessage();
            $tipe  = 'error';
        }
    }
}

// 1. TAMBAH BANNER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $judul    = trim($_POST['judul'] ?? '');
    $subjudul = trim($_POST['subjudul'] ?? '');
    $link_url = trim($_POST['link_url'] ?? '');
    $tipeB    = $_POST['tipe'] ?? 'slider';
    $urutan   = (int)($_POST['urutan'] ?? 1);
    $active   = isset($_POST['is_active']) ? 1 : 0;

    // Bersihkan link internal agar rapi dan pasti bekerja
    if (!preg_match('#^(https?:)?//#i', $link_url) && strpos($link_url, 'wa.me') === false && strpos($link_url, 'mailto:') !== 0 && strpos($link_url, 'tel:') !== 0) {
        $link_url = ltrim($link_url, '/');
        $link_url = preg_replace('#^home/#i', '', $link_url);
        $link_url = preg_replace('#\.php(\?|$)#i', '$1', $link_url);
    }

    if (empty($judul)) {
        $pesan = 'Judul banner wajib diisi.';
        $tipe  = 'error';
    } else {
        $gambar = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $gambar = upload_berkas('gambar', 'banner');
        }
        // Fallback default banner jika tidak ada gambar yang diupload
        if (empty($gambar)) {
            $gambar = 'uploads/banner/1789088208_dc36d6a60037.jpg';
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO banners (judul, subjudul, gambar, link_url, tipe, urutan, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $subjudul, $gambar, $link_url, $tipeB, $urutan, $active]);

            header('Location: banner-admin?msg=added');
            exit;
        } catch (Exception $e) {
            $pesan = 'Gagal menambahkan banner: ' . $e->getMessage();
            $tipe  = 'error';
        }
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

    // Bersihkan link internal agar rapi dan pasti bekerja
    if (!preg_match('#^(https?:)?//#i', $link_url) && strpos($link_url, 'wa.me') === false && strpos($link_url, 'mailto:') !== 0 && strpos($link_url, 'tel:') !== 0) {
        $link_url = ltrim($link_url, '/');
        $link_url = preg_replace('#^home/#i', '', $link_url);
        $link_url = preg_replace('#\.php(\?|$)#i', '$1', $link_url);
    }

    if ($id > 0 && !empty($judul)) {
        try {
            $stmt = $pdo->prepare("UPDATE banners SET judul = ?, subjudul = ?, link_url = ?, tipe = ?, urutan = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$judul, $subjudul, $link_url, $tipeB, $urutan, $active, $id]);

            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $stmt = $pdo->prepare("SELECT gambar FROM banners WHERE id = ?");
                $stmt->execute([$id]);
                $old = $stmt->fetch();
                if ($old && !empty($old['gambar'])) {
                    hapus_file_upload($old['gambar']);
                }
                
                $gambar = upload_berkas('gambar', 'banner');
                if ($gambar) {
                    $pdo->prepare("UPDATE banners SET gambar = ? WHERE id = ?")->execute([$gambar, $id]);
                }
            }

            header('Location: banner-admin?msg=updated');
            exit;
        } catch (Exception $e) {
            $pesan = 'Gagal memperbarui banner: ' . $e->getMessage();
            $tipe  = 'error';
        }
    }
}

// 3. HAPUS BANNER
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    if ($idHapus > 0) {
        try {
            $stmt = $pdo->prepare("SELECT gambar FROM banners WHERE id = ?");
            $stmt->execute([$idHapus]);
            $banner = $stmt->fetch();
            
            if ($banner && !empty($banner['gambar'])) {
                hapus_file_upload($banner['gambar']);
            }
            
            $pdo->prepare("DELETE FROM banners WHERE id = ?")->execute([$idHapus]);
            header('Location: banner-admin?msg=deleted');
            exit;
        } catch (Exception $e) {
            $pesan = 'Gagal menghapus banner: ' . $e->getMessage();
            $tipe  = 'error';
        }
    }
}

// Ambil data untuk referensi pilihan tautan otomatis
$daftarProgram = [];
try {
    $daftarProgram = $pdo->query("SELECT id, nama_program, target_donasi, dana_terkumpul, deskripsi, gambar FROM program_donasi WHERE status = 'aktif' ORDER BY is_featured DESC, id DESC")->fetchAll();
} catch (Exception $e) {}

$daftarBerita = [];
try {
    $daftarBerita = $pdo->query("SELECT id, judul, ringkasan, slug, thumbnail FROM berita WHERE status = 'published' ORDER BY tanggal_publikasi DESC LIMIT 15")->fetchAll();
} catch (Exception $e) {}

$pag = paginate_data($pdo, "SELECT * FROM banners ORDER BY urutan ASC, id DESC", [], 10);
$banners = $pag['items'];

// Peta program & berita untuk pembacaan nama tautan di tabel
$programMap = [];
foreach ($daftarProgram as $p) {
    $programMap[$p['id']] = $p;
}
$beritaMap = [];
foreach ($daftarBerita as $b) {
    $beritaMap[$b['id']] = $b;
}

function label_tautan_banner($url, $programMap, $beritaMap) {
    $url = trim((string)$url);
    if ($url === '' || $url === '/' || $url === 'index') {
        return ['nama' => 'Beranda Utama', 'badge' => 'bg-warm-100 text-warm-800', 'icon' => 'fa-house'];
    }

    $clean = ltrim($url, '/');
    $clean = preg_replace('#^home/#i', '', $clean);
    $clean = preg_replace('#\.php(\?|$)#i', '$1', $clean);

    if ($clean === 'donasi-online') {
        return ['nama' => 'Donasi Online / Infaq', 'badge' => 'bg-emerald-100 text-emerald-800', 'icon' => 'fa-hand-holding-heart'];
    }
    if ($clean === 'program') {
        return ['nama' => 'Katalog Program Donasi', 'badge' => 'bg-blue-100 text-blue-800', 'icon' => 'fa-folder-open'];
    }
    if ($clean === 'transparansi') {
        return ['nama' => 'Transparansi Kas Masjid', 'badge' => 'bg-purple-100 text-purple-800', 'icon' => 'fa-chart-pie'];
    }
    if ($clean === 'profil') {
        return ['nama' => 'Profil Masjid & DKM', 'badge' => 'bg-stone-100 text-stone-800', 'icon' => 'fa-mosque'];
    }
    if ($clean === 'berita') {
        return ['nama' => 'Berita & Warta Jamaah', 'badge' => 'bg-amber-100 text-amber-800', 'icon' => 'fa-newspaper'];
    }
    if ($clean === 'kajian') {
        return ['nama' => 'Video Kajian TV Masjid', 'badge' => 'bg-red-100 text-red-800', 'icon' => 'fa-video'];
    }
    if ($clean === 'kontak') {
        return ['nama' => 'Kontak Layanan DKM', 'badge' => 'bg-teal-100 text-teal-800', 'icon' => 'fa-address-book'];
    }

    if (preg_match('#program-detail\?id=(\d+)#i', $clean, $m)) {
        $pId = (int)$m[1];
        $namaP = $programMap[$pId]['nama_program'] ?? "Program #{$pId}";
        return ['nama' => 'Program: ' . mb_strimwidth($namaP, 0, 28, '...'), 'badge' => 'bg-cyan-100 text-cyan-900', 'icon' => 'fa-handshake-angle'];
    }
    if (preg_match('#donasi-online\?program_id=(\d+)#i', $clean, $m)) {
        $pId = (int)$m[1];
        $namaP = $programMap[$pId]['nama_program'] ?? "Program #{$pId}";
        return ['nama' => 'Donasi: ' . mb_strimwidth($namaP, 0, 28, '...'), 'badge' => 'bg-emerald-100 text-emerald-900', 'icon' => 'fa-circle-dollar-to-slot'];
    }
    if (preg_match('#berita-detail\?id=(\d+)#i', $clean, $m)) {
        $bId = (int)$m[1];
        $judulB = $beritaMap[$bId]['judul'] ?? "Berita #{$bId}";
        return ['nama' => 'Berita: ' . mb_strimwidth($judulB, 0, 28, '...'), 'badge' => 'bg-orange-100 text-orange-900', 'icon' => 'fa-file-lines'];
    }

    if (preg_match('#^(https?:)?//#i', $clean) || strpos($clean, 'wa.me') !== false) {
        return ['nama' => 'Tautan Luar (' . mb_strimwidth($clean, 0, 22, '...') . ')', 'badge' => 'bg-stone-100 text-stone-700', 'icon' => 'fa-arrow-up-right-from-square'];
    }

    return ['nama' => $clean, 'badge' => 'bg-warm-100 text-warm-800', 'icon' => 'fa-link'];
}

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
            Kelola hero slider dan banner kegiatan. Halaman tujuan otomatis terdaftar rapi, tinggal pilih langsung bekerja.
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
        <div>
            <h3 class="font-classic text-base font-bold text-warm-900">
                Daftar Slider &amp; Banner
            </h3>
            <p class="text-[11px] text-warm-800/60 mt-0.5">Semua banner aktif otomatis tampil bergantian pada slider beranda utama</p>
        </div>
        <span class="text-xs text-warm-800/60 font-semibold"><?= number_format($pag['total']) ?> Banner</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-antique-200 bg-warm-50/70 text-warm-800/70 uppercase text-[10px] font-bold">
                    <th class="py-3 px-4 text-center">Urutan</th>
                    <th class="py-3 px-4 text-center">Gambar</th>
                    <th class="py-3 px-4">Judul &amp; Subjudul</th>
                    <th class="py-3 px-4">Tipe Banner</th>
                    <th class="py-3 px-4">Halaman Tujuan (Tautan)</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-antique-100">
                <?php if (empty($banners)): ?>
                    <tr>
                        <td colspan="7" class="py-12 text-center text-warm-800/50 italic">
                            Belum ada slider / banner yang tersimpan. Klik "Tambah Banner Baru" untuk membuat banner pertama.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($banners as $b): 
                    $targetInfo = label_tautan_banner($b['link_url'], $programMap, $beritaMap);
                    $hrefValid = app_url($b['link_url'] ?: 'donasi-online');
                ?>
                    <tr class="hover:bg-warm-50/60 transition">
                        <td class="py-3.5 px-4 text-center font-bold text-warm-900">
                            #<?= $b['urutan'] ?>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <?php if (!empty($b['gambar'])): ?>
                                <img src="<?= e(upload_url($b['gambar'])) ?>" alt="Banner" class="w-16 h-10 object-cover rounded-lg border border-antique-300/60 shadow-xs mx-auto">
                            <?php else: ?>
                                <div class="w-16 h-10 rounded-lg bg-warm-100 border border-antique-200 flex items-center justify-center text-warm-400 mx-auto text-xs">
                                    <i class="fa-regular fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5 px-4 align-top max-w-xs sm:max-w-sm">
                            <strong class="text-sm font-bold text-warm-900 block"><?= e($b['judul']) ?></strong>
                            <span class="text-[11px] text-warm-800/60 block mt-0.5"><?= e($b['subjudul']) ?></span>
                        </td>
                        <td class="py-3.5 px-4 align-top whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-warm-100 text-warm-800 uppercase tracking-wide">
                                <?= str_replace('_', ' ', e($b['tipe'])) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 align-top">
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold <?= $targetInfo['badge'] ?> w-fit">
                                    <i class="fa-solid <?= $targetInfo['icon'] ?> text-[10px]"></i>
                                    <span><?= e($targetInfo['nama']) ?></span>
                                </span>
                                <div class="flex items-center gap-2 text-[10px] text-stone-500 font-mono">
                                    <span><?= e($b['link_url'] ?: '(Beranda Utama)') ?></span>
                                    <a href="<?= e($hrefValid) ?>" target="_blank" class="text-cypress-700 hover:text-cypress-900 underline flex items-center gap-0.5" title="Buka tautan ini di tab baru untuk verifikasi">
                                        <span>Uji</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 align-top text-center whitespace-nowrap">
                            <a href="banner-admin?toggle=<?= $b['id'] ?>" title="Klik untuk beralih status aktif/nonaktif" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold uppercase transition <?= $b['is_active'] ? 'bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border border-emerald-300' : 'bg-stone-200 hover:bg-stone-300 text-stone-600 border border-stone-300' ?>">
                                <span class="w-1.5 h-1.5 rounded-full <?= $b['is_active'] ? 'bg-emerald-600' : 'bg-stone-400' ?>"></span>
                                <span><?= $b['is_active'] ? 'Aktif' : 'Nonaktif' ?></span>
                            </a>
                        </td>
                        <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" data-banner='<?= htmlspecialchars(json_encode($b, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>' onclick="editBannerSafe(this)" class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 border border-blue-200/60" title="Edit Banner">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <a href="banner-admin?hapus=<?= $b['id'] ?>" data-hapus data-judul="Hapus Banner" data-pesan="Banner '<?= e($b['judul']) ?>' ini akan dihapus permanen dari sistem. Lanjutkan?" class="p-2 rounded-lg text-red-600 hover:bg-red-50 border border-red-200/60" title="Hapus">
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

<!-- Modal Tambah / Edit Banner (Simpel & Halaman Otomatis Terdaftar) -->
<div id="modalBanner" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-cypress-950/50 veil-blur" onclick="tutupModalBanner()"></div>
    <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 border border-antique-300 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto text-xs">
        <div class="flex items-center justify-between border-b border-antique-200 pb-3">
            <div>
                <h3 id="modalBannerTitle" class="font-classic text-lg font-bold text-warm-900">Tambah Banner Baru</h3>
                <p class="text-[11px] text-warm-800/60 mt-0.5">Tinggal pilih halaman tujuan dari daftar, banner langsung aktif dan bekerja.</p>
            </div>
            <button type="button" onclick="tutupModalBanner()" class="text-stone-400 hover:text-stone-700 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" id="bannerAksi" name="aksi" value="tambah">
            <input type="hidden" id="bannerId" name="id" value="0">

            <!-- 1. PILIH HALAMAN TUJUAN OTOMATIS (Fitur Simpel & Tinggal Pilih) -->
            <div class="p-3.5 bg-warm-50 rounded-2xl border border-antique-300/80 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block font-bold text-warm-900">
                        <i class="fa-solid fa-compass text-cypress-700 mr-1"></i>
                        Pilih Halaman Tujuan Banner <span class="text-emerald-700 font-semibold">(Tinggal Pilih)</span>
                    </label>
                    <span class="text-[10px] text-stone-500 font-medium">Otomatis &amp; Siap Pakai</span>
                </div>
                
                <select id="bannerPilihHalaman" onchange="onPilihHalamanChange(this)" class="w-full px-3 py-2.5 rounded-xl bg-white border border-antique-300 font-semibold text-warm-900 focus:outline-none focus:border-cypress-700 shadow-xs">
                    <option value="">-- Pilih Halaman Tujuan dari Daftar --</option>
                    
                    <optgroup label="🌟 Halaman Utama Website">
                        <option value="/" data-judul="Selamat Datang di Masjid Jami' Nurul Iman" data-sub="Pusat Ibadah Khusyuk, Tarbiyah Generasi Qur'ani, dan Kebangkitan Ekonomi Ummat">Beranda Utama (Home)</option>
                        <option value="donasi-online" data-judul="Salurkan Infaq &amp; Sedekah Terbaik Anda" data-sub="Kemudahan beramal sholeh kapan saja melalui QRIS instan dan transfer bank">Donasi Online / Infaq (donasi-online)</option>
                        <option value="program" data-judul="Dukung Program Pembangunan &amp; Dakwah Masjid" data-sub="Setiap rupiah donasi menjadi amal jariyah yang pahalanya mengalir tanpa putus">Katalog Program Donasi (program)</option>
                        <option value="transparansi" data-judul="Laporan Kas &amp; Transparansi Keuangan Terbuka" data-sub="Pencatatan pembukuan keuangan masjid amanah, akuntabel, dan dapat diakses siapa saja">Transparansi Kas Masjid (transparansi)</option>
                        <option value="profil" data-judul="Mengenal Masjid &amp; Pengurus DKM" data-sub="Sejarah pendirian, visi misi kemakmuran masjid, serta jajaran pengurus amanah">Profil Masjid &amp; Pengurus DKM (profil)</option>
                        <option value="berita" data-judul="Warta &amp; Berita Agenda Masjid Terkini" data-sub="Informasi terupdate seputar kajian, tabligh akbar, santunan, dan kegiatan jamaah">Berita &amp; Warta Kegiatan (berita)</option>
                        <option value="kajian" data-judul="Video Kajian &amp; Live Streaming Masjid" data-sub="Simak siaran taklim rutin dan khutbah Jumat bersama para asatidz">Video Kajian &amp; TV Masjid (kajian)</option>
                        <option value="kontak" data-judul="Layanan Jamaah &amp; Kontak DKM" data-sub="Informasi kontak pengurus, alamat lokasi masjid, dan layanan konsultasi ummat">Kontak &amp; Layanan DKM (kontak)</option>
                    </optgroup>

                    <?php if (!empty($daftarProgram)): ?>
                        <optgroup label="🎯 Program Donasi Aktif (Otomatis dari Database)">
                            <?php foreach ($daftarProgram as $prog): ?>
                                <option value="program-detail?id=<?= $prog['id'] ?>" 
                                        data-tipe="program" 
                                        data-judul="<?= e($prog['nama_program']) ?>" 
                                        data-sub="<?= e(mb_strimwidth(strip_tags($prog['deskripsi']), 0, 110, '...')) ?>">
                                    Detail: <?= e($prog['nama_program']) ?>
                                </option>
                                <option value="donasi-online?program_id=<?= $prog['id'] ?>" 
                                        data-tipe="program" 
                                        data-judul="Infaq &amp; Donasi: <?= e($prog['nama_program']) ?>" 
                                        data-sub="Mari berkontribusi dalam program kebaikan ini untuk kemaslahatan bersama">
                                    Donasi Cepat: <?= e($prog['nama_program']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>

                    <?php if (!empty($daftarBerita)): ?>
                        <optgroup label="📰 Berita &amp; Artikel Terkini">
                            <?php foreach ($daftarBerita as $ber): ?>
                                <option value="berita-detail?id=<?= $ber['id'] ?>" 
                                        data-tipe="berita" 
                                        data-judul="<?= e($ber['judul']) ?>" 
                                        data-sub="<?= e(mb_strimwidth(strip_tags($ber['ringkasan'] ?? ''), 0, 110, '...')) ?>">
                                    Berita: <?= e(mb_strimwidth($ber['judul'], 0, 45, '...')) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>

                    <optgroup label="🔗 Tautan Lain">
                        <option value="custom">Ketik Tautan Manual / URL Luar (WhatsApp, Web Lain)...</option>
                    </optgroup>
                </select>

                <!-- Tombol Pintas Gunakan Judul dari Pilihan Halaman -->
                <div id="boxAutoIsi" class="hidden pt-1 flex items-center justify-between text-[11px]">
                    <span class="text-stone-600 italic"><i class="fa-solid fa-wand-magic-sparkles text-amber-500 mr-1"></i> Judul &amp; deskripsi bisa otomatis diisi:</span>
                    <button type="button" onclick="salinInfoHalaman()" class="px-2.5 py-1 rounded-lg bg-cypress-800 hover:bg-cypress-900 text-white font-semibold transition flex items-center gap-1 shadow-xs">
                        <span>Terapkan Info ke Banner</span>
                    </button>
                </div>

                <!-- Input Link URL & Uji Coba Langsung -->
                <div class="pt-2 border-t border-antique-200">
                    <label class="block font-bold text-warm-800 text-[11px] mb-1">Tautan / URL Bersih Tersimpan:</label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="bannerLink" name="link_url" placeholder="Contoh: donasi-online atau program-detail?id=1" class="flex-1 px-3 py-1.5 rounded-xl bg-white border border-antique-300 font-mono text-[11px] focus:outline-none focus:border-cypress-700">
                        <a id="btnTestLink" href="#" target="_blank" class="px-3 py-1.5 rounded-xl bg-warm-200 hover:bg-warm-300 text-warm-900 font-semibold text-[11px] whitespace-nowrap transition flex items-center gap-1" title="Uji tautan ini">
                            <span>Uji Link</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. JUDUL UTAMA -->
            <div>
                <label class="block font-bold text-warm-800 mb-1">Judul Utama Banner <span class="text-red-500">*</span></label>
                <input type="text" id="bannerJudul" name="judul" required placeholder="Contoh: Selamat Datang di Masjid..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-semibold focus:outline-none focus:border-cypress-700 text-warm-900">
            </div>

            <!-- 3. SUBJUDUL -->
            <div>
                <label class="block font-bold text-warm-800 mb-1">Subjudul / Deskripsi Pendek</label>
                <textarea id="bannerSub" name="subjudul" rows="2" placeholder="Pesan inspiratif singkat atau ajakan kebaikan..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none focus:border-cypress-700 text-warm-900"></textarea>
            </div>

            <!-- 4. TIPE BANNER & URUTAN -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Tipe Banner</label>
                    <select id="bannerTipe" name="tipe" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none font-semibold">
                        <option value="slider">Slider Hero Beranda</option>
                        <option value="banner_kegiatan">Banner Kegiatan</option>
                        <option value="banner_program">Banner Program Donasi</option>
                        <option value="banner_donasi">Banner Promosi Infaq</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Urutan Tampil (1, 2, ...)</label>
                    <input type="number" id="bannerUrutan" name="urutan" min="1" value="1" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-bold focus:outline-none text-warm-900">
                </div>
            </div>

            <!-- 5. UPLOAD GAMBAR BANNER & PREVIEW -->
            <div>
                <label class="block font-bold text-warm-800 mb-1">Upload Gambar Banner</label>
                <input type="file" id="bannerFile" name="gambar" accept="image/*" onchange="previewUploadImage(this)" class="w-full px-3 py-1.5 rounded-xl bg-warm-50 border border-antique-300 text-[11px] file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-cypress-800 file:text-white">
                <p class="text-[10px] text-stone-500 mt-1">Format: JPG, PNG, WEBP. Rekomendasi rasio horizontal 16:9 atau 4:3 (min 800x450 px).</p>
                
                <!-- Preview Gambar Saat Ini / Baru -->
                <div id="boxImagePreview" class="hidden mt-2 p-2 bg-warm-50 rounded-xl border border-antique-200 flex items-center gap-3">
                    <img id="imgPreview" src="" alt="Preview" class="w-20 h-12 object-cover rounded-lg border border-antique-300">
                    <div class="text-[11px] text-stone-600">
                        <span id="txtImageLabel" class="font-bold text-warm-900 block">Gambar Terpasang</span>
                        <span class="text-[10px] text-stone-500">Biarkan kosong jika tidak ingin mengubah gambar</span>
                    </div>
                </div>
            </div>

            <!-- 6. STATUS AKTIF -->
            <div class="p-3 bg-warm-50 rounded-xl border border-antique-200">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-warm-900">
                    <input type="checkbox" id="bannerActive" name="is_active" value="1" checked class="accent-cypress-700 w-4 h-4 rounded">
                    <span>Status Aktif (Tampilkan di Slider Beranda Utama)</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-antique-200">
                <button type="button" onclick="tutupModalBanner()" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold shadow-xs transition">Simpan Banner</button>
            </div>
        </form>
    </div>
</div>

<script>
const baseUrlApp = '<?= rtrim(base_url(), '/') ?>';

function updateTestLinkBtn(url) {
    const btn = document.getElementById('btnTestLink');
    if (!url || url === '#' || url === '/') {
        btn.href = baseUrlApp ? baseUrlApp + '/' : '/';
    } else if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('//') || url.includes('wa.me')) {
        btn.href = url;
    } else {
        // Hilangkan home/ atau .php jika ada
        let clean = url.replace(/^home\//i, '').replace(/\.php(\?|$)/i, '$1');
        btn.href = baseUrlApp + '/' + clean.replace(/^\/+/, '');
    }
}

document.getElementById('bannerLink').addEventListener('input', function() {
    updateTestLinkBtn(this.value);
});

function onPilihHalamanChange(sel) {
    const val = sel.value;
    const boxAuto = document.getElementById('boxAutoIsi');
    const inputLink = document.getElementById('bannerLink');

    if (val === 'custom') {
        inputLink.focus();
        boxAuto.classList.add('hidden');
        return;
    }

    if (val) {
        inputLink.value = val;
        updateTestLinkBtn(val);
        boxAuto.classList.remove('hidden');
    } else {
        boxAuto.classList.add('hidden');
    }
}

function salinInfoHalaman() {
    const sel = document.getElementById('bannerPilihHalaman');
    const opt = sel.options[sel.selectedIndex];
    if (!opt) return;

    const j = opt.getAttribute('data-judul');
    const s = opt.getAttribute('data-sub');

    if (j) document.getElementById('bannerJudul').value = j;
    if (s) document.getElementById('bannerSub').value = s;
}

function bukaModalBanner() {
    document.getElementById('bannerAksi').value = 'tambah';
    document.getElementById('bannerId').value = '0';
    document.getElementById('modalBannerTitle').textContent = 'Tambah Banner Baru';
    document.getElementById('bannerJudul').value = '';
    document.getElementById('bannerSub').value = '';
    document.getElementById('bannerLink').value = 'donasi-online';
    document.getElementById('bannerUrutan').value = '1';
    document.getElementById('bannerTipe').value = 'slider';
    document.getElementById('bannerActive').checked = true;
    document.getElementById('boxAutoIsi').classList.add('hidden');
    document.getElementById('boxImagePreview').classList.add('hidden');
    document.getElementById('bannerFile').value = '';
    
    // Default pilih Donasi Online
    const sel = document.getElementById('bannerPilihHalaman');
    sel.value = 'donasi-online';
    onPilihHalamanChange(sel);

    document.getElementById('modalBanner').classList.remove('hidden');
}

function editBannerSafe(btn) {
    try {
        const b = JSON.parse(btn.getAttribute('data-banner'));
        document.getElementById('bannerAksi').value = 'edit';
        document.getElementById('bannerId').value = b.id;
        document.getElementById('modalBannerTitle').textContent = 'Edit Banner #' + b.id;
        document.getElementById('bannerJudul').value = b.judul || '';
        document.getElementById('bannerSub').value = b.subjudul || '';
        document.getElementById('bannerTipe').value = b.tipe || 'slider';
        
        let linkClean = (b.link_url || '').replace(/^home\//i, '').replace(/\.php(\?|$)/i, '$1').replace(/^\/+/, '');
        document.getElementById('bannerLink').value = linkClean;
        updateTestLinkBtn(linkClean);

        document.getElementById('bannerUrutan').value = b.urutan || 1;
        document.getElementById('bannerActive').checked = (b.is_active == 1);
        document.getElementById('bannerFile').value = '';

        // Cari apakah link yang tersimpan ada di dropdown
        const sel = document.getElementById('bannerPilihHalaman');
        let matched = false;
        for (let i = 0; i < sel.options.length; i++) {
            let optVal = sel.options[i].value.replace(/^home\//i, '').replace(/\.php(\?|$)/i, '$1').replace(/^\/+/, '');
            if (optVal && (optVal === linkClean || sel.options[i].value === b.link_url)) {
                sel.selectedIndex = i;
                matched = true;
                break;
            }
        }
        if (!matched) {
            sel.value = linkClean ? 'custom' : '';
        }
        document.getElementById('boxAutoIsi').classList.toggle('hidden', !matched);

        // Preview gambar yang saat ini tersimpan
        if (b.gambar) {
            const imgBox = document.getElementById('boxImagePreview');
            const img = document.getElementById('imgPreview');
            img.src = b.gambar.startsWith('http') ? b.gambar : baseUrlApp + '/' + b.gambar;
            document.getElementById('txtImageLabel').textContent = 'Gambar Banner Terpasang';
            imgBox.classList.remove('hidden');
        } else {
            document.getElementById('boxImagePreview').classList.add('hidden');
        }

        document.getElementById('modalBanner').classList.remove('hidden');
    } catch(e) {
        console.error('Error parsing banner data:', e);
    }
}

function previewUploadImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreview').src = e.target.result;
            document.getElementById('txtImageLabel').textContent = 'Pratinjau Gambar Baru';
            document.getElementById('boxImagePreview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function tutupModalBanner() {
    document.getElementById('modalBanner').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
