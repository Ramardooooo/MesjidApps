<?php
// kwitansi.php - e-Kwitansi Tanda Terima Donasi Resmi Digital & Security Lock
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
    header('Location: ' . base_url() . '/');
    exit;
}

if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'donatur') {
    if ($donasi['user_id'] && $donasi['user_id'] !== $_SESSION['user']['id']) {
        header('Location: ' . base_url() . '/donatur/portal-donatur?error=unauthorized');
        exit;
    }
}

$kwUrl = base_url() . '/donatur/kwitansi?no=' . $donasi['no_donasi'];
$pageTitle = 'Tanda Terima Donasi #' . $donasi['no_donasi'] . ' · ' . $profil['nama_masjid'];
$isVerified = ($donasi['status'] === 'diverifikasi');
$isRejected = ($donasi['status'] === 'ditolak');
$isPending  = ($donasi['status'] === 'pending');
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
            <?php if (!$isVerified): ?>
            body { display: none !important; }
            <?php endif; ?>
        }
    </style>
</head>
<body class="pattern-arabesque-light min-h-screen py-10 px-4 flex flex-col items-center justify-center text-warm-900">

    <!-- Top Action Bar (No Print) -->
    <div class="no-print max-w-2xl w-full mb-6 flex items-center justify-between">
        <a href="../" class="text-xs font-semibold text-cypress-700 hover:text-cypress-900 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Beranda</span>
        </a>

        <div class="flex items-center gap-2">
            <?php if ($isVerified): ?>
                <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak / Simpan PDF</span>
                </button>
                <a href="https://api.whatsapp.com/send?text=<?= urlencode('Alhamdulillah, telah terverifikasi donasi resmi untuk ' . $donasi['nama_program'] . ' melalui ' . $profil['nama_masjid'] . ' dengan No. Bukti Sah: ' . $donasi['no_donasi'] . '. Cek e-Kwitansi: ' . $kwUrl) ?>" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>Bagikan</span>
                </a>
            <?php else: ?>
                <button type="button" onclick="alertKwitansiPending()" class="px-4 py-2 rounded-xl bg-stone-200 hover:bg-stone-300 text-stone-700 text-xs font-semibold shadow-xs transition flex items-center gap-1.5" title="e-Kwitansi resmi hanya dapat dicetak setelah diverifikasi bendahara">
                    <i class="fa-solid fa-lock text-stone-500"></i>
                    <span>Cetak PDF (Terkunci)</span>
                </button>
                <a href="https://api.whatsapp.com/send?text=<?= urlencode('Pengajuan donasi untuk ' . $donasi['nama_program'] . ' di ' . $profil['nama_masjid'] . ' dengan No. Pengajuan: ' . $donasi['no_donasi'] . ' sedang menunggu verifikasi dana.') ?>" target="_blank" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-semibold transition flex items-center gap-1.5">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>Bagikan Status</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Banner Notifikasi Status Baru -->
    <?php if (isset($_GET['baru'])): ?>
        <div class="no-print max-w-2xl w-full mb-6 p-4 rounded-2xl bg-amber-50 text-amber-950 border border-amber-200 text-xs font-semibold flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-clock-rotate-left text-amber-600 text-lg shrink-0"></i>
                <div>
                    <strong class="block text-amber-950 font-bold">Pengajuan Donasi Berhasil Dikirim!</strong>
                    <span class="text-amber-800 text-[11px] font-normal">Status saat ini adalah <strong>Menunggu Verifikasi</strong>. e-Kwitansi resmi dengan stempel DKM akan aktif setelah dana diverifikasi oleh Bendahara.</span>
                </div>
            </div>
            <?php if (!empty($profil['whatsapp'])): 
                $waAdmin = preg_replace('/\D/', '', $profil['whatsapp']);
                if (str_starts_with($waAdmin, '0')) $waAdmin = '62' . substr($waAdmin, 1);
                $msgKonfirm = "Assalamu’alaikum Pengurus " . $profil['nama_masjid'] . ", saya telah mengajukan donasi untuk *" . $donasi['nama_program'] . "* sebesar *" . format_rupiah($donasi['nominal']) . "* dengan No. Donasi *" . $donasi['no_donasi'] . "*. Mohon bantuannya untuk diverifikasi. Terima kasih.";
            ?>
                <a href="https://wa.me/<?= $waAdmin ?>?text=<?= urlencode($msgKonfirm) ?>" target="_blank" class="px-3.5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs transition shrink-0 flex items-center justify-center gap-1.5 shadow-xs">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>Konfirmasi ke Admin WA</span>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Banner Peringatan Ditolak (Jika status ditolak) -->
    <?php if ($isRejected): ?>
        <div class="no-print max-w-2xl w-full mb-6 p-4 rounded-2xl bg-red-50 text-red-950 border border-red-200 text-xs flex items-center gap-3 shadow-xs">
            <i class="fa-solid fa-circle-xmark text-red-600 text-xl shrink-0"></i>
            <div>
                <strong class="block text-red-950 font-bold">Donasi Ini Tidak Dapat Diverifikasi</strong>
                <span class="text-red-800 text-[11px]">Alasan: <?= e($donasi['catatan_admin'] ?: 'Bukti transfer tidak valid atau dana belum masuk rekening resmi.') ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Kwitansi Card Formal -->
    <div class="max-w-2xl w-full bg-white rounded-3xl p-8 sm:p-12 border-2 <?= $isVerified ? 'border-antique-500/40' : ($isRejected ? 'border-red-300' : 'border-amber-300') ?> shadow-2xl relative overflow-hidden print-shadow-none">
        
        <!-- Watermark Masjid Halus -->
        <div class="absolute right-6 top-1/3 text-antique-500/5 pointer-events-none text-9xl">
            <i class="fa-solid fa-mosque"></i>
        </div>

        <!-- WATERMARK PENGAMAN DIAGONAL (Jika Belum Terverifikasi / Ditolak) -->
        <?php if ($isPending): ?>
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-10 select-none overflow-hidden">
                <div class="transform -rotate-25 border-4 border-dashed border-amber-600/25 text-amber-600/30 font-black text-xl sm:text-3xl px-8 py-5 rounded-3xl tracking-widest text-center uppercase shadow-inner">
                    DRAFT / BELUM SAH<br>
                    <span class="text-xs sm:text-sm font-bold tracking-normal block mt-1">MENUNGGU VERIFIKASI BENDAHARA</span>
                </div>
            </div>
        <?php elseif ($isRejected): ?>
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-10 select-none overflow-hidden">
                <div class="transform -rotate-25 border-4 border-dashed border-red-600/30 text-red-600/35 font-black text-xl sm:text-3xl px-8 py-5 rounded-3xl tracking-widest text-center uppercase shadow-inner">
                    DONASI DITOLAK<br>
                    <span class="text-xs sm:text-sm font-bold tracking-normal block mt-1">TIDAK SAH / TIDAK MASUK KAS</span>
                </div>
            </div>
        <?php endif; ?>

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
                <span class="text-[10px] uppercase font-bold text-warm-800/60 block">Nomor Registrasi</span>
                <span class="font-mono text-sm sm:text-base font-bold text-cypress-800 tracking-wider block">
                    <?= e($donasi['no_donasi']) ?>
                </span>
                <?php if ($isVerified): ?>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <i class="fa-solid fa-check mr-1"></i> Terverifikasi Sah
                    </span>
                <?php elseif ($isRejected): ?>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-300">
                        <i class="fa-solid fa-xmark mr-1"></i> Ditolak
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
                <?= $isVerified ? 'BUKTI TANDA TERIMA INFAQ / DONASI RESMI' : 'TANDA TERIMA PENGAJUAN DONASI' ?>
            </h2>
            <?php if ($isVerified): ?>
                <p class="text-xs text-warm-800/60 font-arabic text-sm mt-1">
                    جَزَاكُمُ اللهُ خَيْرًا كَثِيْرًا وَبَارَكَ اللهُ فِيْ أَمْوَالِكُمْ
                </p>
            <?php else: ?>
                <p class="text-xs text-amber-800 font-medium mt-1">
                    * Dokumen sementara ini belum disahkan oleh Bendahara DKM
                </p>
            <?php endif; ?>
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
                <span class="text-xl sm:text-2xl font-bold <?= $isVerified ? 'text-cypress-800' : 'text-amber-800' ?> font-classic">
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
            
            <!-- QR Code Status -->
            <div class="flex items-center gap-3">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($kwUrl) ?>" alt="QR Validasi" class="w-16 h-16 rounded-lg border border-stone-300 p-1 <?= !$isVerified ? 'opacity-60 grayscale' : '' ?>">
                <div class="text-[10px] text-warm-800/60 space-y-0.5">
                    <span class="font-bold block text-warm-900">Validasi Sistem Digital</span>
                    <span>Status: <strong class="<?= $isVerified ? 'text-emerald-700' : ($isRejected ? 'text-red-600' : 'text-amber-700') ?>"><?= $isVerified ? 'Sah & Terverifikasi' : ($isRejected ? 'Ditolak' : 'Menunggu Approval') ?></strong></span>
                    <span class="block">Scan untuk cek keaslian.</span>
                </div>
            </div>

            <!-- Tanda Tangan & Stempel Resmi DKM -->
            <div class="text-center sm:text-right space-y-1 text-xs">
                <span class="text-warm-800/60 block"><?= e($profil['kota']) ?>, <?= tanggal_indo(date('Y-m-d')) ?></span>
                <span class="font-semibold block">Pengurus &amp; Bendahara DKM</span>

                <?php if ($isVerified): ?>
                    <!-- STEMPEL & TANDA TANGAN AKTIF KARENA SUDAH SAH -->
                    <div class="h-12 flex items-center justify-center sm:justify-end text-antique-600">
                        <i class="fa-solid fa-stamp text-2xl rotate-12 opacity-80 text-emerald-700"></i>
                    </div>
                    <strong class="block text-warm-900">H. Muhammad Ridwan, SE</strong>
                    <span class="text-[10px] text-warm-800/60 block">Bendahara Umum</span>
                <?php else: ?>
                    <!-- PENGESAHAN DIKUNCI KARENA BELUM DIVERIFIKASI / DITOLAK -->
                    <div class="h-12 flex items-center justify-center sm:justify-end">
                        <span class="px-3 py-1 rounded-xl bg-stone-100 border border-stone-300 text-[10px] font-bold text-stone-600 flex items-center gap-1">
                            <i class="fa-solid fa-lock text-stone-400"></i>
                            <span>Pengesahan Dikunci</span>
                        </span>
                    </div>
                    <strong class="block text-stone-400 italic font-medium">(Menunggu Pengesahan Bendahara)</strong>
                    <span class="text-[10px] text-stone-400 block">Belum Terbit Resmi</span>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script>
    function alertKwitansiPending() {
        alert("PEMBERITAHUAN KEAMANAN:\n\ne-Kwitansi resmi berstempel DKM hanya dapat dicetak atau disimpan setelah dana donasi diverifikasi oleh Bendahara Masjid.\n\nSilakan cek kembali beberapa saat lagi setelah pengurus mengonfirmasi mutasi kas.");
    }
    </script>

</body>
</html>
