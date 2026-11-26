<?php
// program-admin.php - Manajemen Program & Donasi Masjid (Scope 4.b)
require_once __DIR__ . '/../config/database.php';
cek_role(['admin', 'content_admin']);

$profil = get_profil_masjid();
$user   = $_SESSION['user'];
$pesan  = '';
$tipe   = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') {
        $pesan = 'Program donasi berhasil dihapus dari sistem.';
        $tipe  = 'success';
    } elseif ($_GET['msg'] === 'kabar_deleted') {
        $pesan = 'Kabar penyaluran program berhasil dihapus.';
        $tipe  = 'success';
    }
}

// 1. TAMBAH PROGRAM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama      = mb_substr(trim($_POST['nama_program'] ?? ''), 0, 200);
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
        // Generate slug bersih dari nama (tanpa angka random di belakang)
        $baseSlug = rtrim(preg_replace('/-+/', '-', preg_replace('/[^a-z0-9]+/', '-', strtolower($nama))), '-');
        // Pastikan unique: cek DB, tambah -2, -3, dst jika sudah ada
        $slug = $baseSlug;
        $suffix = 2;
        while ($pdo->query("SELECT COUNT(*) FROM program_donasi WHERE slug = " . $pdo->quote($slug))->fetchColumn() > 0) {
            $slug = $baseSlug . '-' . $suffix++;
        }
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
    $nama      = mb_substr(trim($_POST['nama_program'] ?? ''), 0, 200);
    $kategori  = $_POST['kategori'] ?? 'donasi';
    $target    = (float)str_replace(['Rp', '.', ' ', ','], '', $_POST['target_donasi'] ?? '0');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $lengkap   = trim($_POST['deskripsi_lengkap'] ?? '');
    $tglMulai  = $_POST['tanggal_mulai'] ?? date('Y-m-d');
    $tglAkhir  = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
    $status    = $_POST['status'] ?? 'aktif';

    if ($id > 0 && !empty($nama)) {
        // Generate slug bersih dari nama secara realtime
        $baseSlug = rtrim(preg_replace('/-+/', '-', preg_replace('/[^a-z0-9]+/', '-', strtolower($nama))), '-');
        if ($baseSlug === '') $baseSlug = 'program';
        $slug = $baseSlug;
        $suffix = 2;
        while (true) {
            $stmtChk = $pdo->prepare("SELECT COUNT(*) FROM program_donasi WHERE slug = ? AND id != ?");
            $stmtChk->execute([$slug, $id]);
            if ($stmtChk->fetchColumn() == 0) break;
            $slug = $baseSlug . '-' . $suffix++;
        }

        $stmt = $pdo->prepare("UPDATE program_donasi SET nama_program = ?, slug = ?, kategori = ?, deskripsi = ?, deskripsi_lengkap = ?, target_donasi = ?, tanggal_mulai = ?, tanggal_selesai = ?, status = ? WHERE id = ?");
        $stmt->execute([$nama, $slug, $kategori, $deskripsi, $lengkap, $target, $tglMulai, $tglAkhir, $status, $id]);

        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $oldProgram = $pdo->prepare("SELECT gambar FROM program_donasi WHERE id = ?")->execute([$id]) ? $pdo->prepare("SELECT gambar FROM program_donasi WHERE id = ?")->execute([$id]) : null;
            $stmt = $pdo->prepare("SELECT gambar FROM program_donasi WHERE id = ?");
            $stmt->execute([$id]);
            $old = $stmt->fetch();
            if ($old && !empty($old['gambar'])) {
                hapus_file_upload($old['gambar']);
            }
            
            $gambar = upload_berkas('gambar', 'program');
            if ($gambar) {
                $pdo->prepare("UPDATE program_donasi SET gambar = ? WHERE id = ?")->execute([$gambar, $id]);
            }
        }

        $pesan = "Program '{$nama}' berhasil diperbarui!";
        $tipe  = 'success';
    }
}

