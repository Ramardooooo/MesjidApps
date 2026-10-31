
<?php
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$namaMasjid = $profil['nama_masjid'] ?? 'Masjid';

$pageTitle = 'Kebijakan Privasi · ' . $namaMasjid;
$activeNav = 'privacy';

require_once __DIR__ . '/../layouts/public_header.php';

$e = static fn($value) => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$sections = [
    'intro' => 'Pendahuluan',
    'collection' => 'Data yang Dikumpulkan',
    'usage' => 'Penggunaan Data',
    'sharing' => 'Pihak Ketiga',
    'security' => 'Keamanan Data',
    'rights' => 'Hak Pengguna',
    'cookies' => 'Cookies',
    'retention' => 'Retensi Data',
    'changes' => 'Perubahan Kebijakan',
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
        <span class="text-stone-800 font-medium">Kebijakan Privasi</span>
    </nav>

    <header class="legal-hero">
        <div class="legal-eyebrow">
            <span class="legal-dot"></span>
            INFORMASI &amp; PERLINDUNGAN DATA
        </div>

        <h1>Kebijakan Privasi &amp; Perlindungan Data</h1>

        <p>
            Informasi mengenai bagaimana
            <?= $e($namaMasjid) ?> menangani data pribadi
            pengguna dan donatur saat menggunakan layanan digital.
            Privasi Anda perlu diperlakukan dengan bertanggung jawab.
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
                Privasi &amp; Data Pengguna
            </span>
        </div>
    </header>

    <nav class="legal-tabs" aria-label="Dokumen legal">
        <a href="<?= $e(rtrim(base_url(), '/') . '/docs/privacy.php') ?>"
           class="legal-tab active" aria-current="page">
            <svg width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8"
                 aria-hidden="true">
                <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                <path d="M12 11v5M12 8h.01"/>
            </svg>
            Kebijakan Privasi
        </a>

        <a href="<?= $e(rtrim(base_url(), '/') . '/docs/terms.php') ?>" class="legal-tab">
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
                <div class="legal-intro-label">Komitmen Privasi</div>
                <p>
                    Kebijakan ini menjelaskan prinsip penanganan data pribadi
                    pada layanan <strong><?= $e($namaMasjid) ?></strong>.
                    Data hanya seharusnya dikumpulkan dan digunakan sesuai
                    tujuan layanan yang jelas, dengan memperhatikan keamanan,
                    kerahasiaan, dan hak pengguna berdasarkan ketentuan
                    yang berlaku.
                </p>
            </div>

            <article>

                <section id="intro" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">01</span>
                        <h2>Pendahuluan</h2>
                    </div>
                    <p>
                        Kebijakan Privasi ini berlaku bagi pengguna yang
                        mengakses situs dan menggunakan fitur yang disediakan
                        oleh <?= $e($namaMasjid) ?>, termasuk fitur informasi,
                        akun, dan donasi apabila tersedia.
                    </p>
                    <p>
                        Dengan menggunakan fitur yang memerlukan data pribadi,
                        pengguna diharapkan membaca kebijakan ini untuk
                        memahami data apa yang diperlukan dan bagaimana data
                        tersebut digunakan.
                    </p>
                </section>

                <section id="collection" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">02</span>
                        <h2>Informasi yang Dikumpulkan</h2>
                    </div>
                    <p>
                        Jenis informasi yang diproses bergantung pada fitur
                        yang digunakan dan informasi yang diberikan pengguna.
                        Informasi tersebut dapat meliputi:
                    </p>
                    <ul>
                        <li><strong>Informasi identitas:</strong> nama atau
                            nama yang dipilih untuk ditampilkan.</li>
                        <li><strong>Informasi kontak:</strong> alamat email,
                            nomor telepon, atau WhatsApp jika diminta.</li>
                        <li><strong>Informasi donasi:</strong> nominal,
                            tanggal transaksi, status, metode pembayaran,
                            serta bukti transaksi jika diserahkan.</li>
                        <li><strong>Informasi akun:</strong> username dan
                            data autentikasi yang diperlukan untuk login.</li>
                        <li><strong>Informasi teknis:</strong> data yang
                            diperlukan untuk menjalankan sesi dan menjaga
                            fungsi dasar situs.</li>
                    </ul>
                    <p>
                        Pengelola sebaiknya hanya meminta data yang relevan
                        dan diperlukan untuk tujuan pengumpulan yang
                        diinformasikan kepada pengguna.
                    </p>
                </section>

                <section id="usage" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">03</span>
                        <h2>Penggunaan Informasi</h2>
                    </div>
                    <p>
                        Informasi dapat digunakan untuk keperluan operasional
                        layanan, sesuai fitur yang benar-benar tersedia,
                        termasuk:
                    </p>
                    <ul>
                        <li>Memproses dan memverifikasi informasi donasi.</li>
                        <li>Mencatat transaksi dan mendukung administrasi
                            keuangan masjid.</li>
                        <li>Menyediakan riwayat atau konfirmasi transaksi
                            jika fitur tersebut tersedia.</li>
                        <li>Menanggapi pertanyaan, keluhan, dan permintaan
                            bantuan pengguna.</li>
                        <li>Menjaga keamanan akun serta mencegah
                            penyalahgunaan layanan.</li>
                        <li>Menyusun laporan atau ringkasan transparansi
                            sesuai kebutuhan pengelolaan.</li>
                    </ul>
                    <p>
                        Informasi yang dapat mengidentifikasi donatur tidak
                        seharusnya dipublikasikan tanpa dasar yang sesuai
                        atau persetujuan apabila persetujuan diperlukan.
                    </p>
                </section>

                <section id="sharing" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">04</span>
                        <h2>Pengungkapan kepada Pihak Ketiga</h2>
                    </div>
                    <p>
                        Data pribadi tidak seharusnya dijual. Data dapat
                        diungkapkan secara terbatas apabila diperlukan untuk
                        menjalankan layanan, memenuhi kewajiban hukum,
                        menangani sengketa, atau melindungi keamanan sistem.
                    </p>
                    <p>
                        Jika layanan menggunakan penyedia eksternal, seperti
                        penyedia hosting atau pemroses pembayaran, informasi
                        yang diperlukan dapat diproses oleh penyedia tersebut
                        sesuai peran dan ketentuan yang berlaku.
                    </p>
                    <p>
                        Pengelola perlu menjelaskan penggunaan penyedia
                        eksternal secara akurat dan tidak menyatakan adanya
                        integrasi pembayaran tertentu apabila integrasi
                        tersebut belum digunakan.
                    </p>
                </section>

                <section id="security" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">05</span>
                        <h2>Penyimpanan dan Keamanan Data</h2>
                    </div>
                    <p>
                        Pengelola perlu menerapkan langkah-langkah yang
                        wajar untuk melindungi data dari akses tanpa izin,
                        perubahan, kehilangan, pengungkapan, atau
                        penyalahgunaan.
                    </p>
                    <p>Langkah pengamanan yang relevan dapat mencakup:</p>
                    <ul>
                        <li>Menggunakan mekanisme penyimpanan kata sandi
                            yang aman, bukan menyimpan kata sandi asli.</li>
                        <li>Membatasi akses data sesuai tugas dan wewenang
                            masing-masing pengelola.</li>
                        <li>Melindungi file bukti transaksi dan mencegah
                            akses publik yang tidak diperlukan.</li>
                        <li>Memperbarui perangkat lunak dan konfigurasi
                            keamanan secara berkala.</li>
                        <li>Menghindari pengiriman data sensitif melalui
                            kanal yang tidak aman.</li>
                    </ul>
                    <p>
                        Tidak ada sistem elektronik yang dapat dijamin
                        sepenuhnya bebas risiko. Jika terjadi insiden,
                        pengelola perlu menanganinya sesuai prosedur dan
                        kewajiban hukum yang berlaku.
                    </p>
                </section>

                <section id="rights" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">06</span>
                        <h2>Hak Pengguna</h2>
                    </div>
                    <p>
                        Sesuai peraturan perlindungan data yang berlaku,
                        pengguna dapat mengajukan permintaan yang relevan
                        terkait data pribadinya, dengan tetap memperhatikan
                        proses verifikasi dan kewajiban penyimpanan yang sah.
                    </p>
                    <ul>
                        <li>Meminta informasi mengenai pemrosesan data.</li>
                        <li>Meminta koreksi terhadap data yang tidak akurat.</li>
                        <li>Meminta penghapusan data apabila memenuhi
                            persyaratan dan tidak bertentangan dengan
                            kewajiban hukum atau pencatatan yang berlaku.</li>
                        <li>Menyampaikan keberatan atau keluhan terkait
                            penggunaan data pribadi.</li>
                    </ul>
                    <p>
                        Pengelola dapat meminta informasi secukupnya untuk
                        memverifikasi identitas pemohon sebelum menindaklanjuti
                        permintaan.
                    </p>
                </section>

                <section id="cookies" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">07</span>
                        <h2>Cookies dan Penyimpanan Lokal</h2>
                    </div>
                    <p>
                        Situs dapat menggunakan cookie sesi atau teknologi
                        penyimpanan serupa untuk mendukung fungsi dasar,
                        seperti mempertahankan sesi login dan menjaga
                        keamanan navigasi.
                    </p>
                    <p>
                        Jika situs menggunakan analitik, layanan pihak ketiga,
                        atau cookie tambahan, penggunaan tersebut perlu
                        dijelaskan sesuai implementasi yang sebenarnya.
                        Pengguna dapat mengelola cookie melalui pengaturan
                        browser, tetapi menonaktifkan cookie tertentu dapat
                        memengaruhi fungsi situs.
                    </p>
                </section>

                <section id="retention" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">08</span>
                        <h2>Retensi dan Penghapusan Data</h2>
                    </div>
                    <p>
                        Data disimpan selama diperlukan untuk tujuan
                        pengumpulannya, menjalankan layanan, menyelesaikan
                        transaksi, menangani keluhan, serta memenuhi
                        kewajiban hukum atau administrasi yang relevan.
                    </p>
                    <p>
                        Catatan transaksi keuangan dapat perlu disimpan
                        sesuai ketentuan pencatatan yang berlaku. Oleh karena
                        itu, permintaan penghapusan tidak selalu berarti
                        seluruh catatan transaksi dapat langsung dihapus.
                    </p>
                    <p>
                        Setelah masa penyimpanan berakhir, data sebaiknya
                        dihapus atau dianonimkan secara aman apabila
                        tidak ada dasar yang sah untuk menyimpannya lebih
                        lanjut.
                    </p>
                </section>

                <section id="changes" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">09</span>
                        <h2>Perubahan Kebijakan</h2>
                    </div>
                    <p>
                        Kebijakan ini dapat diperbarui ketika terdapat
                        perubahan fitur, cara pengelolaan data, atau
                        ketentuan yang berlaku.
                    </p>
                    <p>
                        Versi terbaru akan ditampilkan pada halaman ini
                        bersama tanggal pembaruannya. Untuk perubahan
                        penting yang memengaruhi pengguna, pengelola
                        sebaiknya memberikan pemberitahuan melalui
                        kanal yang sesuai.
                    </p>
                </section>

                <section id="contact" class="legal-section">
                    <div class="legal-section-heading">
                        <span class="legal-number">10</span>
                        <h2>Kontak Pengelola</h2>
                    </div>
                    <p>
                        Untuk pertanyaan, koreksi, atau permintaan mengenai
                        data pribadi, silakan hubungi pengelola
                        <?= $e($namaMasjid) ?> melalui kanal komunikasi
                        resmi yang tersedia.
                    </p>
                    <p>
                        Anda dapat mengunjungi halaman
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
                    Kebijakan ini perlu disesuaikan dengan fitur, konfigurasi
                    keamanan, penyedia layanan, dan praktik pengelolaan data
                    yang benar-benar diterapkan oleh masjid. Dokumen ini
                    merupakan informasi umum dan bukan pengganti nasihat hukum.
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
