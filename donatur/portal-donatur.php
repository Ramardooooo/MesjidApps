<?php
// portal-donatur.php - Dashboard Mandiri Donatur (Scope 12 & 14)
require_once __DIR__ . '/../config/database.php';
cek_login();

$user = $_SESSION['user'];
if ($user['role'] !== 'donatur') {
    header('Location: ../admin/dashboard.php');
    exit;
}

$profil = get_profil_masjid();
$pageTitle = 'Portal Donatur · ' . $profil['nama_masjid'];

$pesan = '';
$tipe = '';

// Update Profil Donatur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'update_profil') {
    $nama   = trim($_POST['nama_lengkap'] ?? '');
    $no_hp  = trim($_POST['no_hp'] ?? '');
    $pass   = $_POST['password_baru'] ?? '';

    if (empty($nama)) {
        $pesan = 'Nama lengkap tidak boleh kosong.';
        $tipe = 'error';
    } else {
        if (!empty($pass)) {
            if (strlen($pass) < 6) {
                $pesan = 'Kata sandi baru minimal 6 karakter.';
                $tipe = 'error';
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, no_hp = ?, password = ? WHERE id = ?");
                $stmt->execute([$nama, $no_hp, $hash, $user['id']]);
                $pesan = 'Profil dan kata sandi Anda berhasil diperbarui.';
                $tipe = 'success';
            }
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, no_hp = ? WHERE id = ?");
            $stmt->execute([$nama, $no_hp, $user['id']]);
            $pesan = 'Profil Anda berhasil diperbarui.';
            $tipe = 'success';
        }
        $_SESSION['user']['nama'] = $nama;
        $_SESSION['user']['no_hp'] = $no_hp;
    }
}

// Tandai notifikasi terbaca
if (isset($_GET['baca_notif'])) {
    $pdo->prepare("UPDATE notifikasi SET is_read = 1 WHERE user_id = ?")->execute([$user['id']]);
    header('Location: portal-donatur.php');
    exit;
}

