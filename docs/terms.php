
<?php
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$namaMasjid = $profil['nama_masjid'] ?? 'Masjid';

$pageTitle = 'Syarat & Ketentuan · ' . $namaMasjid;
$activeNav = 'terms';

require_once __DIR__ . '/../layouts/public_header.php';

$e = static fn($value) => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$sections = [
    'acceptance' => 'Persetujuan',
    'services' => 'Layanan',
    'accounts' => 'Akun Pengguna',
    'donations' => 'Donasi',
    'payments' => 'Pembayaran',
    'accuracy' => 'Informasi & Transparansi',
    'conduct' => 'Penggunaan Situs',
    'privacy' => 'Privasi',
    'availability' => 'Ketersediaan Layanan',
    'liability' => 'Batas Tanggung Jawab',
    'changes' => 'Perubahan Ketentuan',
    'contact' => 'Kontak Pengelola'
];
?>

<style>
.legal-page {
    --legal-green: #183728;
    --legal-gold: #c5a059;
    --legal-ink: #242923;
    --legal-muted: #74786f;
    --legal-line: #e9e7df;
    --legal-paper: #fffefa;
    color: var(--legal-ink);
}

.legal-page * {
    box-sizing: border-box;
}

.legal-page .legal-hero {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    background: var(--legal-green);
    border-radius: 28px;
    padding: clamp(28px, 5vw, 54px);
    color: #fffefa;
}

.legal-page .legal-hero::before,
.legal-page .legal-hero::after {
    content: "";
    position: absolute;
    z-index: -1;
    pointer-events: none;
    border: 1px solid rgba(197, 160, 89, .25);
    border-radius: 50%;
}

.legal-page .legal-hero::before {
    width: 340px;
    height: 340px;
    right: -105px;
    top: -180px;
}

.legal-page .legal-hero::after {
    width: 250px;
    height: 250px;
    right: -58px;
    top: -135px;
}

.legal-page .legal-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    color: #e6c98e;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .17em;
    text-transform: uppercase;
}

.legal-page .legal-dot {
    width: 7px;
    height: 7px;
    display: inline-block;
    border-radius: 50%;
    background: var(--legal-gold);
    box-shadow: 0 0 12px rgba(197, 160, 89, .55);
}

.legal-page .legal-hero h1 {
    max-width: 720px;
    margin: 20px 0 14px;
    color: #fffefa;
    font-size: clamp(30px, 4.5vw, 48px);
    font-weight: 750;
    letter-spacing: -.045em;
    line-height: 1.13;
}

.legal-page .legal-hero p {
    max-width: 620px;
    color: rgba(255, 254, 250, .75);
    font-size: 15px;
    line-height: 1.9;
}

.legal-page .legal-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 22px;
    margin-top: 26px;
    color: rgba(255, 254, 250, .76);
    font-size: 12px;
}

