<?php
// verifikasi-donasi.php - Verifikasi Donasi Online & Integrasi Otomatis ke Pembukuan (Scope 25)
require_once __DIR__ . '/../config/database.php';
cek_role(['admin', 'bendahara']);

$user = $_SESSION['user'];
$profil = get_profil_masjid();
$pesan = '';
$tipe = '';

// ==============================================================================
// 1. AKSI VERIFIKASI (TERIMA & BUKUKAN KE TRANSAKSI KAS)
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'verifikasi') {
    $idDonasi = (int)($_POST['donasi_id'] ?? 0);

    $stmt = $pdo->prepare("SELECT d.*, p.nama_program 
                           FROM donasi_online d 
                           JOIN program_donasi p ON d.program_id = p.id 
                           WHERE d.id = ? AND d.status = 'pending'");
    $stmt->execute([$idDonasi]);
    $donasi = $stmt->fetch();

    if ($donasi) {
        $nominal = (float)$donasi['nominal'];
        $tanggal = date('Y-m-d');

        // 1. Generate No Transaksi Kas
        $ym = date('Ym');
        $lastTrx = $pdo->query("SELECT no_transaksi FROM transaksi_keuangan WHERE no_transaksi LIKE 'TRX-IN-{$ym}-%' ORDER BY id DESC LIMIT 1")->fetch();
        if ($lastTrx) {
            $num = (int)substr($lastTrx['no_transaksi'], -4);
            $next = str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $next = '0001';
        }
        $noTrx = "TRX-IN-{$ym}-{$next}";

        // Akun Kas
        $akunKas = ($donasi['metode_pembayaran'] === 'qris') ? 'Bank BSI' : ($donasi['bank_tujuan'] ?: 'Bank BSI');

        // Kategori: Donasi Program Khusus (id 3) atau Infaq Transfer & QRIS (id 4)
        $kategoriId = 4;

        $keterangan = "Donasi online (" . $donasi['no_donasi'] . ") dari " . ($donasi['is_anonim'] ? 'Hamba Allah' : $donasi['nama_donatur']) . " untuk " . $donasi['nama_program'];

        // Simpan ke Buku Kas Transaksi Keuangan (is_published = 1 untuk transparansi publik!)
        $stmtTrx = $pdo->prepare("INSERT INTO transaksi_keuangan (
            no_transaksi, tanggal_transaksi, jenis, kategori_id, program_id, akun_kas, 
            nominal, keterangan, bukti_transaksi, user_id, metode_pembayaran, is_published
        ) VALUES (?, ?, 'pemasukan', ?, ?, ?, ?, ?, ?, ?, ?, 1)");

        $stmtTrx->execute([
            $noTrx, $tanggal, $kategoriId, $donasi['program_id'], $akunKas,
            $nominal, $keterangan, $donasi['bukti_pembayaran'], $user['id'], $donasi['metode_pembayaran']
        ]);
        $newTrxId = $pdo->lastInsertId();

        // 2. Tambah dana terkumpul pada Program Terkait
        $pdo->prepare("UPDATE program_donasi SET dana_terkumpul = dana_terkumpul + ? WHERE id = ?")->execute([$nominal, $donasi['program_id']]);

        // 3. Perbarui Status Donasi Online
        $stmtUpdate = $pdo->prepare("UPDATE donasi_online SET status = 'diverifikasi', verified_by = ?, verified_at = NOW(), transaksi_id = ? WHERE id = ?");
        $stmtUpdate->execute([$user['id'], $newTrxId, $idDonasi]);

        // 4. Kirim Notifikasi ke Akun Donatur (jika terdaftar)
        if (!empty($donasi['user_id'])) {
            $stmtNotif = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, 'donasi', ?)");
            $stmtNotif->execute([
                $donasi['user_id'],
                'Donasi #' . $donasi['no_donasi'] . ' Terverifikasi!',
                'Alhamdulillah, donasi Anda sebesar ' . format_rupiah($nominal) . ' telah diverifikasi bendahara dan dicatat pada buku kas masjid.',
                '../donatur/kwitansi.php?no=' . $donasi['no_donasi']
            ]);
        }

        $pesan = "Alhamdulillah! Donasi #{$donasi['no_donasi']} berhasil diverifikasi dan otomatis dibukukan ke Kas Masuk ({$noTrx}).";
        $tipe  = 'success';
    } else {
        $pesan = "Data donasi tidak ditemukan atau sudah diproses.";
        $tipe  = 'error';
    }
}

