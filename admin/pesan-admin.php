<?php
// pesan-admin.php - Manajemen Kotak Masuk & Pesan Kontak Jamaah (Scope 7)
require_once __DIR__ . '/../config/database.php';
cek_role(['admin', 'content_admin']);

$profil = get_profil_masjid();
$user   = $_SESSION['user'];
$pesan  = '';
$tipe   = '';

// 1. TANDAI SEMUA SUDAH DIBACA
if (isset($_GET['mark_all_read']) && $_GET['mark_all_read'] == '1') {
    $pdo->query("UPDATE pesan_kontak SET is_read = 1 WHERE is_read = 0");
    header('Location: pesan-admin?msg=all_read');
    exit;
}

// 2. TOGGLE STATUS BACA
if (isset($_GET['toggle_read'])) {
    $idToggle = (int)$_GET['toggle_read'];
    $pdo->prepare("UPDATE pesan_kontak SET is_read = 1 - is_read WHERE id = ?")->execute([$idToggle]);
    header('Location: pesan-admin?msg=status_updated');
    exit;
}

// 3. TANDAI DIBACA SAAT BUKA MODAL
if (isset($_GET['mark_read'])) {
    $idRead = (int)$_GET['mark_read'];
    $pdo->prepare("UPDATE pesan_kontak SET is_read = 1 WHERE id = ?")->execute([$idRead]);
    header('Location: pesan-admin?view=' . $idRead);
    exit;
}

// 4. HAPUS PESAN
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM pesan_kontak WHERE id = ?")->execute([$idHapus]);
    header('Location: pesan-admin?msg=deleted');
    exit;
}

// Pesan Notifikasi
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') {
        $pesan = 'Pesan kontak berhasil dihapus.';
        $tipe  = 'success';
    } elseif ($_GET['msg'] === 'all_read') {
        $pesan = 'Semua pesan berhasil ditandai sudah dibaca.';
        $tipe  = 'success';
    } elseif ($_GET['msg'] === 'status_updated') {
        $pesan = 'Status pesan berhasil diperbarui.';
        $tipe  = 'success';
    }
}

// Filter Status & Search
$filterStatus = $_GET['status'] ?? 'semua';
$search       = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM pesan_kontak WHERE 1=1";
$params = [];

if ($filterStatus === 'unread') {
    $sql .= " AND is_read = 0";
} elseif ($filterStatus === 'read') {
    $sql .= " AND is_read = 1";
}

if (!empty($search)) {
    $sql .= " AND (nama LIKE ? OR email LIKE ? OR no_hp LIKE ? OR subjek LIKE ? OR pesan LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= " ORDER BY created_at DESC, id DESC";

$pag = paginate_data($pdo, $sql, $params, 10);
$pesanList = $pag['items'];

$countTotal  = (int)$pdo->query("SELECT COUNT(*) FROM pesan_kontak")->fetchColumn();
$countUnread = (int)$pdo->query("SELECT COUNT(*) FROM pesan_kontak WHERE is_read = 0")->fetchColumn();
$countRead   = (int)$pdo->query("SELECT COUNT(*) FROM pesan_kontak WHERE is_read = 1")->fetchColumn();

$pageTitle    = 'Kotak Masuk Pesan Jamaah · ' . $profil['nama_masjid'];
$activeMenu   = 'pesan-admin';
$pageSubtitle = 'Manajemen Pesan Masuk, Kritik & Saran Jamaah';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<!-- Header Action Bar -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">
            Kotak Masuk Pesan Jamaah
        </h2>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">
            Pesan, masukan, permohonan konsultasi, serta saran kemakmuran yang dikirim jamaah melalui website.
        </p>
    </div>

    <?php if ($countUnread > 0): ?>
        <a href="pesan-admin?mark_all_read=1" class="px-4 py-2.5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs shadow-xs transition flex items-center gap-2 self-start sm:self-auto">
            <i class="fa-solid fa-envelope-circle-check"></i>
            <span>Tandai Semua Dibaca</span>
        </a>
    <?php endif; ?>
</div>

<?php if ($pesan): ?>
    <div class="p-4 rounded-2xl <?= $tipe === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?> text-xs font-semibold flex items-center gap-2">
        <i class="fa-solid <?= $tipe === 'success' ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-red-600' ?>"></i>
        <span><?= e($pesan) ?></span>
    </div>
<?php endif; ?>

<!-- Filter Tabs & Search -->
<div class="bg-white p-4 sm:p-5 rounded-3xl border border-antique-300/50 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
    <!-- Status Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto text-xs font-semibold">
        <a href="pesan-admin?status=semua<?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 <?= $filterStatus === 'semua' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
            <span>Semua Pesan</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] <?= $filterStatus === 'semua' ? 'bg-white/20' : 'bg-stone-200 text-stone-700' ?> font-bold"><?= $countTotal ?></span>
        </a>
        <a href="pesan-admin?status=unread<?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 <?= $filterStatus === 'unread' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
            <span>Belum Dibaca</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] <?= $filterStatus === 'unread' ? 'bg-white/20' : 'bg-emerald-100 text-emerald-800' ?> font-bold"><?= $countUnread ?></span>
        </a>
        <a href="pesan-admin?status=read<?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 <?= $filterStatus === 'read' ? 'bg-stone-700 text-white shadow-xs' : 'bg-warm-50 text-warm-800 hover:bg-warm-100 border border-antique-200' ?>">
            <span>Sudah Dibaca</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] <?= $filterStatus === 'read' ? 'bg-white/20' : 'bg-stone-200 text-stone-700' ?> font-bold"><?= $countRead ?></span>
        </a>
    </div>

    <!-- Search Form -->
    <form method="GET" action="pesan-admin" class="flex items-center gap-2">
        <?php if ($filterStatus !== 'semua'): ?>
            <input type="hidden" name="status" value="<?= e($filterStatus) ?>">
        <?php endif; ?>
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama, subjek, no HP..." class="px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:outline-none w-full sm:w-60">
        <button type="submit" class="px-3.5 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs transition shrink-0">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </form>