// 2.b. TAMBAH KABAR PENYALURAN (MILESTONE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah_kabar') {
    $progId      = (int)($_POST['program_id'] ?? 0);
    $judul       = mb_substr(trim($_POST['judul'] ?? ''), 0, 250);
    $isi         = trim($_POST['isi'] ?? '');
    $dana        = (float)str_replace(['Rp', '.', ' ', ','], '', $_POST['dana_disalurkan'] ?? '0');
    $tglUpdate   = !empty($_POST['tanggal_update']) ? $_POST['tanggal_update'] : date('Y-m-d');

    if ($progId <= 0 || empty($judul) || empty($isi)) {
        $pesan = 'Judul dan rincian kabar penyaluran wajib diisi.';
        $tipe  = 'error';
    } else {
        // Ambil data program & hitung sisa kas donasi terikat program
        $stmtProg = $pdo->prepare("SELECT p.nama_program, p.slug, p.dana_terkumpul, 
            COALESCE((SELECT SUM(pu.dana_disalurkan) FROM program_updates pu WHERE pu.program_id = p.id), 0) AS total_sudah_salur
            FROM program_donasi p WHERE p.id = ?");
        $stmtProg->execute([$progId]);
        $progData = $stmtProg->fetch();

        if (!$progData) {
            $pesan = 'Program tidak ditemukan.';
            $tipe  = 'error';
        } else {
            $danaTerkumpul   = (float)$progData['dana_terkumpul'];
            $totalSudahSalur = (float)$progData['total_sudah_salur'];
            $sisaKasProgram  = max(0, $danaTerkumpul - $totalSudahSalur);

            // Validasi: Kas donasi program terpisah dari kas umum, tidak boleh dicairkan melebihi sisa dana yang terkumpul
            if ($dana > 0 && $dana > $sisaKasProgram) {
                $pesan = "Penyaluran ditolak: Nominal pencairan (" . format_rupiah($dana) . ") melebihi sisa kas program (" . format_rupiah($sisaKasProgram) . "). Kas donasi terikat program terpisah dari kas umum dan tidak boleh defisit!";
                $tipe  = 'error';
            } else {
                $foto = null;
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $foto = upload_berkas('foto', 'program');
                }

                $stmt = $pdo->prepare("INSERT INTO program_updates (program_id, judul, isi, dana_disalurkan, foto, tanggal_update) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$progId, $judul, $isi, $dana, $foto, $tglUpdate]);

                // Notifikasi ke seluruh donatur terverifikasi di program ini
                $stmtDonatur = $pdo->prepare("SELECT DISTINCT user_id FROM donasi_online WHERE program_id = ? AND status = 'diverifikasi' AND user_id IS NOT NULL");
                $stmtDonatur->execute([$progId]);
                $donorIds = $stmtDonatur->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($donorIds)) {
                    $notifJudul = mb_substr("Kabar Progres: " . $progData['nama_program'], 0, 150);
                    $notifPesan = "Pembaruan penyaluran program telah dipublikasikan: \"" . mb_substr($judul, 0, 80) . "\".";
                    $notifLink  = base_url() . "/program/" . $progData['slug'] . "#kabar-penyaluran";
                    $stmtNotif = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, 'donasi', ?)");
                    foreach ($donorIds as $dUid) {
                        $stmtNotif->execute([$dUid, $notifJudul, $notifPesan, $notifLink]);
                    }
                }

                $pesan = "Kabar penyaluran berhasil dipublikasikan dan dinotifikasikan ke para donatur!";
                $tipe  = 'success';
            }
        }
    }
}

// 3. HAPUS PROGRAM
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    if ($idHapus > 0) {
        $stmt = $pdo->prepare("SELECT gambar FROM program_donasi WHERE id = ?");
        $stmt->execute([$idHapus]);
        $program = $stmt->fetch();
        
        if ($program && !empty($program['gambar'])) {
            hapus_file_upload($program['gambar']);
        }

        // Hapus foto dokumentasi kabar updates yang terkait
        $stmtUpdates = $pdo->prepare("SELECT foto FROM program_updates WHERE program_id = ?");
        $stmtUpdates->execute([$idHapus]);
        $oldUpdates = $stmtUpdates->fetchAll();
        foreach ($oldUpdates as $ou) {
            if (!empty($ou['foto'])) {
                hapus_file_upload($ou['foto']);
            }
        }
        $pdo->prepare("DELETE FROM program_updates WHERE program_id = ?")->execute([$idHapus]);
        
        $pdo->prepare("DELETE FROM program_donasi WHERE id = ?")->execute([$idHapus]);
        header('Location: program-admin?msg=deleted');
        exit;
    }
}