// ==============================================================================
// 2. AKSI TOLAK DONASI
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tolak') {
    $idDonasi = (int)($_POST['donasi_id'] ?? 0);
    $alasan   = trim($_POST['alasan'] ?? 'Bukti transfer tidak valid atau dana belum masuk.');

    $stmt = $pdo->prepare("SELECT * FROM donasi_online WHERE id = ? AND status = 'pending'");
    $stmt->execute([$idDonasi]);
    $donasi = $stmt->fetch();

    if ($donasi) {
        $stmtUpdate = $pdo->prepare("UPDATE donasi_online SET status = 'ditolak', catatan_admin = ?, verified_by = ?, verified_at = NOW() WHERE id = ?");
        $stmtUpdate->execute([$alasan, $user['id'], $idDonasi]);

        if (!empty($donasi['user_id'])) {
            $stmtNotif = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, 'sistem', ?)");
            $stmtNotif->execute([
                $donasi['user_id'],
                'Donasi #' . $donasi['no_donasi'] . ' Ditolak',
                'Mohon maaf, donasi Anda tidak dapat diverifikasi: ' . $alasan,
                '../donatur/kwitansi.php?no=' . $donasi['no_donasi']
            ]);
        }

        $pesan = "Donasi #{$donasi['no_donasi']} telah ditolak.";
        $tipe  = 'success';
    }
}

// ==============================================================================
// 3. QUERY DAFTAR DONASI ONLINE
// ==============================================================================
$filterStatus = $_GET['status'] ?? 'pending';

$sql = "SELECT d.*, p.nama_program, u.nama_lengkap as verifikator 
        FROM donasi_online d 
        JOIN program_donasi p ON d.program_id = p.id 
        LEFT JOIN users u ON d.verified_by = u.id 
        WHERE 1=1";
$params = [];

if ($filterStatus !== 'semua' && in_array($filterStatus, ['pending', 'diverifikasi', 'ditolak'])) {
    $sql .= " AND d.status = ?";
    $params[] = $filterStatus;
}

$sql .= " ORDER BY (d.status = 'pending') DESC, d.id DESC";

$pag = paginate_data($pdo, $sql, $params, 10);
$daftarDonasi = $pag['items'];

// Hitung Statistik
$countPending = (int)$pdo->query("SELECT COUNT(*) FROM donasi_online WHERE status = 'pending'")->fetchColumn();
$countVerif   = (int)$pdo->query("SELECT COUNT(*) FROM donasi_online WHERE status = 'diverifikasi'")->fetchColumn();
$countDitolak = (int)$pdo->query("SELECT COUNT(*) FROM donasi_online WHERE status = 'ditolak'")->fetchColumn();

$pageTitle    = 'Verifikasi Donasi Masuk · ' . $profil['nama_masjid'];
$activeMenu   = 'verifikasi';
$pageSubtitle = 'Verifikasi Pembayaran & Integrasi Buku Kas';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">
            Verifikasi Donasi Online
        </h2>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">
            Validasi setoran infaq jamaah melalui QRIS/Transfer untuk dibukukan otomatis ke Buku Kas Kasir.
        </p>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto text-xs font-semibold">
        <a href="verifikasi-donasi.php?status=pending" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 <?= $filterStatus === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-warm-800 border border-antique-200 hover:bg-warm-50' ?>">
            <span>Menunggu</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20 font-bold"><?= $countPending ?></span>
        </a>
        <a href="verifikasi-donasi.php?status=diverifikasi" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 <?= $filterStatus === 'diverifikasi' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-white text-warm-800 border border-antique-200 hover:bg-warm-50' ?>">
            <span>Diterima</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20 font-bold"><?= $countVerif ?></span>
        </a>
        <a href="verifikasi-donasi.php?status=ditolak" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 <?= $filterStatus === 'ditolak' ? 'bg-red-700 text-white shadow-xs' : 'bg-white text-warm-800 border border-antique-200 hover:bg-warm-50' ?>">
            <span>Ditolak</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20 font-bold"><?= $countDitolak ?></span>
        </a>
        <a href="verifikasi-donasi.php?status=semua" class="px-3.5 py-2 rounded-xl transition <?= $filterStatus === 'semua' ? 'bg-cypress-800 text-white shadow-xs' : 'bg-white text-warm-800 border border-antique-200 hover:bg-warm-50' ?>">
            Semua Donasi
        </a>
    </div>
