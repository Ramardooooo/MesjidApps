<?php
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pageTitle = 'Kebijakan Privasi · ' . $profil['nama_masjid'];
$activeNav = 'privacy';

require_once __DIR__ . '/../layouts/public_header.php';
?>

<style>
.policy-card {
    border: 1px solid var(--color-border-subtle);
    background: var(--color-bg-surface);
    transition: all 0.3s ease;
}
.policy-card:hover {
    border-color: var(--color-accent-gold);
    box-shadow: 0 10px 25px -5px rgba(197, 160, 89, 0.1);
}
</style>

<section class="relative bg-cypress-950 text-white overflow-hidden pattern-arabesque-dark border-b border-antique-500/30">
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-antique-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 right-10 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 relative z-10 text-center">
        <h1 class="text-3xl sm:text-5xl font-bold mb-4 font-classic tracking-wide">Kebijakan Privasi</h1>
        <p class="text-stone-300 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
            Komitmen <strong class="text-antique-300"><?= e($profil['nama_masjid']) ?></strong> dalam menjaga kerahasiaan dan keamanan informasi pribadi donatur serta jamaah.
        </p>
    </div>
</section>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
    
    <!-- Meta Update Banner -->
    <div class="bg-gradient-to-r from-antique-50 to-warm-50 rounded-2xl border border-antique-200 p-6 shadow-soft flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-antique-500/10 border border-antique-500/30 flex items-center justify-center text-antique-600 shrink-0">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="text-xs text-warm-600 font-semibold uppercase tracking-wider">Status Dokumen</p>
                <p class="text-sm font-bold text-warm-900">Efektif per Oktober 2026</p>
            </div>
        </div>
        <div class="text-xs text-warm-600 bg-white px-3 py-1.5 rounded-lg border border-warm-200">
            <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> Terenkripsi &amp; Aman
        </div>
    </div>

    <!-- Section 1 -->
    <div class="policy-card bg-white rounded-2xl border border-warm-200 p-8 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-cypress-900 text-antique-400 flex items-center justify-center font-bold text-sm shrink-0">1</div>
            <h2 class="text-xl font-bold text-warm-900">Informasi yang Kami Kumpulkan</h2>
        </div>
        <p class="text-sm text-warm-700 leading-relaxed pl-11">
            Kami mengumpulkan informasi yang Anda berikan secara langsung saat berinteraksi dengan sistem platform digital kami, meliputi:
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pl-11 pt-2">
            <div class="p-3.5 rounded-xl bg-warm-50 border border-warm-200">
                <div class="font-bold text-xs text-warm-900 mb-1"><i class="fa-solid fa-id-card text-antique-600 mr-1.5"></i> Data Identitas</div>
                <p class="text-xs text-warm-600">Nama lengkap atau nama samaran (Hamba Allah) saat berdonasi.</p>
            </div>
            <div class="p-3.5 rounded-xl bg-warm-50 border border-warm-200">
                <div class="font-bold text-xs text-warm-900 mb-1"><i class="fa-solid fa-address-book text-antique-600 mr-1.5"></i> Data Kontak</div>
                <p class="text-xs text-warm-600">Nomor WhatsApp dan Email aktif untuk pengiriman kwitansi &amp; notifikasi.</p>
            </div>
            <div class="p-3.5 rounded-xl bg-warm-50 border border-warm-200">
                <div class="font-bold text-xs text-warm-900 mb-1"><i class="fa-solid fa-receipt text-antique-600 mr-1.5"></i> Data Transaksi</div>
                <p class="text-xs text-warm-600">Nominal donasi, metode pembayaran, pesan doa, dan bukti transfer.</p>
            </div>
            <div class="p-3.5 rounded-xl bg-warm-50 border border-warm-200">
                <div class="font-bold text-xs text-warm-900 mb-1"><i class="fa-solid fa-user-shield text-antique-600 mr-1.5"></i> Data Akun</div>
                <p class="text-xs text-warm-600">Kredensial login terenkripsi jika mendaftar di Portal Donatur.</p>
            </div>
        </div>
    </div>

    <!-- Section 2 -->
    <div class="policy-card bg-white rounded-2xl border border-warm-200 p-8 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-cypress-900 text-antique-400 flex items-center justify-center font-bold text-sm shrink-0">2</div>
            <h2 class="text-xl font-bold text-warm-900">Penggunaan Informasi Pribadi</h2>
        </div>
        <p class="text-sm text-warm-700 leading-relaxed pl-11">
            Seluruh informasi yang dikumpulkan digunakan secara eksklusif untuk kepentingan operasional masjid dan pelayanan jamaah, antara lain:
        </p>
        <ul class="space-y-2 pl-11 text-sm text-warm-700">
            <li class="flex items-start gap-2"><i class="fa-solid fa-check text-emerald-600 mt-1"></i> Memverifikasi transfer donasi dan pencatatan buku kas transparan.</li>
            <li class="flex items-start gap-2"><i class="fa-solid fa-check text-emerald-600 mt-1"></i> Menerbitkan dan mengirimkan E-Kwitansi resmi ber-QR Code.</li>
            <li class="flex items-start gap-2"><i class="fa-solid fa-check text-emerald-600 mt-1"></i> Menyediakan akses rekam jejak infak via Portal Donatur.</li>
            <li class="flex items-start gap-2"><i class="fa-solid fa-check text-emerald-600 mt-1"></i> Memberikan informasi program sosial dan laporan keuangan berkala.</li>
        </ul>
    </div>

    <!-- Section 3 -->
    <div class="policy-card bg-white rounded-2xl border border-warm-200 p-8 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-cypress-900 text-antique-400 flex items-center justify-center font-bold text-sm shrink-0">3</div>
            <h2 class="text-xl font-bold text-warm-900">Perlindungan &amp; Keamanan Data</h2>
        </div>
        <p class="text-sm text-warm-700 leading-relaxed pl-11">
            Kami menerapkan standar teknis keamanan tinggi meliputi enkripsi database, pembatasan akses khusus pengurus berwenang, dan proteksi file direktori unggahan bukti pembayaran.
        </p>
    </div>

    <!-- Section 4 -->
    <div class="policy-card bg-white rounded-2xl border border-warm-200 p-8 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-cypress-900 text-antique-400 flex items-center justify-center font-bold text-sm shrink-0">4</div>
            <h2 class="text-xl font-bold text-warm-900">Hubungi Pengurus</h2>
        </div>
        <p class="text-sm text-warm-700 leading-relaxed pl-11">
            Jika ada pertanyaan atau permintaan terkait data pribadi Anda, silakan hubungi pengurus melalui halaman <a href="<?= base_url() ?>/kontak" class="text-antique-600 font-semibold underline">Kontak</a> atau layanan WhatsApp resmi masjid.
        </p>
    </div>

</main>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