</div>

<!-- Table / Card List Pesan -->
<div class="bg-white rounded-3xl border border-antique-300/50 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-antique-200 flex items-center justify-between">
        <h3 class="font-classic text-base font-bold text-warm-900">
            Daftar Pesan Masuk
        </h3>
        <span class="text-xs text-warm-800/60 font-semibold"><?= number_format($pag['total']) ?> Pesan</span>
    </div>

    <?php if (empty($pesanList)): ?>
        <div class="text-center py-16 space-y-3">
            <div class="w-16 h-16 rounded-full bg-warm-50 border border-antique-200 flex items-center justify-center mx-auto text-stone-300">
                <i class="fa-solid fa-inbox text-2xl"></i>
            </div>
            <p class="text-sm font-semibold text-warm-900">Belum ada pesan masuk</p>
            <p class="text-xs text-warm-800/60 max-w-sm mx-auto">Pesan yang dikirimkan jamaah melalui formulir kontak di website publik akan tampil di sini.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-antique-200 bg-warm-50/70 text-warm-800/70 uppercase text-[10px] font-bold">
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Pengirim &amp; Kontak</th>
                        <th class="py-3 px-4">Subjek &amp; Pesan</th>
                        <th class="py-3 px-4">Waktu Kirim</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-antique-100">
                    <?php foreach ($pesanList as $item): ?>
                        <tr class="hover:bg-warm-50/60 transition <?= !$item['is_read'] ? 'bg-emerald-50/30 font-medium' : '' ?>">
                            <td class="py-3.5 px-4 text-center align-top whitespace-nowrap">
                                <a href="pesan-admin?toggle_read=<?= $item['id'] ?>" title="Klik untuk mengubah status baca">
                                    <?php if (!$item['is_read']): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span> Baru
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-stone-100 text-stone-600">
                                            Dibaca
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </td>

                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <strong class="font-bold text-warm-900 block leading-tight"><?= e($item['nama']) ?></strong>
                                <span class="text-[11px] text-warm-800/70 block mt-0.5"><?= e($item['email']) ?></span>
                                <?php if (!empty($item['no_hp'])): ?>
                                    <span class="text-[10px] text-emerald-800 font-semibold block mt-0.5">
                                        <i class="fa-brands fa-whatsapp"></i> <?= e($item['no_hp']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="py-3.5 px-4 align-top max-w-xs md:max-w-md">
                                <span class="font-bold text-warm-900 block text-xs truncate"><?= e($item['subjek']) ?></span>
                                <p class="text-[11px] text-warm-800/70 line-clamp-2 mt-0.5"><?= e($item['pesan']) ?></p>
                            </td>

                            <td class="py-3.5 px-4 align-top whitespace-nowrap text-warm-800/60">
                                <span class="block font-medium"><?= date('d/m/Y', strtotime($item['created_at'])) ?></span>
                                <span class="text-[10px] font-mono"><?= date('H:i', strtotime($item['created_at'])) ?> WIB</span>
                            </td>

                            <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" 
                                            data-pesan="<?= htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>" 
                                            onclick="bukaModalPesanSafe(this)" 
                                            class="px-2.5 py-1.5 rounded-lg bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs transition flex items-center gap-1 shadow-xs" 
                                            title="Baca Detail Pesan">
                                        <i class="fa-solid fa-envelope-open"></i>
                                        <span>Baca</span>
                                    </button>

                                    <a href="pesan-admin?hapus=<?= $item['id'] ?>" 
                                       data-hapus 
                                       data-judul="Hapus Pesan" 
                                       data-pesan="Pesan dari '<?= e($item['nama']) ?>' akan dihapus permanen. Lanjutkan?" 
                                       class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition" 
                                       title="Hapus Pesan">
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
    <?php endif; ?>
</div>

<!-- Modal Detail & Balas Pesan -->
<div id="modalDetailPesan" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-cypress-950/60 veil-blur" onclick="tutupModalPesan()"></div>
    <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 border border-antique-300 shadow-2xl space-y-4 text-xs">
        <div class="flex items-center justify-between border-b border-antique-200 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-cypress-900 text-antique-300 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-envelope-open"></i>
                </div>
                <div>
                    <h3 class="font-classic text-base font-bold text-warm-900">Detail Pesan Masuk</h3>
                    <span id="modalWaktu" class="text-[10px] text-warm-800/60 font-mono"></span>
                </div>
            </div>
            <button type="button" onclick="tutupModalPesan()" class="text-stone-400 hover:text-stone-700 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="space-y-3 bg-warm-50/70 p-4 rounded-2xl border border-antique-200">
            <div class="grid grid-cols-2 gap-2 text-[11px]">
                <div>
                    <span class="text-warm-800/60 block text-[10px] font-semibold uppercase">Nama Pengirim</span>
                    <strong id="modalNama" class="font-bold text-warm-900 text-xs"></strong>
                </div>
                <div>
                    <span class="text-warm-800/60 block text-[10px] font-semibold uppercase">Alamat Email</span>
                    <a id="modalEmailLink" href="#" class="text-cypress-800 font-semibold hover:underline block truncate"></a>
                </div>
            </div>
            <div id="boxNoHp" class="text-[11px]">
                <span class="text-warm-800/60 block text-[10px] font-semibold uppercase">Nomor WhatsApp / HP</span>
                <span id="modalNoHp" class="font-mono font-bold text-emerald-800"></span>
            </div>
        </div>

        <div>
            <span class="text-warm-800/60 block text-[10px] font-semibold uppercase mb-1">Subjek</span>
            <h4 id="modalSubjek" class="font-bold text-warm-900 text-sm"></h4>
        </div>

        <div>
            <span class="text-warm-800/60 block text-[10px] font-semibold uppercase mb-1">Isi Pesan</span>
            <div id="modalIsiPesan" class="p-3.5 bg-white rounded-xl border border-antique-300 font-sans text-warm-900 text-xs whitespace-pre-line leading-relaxed max-h-48 overflow-y-auto"></div>
        </div>

        <!-- Tombol Aksi Balas Cepat -->
        <div class="pt-3 border-t border-antique-200 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <a id="btnBalasWA" href="#" target="_blank" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-xs">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>Balas via WA</span>
                </a>
                <a id="btnBalasEmail" href="#" class="px-3.5 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-xs">
                    <i class="fa-regular fa-envelope"></i>
                    <span>Balas via Email</span>
                </a>
            </div>

            <button type="button" onclick="tutupModalPesan()" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold text-xs transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
function bukaModalPesanSafe(btn) {
    try {
        const item = JSON.parse(btn.getAttribute('data-pesan'));
        document.getElementById('modalNama').textContent = item.nama || '-';
        
        const emailLink = document.getElementById('modalEmailLink');
        emailLink.textContent = item.email || '-';
        emailLink.href = 'mailto:' + encodeURIComponent(item.email || '');

        const boxHp = document.getElementById('boxNoHp');
        const modalHp = document.getElementById('modalNoHp');
        const btnWA = document.getElementById('btnBalasWA');

        if (item.no_hp && item.no_hp.trim() !== '') {
            boxHp.classList.remove('hidden');
            modalHp.textContent = item.no_hp;
            
            // Format WA number (e.g. 08... -> 628...)
            let cleanPhone = item.no_hp.replace(/\D/g, '');
            if (cleanPhone.startsWith('0')) {
                cleanPhone = '62' + cleanPhone.substring(1);
            }
            const waGreeting = `Assalamu’alaikum Wr. Wb. Yth. ${item.nama},\n\nTerima kasih telah menghubungi Pengurus ${<?= json_encode($profil['nama_masjid']) ?>} terkait "${item.subjek}".\n\n`;
            btnWA.href = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(waGreeting)}`;
            btnWA.classList.remove('hidden');
        } else {
            boxHp.classList.add('hidden');
            btnWA.classList.add('hidden');
        }

        const emailGreeting = `Assalamu’alaikum Wr. Wb. Yth. ${item.nama},\n\nTerima kasih telah menghubungi Pengurus ${<?= json_encode($profil['nama_masjid']) ?>}.\n\nMenanggapi pesan Anda mengenai "${item.subjek}":\n\n`;
        document.getElementById('btnBalasEmail').href = `mailto:${encodeURIComponent(item.email)}?subject=${encodeURIComponent('Tanggapan: ' + item.subjek)}&body=${encodeURIComponent(emailGreeting)}`;

        document.getElementById('modalSubjek').textContent = item.subjek || '-';
        document.getElementById('modalIsiPesan').textContent = item.pesan || '-';
        document.getElementById('modalWaktu').textContent = item.created_at || '';

        // Pastikan modal menempel di document.body agar blur menutupi 100% layar (termasuk sidebar)
        const modal = document.getElementById('modalDetailPesan');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        // Tampilkan Modal
        modal.classList.remove('hidden');

        // Otomatis tandai dibaca jika sebelumnya belum dibaca
        if (item.is_read == 0) {
            fetch('pesan-admin?toggle_read=' + item.id, { method: 'GET' });
        }
    } catch (e) {
        console.error('Gagal membuka detail pesan:', e);
    }
}

function tutupModalPesan() {
    document.getElementById('modalDetailPesan').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
