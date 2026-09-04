<?php
// donasi-online.php - Formulir Donasi Online (Metode QRIS & Transfer Bank)
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pageTitle = 'Formulir Infaq & Donasi Online · ' . $profil['nama_masjid'];
$activeNav = 'program';

$selectedProgramId = (int)($_GET['program_id'] ?? 0);
$programs = $pdo->query("SELECT id, nama_program, target_donasi, dana_terkumpul FROM program_donasi WHERE status = 'aktif' ORDER BY is_featured DESC, id DESC")->fetchAll();
$rekeningList = $pdo->query("SELECT * FROM rekening_donasi WHERE is_active = 1 ORDER BY urutan ASC")->fetchAll();

$user = $_SESSION['user'] ?? null;
$pesan = '';
$tipe = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program_id   = (int)($_POST['program_id'] ?? 0);
    $nominal      = (float)str_replace(['Rp', '.', ' ', ','], '', $_POST['nominal'] ?? '0');
    $is_anonim    = isset($_POST['is_anonim']) ? 1 : 0;
    $nama_donatur = trim($_POST['nama_donatur'] ?? '');
    $no_wa        = trim($_POST['no_wa'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $metode       = $_POST['metode_pembayaran'] ?? 'qris';
    $bank_tujuan  = trim($_POST['bank_tujuan'] ?? 'QRIS Dinamis');
    $doa_donatur  = trim($_POST['doa_donatur'] ?? '');

    if ($is_anonim && empty($nama_donatur)) {
        $nama_donatur = 'Hamba Allah';
    }

    if ($program_id <= 0) {
        $pesan = 'Silakan pilih program tujuan donasi.';
        $tipe = 'error';
    } elseif ($nominal < 10000) {
        $pesan = 'Nominal donasi minimal Rp 10.000.';
        $tipe = 'error';
    } elseif (empty($nama_donatur)) {
        $pesan = 'Nama donatur wajib diisi (atau centang Hamba Allah).';
        $tipe = 'error';
    } else {
        // Upload Bukti Pembayaran (jika ada)
        $buktiPath = null;
        if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
            $buktiPath = upload_berkas('bukti_pembayaran', 'bukti');
        }

        // Generate No Donasi Unik: DON-YYYYMM-XXXX
        $blnThn = date('Ym');
        $lastTrx = $pdo->query("SELECT no_donasi FROM donasi_online WHERE no_donasi LIKE 'DON-$blnThn-%' ORDER BY id DESC LIMIT 1")->fetch();
        if ($lastTrx) {
            $lastNum = (int)substr($lastTrx['no_donasi'], -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }
        $noDonasi = "DON-$blnThn-$nextNum";

        $userId = ($user && isset($user['id'])) ? $user['id'] : null;

        $stmt = $pdo->prepare("INSERT INTO donasi_online (
            no_donasi, user_id, nama_donatur, is_anonim, email, no_wa, program_id, 
            nominal, metode_pembayaran, bank_tujuan, bukti_pembayaran, doa_donatur, status, tanggal_donasi
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', CURDATE())");

        $stmt->execute([
            $noDonasi, $userId, $nama_donatur, $is_anonim, $email, $no_wa, $program_id,
            $nominal, $metode, $bank_tujuan, $buktiPath, $doa_donatur
        ]);

        // Tambahkan notifikasi jika donatur memiliki akun
        if ($userId) {
            $stmtNotif = $pdo->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, 'donasi', ?)");
            $stmtNotif->execute([
                $userId,
                'Donasi Baru Tercatat #' . $noDonasi,
                'Donasi Anda sebesar ' . format_rupiah($nominal) . ' berhasil diajukan dan sedang menunggu verifikasi bendahara.',
                '../donatur/kwitansi.php?no=' . $noDonasi
            ]);
        }

        header('Location: ../donatur/kwitansi.php?no=' . $noDonasi . '&baru=1');
        exit;
    }
}

require_once __DIR__ . '/../layouts/public_header.php';
?>

<!-- Header Banner -->
<div class="bg-cypress-950 text-white py-12 relative pattern-arabesque-dark border-b border-antique-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-2 relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cypress-900 border border-antique-500/40 text-antique-300 text-xs font-semibold uppercase tracking-wider">
            <i class="fa-solid fa-heart text-emerald-400"></i>
            <span>Kemudahan Berinfaq Digital</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight font-classic text-white">
            Formulir Infaq &amp; Donasi Online
        </h1>
        <p class="text-stone-300 text-xs sm:text-sm max-w-lg mx-auto">
            Salurkan kebaikan dengan aman melalui metode QRIS Standar Nasional atau Transfer Rekening Resmi.
        </p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <?php if ($pesan): ?>
        <div class="mb-6 p-4 rounded-2xl <?= $tipe === 'error' ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' ?> text-xs font-medium flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation text-base"></i>
            <span><?= e($pesan) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="donasi-online.php" enctype="multipart/form-data" class="bg-white rounded-3xl p-6 sm:p-10 border border-antique-300/50 shadow-xl space-y-8">
        
        <!-- Bagian 1: Pilih Program & Nominal -->
        <div class="space-y-4">
            <h3 class="text-base sm:text-lg font-bold text-warm-900 font-classic border-b border-antique-200 pb-2 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-cypress-900 text-antique-300 text-xs flex items-center justify-center font-sans font-bold">1</span>
                <span>Pilih Program &amp; Nominal Donasi</span>
            </h3>

            <!-- Pilih Program -->
            <div>
                <label class="block text-xs font-bold text-warm-800 mb-1.5">Program Tujuan Donasi <span class="text-red-500">*</span></label>
                <select name="program_id" required class="w-full px-4 py-3 rounded-xl bg-warm-50 border border-antique-300 text-xs sm:text-sm font-semibold text-warm-900 focus:ring-2 focus:ring-antique-500 focus:outline-none">
                    <option value="">-- Pilih Salah Satu Program --</option>
                    <?php foreach ($programs as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($selectedProgramId === $p['id']) ? 'selected' : '' ?>>
                            <?= e($p['nama_program']) ?> (Target: <?= format_rupiah($p['target_donasi']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Tombol Preset Nominal Cepat -->
            <div>
                <label class="block text-xs font-bold text-warm-800 mb-2">Pilih Nominal Cepat (Rp)</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    <?php foreach ([20000, 50000, 100000, 250000, 500000, 1000000] as $preset): ?>
                        <button type="button" onclick="setNominal(<?= $preset ?>)" class="preset-btn py-2.5 px-3 rounded-xl border border-antique-300 hover:border-antique-500 bg-warm-50 hover:bg-antique-50 text-xs font-bold text-warm-900 transition text-center">
                            <?= format_rupiah($preset) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Input Nominal Manual -->
            <div>
                <label class="block text-xs font-bold text-warm-800 mb-1.5">Atau Masukkan Nominal Kustom (Rp) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-4 top-3 text-sm font-bold text-warm-800">Rp</span>
                    <input type="number" id="nominalInput" name="nominal" required min="10000" step="5000" placeholder="Contoh: 100000" class="w-full pl-12 pr-4 py-3 rounded-xl bg-warm-50 border border-antique-300 text-sm sm:text-base font-bold text-cypress-900 focus:ring-2 focus:ring-antique-500 focus:outline-none">
                </div>
                <span class="text-[11px] text-warm-800/60 block mt-1">Minimal donasi Rp 10.000</span>
            </div>
        </div>

        <!-- Bagian 2: Metode Pembayaran (QRIS & Transfer Bank) -->
        <div class="space-y-4">
            <h3 class="text-base sm:text-lg font-bold text-warm-900 font-classic border-b border-antique-200 pb-2 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-cypress-900 text-antique-300 text-xs flex items-center justify-center font-sans font-bold">2</span>
                <span>Pilih Cara Pembayaran</span>
            </h3>

            <!-- Switch Radio Metode -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Opsi 1: QRIS -->
                <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition has-[:checked]:border-antique-500 has-[:checked]:bg-antique-50/40 border-stone-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" name="metode_pembayaran" value="qris" checked onchange="toggleMetode('qris')" class="accent-cypress-700 w-4 h-4">
                            <span class="font-bold text-sm text-warm-900">QRIS (Semua Pembayaran)</span>
                        </div>
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Instan</span>
                    </div>
                    <span class="text-xs text-warm-800/60 mt-1 pl-6">Scan via BCA, BSI, GoPay, OVO, ShopeePay, Dana, dll.</span>
                </label>

                <!-- Opsi 2: Transfer Bank -->
                <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition has-[:checked]:border-antique-500 has-[:checked]:bg-antique-50/40 border-stone-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" name="metode_pembayaran" value="transfer" onchange="toggleMetode('transfer')" class="accent-cypress-700 w-4 h-4">
                            <span class="font-bold text-sm text-warm-900">Transfer Rekening Bank</span>
                        </div>
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-blue-100 text-blue-800">ATM/Mobile</span>
                    </div>
                    <span class="text-xs text-warm-800/60 mt-1 pl-6">Transfer ke BSI, Muamalat, BRI, atau BCA resmi masjid.</span>
                </label>

            </div>

            <!-- Kontainer Tampilan QRIS -->
            <div id="qrisContainer" class="p-6 rounded-2xl bg-cypress-950 text-white pattern-arabesque-dark border border-antique-500/40 flex flex-col items-center text-center space-y-4">
                <div class="space-y-1">
                    <span class="text-[10px] uppercase font-bold text-antique-300 tracking-wider">QRIS Standar Nasional</span>
                    <h4 class="font-classic font-bold text-base text-white"><?= strtoupper(e($profil['nama_masjid'])) ?></h4>
                    <p class="text-xs text-stone-300">NMID: ID1020030040050</p>
                </div>

                <!-- Box QR Code Mockup Cantik & Terbaca -->
                <div class="bg-white p-4 rounded-2xl shadow-xl flex flex-col items-center border-4 border-antique-500">
                    < src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= urlencode('https://masjidnuruliman.id/donasi?n=' . $profil['nama_masjid']) ?>" alt="QRIS Masjid Nurul Iman" class="w-48 h-48 sm:w-56 sm:h-56 object-contain rounded-lg">
                    <span class="text-[11px] font-bold text-imgstone-800 mt-2">Scan dengan aplikasi bank / e-wallet apa saja</span>
                </div>

                <div class="text-xs text-stone-300 space-y-1 max-w-sm">
                    <p>1. Buka aplikasi m-Banking atau E-Wallet Anda.</p>
                    <p>2. Pilih menu <strong>Scan QR / Bayar</strong> lalu arahkan kamera ke QR di atas.</p>
                    <p>3. Masukkan nominal donasi dan selesaikan transaksi.</p>
                </div>
            </div>

            <!-- Kontainer Pilihan Rekening Bank (Tampil jika transfer dipilih) -->
            <div id="transferContainer" class="hidden space-y-3">
                <label class="block text-xs font-bold text-warm-800">Pilih Rekening Tujuan Transfer</label>
                <div class="space-y-2.5">
                    <?php foreach ($rekeningList as $idx => $rek): ?>
                        <div class="p-3.5 rounded-xl border border-antique-300 bg-warm-50 flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-warm-900"><?= e($rek['nama_bank']) ?></span>
                                    <span class="text-[9px] bg-antique-200 text-antique-900 px-1.5 py-0.5 rounded"><?= e($rek['kategori_donasi']) ?></span>
                                </div>
                                <span class="font-mono text-sm font-bold text-cypress-700 tracking-wider block mt-0.5"><?= e($rek['nomor_rekening']) ?></span>
                                <span class="text-[11px] text-warm-800/60 block">a.n <?= e($rek['atas_nama']) ?></span>
                            </div>
                            <button type="button" onclick="navigator.clipboard.writeText('<?= e($rek['nomor_rekening']) ?>'); alert('Nomor rekening <?= e($rek['nama_bank']) ?> tersalin!');" class="px-3 py-1.5 rounded-lg bg-cypress-800 text-white text-xs font-semibold hover:bg-cypress-900 transition">
                                <i class="fa-regular fa-copy mr-1"></i> Salin
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Bagian 3: Data Donatur & Bukti -->
        <div class="space-y-4">
            <h3 class="text-base sm:text-lg font-bold text-warm-900 font-classic border-b border-antique-200 pb-2 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-cypress-900 text-antique-300 text-xs flex items-center justify-center font-sans font-bold">3</span>
                <span>Identitas Donatur &amp; Bukti Pembayaran</span>
            </h3>

            <div class="space-y-3">
                <!-- Checkbox Anonim -->
                <label class="flex items-center gap-2.5 text-xs font-semibold text-warm-900 cursor-pointer">
                    <input type="checkbox" id="anonimCheckbox" name="is_anonim" onchange="toggleAnonim(this.checked)" class="accent-cypress-700 w-4 h-4 rounded">
                    <span>Sembunyikan nama saya (Tampilkan sebagai "Hamba Allah")</span>
                </label>

                <!-- Nama Lengkap -->
                <div id="namaContainer">
                    <label class="block text-xs font-bold text-warm-800 mb-1">Nama Lengkap Donatur <span class="text-red-500">*</span></label>
                    <input type="text" id="namaDonatur" name="nama_donatur" value="<?= e($user['nama'] ?? '') ?>" placeholder="Masukkan nama Anda..." class="w-full px-4 py-2.5 rounded-xl bg-warm-50 border border-antique-300 text-xs sm:text-sm text-warm-900 focus:ring-2 focus:ring-antique-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-warm-800 mb-1">Nomor WhatsApp (Aktif) <span class="text-red-500">*</span></label>
                        <input type="text" name="no_wa" required placeholder="Contoh: 081234567890" class="w-full px-4 py-2.5 rounded-xl bg-warm-50 border border-antique-300 text-xs sm:text-sm text-warm-900 focus:ring-2 focus:ring-antique-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-warm-800 mb-1">Alamat Email (Opsional)</label>
                        <input type="email" name="email" value="<?= e($user['email'] ?? '') ?>" placeholder="nama@email.com" class="w-full px-4 py-2.5 rounded-xl bg-warm-50 border border-antique-300 text-xs sm:text-sm text-warm-900 focus:ring-2 focus:ring-antique-500 focus:outline-none">
                    </div>
                </div>

                <!-- Doa / Harapan -->
                <div>
                    <label class="block text-xs font-bold text-warm-800 mb-1">Doa / Harapan / Pesan Kebaikan (Opsional)</label>
                    <textarea name="doa_donatur" rows="2" placeholder="Tuliskan doa kebaikan atau hajat yang ingin didoakan oleh para jamaah..." class="w-full px-4 py-2.5 rounded-xl bg-warm-50 border border-antique-300 text-xs sm:text-sm text-warm-900 focus:ring-2 focus:ring-antique-500 focus:outline-none"></textarea>
                </div>

                <!-- Upload Bukti Transfer -->
                <div>
                    <label class="block text-xs font-bold text-warm-800 mb-1">Upload Bukti Transfer / Screenshot Pembayaran</label>
                    <input type="file" name="bukti_pembayaran" accept="image/jpeg,image/png,image/webp,application/pdf" class="w-full px-4 py-2 rounded-xl bg-warm-50 border border-antique-300 text-xs text-warm-900 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-cypress-800 file:text-white hover:file:bg-cypress-900">
                    <span class="text-[11px] text-warm-800/60 block mt-1">Format: JPG, PNG, WEBP, atau PDF. Maksimal 5MB.</span>
                </div>
            </div>
        </div>

        <!-- Tombol Submit Donasi -->
        <div class="pt-4 border-t border-antique-200">
            <button type="submit" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-antique-500 via-antique-600 to-antique-500 hover:brightness-105 text-cypress-950 font-bold text-sm tracking-wide shadow-xl shadow-antique-500/20 transition-luxury flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Konfirmasi Infaq &amp; Dapatkan e-Kwitansi Resmi</span>
            </button>
            <p class="text-center text-[11px] text-warm-800/60 mt-2">
                Dengan mengklik tombol di atas, Anda telah berniat tulus menginfaqkan rezeki di jalan Allah SWT.
            </p>
        </div>

    </form>

</div>

<script>
function setNominal(val) {
    document.getElementById('nominalInput').value = val;
}

function toggleMetode(metode) {
    const qrisBox = document.getElementById('qrisContainer');
    const transferBox = document.getElementById('transferContainer');
    if (metode === 'qris') {
        qrisBox.classList.remove('hidden');
        transferBox.classList.add('hidden');
    } else {
        qrisBox.classList.add('hidden');
        transferBox.classList.remove('hidden');
    }
}

function toggleAnonim(checked) {
    const namaInput = document.getElementById('namaDonatur');
    if (checked) {
        namaInput.value = 'Hamba Allah';
        namaInput.disabled = true;
    } else {
        namaInput.value = '';
        namaInput.disabled = false;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
