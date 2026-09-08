<?php
// kwitansi.php - e-Kwitansi Tanda Terima Donasi Resmi Digital
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$no = trim($_GET['no'] ?? '');

$stmt = $pdo->prepare("SELECT d.*, p.nama_program 
                       FROM donasi_online d 
                       JOIN program_donasi p ON d.program_id = p.id 
                       WHERE d.no_donasi = ?");
$stmt->execute([$no]);
$donasi = $stmt->fetch();

if (!$donasi) {
    header('Location: ../index.php');
    exit;
}

$kwUrl = base_url() . '/donatur/kwitansi.php?no=' . $donasi['no_donasi'];
$pageTitle = 'Tanda Terima Donasi #' . $donasi['no_donasi'] . ' · ' . $profil['nama_masjid'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/classic-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-shadow-none { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body class="pattern-arabesque-light min-h-screen py-10 px-4 flex flex-col items-center justify-center text-warm-900">

    <!-- Top Action Bar (No Print) -->
    <div class="no-print max-w-2xl w-full mb-6 flex items-center justify-between">
        <a href="../index.php" class="text-xs font-semibold text-cypress-700 hover:text-cypress-900 flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Beranda</span>
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Cetak / Simpan PDF</span>
            </button>
            <a href="https://api.whatsapp.com/send?text=<?= urlencode('Alhamdulillah, telah disalurkan infaq/donasi untuk ' . $donasi['nama_program'] . ' melalui ' . $profil['nama_masjid'] . ' dengan No. Bukti: ' . $donasi['no_donasi'] . '. Cek kwitansi digital: ' . $kwUrl) ?>" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <i class="fa-brands fa-whatsapp"></i>
                <span>Bagikan</span>
            </a>
        </div>
    </div>

    <!-- Kwitansi Card Formal -->
    <div class="max-w-2xl w-full bg-white rounded-3xl p-8 sm:p-12 border-2 border-antique-500/40 shadow-2xl relative overflow-hidden print-shadow-none">
        
        <!-- Watermark -->
        <div class="absolute right-6 top-1/3 text-antique-500/5 pointer-events-none text-9xl">
            <i class="fa-solid fa-mosque"></i>
        </div>

        <!-- Kop Surat Resmi -->
        <div class="border-b-2 border-cypress-900/20 pb-6 mb-6 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-14 h-14 rounded-2xl bg-cypress-900 border border-antique-500/50 flex items-center justify-center text-antique-400 shrink-0">
                    <i class="fa-solid fa-mosque text-2xl"></i>
                </div>
                <div>
                    <span class="font-classic text-lg font-bold tracking-wider block text-cypress-900 leading-tight">
                        <?= strtoupper(e($profil['nama_masjid'])) ?>
                    </span>
                    <span class="text-xs text-antique-700 uppercase tracking-wider block font-semibold">
                        DEWAN KEMAKMURAN MASJID (DKM)
                    </span>
                    <span class="text-[11px] text-warm-800/60 block mt-0.5"><?= e($profil['alamat']) ?>, <?= e($profil['kota']) ?></span>
                </div>
            </div>

            <div class="text-right shrink-0">
                <span class="text-[10px] uppercase font-bold text-warm-800/60 block">Nomor Tanda Terima</span>
                <span class="font-mono text-sm sm:text-base font-bold text-cypress-800 tracking-wider block">
                    <?= e($donasi['no_donasi']) ?>
                </span>
                <?php if ($donasi['status'] === 'diverifikasi'): ?>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <i class="fa-solid fa-check mr-1"></i> Terverifikasi Sah
                    </span>
                <?php else: ?>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        <i class="fa-regular fa-clock mr-1"></i> Menunggu Verifikasi
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mb-6">
            <h2 class="font-classic text-xl font-bold uppercase tracking-widest text-warm-900">
                BUKTI TANDA TERIMA INFAQ / DONASI
            </h2>
            <p class="text-xs text-warm-800/60 font-arabic text-sm mt-1">
                جَزَاكُمُ اللهُ خَيْرًا كَثِيْرًا وَبَارَكَ اللهُ فِيْ أَمْوَالِكُمْ
            </p>
        </div>

        <!-- Tabel Rincian Donasi -->
        <div class="bg-warm-50 rounded-2xl p-6 border border-antique-200 space-y-3.5 text-xs sm:text-sm">
            <div class="flex items-center justify-between border-b border-antique-200/70 pb-2">
                <span class="text-warm-800/70">Telah Terima Dari:</span>
                <strong class="text-warm-900"><?= $donasi['is_anonim'] ? 'Hamba Allah (Anonim)' : e($donasi['nama_donatur']) ?></strong>
            </div>

            <div class="flex items-center justify-between border-b border-antique-200/70 pb-2">
                <span class="text-warm-800/70">Tanggal Infaq:</span>
                <strong class="text-warm-900"><?= tanggal_indo($donasi['tanggal_donasi'], true) ?></strong>
            </div>

            <div class="flex items-center justify-between border-b border-antique-200/70 pb-2">
                <span class="text-warm-800/70">Alokasi Program:</span>
                <strong class="text-warm-900 text-right"><?= e($donasi['nama_program']) ?></strong>
            </div>

            <div class="flex items-center justify-between border-b border-antique-200/70 pb-2">
                <span class="text-warm-800/70">Metode Pembayaran:</span>
                <strong class="text-warm-900 uppercase"><?= e($donasi['metode_pembayaran']) ?> (<?= e($donasi['bank_tujuan'] ?: 'Digital') ?>)</strong>
            </div>

            <div class="flex items-center justify-between pt-2">
                <span class="text-warm-800 font-bold">Jumlah Nominal Infaq:</span>
                <span class="text-xl sm:text-2xl font-bold text-cypress-800 font-classic">
                    <?= format_rupiah($donasi['nominal']) ?>
                </span>
            </div>
        </div>

        <!-- Doa Donatur (Jika ada) -->
        <?php if (!empty($donasi['doa_donatur'])): ?>
            <div class="mt-4 p-4 rounded-xl bg-antique-50/60 border border-antique-200 text-xs">
                <span class="font-bold text-antique-900 block mb-1">Doa / Hajat Donatur:</span>
                <p class="italic text-warm-800">"<?= e($donasi['doa_donatur']) ?>"</p>
            </div>
        <?php endif; ?>

        <!-- QR Verification & Tanda Tangan DKM -->
        <div class="mt-8 pt-6 border-t border-antique-200 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($kwUrl) ?>" alt="QR Validasi" class="w-16 h-16 rounded-lg border border-stone-300 p-1">
                <div class="text-[10px] text-warm-800/60 space-y-0.5">
                    <span class="font-bold block text-warm-900">Validasi Sistem Digital</span>
                    <span>Scan untuk memastikan</span>
                    <span>keaslian tanda terima ini.</span>
                </div>
            </div>

            <div class="text-center sm:text-right space-y-1 text-xs">
                <span class="text-warm-800/60 block"><?= e($profil['kota']) ?>, <?= tanggal_indo(date('Y-m-d')) ?></span>
                <span class="font-semibold block">Pengurus &amp; Bendahara DKM</span>
                <div class="h-12 flex items-center justify-center sm:justify-end text-antique-600">
                    <i class="fa-solid fa-stamp text-2xl rotate-12 opacity-80"></i>
                </div>
                <strong class="block text-warm-900">H. Muhammad Ridwan, SE</strong>
                <span class="text-[10px] text-warm-800/60 block">Bendahara Umum</span>
            </div>
        </div>

    </div>

</body>
</html>