// Ambil Riwayat Donasi Donatur Ini
$stmtDonasi = $pdo->prepare("SELECT d.*, p.nama_program 
                             FROM donasi_online d 
                             JOIN program_donasi p ON d.program_id = p.id 
                             WHERE d.user_id = ? OR d.email = ? 
                             ORDER BY d.tanggal_donasi DESC, d.id DESC");
$stmtDonasi->execute([$user['id'], $user['email'] ?? '']);
$riwayatDonasi = $stmtDonasi->fetchAll();

// Hitung Statistik Donasi Donatur
$totalDonasiSaya = 0;
$totalDonasiVerif = 0;
$jumlahTransaksi = count($riwayatDonasi);
foreach ($riwayatDonasi as $r) {
    if ($r['status'] === 'diverifikasi') {
        $totalDonasiVerif += (float)$r['nominal'];
    }
    $totalDonasiSaya += (float)$r['nominal'];
}

// Ambil Notifikasi Donatur (Scope 14)
$stmtNotif = $pdo->prepare("SELECT * FROM notifikasi WHERE user_id = ? ORDER BY id DESC LIMIT 5");
$stmtNotif->execute([$user['id']]);
$daftarNotifikasi = $stmtNotif->fetchAll();
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
</head>
<body class="pattern-arabesque-light min-h-screen text-warm-900 antialiased flex flex-col justify-between">

    <!-- Topbar Donatur -->
    <header class="bg-cypress-900 text-white border-b border-antique-500/30 px-4 sm:px-8 py-3.5 flex items-center justify-between sticky top-0 z-30 shadow-md">
        <div class="flex items-center gap-3">
            <a href="../index.php" class="w-10 h-10 rounded-xl bg-cypress-950 border border-antique-500/40 flex items-center justify-center text-antique-300">
                <i class="fa-solid fa-mosque text-lg"></i>
            </a>
            <div>
                <span class="font-classic text-sm font-bold block leading-tight"><?= strtoupper(e($profil['nama_masjid'])) ?></span>
                <span class="text-[10px] text-antique-300 uppercase tracking-widest block font-medium">Portal Sahabat Donatur</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="../index.php" class="text-xs text-stone-300 hover:text-white hidden sm:inline-block">
                Website Utama
            </a>
            <span class="text-white/20 hidden sm:inline-block">|</span>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-cypress-800 border border-antique-500/50 flex items-center justify-center text-antique-300 font-bold text-xs">
                    <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                </div>
                <span class="text-xs font-bold text-white hidden md:inline-block"><?= e($user['nama']) ?></span>
            </div>
            <a href="../auth/logout.php" class="px-3 py-1.5 rounded-lg bg-red-950/60 hover:bg-red-900/80 border border-red-500/30 text-red-200 text-xs font-medium transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                <span class="hidden sm:inline">Keluar</span>
            </a>
        </div>
    </header>

    <!-- Konten Dashboard Donatur -->
    <main class="max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 flex-1">
        
        <?php if ($pesan): ?>
            <div class="p-4 rounded-2xl <?= $tipe === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?> text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid <?= $tipe === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <span><?= e($pesan) ?></span>
            </div>
        <?php endif; ?>

        <!-- Sambutan & Kartu Ringkasan -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-antique-50 border border-antique-300/50 text-antique-700 text-xs font-semibold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-hands-praying text-cypress-700"></i>
                    <span>Jazakumullahu Khairan</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-warm-900 tracking-tight font-classic">
                    Ahlan wa Sahlan, <span class="text-cypress-700"><?= e($user['nama']) ?></span>
                </h1>
                <p class="text-xs sm:text-sm text-warm-800/70 mt-1 max-w-xl">
                    Terima kasih atas keikutsertaan Anda dalam memakmurkan rumah Allah dan meringankan beban sesama melalui infaq dan sedekah.
                </p>
            </div>

            <a href="../home/donasi-online.php" class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-antique-500 to-antique-600 hover:brightness-105 text-cypress-950 font-bold text-xs tracking-wide shadow-lg shadow-antique-500/20 transition shrink-0 flex items-center gap-2">
                <i class="fa-solid fa-hand-holding-heart text-base"></i>
                <span>Infaq Donasi Baru</span>
            </a>
        </div>

        <!-- 3 Kartu Metrik Donatur -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-1">
                <span class="text-[11px] uppercase font-bold text-warm-800/60 tracking-wider block">Total Infaq Terverifikasi</span>
                <h3 class="text-2xl font-bold text-cypress-700 font-classic">
                    <?= format_rupiah($totalDonasiVerif) ?>
                </h3>
                <span class="text-[11px] text-emerald-600 font-medium block">Tercatat sah di pembukuan masjid</span>
            </div>

            <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-1">
                <span class="text-[11px] uppercase font-bold text-warm-800/60 tracking-wider block">Frekuensi Donasi</span>
                <h3 class="text-2xl font-bold text-warm-900 font-classic">
                    <?= $jumlahTransaksi ?> Kali Berinfaq
                </h3>
                <span class="text-[11px] text-warm-800/60 block">Infaq via QRIS &amp; Transfer</span>
            </div>

            <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-1">
                <span class="text-[11px] uppercase font-bold text-warm-800/60 tracking-wider block">Status Keanggotaan</span>
                <h3 class="text-2xl font-bold text-antique-700 font-classic">
                    Donatur Aktif
                </h3>
                <span class="text-[11px] text-warm-800/60 block"><?= e($user['email'] ?? 'Akun Terverifikasi') ?></span>
            </div>
        </div>

        <!-- Grid 2 Kolom: Riwayat Donasi & Pengaturan Profil -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Kolom Kiri: Riwayat Donasi & Unduh Kwitansi -->
            <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-antique-200 pb-4">
                    <h3 class="font-classic text-lg font-bold text-warm-900">
                        Riwayat Infaq &amp; Tanda Terima
                    </h3>
                    <span class="text-xs text-warm-800/60"><?= count($riwayatDonasi) ?> Donasi</span>
                </div>

                <?php if (empty($riwayatDonasi)): ?>
                    <div class="text-center py-10 space-y-3">
                        <i class="fa-solid fa-receipt text-4xl text-stone-300"></i>
                        <p class="text-xs text-warm-800/60">Anda belum memiliki catatan donasi online.</p>
                        <a href="../home/donasi-online.php" class="inline-block px-4 py-2 rounded-xl bg-cypress-700 text-white text-xs font-semibold">Salurkan Infaq Sekarang</a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-antique-200 text-warm-800/70 uppercase text-[10px] font-bold">
                                    <th class="pb-3 pr-3">No. Donasi / Tgl</th>
                                    <th class="pb-3 pr-3">Program &amp; Metode</th>
                                    <th class="pb-3 pr-3">Nominal</th>
                                    <th class="pb-3 pr-3 text-center">Status</th>
                                    <th class="pb-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-antique-100">
                                <?php foreach ($riwayatDonasi as $r): ?>
                                    <tr class="hover:bg-warm-50/60 transition">
                                        <td class="py-3.5 pr-3 align-top">
                                            <span class="font-mono font-bold text-cypress-800 block"><?= e($r['no_donasi']) ?></span>
                                            <span class="text-[11px] text-warm-800/50 block"><?= tanggal_indo($r['tanggal_donasi']) ?></span>
                                        </td>
                                        <td class="py-3.5 pr-3 align-top">
                                            <span class="font-bold text-warm-900 block"><?= e($r['nama_program']) ?></span>
                                            <span class="text-[10px] uppercase font-semibold text-warm-800/60 block mt-0.5">
                                                <?= e($r['metode_pembayaran']) ?> (<?= e($r['bank_tujuan'] ?: 'Digital') ?>)
                                            </span>
                                        </td>
                                        <td class="py-3.5 pr-3 align-top whitespace-nowrap">
                                            <span class="font-bold font-classic text-sm text-cypress-700 block">
                                                <?= format_rupiah($r['nominal']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 pr-3 align-top text-center">
                                            <?php if ($r['status'] === 'diverifikasi'): ?>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                    Diterima
                                                </span>
                                            <?php elseif ($r['status'] === 'ditolak'): ?>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-300">
                                                    Ditolak
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                    Menunggu
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 align-top text-right whitespace-nowrap">
                                            <a href="kwitansi.php?no=<?= e($r['no_donasi']) ?>" target="_blank" class="px-3 py-1.5 rounded-lg bg-cypress-800 hover:bg-cypress-900 text-white text-[11px] font-semibold transition inline-flex items-center gap-1 shadow-xs">
                                                <i class="fa-solid fa-receipt text-[10px]"></i>
                                                <span>e-Kwitansi</span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Kolom Kanan: Notifikasi & Ubah Profil -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Notifikasi Pengguna (Scope 14) -->
                <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-antique-200 pb-2.5">
                        <h4 class="font-classic text-sm font-bold text-warm-900 flex items-center gap-2">
                            <i class="fa-regular fa-bell text-antique-600"></i>
                            <span>Notifikasi In-App</span>
                        </h4>
                        <a href="portal-donatur.php?baca_notif=1" class="text-[10px] text-warm-800/60 hover:text-cypress-700">Tandai Baca</a>
                    </div>

                    <div class="space-y-3">
                        <?php if (empty($daftarNotifikasi)): ?>
                            <p class="text-xs text-warm-800/60 italic text-center py-2">Belum ada notifikasi baru.</p>
                        <?php else: ?>
                            <?php foreach ($daftarNotifikasi as $n): ?>
                                <div class="p-3 rounded-xl <?= $n['is_read'] ? 'bg-warm-50 border border-antique-200/60' : 'bg-antique-50 border border-antique-300' ?> text-xs space-y-1">
                                    <div class="flex items-center justify-between">
                                        <strong class="text-warm-900 font-semibold"><?= e($n['judul']) ?></strong>
                                        <span class="text-[10px] text-warm-800/50"><?= date('d/m', strtotime($n['created_at'])) ?></span>
                                    </div>
                                    <p class="text-warm-800/80 text-[11px] leading-relaxed"><?= e($n['pesan']) ?></p>
                                    <?php if (!empty($n['link'])): ?>
                                        <a href="<?= e($n['link']) ?>" class="text-[10px] font-bold text-cypress-700 hover:underline block pt-1">
                                            Lihat Kwitansi &rarr;
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Formulir Ubah Profil -->
                <div class="bg-white rounded-3xl p-6 border border-antique-300/50 shadow-sm space-y-4">
                    <h4 class="font-classic text-sm font-bold text-warm-900 border-b border-antique-200 pb-2.5">
                        Pengaturan Profil Saya
                    </h4>

                    <form method="POST" action="portal-donatur.php" class="space-y-3">
                        <input type="hidden" name="aksi" value="update_profil">

                        <div>
                            <label class="block text-[11px] font-bold text-warm-800 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?= e($user['nama']) ?>" required class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:ring-2 focus:ring-antique-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-warm-800 mb-1">Nomor WhatsApp</label>
                            <input type="text" name="no_hp" value="<?= e($user['no_hp'] ?? '') ?>" placeholder="08..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:ring-2 focus:ring-antique-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-warm-800 mb-1">Ganti Kata Sandi (Kosongkan jika tidak diubah)</label>
                            <input type="password" name="password_baru" placeholder="Kata sandi baru..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:ring-2 focus:ring-antique-500 focus:outline-none">
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs shadow-xs transition">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </main>

    <footer class="bg-white border-t border-antique-200 py-4 text-center text-xs text-stone-400">
        &copy; <?= date('Y') ?> <?= e($profil['nama_masjid']) ?> · Portal Donatur Mandiri
    </footer>

</body>
</html>