.legal-page .legal-meta span {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.legal-page .legal-meta svg {
    width: 15px;
    height: 15px;
    color: #e6c98e;
    flex-shrink: 0;
}

.legal-page .legal-tabs {
    display: flex;
    gap: 8px;
    padding: 6px;
    margin: 24px 0 34px;
    width: fit-content;
    max-width: 100%;
    background: #f4f2eb;
    border: 1px solid var(--legal-line);
    border-radius: 14px;
}

.legal-page .legal-tab {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 16px;
    border-radius: 10px;
    color: #777a70;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: background .2s ease, color .2s ease;
}

.legal-page .legal-tab:hover {
    color: var(--legal-green);
    background: rgba(255, 255, 255, .75);
}

.legal-page .legal-tab.active {
    background: var(--legal-green);
    color: white;
    box-shadow: 0 3px 10px rgba(24, 55, 40, .12);
}

.legal-page .legal-layout {
    display: grid;
    grid-template-columns: 245px minmax(0, 1fr);
    align-items: start;
    gap: 36px;
}

.legal-page .legal-toc {
    position: sticky;
    top: 24px;
    padding: 20px 16px;
    border: 1px solid var(--legal-line);
    border-radius: 18px;
    background: var(--legal-paper);
}

.legal-page .legal-toc-title {
    padding: 0 10px 14px;
    color: var(--legal-green);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .13em;
    text-transform: uppercase;
}

.legal-page .legal-toc a {
    display: block;
    padding: 10px;
    margin: 2px 0;
    border-left: 2px solid transparent;
    border-radius: 0 8px 8px 0;
    color: var(--legal-muted);
    font-size: 12px;
    line-height: 1.5;
    text-decoration: none;
    transition: .2s ease;
}

.legal-page .legal-toc a:hover,
.legal-page .legal-toc a.is-active {
    color: var(--legal-green);
    background: #f4f5ef;
    border-left-color: var(--legal-gold);
}

.legal-page .legal-content {
    min-width: 0;
}

.legal-page .legal-intro {
    padding: 24px 26px;
    margin-bottom: 18px;
    border: 1px solid #e9e5d8;
    border-radius: 18px;
    background: linear-gradient(135deg, #faf8f0, #fffefa);
}

.legal-page .legal-intro-label {
    margin-bottom: 9px;
    color: #98743b;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .15em;
    text-transform: uppercase;
}

.legal-page .legal-intro p {
    color: #555a50;
    font-size: 14px;
    line-height: 1.9;
}

.legal-page .legal-section {
    padding: 26px;
    margin-bottom: 14px;
    border: 1px solid var(--legal-line);
    border-radius: 18px;
    background: var(--legal-paper);
    scroll-margin-top: 24px;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.legal-page .legal-section:hover {
    border-color: #d9c79f;
    box-shadow: 0 8px 28px rgba(34, 46, 34, .045);
}

.legal-page .legal-section-heading {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 14px;
}

.legal-page .legal-number {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    border: 1px solid #e9dfc7;
    border-radius: 12px;
    background: #f8f3e7;
    color: #98743b;
    font-size: 12px;
    font-weight: 800;
}

.legal-page .legal-section h2 {
    margin: 5px 0 0;
    color: var(--legal-green);
    font-size: 17px;
    font-weight: 750;
    letter-spacing: -.02em;
    line-height: 1.5;
}

.legal-page .legal-section p,
.legal-page .legal-section li {
    color: #65695f;
    font-size: 13px;
    line-height: 1.95;
}

.legal-page .legal-section p + p {
    margin-top: 10px;
}

.legal-page .legal-section ul {
    padding-left: 19px;
    margin-top: 9px;
    list-style: disc;
}

.legal-page .legal-section li {
    padding-left: 4px;
    margin-bottom: 5px;
}

.legal-page .legal-section a {
    color: #8b6a31;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.legal-page .legal-note {
    display: flex;
    gap: 12px;
    padding: 17px 19px;
    margin-top: 20px;
    border: 1px solid #e9dfc7;
    border-radius: 14px;
    background: #faf7ef;
}

.legal-page .legal-note svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    color: #98743b;
}

.legal-page .legal-note p {
    color: #6c6555;
    font-size: 12px;
    line-height: 1.8;
}

.legal-page .legal-back-top {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    margin: 12px 0 0;
    color: #74786f;
    font-size: 12px;
    text-decoration: none;
}

.legal-page .legal-back-top:hover {
    color: var(--legal-green);
}

@media (max-width: 900px) {
    .legal-page .legal-layout {
        grid-template-columns: 1fr;
        gap: 22px;
    }

    .legal-page .legal-toc {
        position: static;
    }

    .legal-page .legal-toc ul {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 2px 10px;
    }
}

@media (max-width: 560px) {
    .legal-page .legal-hero {
        border-radius: 20px;
        padding: 28px 22px;
    }

    .legal-page .legal-tabs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        width: 100%;
    }

    .legal-page .legal-tab {
        padding: 11px 8px;
        font-size: 12px;
    }

    .legal-page .legal-toc {
        padding: 16px 12px;
    }

    .legal-page .legal-toc ul {
        grid-template-columns: 1fr;
    }

    .legal-page .legal-section {
        padding: 21px 18px;
        border-radius: 15px;
    }

    .legal-page .legal-intro {
        padding: 20px;
    }

    .legal-page .legal-section h2 {
        font-size: 16px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .legal-page *,
    .legal-page *::before,
    .legal-page *::after {
        scroll-behavior: auto !important;
        transition: none !important;
    }
}
</style>

<main class="legal-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <nav class="flex flex-wrap items-center gap-2 text-xs text-stone-500 mb-6"
         aria-label="Breadcrumb">
        <a href="<?= $e(base_url()) ?>"
           class="hover:text-emerald-900 transition-colors">Beranda</a>
        <span aria-hidden="true">/</span>
        <span>Legal</span>
        <span aria-hidden="true">/</span>
        <span class="text-stone-800 font-medium">Syarat &amp; Ketentuan</span>
    </nav>

    <header class="legal-hero">
        <div class="legal-eyebrow">
            <span class="legal-dot"></span>
            INFORMASI &amp; KETENTUAN
        </div>

        <h1>Syarat &amp; Ketentuan Penggunaan</h1>

        <p>
            Ketentuan penggunaan layanan digital
            <?= $e($namaMasjid) ?>. Pelajari hak, kewajiban,
            serta aturan penggunaan layanan sebelum mengakses
            fitur yang tersedia.
        </p>

        <div class="legal-meta">
            <span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path d="M16 3v4M8 3v4M3 10h18"/>
                </svg>
                Diperbarui 23 Oktober 2026
            </span>

            <span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" aria-hidden="true">
                    <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
                Ketentuan Layanan
            </span>
        </div>
    </header>

    <nav class="legal-tabs" aria-label="Dokumen legal">
        <a href="<?= $e(rtrim(base_url(), '/') . '/docs/privacy.php') ?>" class="legal-tab">
            <svg width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8"
                 aria-hidden="true">
                <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                <path d="M12 11v5M12 8h.01"/>
            </svg>
            Kebijakan Privasi
        </a>

        <a href="<?= $e(rtrim(base_url(), '/') . '/docs/terms.php') ?>"
           class="legal-tab active" aria-current="page">
            <svg width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8"
                 aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                <path d="M14 2v6h6M8 13h8M8 17h8M8 9h2"/>
            </svg>
            Syarat &amp; Ketentuan
        </a>
    </nav>

    <div class="legal-layout">

        <aside class="legal-toc">
            <div class="legal-toc-title">Daftar Isi</div>
            <ul class="space-y-1">
                <?php $index = 1; ?>
                <?php foreach ($sections as $id => $label): ?>
                    <li>
                        <a href="#<?= $e($id) ?>"
                           data-legal-link="<?= $e($id) ?>">
                            <span class="mr-2 opacity-60">
                                <?= str_pad((string) $index++, 2, '0', STR_PAD_LEFT) ?>
                            </span>
                            <?= $e($label) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <div class="legal-content">

            <div class="legal-intro">
                <div class="legal-intro-label">Sebelum Menggunakan Layanan</div>
                <p>
                    Syarat dan ketentuan ini mengatur penggunaan situs
                    serta fitur yang disediakan oleh
                    <strong><?= $e($namaMasjid) ?></strong>.
                    Dengan mengakses atau menggunakan layanan ini,
                    Anda diharapkan membaca dan memahami ketentuan
                    yang relevan. Jika tidak menyetujuinya, hentikan
                    penggunaan fitur terkait.
                </p>
            </div>

            <article>

                <section id="acceptance" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">01</span>
                        <h2>Persetujuan terhadap Ketentuan</h2>
                    </div>
                    <p>
                        Dengan mengakses situs <?= $e($namaMasjid) ?>,
                        Anda setuju menggunakan layanan secara wajar,
                        bertanggung jawab, dan sesuai dengan ketentuan
                        ini serta peraturan yang berlaku.
                    </p>
                    <p>
                        Ketentuan tambahan dapat berlaku untuk fitur
                        tertentu, seperti akun pengguna, formulir donasi,
                        atau layanan komunikasi.
                    </p>
                </section>

                <section id="services" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">02</span>
                        <h2>Layanan yang Disediakan</h2>
                    </div>
                    <p>
                        Situs dapat menyediakan informasi mengenai masjid,
                        kegiatan keagamaan, jadwal kegiatan, pengumuman,
                        laporan, serta informasi dan fasilitas donasi
                        sesuai fitur yang tersedia.
                    </p>
                    <p>
                        Ketersediaan fitur dapat berubah mengikuti kebutuhan
                        pengelolaan dan kemampuan sistem. Informasi pada
                        situs ditujukan untuk membantu pengguna memperoleh
                        informasi terkait kegiatan masjid.
                    </p>
                </section>

                <section id="accounts" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">03</span>
                        <h2>Akun dan Tanggung Jawab Pengguna</h2>
                    </div>
                    <p>
                        Jika situs menyediakan fitur akun, pengguna
                        bertanggung jawab memberikan informasi yang benar
                        serta menjaga kerahasiaan kredensial akunnya.
                    </p>
                    <ul>
                        <li>Jangan membagikan kata sandi kepada orang lain.</li>
                        <li>Jangan menggunakan akun orang lain tanpa izin.</li>
                        <li>Laporkan aktivitas akun yang mencurigakan
                            kepada pengelola.</li>
                        <li>Jangan menggunakan akun untuk menyalahgunakan
                            layanan atau merugikan pihak lain.</li>
                    </ul>
                    <p>
                        Pengelola dapat meninjau atau membatasi akses akun
                        apabila ditemukan indikasi penyalahgunaan, dengan
                        mempertimbangkan keadaan dan ketentuan yang berlaku.
                    </p>
                </section>

                <section id="donations" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">04</span>
                        <h2>Donasi dan Penyaluran Dana</h2>
                    </div>
                    <p>
                        Donasi yang diberikan melalui atau atas nama
                        <?= $e($namaMasjid) ?> ditujukan untuk mendukung
                        kegiatan dan kebutuhan masjid sesuai informasi
                        yang disampaikan kepada donatur.
                    </p>
                    <ul>
                        <li>Periksa nama penerima dan tujuan donasi sebelum
                            melakukan pembayaran.</li>
                        <li>Pastikan nominal donasi sudah benar.</li>
                        <li>Simpan bukti transaksi untuk verifikasi
                            atau klarifikasi.</li>
                        <li>Hubungi pengelola jika terjadi kesalahan nominal,
                            transaksi ganda, atau kendala lainnya.</li>
                    </ul>
                    <p>
                        Permintaan koreksi atau pengembalian dana akan
                        ditinjau berdasarkan bukti transaksi, keadaan kasus,
                        dan ketentuan yang berlaku. Pengajuan tidak otomatis
                        menjamin pengembalian dana.
                    </p>
                </section>

                <section id="payments" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">05</span>
                        <h2>Pembayaran dan Verifikasi Transaksi</h2>
                    </div>
                    <p>
                        Apabila fitur pembayaran tersedia, pengguna harus
                        mengikuti instruksi pembayaran yang ditampilkan
                        pada situs. Status transaksi dapat memerlukan
                        verifikasi sebelum dinyatakan selesai.
                    </p>
                    <ul>
                        <li>Jangan membagikan PIN, kata sandi perbankan,
                            atau kode OTP kepada siapa pun.</li>
                        <li>Gunakan kanal pembayaran yang diinformasikan
                            melalui saluran resmi.</li>
                        <li>Hubungi pengelola jika status transaksi tidak
                            sesuai dengan bukti pembayaran.</li>
                    </ul>
                    <p>
                        Pengelola tidak memerlukan kredensial rahasia
                        perbankan pengguna untuk memverifikasi donasi.
                    </p>
                </section>

                <section id="accuracy" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">06</span>
                        <h2>Informasi dan Transparansi</h2>
                    </div>
                    <p>
                        Pengelola berupaya menyajikan informasi yang relevan
                        dan akurat mengenai kegiatan serta pengelolaan dana.
                        Informasi dapat diperbarui atau dikoreksi apabila
                        ditemukan ketidaksesuaian.
                    </p>
                    <p>
                        Laporan yang ditampilkan kepada publik dapat disajikan
                        dalam bentuk ringkasan. Pertanyaan mengenai pencatatan
                        atau laporan tertentu dapat disampaikan kepada
                        pengelola melalui kanal komunikasi resmi.
                    </p>
                </section>

                <section id="conduct" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">07</span>
                        <h2>Aturan Penggunaan Situs</h2>
                    </div>
                    <p>Pengguna dilarang menggunakan situs untuk:</p>
                    <ul>
                        <li>Melakukan tindakan yang melanggar hukum.</li>
                        <li>Mengakses sistem, akun, atau data tanpa izin.</li>
                        <li>Mengunggah file berbahaya atau mengganggu
                            keamanan dan kinerja sistem.</li>
                        <li>Menyebarkan penipuan, spam, atau konten yang
                            melanggar hak orang lain.</li>
                        <li>Mengganggu operasional situs atau mencoba
                            melewati mekanisme keamanan.</li>
                    </ul>
                    <p>
                        Pengelola dapat mengambil tindakan yang wajar untuk
                        menangani pelanggaran, termasuk membatasi akses
                        terhadap fitur yang terdampak.
                    </p>
                </section>

                <section id="privacy" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">08</span>
                        <h2>Privasi dan Perlindungan Data</h2>
                    </div>
                    <p>
                        Penggunaan data pribadi mengikuti Kebijakan Privasi
                        yang menjelaskan jenis data yang diproses, tujuan
                        penggunaannya, penyimpanan, dan hak pengguna.
                    </p>
                    <p>
                        Baca dokumen
                        <a href="<?= $e(rtrim(base_url(), '/') . '/docs/privacy.php') ?>">
                            Kebijakan Privasi
                        </a>
                        sebelum mengirimkan data pribadi atau bukti transaksi.
                        Kirimkan hanya informasi yang diperlukan untuk
                        tujuan layanan.
                    </p>
                </section>

                <section id="availability" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">09</span>
                        <h2>Ketersediaan dan Perubahan Layanan</h2>
                    </div>
                    <p>
                        Pengelola berupaya menjaga agar situs dapat diakses
                        dengan baik, tetapi tidak menjamin layanan selalu
                        tersedia tanpa gangguan. Gangguan dapat terjadi akibat
                        pemeliharaan, masalah jaringan, kegagalan penyedia
                        layanan, atau keadaan di luar kendali pengelola.
                    </p>
                    <p>
                        Fitur atau konten dapat diperbarui, dihentikan
                        sementara, atau disesuaikan berdasarkan kebutuhan
                        operasional.
                    </p>
                </section>

                <section id="liability" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">10</span>
                        <h2>Batas Tanggung Jawab</h2>
                    </div>
                    <p>
                        Informasi pada situs disediakan untuk mendukung
                        komunikasi dan pelayanan masjid. Pengguna perlu
                        memeriksa informasi penting sebelum mengambil
                        tindakan, terutama terkait nominal, tujuan,
                        dan status transaksi.
                    </p>
                    <p>
                        Pengelola akan menindaklanjuti laporan kendala
                        secara wajar. Ketentuan ini tidak menghapus hak
                        atau tanggung jawab yang tidak dapat dikesampingkan
                        berdasarkan hukum yang berlaku.
                    </p>
                </section>

                <section id="changes" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">11</span>
                        <h2>Perubahan Syarat dan Ketentuan</h2>
                    </div>
                    <p>
                        Syarat dan ketentuan dapat diperbarui jika terdapat
                        perubahan layanan, kebutuhan operasional, atau
                        ketentuan hukum.
                    </p>
                    <p>
                        Versi terbaru akan ditampilkan pada halaman ini
                        bersama tanggal pembaruannya. Pengguna disarankan
                        meninjau halaman ini secara berkala.
                    </p>
                </section>

                <section id="contact" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">12</span>
                        <h2>Kontak dan Pertanyaan</h2>
                    </div>
                    <p>
                        Jika memiliki pertanyaan, ingin melaporkan kendala,
                        atau membutuhkan klarifikasi mengenai ketentuan ini,
                        hubungi pengelola <?= $e($namaMasjid) ?> melalui
                        kanal komunikasi resmi yang tersedia.
                    </p>
                    <p>
                        Anda juga dapat mengunjungi halaman
                        <a href="<?= $e(base_url()) ?>/kontak">Kontak</a>
                        apabila rute tersebut tersedia pada situs.
                    </p>
                </section>

            </article>

            <div class="legal-note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 11v5M12 8h.01"/>
                </svg>
                <p>
                    Dokumen ini merupakan informasi umum penggunaan layanan.
                    Sesuaikan ketentuannya dengan alur donasi, sistem
                    pembayaran, dan praktik pengelolaan yang benar-benar
                    diterapkan oleh masjid.
                </p>
            </div>

            <a href="#" class="legal-back-top"
               onclick="window.scrollTo({top:0,behavior:'smooth'}); return false;">
                <svg width="14" height="14" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     aria-hidden="true">
                    <path d="m18 15-6-6-6 6"/>
                </svg>
                Kembali ke atas
            </a>

        </div>
    </div>
</main>

<script>
(function () {
    const links = document.querySelectorAll('[data-legal-link]');
    const sections = Array.from(links)
        .map(link => document.getElementById(link.dataset.legalLink))
        .filter(Boolean);

    if (!('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;

            links.forEach(link => {
                const active =
                    link.dataset.legalLink === entry.target.id;

                link.classList.toggle('is-active', active);

                if (active) {
                    link.setAttribute('aria-current', 'location');
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        });
    }, {
        rootMargin: '-15% 0px -70% 0px',
        threshold: 0
    });

    sections.forEach(section => observer.observe(section));
})();
</script>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