// 4. HAPUS KABAR PENYALURAN
if (isset($_GET['hapus_kabar'])) {
    $idHapus = (int)$_GET['hapus_kabar'];
    if ($idHapus > 0) {
        $stmt = $pdo->prepare("SELECT foto FROM program_updates WHERE id = ?");
        $stmt->execute([$idHapus]);
        $kabar = $stmt->fetch();
        if ($kabar && !empty($kabar['foto'])) {
            hapus_file_upload($kabar['foto']);
        }
        $pdo->prepare("DELETE FROM program_updates WHERE id = ?")->execute([$idHapus]);
        header('Location: program-admin?msg=kabar_deleted');
        exit;
    }
}

$pag = paginate_data($pdo, "SELECT p.*,
    (SELECT COUNT(*) FROM program_updates pu WHERE pu.program_id = p.id) AS jumlah_update,
    (SELECT COALESCE(SUM(dana_disalurkan), 0) FROM program_updates pu WHERE pu.program_id = p.id) AS total_disalurkan
    FROM program_donasi p ORDER BY p.id DESC", [], 10);
$programs = $pag['items'];

// Ambil riwayat kabar penyaluran untuk modal interaktif
$allUpdates = $pdo->query("SELECT * FROM program_updates ORDER BY tanggal_update DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
$updatesByProg = [];
foreach ($allUpdates as $u) {
    $updatesByProg[$u['program_id']][] = [
        'id'              => (int)$u['id'],
        'judul'           => $u['judul'],
        'isi'             => $u['isi'],
        'dana_disalurkan' => (float)$u['dana_disalurkan'],
        'dana_format'     => format_rupiah($u['dana_disalurkan']),
        'tanggal'         => $u['tanggal_update'],
        'tanggal_indo'    => tanggal_indo($u['tanggal_update'], true),
        'foto'            => !empty($u['foto']) ? upload_url($u['foto']) : null,
    ];
}

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

    <div class="overflow-x-auto -mx-5 px-5">
        <table class="w-full text-left text-xs min-w-[700px]">
            <thead>
                <tr class="border-b border-antique-200 bg-warm-50/70 text-warm-800/70 uppercase text-[10px] font-bold">
                    <th class="py-3 px-4">Nama Program &amp; Kategori</th>
                    <th class="py-3 px-4">Target Donasi</th>
                    <th class="py-3 px-4">Terkumpul &amp; Disalurkan</th>
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
                        <td class="py-3.5 px-4 align-top whitespace-nowrap">
                            <?php $sisaKasRow = max(0, (float)$p['dana_terkumpul'] - (float)$p['total_disalurkan']); ?>
                            <div class="font-classic font-bold text-emerald-700"><?= format_rupiah($p['dana_terkumpul']) ?></div>
                            <div class="space-y-0.5 mt-0.5">
                                <?php if ((float)$p['total_disalurkan'] > 0): ?>
                                    <span class="text-[10px] text-warm-800/60 font-semibold block">
                                        <i class="fa-solid fa-hand-holding-dollar text-emerald-600 mr-0.5"></i> Salur: <?= format_rupiah($p['total_disalurkan']) ?>
                                    </span>
                                <?php endif; ?>
                                <span class="text-[10px] text-cypress-800 font-bold block">
                                    <i class="fa-solid fa-wallet text-antique-500 mr-0.5"></i> Sisa: <?= format_rupiah($sisaKasRow) ?>
                                </span>
                            </div>
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
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" onclick="bukaModalKabar(<?= $p['id'] ?>, '<?= e(addslashes($p['nama_program'])) ?>', <?= (float)$p['dana_terkumpul'] ?>, <?= (float)$p['total_disalurkan'] ?>)" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs border border-emerald-200/80 transition" title="Kelola Kabar & Milestone Penyaluran">
                                    <i class="fa-solid fa-bullhorn text-emerald-600"></i>
                                    <span>Kabar (<?= (int)$p['jumlah_update'] ?>)</span>
                                </button>
                                <a href="../program/<?= e($p['slug']) ?>" target="_blank" class="p-1.5 rounded-lg text-cypress-700 hover:bg-cypress-50" title="Pratinjau">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                                <button type="button" data-program='<?= htmlspecialchars(json_encode($p, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>' onclick="editProgramSafe(this)" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <a href="program-admin?hapus=<?= $p['id'] ?>" data-hapus data-judul="Hapus Program" data-pesan="Program '<?= e($p['nama_program'] ?? '') ?>' beserta progres donasinya akan dihapus. Lanjutkan?" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50" title="Hapus">
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
<div id="modalProgram" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-cypress-950/50 veil-blur"></div>
    <div class="relative bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 border border-antique-300 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto text-xs">
        <div class="flex items-center justify-between border-b border-antique-200 pb-3">
            <h3 id="modalProgTitle" class="font-classic text-lg font-bold text-warm-900">Tambah Program Baru</h3>
            <button type="button" onclick="tutupModalProgram()" class="text-stone-400 hover:text-stone-700 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" id="progAksi" name="aksi" value="tambah">
            <input type="hidden" id="progId" name="id" value="0">

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block font-bold text-warm-800">Nama Program <span class="text-red-500">*</span></label>
                    <span class="text-[11px] text-stone-500"><span id="progNamaCounter">0</span>/200</span>
                </div>
                <input type="text" id="progNama" name="nama_program" maxlength="200" required oninput="updateProgNamaCounter()" placeholder="Contoh: Pembangunan Menara & Wudhu..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-semibold focus:outline-none">
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
                    <input type="text" id="progTarget" name="target_donasi" min="0" max="99999999" placeholder="0" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-bold focus:outline-none" oninput="this.value=this.value.replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.')">
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
                <textarea id="progDesk" name="deskripsi" rows="2" maxlength="500" placeholder="Ringkasan 1-2 kalimat program..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none"></textarea>
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

<!-- Modal Kabar Penyaluran Donasi / Milestone Tracker -->
<div id="modalKabarPenyaluran" class="fixed inset-0 z-[75] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-cypress-950/60 veil-blur" onclick="tutupModalKabar()"></div>
    <div class="relative bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-8 border border-antique-300 shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto text-xs">
        <div class="flex items-center justify-between border-b border-antique-200 pb-4">
            <div>
                <div class="flex items-center gap-2 text-emerald-800 text-[11px] font-bold uppercase tracking-wider mb-0.5">
                    <i class="fa-solid fa-bullhorn text-antique-500"></i>
                    <span>Milestone &amp; Penyaluran Donasi</span>
                </div>
                <h3 class="font-classic text-lg font-bold text-warm-900" id="kabarProgNama">Kabar Penyaluran</h3>
            </div>
            <button type="button" onclick="tutupModalKabar()" class="text-stone-400 hover:text-stone-700 p-1.5 rounded-xl hover:bg-warm-100 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Ringkasan Dompet Kas Program (Terpisah dari Kas Umum) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-2xl bg-warm-50/90 border border-antique-200 text-center">
            <div class="p-2.5 rounded-xl bg-white border border-antique-200/70">
                <span class="text-[10px] font-bold uppercase tracking-wider text-warm-800/60 block">Dana Terkumpul</span>
                <span id="kabarDanaTerkumpul" class="text-sm font-bold text-cypress-800 font-classic mt-0.5 block">Rp 0</span>
            </div>
            <div class="p-2.5 rounded-xl bg-white border border-antique-200/70">
                <span class="text-[10px] font-bold uppercase tracking-wider text-warm-800/60 block">Total Disalurkan</span>
                <span id="kabarTotalDisalurkan" class="text-sm font-bold text-stone-700 font-classic mt-0.5 block">Rp 0</span>
            </div>
            <div class="p-2.5 rounded-xl bg-emerald-50/90 border border-emerald-200">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">Sisa Kas Program (Siap Salur)</span>
                <span id="kabarSisaKas" class="text-sm font-extrabold text-emerald-900 font-classic mt-0.5 block">Rp 0</span>
            </div>
        </div>

        <!-- Formulir Tambah Kabar Baru -->
        <div class="bg-warm-50/70 p-5 rounded-2xl border border-antique-200 space-y-4">
            <h4 class="font-classic font-bold text-sm text-warm-900 flex items-center gap-2">
                <i class="fa-solid fa-paper-plane text-cypress-700"></i>
                <span>Publikasikan Kabar Baru</span>
            </h4>

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-3">
                <input type="hidden" name="aksi" value="tambah_kabar">
                <input type="hidden" id="kabarProgId" name="program_id" value="0">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-warm-800 mb-1">Judul Kabar / Milestone <span class="text-red-500">*</span></label>
                        <input type="text" id="kabarJudul" name="judul" maxlength="250" required placeholder="Contoh: Penyaluran Tahap 1 - Pembelian Bahan Bangunan..." class="w-full px-3 py-2 rounded-xl bg-white border border-antique-300 font-semibold focus:outline-none focus:border-cypress-700">
                    </div>
                    <div>
                        <label class="block font-bold text-warm-800 mb-1">Tanggal Update <span class="text-red-500">*</span></label>
                        <input type="date" id="kabarTanggal" name="tanggal_update" required value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 rounded-xl bg-white border border-antique-300 focus:outline-none focus:border-cypress-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-bold text-warm-800">Nominal Dicairkan / Belanja (Rp)</label>
                            <button type="button" onclick="gunakanSemuaSisaKas()" class="text-[10px] text-cypress-800 hover:text-cypress-950 font-bold underline">
                                Gunakan Sisa Kas
                            </button>
                        </div>
                        <input type="text" id="kabarDana" name="dana_disalurkan" placeholder="0" class="w-full px-3 py-2 rounded-xl bg-white border border-antique-300 font-bold focus:outline-none focus:border-cypress-700" oninput="cekBatasPenyaluran(this)">
                        <span id="kabarDanaHint" class="text-[10px] text-warm-800/60 mt-0.5 block">Maksimal pencairan: Rp 0 (sesuai kas donasi program).</span>
                        <div id="kabarDanaWarning" class="hidden text-[11px] text-red-600 font-bold mt-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Peringatan: Nominal melebihi sisa kas program yang tersedia!</span>
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-warm-800 mb-1">Foto Dokumentasi / Nota / Kuitansi</label>
                        <input type="file" name="foto" accept="image/*" class="w-full px-3 py-1.5 rounded-xl bg-white border border-antique-300 text-[11px] file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:bg-cypress-800 file:text-white">
                        <span class="text-[10px] text-warm-800/50 mt-0.5 block">Format: JPG, PNG, WEBP (Maks 5MB)</span>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-warm-800 mb-1">Rincian Kabar &amp; Realisasi Amanah <span class="text-red-500">*</span></label>
                    <textarea id="kabarIsi" name="isi" rows="3" maxlength="2000" required placeholder="Jelaskan rincian progres, jumlah penerima manfaat, nomor nota belanja, atau kendala yang dihadapi..." class="w-full px-3 py-2 rounded-xl bg-white border border-antique-300 focus:outline-none focus:border-cypress-700 leading-relaxed"></textarea>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <span class="text-[11px] text-emerald-800/80 font-medium flex items-center gap-1.5">
                        <i class="fa-solid fa-bell"></i>
                        <span>Otomatis mengirim notifikasi ke donatur yang telah berdonasi</span>
                    </span>
                    <button type="submit" id="kabarSubmitBtn" class="px-5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold shadow-xs flex items-center gap-2 transition">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Kirim &amp; Publikasikan</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar Riwayat Kabar Penyaluran -->
        <div class="space-y-3">
            <div class="flex items-center justify-between border-b border-antique-200 pb-2">
                <h4 class="font-classic font-bold text-sm text-warm-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-antique-500"></i>
                    <span>Riwayat Kabar yang Telah Terbit</span>
                </h4>
                <span class="px-2.5 py-0.5 rounded-full bg-cypress-950 text-antique-300 text-[10px] font-bold">
                    <span id="kabarCountBadge">0</span> Kabar
                </span>
            </div>

            <div id="kabarListContainer">
                <!-- Diisi secara dinamis oleh JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
const programUpdatesMap = <?= json_encode($updatesByProg, JSON_UNESCAPED_UNICODE) ?>;
let currentProgSisaKas = 0;

// ============================================================
// Kabar Penyaluran (Milestone) Modal
// ============================================================
function bukaModalKabar(progId, progNama, danaTerkumpul, totalDisalurkan) {
    danaTerkumpul  = danaTerkumpul  || 0;
    totalDisalurkan = totalDisalurkan || 0;

    document.getElementById('kabarProgId').value = progId;
    document.getElementById('kabarProgNama').textContent = progNama;
    document.getElementById('kabarJudul').value = '';
    document.getElementById('kabarIsi').value = '';
    document.getElementById('kabarDana').value = '';
    document.getElementById('kabarTanggal').value = '<?= date('Y-m-d') ?>';

    // Ringkasan dompet program
    currentProgSisaKas = Math.max(0, danaTerkumpul - totalDisalurkan);
    document.getElementById('kabarDanaTerkumpul').textContent   = formatRupiahJs(danaTerkumpul);
    document.getElementById('kabarTotalDisalurkan').textContent  = formatRupiahJs(totalDisalurkan);
    document.getElementById('kabarSisaKas').textContent          = formatRupiahJs(currentProgSisaKas);
    document.getElementById('kabarDanaHint').textContent = 'Maksimal pencairan: ' + formatRupiahJs(currentProgSisaKas) + ' (sesuai kas donasi program).';

    var warning = document.getElementById('kabarDanaWarning');
    if (warning) warning.classList.add('hidden');
    var submitBtn = document.getElementById('kabarSubmitBtn');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }

    // Render riwayat kabar
    var container = document.getElementById('kabarListContainer');
    var updates   = programUpdatesMap[progId] || [];
    document.getElementById('kabarCountBadge').textContent = updates.length;

    if (updates.length === 0) {
        container.innerHTML =
            '<div class="text-center py-8 text-warm-800/60 bg-warm-50/70 rounded-2xl border border-dashed border-antique-300">' +
                '<i class="fa-solid fa-folder-open text-2xl text-antique-400 mb-2"></i>' +
                '<p class="text-xs">Belum ada kabar penyaluran untuk program ini.</p>' +
                '<p class="text-[11px] text-warm-800/40 mt-0.5">Isi formulir di atas untuk membagikan progres pertama ke para donatur.</p>' +
            '</div>';
    } else {
        var html = '<div class="space-y-3">';
        updates.forEach(function(u) {
            var fotoBlock = u.foto
                ? '<a href="' + u.foto + '" target="_blank" class="shrink-0 block"><img src="' + u.foto + '" alt="Foto" class="w-16 h-16 rounded-xl object-cover border border-antique-300 shadow-2xs"></a>'
                : '<div class="w-16 h-16 rounded-xl bg-antique-100 text-antique-500 border border-antique-200 flex items-center justify-center shrink-0 text-lg"><i class="fa-solid fa-image"></i></div>';
            var danaTag = u.dana_disalurkan > 0
                ? '<span class="text-[10px] font-bold text-emerald-800 bg-emerald-100/70 px-2 py-0.5 rounded">Cair: ' + u.dana_format + '</span>'
                : '';
            html +=
                '<div class="p-3.5 bg-warm-50/80 rounded-2xl border border-antique-200/80 flex flex-col sm:flex-row gap-3 items-start justify-between">' +
                    '<div class="flex gap-3 min-w-0">' +
                        fotoBlock +
                        '<div class="space-y-1 min-w-0">' +
                            '<div class="flex flex-wrap items-center gap-2">' +
                                '<span class="font-bold text-warm-900 text-xs">' + escapeHtml(u.judul) + '</span>' +
                                '<span class="text-[10px] text-stone-500 font-semibold bg-white px-2 py-0.5 rounded border border-antique-200">' + u.tanggal_indo + '</span>' +
                                danaTag +
                            '</div>' +
                            '<p class="text-[11px] text-warm-800/80 line-clamp-2 leading-relaxed">' + escapeHtml(u.isi) + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<a href="program-admin?hapus_kabar=' + u.id + '" data-hapus data-judul="Hapus Kabar Penyaluran" data-pesan="Apakah Anda yakin ingin menghapus update \'' + escapeHtml(u.judul) + '\'?" class="shrink-0 p-1.5 rounded-lg text-red-600 hover:bg-red-50 text-xs font-bold" title="Hapus Update">' +
                        '<i class="fa-solid fa-trash-can"></i>' +
                    '</a>' +
                '</div>';
        });
        html += '</div>';
        container.innerHTML = html;
    }

    document.getElementById('modalKabarPenyaluran').classList.remove('hidden');
}

function tutupModalKabar() {
    document.getElementById('modalKabarPenyaluran').classList.add('hidden');
}

function gunakanSemuaSisaKas() {
    if (currentProgSisaKas <= 0) {
        alert('Sisa kas donasi program ini adalah Rp 0. Belum ada dana donasi yang siap disalurkan.');
        return;
    }
    var input = document.getElementById('kabarDana');
    input.value = Math.round(currentProgSisaKas).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    cekBatasPenyaluran(input);
}

function cekBatasPenyaluran(input) {
    input.value = input.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    var val = parseFloat(input.value.replace(/\./g, '')) || 0;
    var warning   = document.getElementById('kabarDanaWarning');
    var submitBtn = document.getElementById('kabarSubmitBtn');
    if (val > currentProgSisaKas) {
        if (warning)   warning.classList.remove('hidden');
        if (submitBtn) { submitBtn.disabled = true;  submitBtn.classList.add('opacity-50', 'cursor-not-allowed'); }
    } else {
        if (warning)   warning.classList.add('hidden');
        if (submitBtn) { submitBtn.disabled = false; submitBtn.classList.remove('opacity-50', 'cursor-not-allowed'); }
    }
}

function formatRupiahJs(angka) {
    return 'Rp ' + Number(angka || 0).toLocaleString('id-ID');
}

function escapeHtml(text) {
    if (!text) return '';
    var map = { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

// ============================================================
// Program Modal (Tambah / Edit)
// ============================================================
function bukaModalProgram() {
    var modal = document.getElementById('modalProgram');
    if (!modal) return;
    modal.classList.remove('hidden');
    document.getElementById('progAksi').value  = 'tambah';
    document.getElementById('progId').value    = '0';
    document.getElementById('modalProgTitle').textContent = 'Tambah Program Baru';
    document.getElementById('progNama').value     = '';
    document.getElementById('progKategori').selectedIndex = 0;
    document.getElementById('progTarget').value  = '';
    document.getElementById('progDesk').value     = '';
    document.getElementById('progLengkap').value  = '';
    document.getElementById('progMulai').value    = '';
    document.getElementById('progSelesai').value  = '';
    document.getElementById('progStatus').selectedIndex = 0;
    updateProgNamaCounter();
}

function tutupModalProgram() {
    var modal = document.getElementById('modalProgram');
    if (modal) modal.classList.add('hidden');
}

function editProgramSafe(btn) {
    var data = btn.getAttribute('data-program');
    if (!data) return;
    var prog;
    try { prog = JSON.parse(data); } catch(e) { console.error(e); return; }
    var modal = document.getElementById('modalProgram');
    if (!modal) return;
    modal.classList.remove('hidden');
    document.getElementById('modalProgTitle').textContent = 'Edit Program: ' + (prog.nama_program || '');
    document.getElementById('progAksi').value  = 'edit';
    document.getElementById('progId').value    = prog.id;
    document.getElementById('progNama').value  = prog.nama_program || '';
    document.getElementById('progKategori').value = prog.kategori || '';
    document.getElementById('progTarget').value = prog.target_donasi
        ? String(prog.target_donasi).replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')
        : '';
    document.getElementById('progDesk').value     = prog.deskripsi || '';
    document.getElementById('progLengkap').value  = prog.deskripsi_lengkap || '';
    document.getElementById('progMulai').value    = prog.tanggal_mulai || '';
    document.getElementById('progSelesai').value  = prog.tanggal_selesai || '';
    document.getElementById('progStatus').value   = prog.status || 'aktif';
    updateProgNamaCounter();
}

function updateProgNamaCounter() {
    var input   = document.getElementById('progNama');
    var counter = document.getElementById('progNamaCounter');
    if (input && counter) counter.textContent = input.value.length;
}

// ============================================================
// DOMContentLoaded — counter listeners
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    var nameInput = document.getElementById('progNama');
    if (nameInput) {
        nameInput.addEventListener('input', updateProgNamaCounter);
        updateProgNamaCounter();
    }
});
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
