<?php
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pageTitle = 'Syarat & Ketentuan · ' . $profil['nama_masjid'];
$activeNav = 'terms';

require_once __DIR__ . '/../layouts/public_header.php';
?>

<style>
.term-card {
    border: 1px solid var(--color-border-subtle);
    background: var(--color-bg-surface);
    transition: all 0.3s ease;
}
.term-card:hover {
    border-color: var(--color-accent-gold);
    box-shadow: 0 10px 25px -5px rgba(197, 160, 89, 0.1);
}
</style>

<section class="relative bg-cypress-950 text-white overflow-hidden pattern-arabesque-dark border-b border-antique-500/30">
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-antique-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 right-10 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 relative z-10 text-center">
        <h1 class="text-3xl sm:text-5xl font-bold mb-4 font-classic tracking-wide">Syarat & Ketentuan</h1>
        <p class="text-stone-300 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
            Harap membaca syarat dan ketentuan berikut dengan saksama sebelum menggunakan platform digital <strong class="text-antique-300"><?= e($profil['nama_masjid']) ?></strong>.
        </p>
    </div>
</section>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6 text-warm-800">

    <div class="term-card bg-white rounded-2xl border border-warm-200 p-8 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-cypress-900 text-antique-400 flex items-center justify-center font-bold text-sm shrink-0">1</div>
            <h2 class="text-xl font-bold text-warm-900">Ketentuan Umum</h2>
        </div>
        <p class="text-sm text-warm-700 leading-relaxed pl-11">
            Dengan mengakses, mendaftar, atau melakukan donasi melalui website resmi <strong><?= e($profil['nama_masjid']) ?></strong>, Anda dianggap telah membaca, memahami, dan menyetujui seluruh ketentuan yang tercantum dalam dokumen Syarat & Ketentuan ini. Jika Anda tidak setuju dengan ketentuan ini, mohon untuk tidak melanjutkan penggunaan website.
        </p>
    </div>

    <div class="term-card bg-white rounded-2xl border border-warm-200 p-8 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-cypress-900 text-antique-400 flex items-center justify-center font-bold text-sm shrink-0">2</div>
            <h2 class="text-xl font-bold text-warm-900">Prosedur Donasi Online</h2>
        </div>
        <div class="pl-11 space-y-3">
            <div class="flex gap-3 text-sm text-warm-700">
                <span class="font-bold text-antique-600">01.</span>
                <p>Donatur wajib mengisi data diri dengan benar dan jujur.</p>
            </div>
            <div class="flex gap-3 text-sm text-warm-700">
                <span class="font-bold text-antique-600">02.</span>
                <p>Donasi dianggap sah setelah donatur mentransfer dana sesuai nominal yang dipilih dan mengunggah bukti transfer melalui sistem.</p>
            </div>
            <div class="flex gap-3 text-sm text-warm-700">
                <span class="font-bold text-antique-600">03.</span>
                <p>Status donasi bersifat "Pending" sebelum diverifikasi oleh Admin/Bendahara masjid.</p>
            </div>
            <div class="flex gap-3 text-sm text-warm-700">
                <span class="font-bold text-antique-600">04.</span>
                <p>Pengurus masjid berhak menolak donasi apabila bukti transfer tidak dapat divalidasi atau tidak sesuai.</p>
            </div>
        </div>
    </div>

    <div class="term-card bg-white rounded-2xl border border-warm-200 p-8 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-cypress-900 text-antique-400 flex items-center justify-center font-bold text-sm shrink-0">3</div>
            <h2 class="text-xl font-bold text-warm-900">Pengelolaan Dana</h2>
        </div>
        <p class="text-sm text-warm-700 leading-relaxed pl-11">
            Seluruh dana infak, sedekah, dan wakaf yang diterima akan dikelola secara amanah sesuai dengan syariat Islam dan kebijakan operasional masjid. Laporan penggunaan dana dapat diakses publik melalui menu <strong>Transparansi</strong> secara berkala.
        </p>
    </div>

    <div class="term-card bg-white rounded-2xl border border-warm-200 p-8 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-cypress-900 text-antique-400 flex items-center justify-center font-bold text-sm shrink-0">4</div>
            <h2 class="text-xl font-bold text-warm-900">Akun Pengguna</h2>
        </div>
        <p class="text-sm text-warm-700 leading-relaxed pl-11">
            Pengguna (donatur) bertanggung jawab sepenuhnya atas kerahasiaan data akun dan password mereka. Kami tidak bertanggung jawab atas kerugian yang timbul akibat penyalahgunaan akun oleh pihak ketiga akibat kelalaian pemilik akun.
        </p>
    </div>

    <div class="term-card bg-white rounded-2xl border border-warm-200 p-8 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-cypress-900 text-antique-400 flex items-center justify-center font-bold text-sm shrink-0">5</div>
            <h2 class="text-xl font-bold text-warm-900">Perubahan Ketentuan</h2>
        </div>
        <p class="text-sm text-warm-700 leading-relaxed pl-11">
            <?= e($profil['nama_masjid']) ?> berhak untuk memperbarui, mengubah, atau menambah syarat dan ketentuan sewaktu-waktu tanpa pemberitahuan sebelumnya. Pengguna disarankan untuk memeriksa halaman ini secara rutin untuk mendapatkan informasi terbaru.
        </p>
    </div>

</main>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
