<?php
// kontak.php - Informasi Kontak Resmi & Formulir Pesan Jamaah (Scope 7)
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pageTitle = 'Kontak & Sekretariat DKM · ' . $profil['nama_masjid'];
$activeNav = 'kontak';

$pesanAlert = '';
$tipeAlert = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = trim($_POST['nama'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $no_hp  = trim($_POST['no_hp'] ?? '');
    $subjek = trim($_POST['subjek'] ?? '');
    $pesan  = trim($_POST['pesan'] ?? '');

    if (empty($nama) || empty($email) || empty($subjek) || empty($pesan)) {
        $pesanAlert = 'Mohon lengkapi formulir pesan Anda.';
        $tipeAlert = 'error';
    } else {
        $stmt = $pdo->prepare("INSERT INTO pesan_kontak (nama, email, no_hp, subjek, pesan) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $email, $no_hp, $subjek, $pesan]);
        $pesanAlert = 'Alhamdulillah, pesan Anda telah terkirim kepada sekretariat pengurus DKM.';
        $tipeAlert = 'success';
    }
}

$rekeningList = $pdo->query("SELECT * FROM rekening_donasi WHERE is_active = 1 ORDER BY urutan ASC")->fetchAll();

require_once __DIR__ . '/../layouts/public_header.php';
?>

<!-- Header Banner -->
<div class="bg-cypress-950 text-white py-14 relative pattern-arabesque-dark border-b border-antique-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3 relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cypress-900 border border-antique-500/40 text-antique-300 text-xs font-semibold uppercase tracking-wider">
            <i class="fa-solid fa-headset text-emerald-400"></i>
            <span>Layanan Jamaah &amp; Donatur</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight font-classic text-white">
            Hubungi Pengurus &amp; Sekretariat
        </h1>
        <p class="text-stone-300 text-xs sm:text-sm max-w-xl mx-auto">
            Sampaikan pertanyaan, saran kemakmuran, konfirmasi donasi, atau informasi konsultasi keagamaan.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        <!-- Kolom Kiri: Informasi Kontak & Rekening -->
        <div class="lg:col-span-5 space-y-6">
            
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-sm space-y-5">
                <h3 class="font-classic text-lg font-bold text-warm-900 border-b border-antique-200 pb-3">
                    Sekretariat DKM
                </h3>

                <div class="space-y-4 text-xs">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-cypress-50 border border-cypress-100 flex items-center justify-center text-cypress-700 shrink-0">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <span class="font-bold text-warm-900 block">Alamat Masjid:</span>
                            <span class="text-warm-800/70 leading-relaxed block mt-0.5"><?= e($profil['alamat']) ?>, <?= e($profil['kota']) ?></span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                        </div>
                        <div>
                            <span class="font-bold text-warm-900 block">WhatsApp Resmi:</span>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $profil['whatsapp']) ?>" target="_blank" class="text-emerald-700 font-semibold hover:underline block mt-0.5">
                                <?= e($profil['whatsapp']) ?> (Layanan Cepat DKM)
                            </a>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <span class="font-bold text-warm-900 block">Email Resmi:</span>
                            <span class="text-warm-800/70 block mt-0.5"><?= e($profil['email']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-antique-200">
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $profil['whatsapp']) ?>?text=Assalamu'alaikum%20Pengurus%20Masjid..." target="_blank" class="w-full text-center py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                        <i class="fa-brands fa-whatsapp text-base"></i>
                        <span>Chat WhatsApp Langsung</span>
                    </a>
                </div>
            </div>

            <!-- Rekening Resmi Singkat -->
            <div class="bg-cypress-900 text-white rounded-3xl p-6 border border-antique-500/40 pattern-arabesque-dark space-y-4">
                <h4 class="font-classic text-sm font-bold text-antique-300 uppercase tracking-wider">
                    Rekening Infaq Resmi
                </h4>
                <div class="space-y-2.5">
                    <?php foreach ($rekeningList as $r): ?>
                        <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-xs flex items-center justify-between">
                            <div>
                                <span class="font-bold block"><?= e($r['nama_bank']) ?></span>
                                <span class="font-mono text-antique-200 tracking-wider font-semibold"><?= e($r['nomor_rekening']) ?></span>
                            </div>
                            <button type="button" id="copyBtnKontak-<?= htmlspecialchars(md5($r['id'])) ?>" onclick="copyToClipboardBtn('<?= e($r['nomor_rekening']) ?>', '<?= htmlspecialchars(md5($r['id'])) ?>', 'kontak');" class="text-[10px] text-antique-300 hover:text-antique-200 px-2 py-1 rounded bg-cypress-950 border border-antique-500/40 transition-luxury">
                                Salin
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Formulir Kirim Pesan -->
        <div class="lg:col-span-7">
            <div class="bg-white rounded-3xl p-6 sm:p-10 border border-antique-300/50 shadow-sm space-y-6">
                <div>
                    <h3 class="font-classic text-xl font-bold text-warm-900">
                        Kirim Pesan / Pertanyaan
                    </h3>
                    <p class="text-xs text-warm-800/70 mt-1">
                        Sampaikan masukan, kritik membangun, atau permohonan konsultasi kepada dewan pengurus.
                    </p>
                </div>

                <?php if ($pesanAlert): ?>
                    <div class="p-4 rounded-2xl <?= $tipeAlert === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?> text-xs font-semibold flex items-center gap-2.5">
                        <i class="fa-solid <?= $tipeAlert === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
                        <span><?= e($pesanAlert) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="kontak.php" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-warm-800 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" required placeholder="Nama Anda..." class="w-full px-4 py-2.5 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:ring-2 focus:ring-antique-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-warm-800 mb-1">Alamat Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required placeholder="email@anda.com" class="w-full px-4 py-2.5 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:ring-2 focus:ring-antique-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-warm-800 mb-1">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="no_hp" placeholder="Contoh: 081234567890" class="w-full px-4 py-2.5 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:ring-2 focus:ring-antique-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-warm-800 mb-1">Subjek Pesan <span class="text-red-500">*</span></label>
                            <input type="text" name="subjek" required placeholder="Contoh: Pertanyaan Infaq / Jadwal Taklim" class="w-full px-4 py-2.5 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:ring-2 focus:ring-antique-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-warm-800 mb-1">Isi Pesan / Saran <span class="text-red-500">*</span></label>
                        <textarea name="pesan" rows="5" required placeholder="Tuliskan pesan Anda secara lengkap..." class="w-full px-4 py-2.5 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:ring-2 focus:ring-antique-500 focus:outline-none"></textarea>
                    </div>

                    <button type="submit" class="w-full py-3.5 px-6 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim Pesan ke Pengurus</span>
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