</div>

<?php if ($pesan): ?>
    <div class="p-4 rounded-2xl <?= $tipe === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?> text-xs font-semibold flex items-center gap-2">
        <i class="fa-solid <?= $tipe === 'success' ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-red-600' ?>"></i>
        <span><?= e($pesan) ?></span>
    </div>
<?php endif; ?>

<!-- Tabel Daftar Donasi Masuk -->
<div class="bg-white rounded-3xl border border-antique-300/50 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-antique-200 flex items-center justify-between">
        <h3 class="font-classic text-base font-bold text-warm-900">
            Daftar Setoran Donasi Jamaah
        </h3>
        <span class="text-xs text-warm-800/60 font-semibold"><?= number_format($pag['total']) ?> Donasi Ditemukan</span>
    </div>

    <?php if (empty($daftarDonasi)): ?>
        <div class="text-center py-12 space-y-2">
            <i class="fa-solid fa-clipboard-check text-4xl text-stone-300"></i>
            <p class="text-xs text-warm-800/60 font-medium">Tidak ada permohonan donasi pada status ini.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-antique-200 bg-warm-50/70 text-warm-800/70 uppercase text-[10px] font-bold">
                        <th class="py-3 px-4">No. Donasi / Tgl</th>
                        <th class="py-3 px-4">Donatur &amp; Kontak</th>
                        <th class="py-3 px-4">Program Tujuan</th>
                        <th class="py-3 px-4">Metode / Bank</th>
                        <th class="py-3 px-4 text-right">Nominal</th>
                        <th class="py-3 px-4 text-center">Bukti</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-antique-100">
                    <?php foreach ($daftarDonasi as $d): ?>
                        <tr class="hover:bg-warm-50/60 transition">
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <span class="font-mono font-bold text-cypress-800 block"><?= e($d['no_donasi']) ?></span>
                                <span class="text-[11px] text-warm-800/60 block"><?= tanggal_indo($d['tanggal_donasi']) ?></span>
                            </td>
                            <td class="py-3.5 px-4 align-top">
                                <strong class="text-warm-900 block font-bold">
                                    <?= $d['is_anonim'] ? 'Hamba Allah (Anonim)' : e($d['nama_donatur']) ?>
                                </strong>
                                <span class="text-[11px] text-warm-800/60 block mt-0.5">
                                    <i class="fa-brands fa-whatsapp text-emerald-600 mr-1"></i> <?= e($d['no_wa'] ?: '-') ?>
                                </span>
                                <?php if (!empty($d['doa_donatur'])): ?>
                                    <p class="text-[11px] italic text-antique-800 mt-1 bg-antique-50/80 p-1.5 rounded border border-antique-200 max-w-xs">
                                        "<?= e($d['doa_donatur']) ?>"
                                    </p>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 align-top">
                                <span class="font-semibold text-warm-900 block"><?= e($d['nama_program']) ?></span>
                            </td>
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                <span class="text-[11px] font-bold uppercase block text-warm-900"><?= e($d['metode_pembayaran']) ?></span>
                                <span class="text-[10px] text-warm-800/60 block"><?= e($d['bank_tujuan'] ?: 'Digital') ?></span>
                            </td>
                            <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                <span class="font-bold font-classic text-sm text-cypress-700 block">
                                    <?= format_rupiah($d['nominal']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 align-top text-center">
                                <?php if (!empty($d['bukti_pembayaran'])): ?>
                                    <?php $buktiExt = strtolower(pathinfo($d['bukti_pembayaran'], PATHINFO_EXTENSION)); ?>
                                    <?php if (in_array($buktiExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'])): ?>
                                        <a href="<?= e(upload_url($d['bukti_pembayaran'])) ?>" target="_blank" class="block mx-auto" title="Lihat bukti pembayaran">
                                            <img src="<?= e(upload_url($d['bukti_pembayaran'])) ?>" alt="Bukti <?= e($d['no_donasi']) ?>" class="h-16 w-16 object-cover rounded-lg border border-antique-300/60 shadow-xs hover:scale-105 transition">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= e(upload_url($d['bukti_pembayaran'])) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-cypress-50 border border-cypress-200 text-cypress-800 hover:bg-cypress-100 font-semibold text-[11px] transition" title="Lihat bukti pembayaran">
                                            <i class="fa-solid fa-file"></i>
                                            <span>Lihat</span>
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-stone-400 text-[10px] italic">Tanpa Bukti</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 align-top text-center whitespace-nowrap">
                                <?php if ($d['status'] === 'diverifikasi'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        Terverifikasi
                                    </span>
                                <?php elseif ($d['status'] === 'ditolak'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-300">
                                        Ditolak
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">
                                        Menunggu
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                <?php if ($d['status'] === 'pending'): ?>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Form Verifikasi Terima -->
                                        <form method="POST" action="verifikasi-donasi.php" data-hapus data-variant="success" data-judul="Verifikasi Donasi" data-pesan="Verifikasi donasi ini dan otomatis bukukan ke transaksi kas?">
                                            <input type="hidden" name="aksi" value="verifikasi">
                                            <input type="hidden" name="donasi_id" value="<?= $d['id'] ?>">
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs transition flex items-center gap-1 shadow-xs">
                                                <i class="fa-solid fa-check"></i> Terima
                                            </button>
                                        </form>

                                        <!-- Form Tolak -->
                                        <button type="button" onclick="bukaModalTolak(<?= $d['id'] ?>, '<?= e($d['no_donasi']) ?>')" class="px-2.5 py-1.5 rounded-lg bg-red-100 hover:bg-red-200 text-red-800 font-bold text-xs transition">
                                            <i class="fa-solid fa-xmark"></i> Tolak
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <a href="../donatur/kwitansi.php?no=<?= e($d['no_donasi']) ?>" target="_blank" class="px-2.5 py-1 rounded-lg bg-warm-100 hover:bg-warm-200 text-warm-800 text-[11px] font-semibold transition">
                                        Kwitansi
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php render_pagination($pag['totalHalaman'], $pag['halaman'], $pag['total'], $pag['dari'], $pag['sampai']); ?>
    <?php endif; ?>
</div>

<!-- Modal Tolak Donasi -->
<div id="modalTolak" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 border border-red-200 shadow-2xl space-y-4">
        <h3 class="font-classic text-base font-bold text-red-900">
            Tolak Donasi Online <span id="labelTolakNo"></span>
        </h3>
        <form method="POST" action="verifikasi-donasi.php" class="space-y-3 text-xs">
            <input type="hidden" name="aksi" value="tolak">
            <input type="hidden" id="inputTolakId" name="donasi_id" value="0">
            
            <div>
                <label class="block font-bold text-warm-800 mb-1">Alasan Penolakan</label>
                <textarea name="alasan" rows="3" required placeholder="Contoh: Bukti transfer tidak terbaca / dana belum masuk rekening..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalTolak()" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-700 font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white font-bold">Konfirmasi Tolak</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalTolak(id, no) {
    document.getElementById('inputTolakId').value = id;
    document.getElementById('labelTolakNo').textContent = '#' + no;
    document.getElementById('modalTolak').classList.remove('hidden');
}
function tutupModalTolak() {
    document.getElementById('modalTolak').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
