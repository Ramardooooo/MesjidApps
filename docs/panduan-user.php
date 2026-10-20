<?php
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pageTitle = 'Panduan Pengguna & Donatur · ' . $profil['nama_masjid'];
$activeNav = 'panduan-user';

require_once __DIR__ . '/../layouts/public_header.php';
?>

<style>
.step-number {
    background: linear-gradient(135deg, #c5a059 0%, #b08a42 100%);
    color: #0d1d15;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    flex-shrink: 0;
}
</style>

<section class="relative bg-cypress-950 text-white overflow-hidden pattern-arabesque-dark border-b border-antique-500/30">
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-antique-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 right-10 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 relative z-10 text-center">
        <h1 class="text-3xl sm:text-4xl font-bold mb-3 font-classic tracking-wide">Panduan Penggunaan Website</h1>
        <p class="text-stone-300 text-sm sm:text-base max-w-2xl mx-auto">
            Panduan lengkap cara melakukan donasi online, melihat transparansi kas, membaca berita, dan mengakses portal donatur.
        </p>
    </div>
</section>

<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">

    <!-- Quick Links -->
    <div class="bg-white rounded-2xl border border-warm-200 p-6 shadow-soft">
        <h2 class="text-xl font-bold text-warm-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-list text-antique-600"></i>
            Daftar Isi Panduan Pengguna
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <a href="#donasi-online" class="flex items-center gap-2 p-3 rounded-lg border border-warm-200 hover:border-antique-300 hover:bg-warm-50 transition">
                <i class="fa-solid fa-hand-holding-dollar text-antique-600"></i>
                <span>Cara Donasi Online & Konfirmasi</span>
            </a>
            <a href="#portal-donatur" class="flex items-center gap-2 p-3 rounded-lg border border-warm-200 hover:border-antique-300 hover:bg-warm-50 transition">
                <i class="fa-solid fa-id-card text-antique-600"></i>
                <span>Portal Donatur & E-Kwitansi</span>
            </a>
            <a href="#transparansi" class="flex items-center gap-2 p-3 rounded-lg border border-warm-200 hover:border-antique-300 hover:bg-warm-50 transition">
                <i class="fa-solid fa-chart-line text-antique-600"></i>
                <span>Cek Transparansi Kas & Laporan</span>
            </a>
            <a href="#fitur-lain" class="flex items-center gap-2 p-3 rounded-lg border border-warm-200 hover:border-antique-300 hover:bg-warm-50 transition">
                <i class="fa-solid fa-newspaper text-antique-600"></i>
                <span>Berita, Kajian & Kontak</span>
            </a>
        </div>
    </div>

    <!-- Section 1: Donasi Online -->
    <div id="donasi-online" class="space-y-6">
        <div class="border-l-4 border-antique-500 pl-4">
            <h2 class="text-2xl font-bold text-warm-900 font-classic">Cara Donasi Online & Konfirmasi Bukti Transfer</h2>
            <p class="text-sm text-warm-700 mt-1">Panduan lengkap bagi dermawan untuk menyalurkan infak, sedekah, atau zakat melalui website.</p>
        </div>

        <div class="bg-white rounded-2xl border border-warm-200 p-6 space-y-6">
            <div class="flex items-start gap-4">
                <div class="step-number">1</div>
                <div class="space-y-2 flex-1">
                    <h3 class="font-bold text-warm-900">Buka Menu Donasi Online</h3>
                    <p class="text-sm text-warm-700">Akses halaman utama website masjid, lalu klik tombol <strong>"Donasi Sekarang"</strong> pada navigasi atas atau banner utama untuk menuju halaman <code class="bg-warm-100 px-1.5 py-0.5 rounded text-xs">donasi-online.php</code>.</p>
                    <div class="bg-warm-50 p-4 rounded-xl border border-warm-200 text-xs text-warm-600">
                        <i class="fa-solid fa-lightbulb text-amber-500 mr-1"></i> <strong>Tips:</strong> Anda juga bisa memilih program donasi spesifik (misal: Pembangunan Masjid, Santunan Yatim, atau Operasional Ramadhan) di halaman Program.
                    </div>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="step-number">2</div>
                <div class="space-y-2 flex-1">
                    <h3 class="font-bold text-warm-900">Isi Formulir Donasi & Pilih Rekening Tujuan</h3>
                    <p class="text-sm text-warm-700">Lengkapi formulir dengan data Anda:</p>
                    <ul class="list-disc list-inside text-sm text-warm-700 space-y-1 ml-2">
                        <li>Nama Lengkap / Hamba Allah</li>
                        <li>Nomor WhatsApp & Email aktif (untuk pengiriman notifikasi dan e-kwitansi OTP)</li>
                        <li>Nominal donasi (pilih tombol cepat atau masukkan angka manual)</li>
                        <li>Pilih Bank tujuan transfer / QRIS</li>
                        <li>Pesan / Doa khusus (opsional)</li>
                    </ul>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="step-number">3</div>
                <div class="space-y-2 flex-1">
                    <h3 class="font-bold text-warm-900">Lakukan Transfer & Unggah Bukti Pembayaran</h3>
                    <p class="text-sm text-warm-700">Transfer sesuai nominal ke nomor rekening masjid yang tertera. Setelah transfer berhasil, unggah foto/screenshot bukti transfer pada form konfirmasi di halaman yang sama.</p>
                    <div class="border border-amber-200 bg-amber-50 rounded-xl p-4 text-xs text-amber-800 space-y-1">
                        <div class="font-bold"><i class="fa-solid fa-circle-info mr-1"></i> Catatan Verifikasi:</div>
                        <div>Setelah diunggah, status donasi Anda akan menjadi "Pending" dan diverifikasi oleh pengurus masjid dalam 1x24 jam. Anda akan menerima notifikasi via Email/WhatsApp beserta link E-Kwitansi resmi setelah disetujui.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Portal Donatur -->
    <div id="portal-donatur" class="space-y-6">
        <div class="border-l-4 border-antique-500 pl-4">
            <h2 class="text-2xl font-bold text-warm-900 font-classic">Portal Donatur & E-Kwitansi Resmi</h2>
            <p class="text-sm text-warm-700 mt-1">Cara melacak riwayat donasi dan mengunduh kwitansi digital.</p>
        </div>

        <div class="bg-white rounded-2xl border border-warm-200 p-6 space-y-6">
            <div class="flex items-start gap-4">
                <div class="step-number">1</div>
                <div class="space-y-2 flex-1">
                    <h3 class="font-bold text-warm-900">Akses Portal Donatur</h3>
                    <p class="text-sm text-warm-700">Kunjungi halaman <code class="bg-warm-100 px-1.5 py-0.5 rounded text-xs">donatur/portal-donatur.php</code> atau login menggunakan akun Anda untuk melihat seluruh rekam jejak infak yang pernah disalurkan.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="step-number">2</div>
                <div class="space-y-2 flex-1">
                    <h3 class="font-bold text-warm-900">Cetak & Unduh E-Kwitansi</h3>
                    <p class="text-sm text-warm-700">Pada riwayat donasi yang statusnya <strong>"Berhasil / Disetujui"</strong>, klik tombol <strong>"Unduh Kwitansi"</strong> untuk membuka halaman <code class="bg-warm-100 px-1.5 py-0.5 rounded text-xs">donatur/kwitansi.php</code> yang dilengkapi QR Code verifikasi keaslian.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Transparansi Kas -->
    <div id="transparansi" class="space-y-6">
        <div class="border-l-4 border-antique-500 pl-4">
            <h2 class="text-2xl font-bold text-warm-900 font-classic">Cek Transparansi Kas & Laporan Keuangan</h2>
            <p class="text-sm text-warm-700 mt-1">Melihat rincian pemasukan dan pengeluaran kas masjid secara real-time.</p>
        </div>

        <div class="bg-white rounded-2xl border border-warm-200 p-6 space-y-6">
            <p class="text-sm text-warm-700">
                Menu <strong>Transparansi</strong> (<code class="bg-warm-100 px-1.5 py-0.5 rounded text-xs">home/transparansi.php</code>) menyajikan laporan keuangan terbuka untuk jamaah. Anda dapat memfilter laporan berdasarkan bulan, tahun, atau jenis kategori kas (Kas Umum, Kotak Amal Jumat, Zakat, Pembangunan, dll).
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-warm-50 p-4 rounded-xl border border-warm-200 text-center">
                    <div class="text-emerald-600 font-bold text-lg mb-1"><i class="fa-solid fa-arrow-down-long"></i> Total Pemasukan</div>
                    <p class="text-xs text-warm-600">Akumulasi seluruh infak, sedekah, dan donasi terverifikasi.</p>
                </div>
                <div class="bg-warm-50 p-4 rounded-xl border border-warm-200 text-center">
                    <div class="text-rose-600 font-bold text-lg mb-1"><i class="fa-solid fa-arrow-up-long"></i> Total Pengeluaran</div>
                    <p class="text-xs text-warm-600">Rincian penggunaan dana untuk operasional dan sosial.</p>
                </div>
                <div class="bg-warm-50 p-4 rounded-xl border border-warm-200 text-center">
                    <div class="text-antique-600 font-bold text-lg mb-1"><i class="fa-solid fa-wallet"></i> Saldo Kas Akhir</div>
                    <p class="text-xs text-warm-600">Sisa saldo riil kas masjid yang tersedia saat ini.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Fitur Lain -->
    <div id="fitur-lain" class="space-y-6">
        <div class="border-l-4 border-antique-500 pl-4">
            <h2 class="text-2xl font-bold text-warm-900 font-classic">Berita, Kajian & Kontak</h2>
            <p class="text-sm text-warm-700 mt-1">Informasi kegiatan masjid dan cara menghubungi pengurus.</p>
        </div>

        <div class="bg-white rounded-2xl border border-warm-200 p-6 space-y-4 text-sm text-warm-700">
            <p><strong>Berita & Pengumuman:</strong> Baca informasi terbaru seputar kegiatan DKM dan artikel keIslaman di menu Berita.</p>
            <p><strong>Jadwal Kajian:</strong> Lihat jadwal taklim, kajian rutin, dan pemateri di menu Kajian.</p>
            <p><strong>Kontak & Lokasi:</strong> Hubungi pengurus melalui form pesan di menu Kontak atau langsung kunjungi lokasi masjid yang tertera pada peta footer.</p>
        </div>
    </div>

    <!-- FAQ Section -->
    <div id="faq" class="space-y-6">
        <div class="border-l-4 border-antique-500 pl-4">
            <h2 class="text-2xl font-bold text-warm-900 font-classic">Pertanyaan Umum (FAQ)</h2>
            <p class="text-sm text-warm-700 mt-1">Pertanyaan yang sering diajukan oleh donatur dan jamaah</p>
        </div>
        <div class="space-y-3">
            <details class="bg-white rounded-xl border border-warm-200 p-4 group">
                <summary class="font-bold text-warm-900 cursor-pointer flex justify-between items-center">
                    <span>Bagaimana cara melakukan donasi online?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-antique-600 transition-transform group-open:rotate-180"></i>
                </summary>
                <p class="text-sm text-warm-700 mt-3 pt-3 border-t border-warm-100">Pilih menu Donasi, isi formulir nama, nominal, pilih metode pembayaran (QRIS/Transfer), lalu unggah bukti transfer pada halaman konfirmasi.</p>
            </details>

            <details class="bg-white rounded-xl border border-warm-200 p-4 group">
                <summary class="font-bold text-warm-900 cursor-pointer flex justify-between items-center">
                    <span>Berapa lama waktu verifikasi donasi?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-antique-600 transition-transform group-open:rotate-180"></i>
                </summary>
                <p class="text-sm text-warm-700 mt-3 pt-3 border-t border-warm-100">Verifikasi oleh pengurus masjid dilakukan maksimal 1x24 jam setelah bukti pembayaran diunggah.</p>
            </details>

            <details class="bg-white rounded-xl border border-warm-200 p-4 group">
                <summary class="font-bold text-warm-900 cursor-pointer flex justify-between items-center">
                    <span>Apakah e-kwitansi donasi sah dan resmi?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-antique-600 transition-transform group-open:rotate-180"></i>
                </summary>
                <p class="text-sm text-warm-700 mt-3 pt-3 border-t border-warm-100">Ya, setiap e-kwitansi dilengkapi nomor unik dan QR Code yang dapat dipindai untuk mencocokkan data langsung dengan database masjid.</p>
            </details>

            <details class="bg-white rounded-xl border border-warm-200 p-4 group">
                <summary class="font-bold text-warm-900 cursor-pointer flex justify-between items-center">
                    <span>Bagaimana cara melihat transparansi keuangan masjid?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-antique-600 transition-transform group-open:rotate-180"></i>
                </summary>
                <p class="text-sm text-warm-700 mt-3 pt-3 border-t border-warm-100">Anda dapat membuka menu Transparansi untuk melihat rincian pemasukan, pengeluaran, dan saldo kas akhir masjid secara real-time.</p>
            </details>

            <details class="bg-white rounded-xl border border-warm-200 p-4 group">
                <summary class="font-bold text-warm-900 cursor-pointer flex justify-between items-center">
                    <span>Bagaimana jika saya salah memasukkan nominal donasi?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-antique-600 transition-transform group-open:rotate-180"></i>
                </summary>
                <p class="text-sm text-warm-700 mt-3 pt-3 border-t border-warm-100">Anda dapat menghubungi pengurus melalui menu Kontak atau WhatsApp resmi masjid untuk bantuan koreksi data transaksi.</p>
            </details>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-warm-200 p-8 text-center">
        <div class="w-16 h-16 bg-antique-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-headset text-2xl text-antique-600"></i>
        </div>
        <h3 class="text-xl font-bold text-warm-900 mb-2">Butuh Bantuan?</h3>
        <p class="text-sm text-warm-700 mb-6">Tim support siap membantu Anda</p>
        <div class="flex flex-wrap justify-center gap-3">
            <a href="<?= base_url() ?>/kontak" class="px-6 py-2.5 bg-cypress-900 hover:bg-cypress-800 text-white rounded-lg font-semibold text-sm transition">
                Hubungi Kami
            </a>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
