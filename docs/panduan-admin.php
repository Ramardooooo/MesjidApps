<?php
require_once __DIR__ . '/../config/database.php';
mulai_session();

// Proteksi: Hanya role admin, bendahara, atau content_admin yang bisa akses
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'bendahara', 'content_admin'])) {
    header('Location: ' . base_url() . '/403.php');
    exit;
}

$profil = get_profil_masjid();
$pageTitle = 'Panduan Admin Panel · ' . $profil['nama_masjid'];
$activeNav = 'panduan';

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
        <h1 class="text-3xl sm:text-4xl font-bold mb-3 font-classic tracking-wide">Panduan Admin Panel</h1>
        <p class="text-stone-300 text-sm sm:text-base max-w-2xl mx-auto">
            Panduan lengkap untuk mengelola website masjid, pembukuan kas, verifikasi donasi, dan konten
        </p>
    </div>
</section>

<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">

    <!-- Quick Links -->
    <div class="bg-white rounded-2xl border border-warm-200 p-6 shadow-soft">
        <h2 class="text-xl font-bold text-warm-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-list text-antique-600"></i>
            Daftar Isi
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <a href="#login" class="flex items-center gap-2 p-3 rounded-lg border border-warm-200 hover:border-antique-300 hover:bg-warm-50 transition">
                <i class="fa-solid fa-arrow-right-to-bracket text-antique-600"></i>
                <span>Login & Akses Dashboard</span>
            </a>
            <a href="#donasi" class="flex items-center gap-2 p-3 rounded-lg border border-warm-200 hover:border-antique-300 hover:bg-warm-50 transition">
                <i class="fa-solid fa-check-to-slot text-antique-600"></i>
                <span>Verifikasi Donasi</span>
            </a>
            <a href="#pembukuan" class="flex items-center gap-2 p-3 rounded-lg border border-warm-200 hover:border-antique-300 hover:bg-warm-50 transition">
                <i class="fa-solid fa-book-journal-whills text-antique-600"></i>
                <span>Pembukuan Kas</span>
            </a>
            <a href="#konten" class="flex items-center gap-2 p-3 rounded-lg border border-warm-200 hover:border-antique-300 hover:bg-warm-50 transition">
                <i class="fa-solid fa-newspaper text-antique-600"></i>
                <span>Manajemen Konten</span>
            </a>
            <a href="#laporan" class="flex items-center gap-2 p-3 rounded-lg border border-warm-200 hover:border-antique-300 hover:bg-warm-50 transition">
                <i class="fa-solid fa-chart-pie text-antique-600"></i>
                <span>Laporan Keuangan</span>
            </a>
            <a href="#profil" class="flex items-center gap-2 p-3 rounded-lg border border-warm-200 hover:border-antique-300 hover:bg-warm-50 transition">
                <i class="fa-solid fa-user-gear text-antique-600"></i>
                <span>Pengaturan Profil</span>
            </a>
        </div>
    </div>

    <!-- Section 1: Login -->
    <div id="login" class="space-y-6">
        <div class="border-l-4 border-antique-500 pl-4">
            <h2 class="text-2xl font-bold text-warm-900 font-classic">Login & Akses Dashboard</h2>
            <p class="text-sm text-warm-700 mt-1">Cara masuk ke admin panel dan memahami dashboard utama</p>
        </div>

        <div class="bg-white rounded-2xl border border-warm-200 p-6 space-y-6">
            <div class="flex items-start gap-4">
                <div class="step-number">1</div>
                <div class="flex-1">
                    <h3 class="font-bold text-warm-900 mb-2">Buka Halaman Login</h3>
                    <p class="text-sm text-warm-700 mb-3">Akses halaman login melalui URL: <code class="px-2 py-1 bg-warm-100 rounded text-xs font-mono"><?= base_url() ?>/auth/login</code></p>
                    <div class="bg-warm-50 border border-warm-200 rounded-lg p-4">
                        <p class="text-xs text-warm-700"><strong>Akun Default:</strong></p>
                        <ul class="text-xs text-warm-700 mt-2 space-y-1">
                            <li>• <strong>Admin:</strong> username <code>admin</code> / password <code>admin123</code></li>
                            <li>• <strong>Bendahara:</strong> username <code>bendahara</code> / password <code>bendahara123</code></li>
                            <li>• <strong>Content Admin:</strong> username <code>konten</code> / password <code>konten123</code></li>
                        </ul>
                        <p class="text-xs text-red-600 mt-3"><i class="fa-solid fa-triangle-exclamation"></i> <strong>Penting:</strong> Segera ganti password default setelah login pertama kali!</p>
                    </div>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="step-number">2</div>
                <div class="flex-1">
                    <h3 class="font-bold text-warm-900 mb-2">Masukkan Username & Password</h3>
                    <p class="text-sm text-warm-700">Username bisa berupa username atau email yang terdaftar. Password minimal 6 karakter.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="step-number">3</div>
                <div class="flex-1">
                    <h3 class="font-bold text-warm-900 mb-2">Dashboard Utama</h3>
                    <p class="text-sm text-warm-700 mb-3">Setelah login berhasil, Anda akan diarahkan ke dashboard yang menampilkan:</p>
                    <ul class="text-sm text-warm-700 space-y-2 ml-4">
                        <li>✓ <strong>Saldo Kas Terkini</strong> — Total kas masjid realtime</li>
                        <li>✓ <strong>Donasi Pending</strong> — Donasi yang menunggu verifikasi</li>
                        <li>✓ <strong>Grafik Keuangan</strong> — Trend pemasukan & pengeluaran</li>
                        <li>✓ <strong>Transaksi Terbaru</strong> — 10 transaksi terakhir</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Verifikasi Donasi -->
    <div id="donasi" class="space-y-6">
        <div class="border-l-4 border-antique-500 pl-4">
            <h2 class="text-2xl font-bold text-warm-900 font-classic">Verifikasi Donasi</h2>
            <p class="text-sm text-warm-700 mt-1">Cara memverifikasi atau menolak donasi yang masuk</p>
        </div>

        <div class="bg-white rounded-2xl border border-warm-200 p-6 space-y-6">
            <div class="flex items-start gap-4">
                <div class="step-number">1</div>
                <div class="flex-1">
                    <h3 class="font-bold text-warm-900 mb-2">Buka Menu Verifikasi Donasi</h3>
                    <p class="text-sm text-warm-700">Di sidebar kiri, klik menu <strong>"Verifikasi Donasi"</strong>. Badge merah akan muncul jika ada donasi pending.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="step-number">2</div>
                <div class="flex-1">
                    <h3 class="font-bold text-warm-900 mb-2">Review Data Donasi</h3>
                    <p class="text-sm text-warm-700 mb-3">Pada tabel verifikasi, Anda akan melihat:</p>
                    <ul class="text-sm text-warm-700 space-y-1 ml-4">
                        <li>• Nama donatur & kontak</li>
                        <li>• Nominal donasi</li>
                        <li>• Metode pembayaran (QRIS/Transfer)</li>
                        <li>• Bukti transfer (klik untuk zoom)</li>
                        <li>• Tanggal transaksi</li>
                    </ul>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="step-number">3</div>
                <div class="flex-1">
                    <h3 class="font-bold text-warm-900 mb-2">Verifikasi atau Tolak</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                            <p class="text-sm font-bold text-emerald-800 mb-2"><i class="fa-solid fa-check-circle"></i> Verifikasi (Setujui)</p>
                            <p class="text-xs text-emerald-700">Jika bukti valid, klik tombol hijau "Verifikasi". Donasi akan:</p>
                            <ul class="text-xs text-emerald-700 mt-2 space-y-1">
                                <li>• Masuk ke buku kas otomatis</li>
                                <li>• Status kwitansi jadi "Diverifikasi"</li>
                                <li>• Email notifikasi terkirim ke donatur</li>
                            </ul>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-sm font-bold text-red-800 mb-2"><i class="fa-solid fa-times-circle"></i> Tolak</p>
                            <p class="text-xs text-red-700">Jika bukti tidak valid/nominal tidak sesuai, klik tombol merah "Tolak". Donasi akan:</p>
                            <ul class="text-xs text-red-700 mt-2 space-y-1">
                                <li>• Status jadi "Ditolak"</li>
                                <li>• Tidak masuk ke kas</li>
                                <li>• Email penolakan terkirim ke donatur</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Pembukuan Kas -->
    <div id="pembukuan" class="space-y-6">
        <div class="border-l-4 border-antique-500 pl-4">
            <h2 class="text-2xl font-bold text-warm-900 font-classic">Pembukuan Kas</h2>
            <p class="text-sm text-warm-700 mt-1">Cara mencatat pemasukan & pengeluaran kas masjid</p>
        </div>

        <div class="bg-white rounded-2xl border border-warm-200 p-6 space-y-6">
            <div class="flex items-start gap-4">
                <div class="step-number">1</div>
                <div class="flex-1">
                    <h3 class="font-bold text-warm-900 mb-2">Buka Menu Buku Kas Transaksi</h3>
                    <p class="text-sm text-warm-700">Di sidebar, klik <strong>"Buku Kas Transaksi"</strong>. Anda akan melihat semua transaksi kas masjid.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="step-number">2</div>
                <div class="flex-1">
                    <h3 class="font-bold text-warm-900 mb-2">Tambah Transaksi Baru</h3>
                    <p class="text-sm text-warm-700 mb-3">Klik tombol <strong>"+ Tambah Transaksi"</strong> dan pilih jenis:</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                        <div class="border border-emerald-200 rounded-lg p-4">
                            <p class="text-sm font-bold text-emerald-700 mb-2">💰 Pemasukan</p>
                            <p class="text-xs text-warm-700">Donasi manual, infaq jumat, zakat fitrah, hibah, dll.</p>
                        </div>
                        <div class="border border-red-200 rounded-lg p-4">
                            <p class="text-sm font-bold text-red-700 mb-2">💸 Pengeluaran</p>
                            <p class="text-xs text-warm-700">Operasional, listrik, air, renovasi, honor, dll.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="step-number">3</div>
                <div class="flex-1">
                    <h3 class="font-bold text-warm-900 mb-2">Isi Form Transaksi</h3>
                    <ul class="text-sm text-warm-700 space-y-1 ml-4">
                        <li>• <strong>Tanggal:</strong> Tanggal transaksi terjadi</li>
                        <li>• <strong>Kategori:</strong> Pilih kategori sesuai jenis transaksi</li>
                        <li>• <strong>Nominal:</strong> Jumlah uang dalam Rupiah</li>
                        <li>• <strong>Keterangan:</strong> Deskripsi detail transaksi</li>
                        <li>• <strong>Bukti:</strong> Upload foto nota/kwitansi (opsional)</li>
                    </ul>
                </div>
            </div>

            <div class="bg-antique-50 border border-antique-200 rounded-lg p-4 mt-4">
                <p class="text-xs font-bold text-antique-800 mb-2"><i class="fa-solid fa-lightbulb"></i> Tips Pembukuan:</p>
                <ul class="text-xs text-antique-700 space-y-1">
                    <li>• Catat transaksi secara konsisten setiap hari</li>
                    <li>• Selalu upload bukti untuk transparansi</li>
                    <li>• Gunakan keterangan yang jelas & detail</li>
                    <li>• Cek saldo kas secara berkala untuk memastikan akurasi</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Section 4: Manajemen Konten -->
    <div id="konten" class="space-y-6">
        <div class="border-l-4 border-antique-500 pl-4">
            <h2 class="text-2xl font-bold text-warm-900 font-classic">Manajemen Konten</h2>
            <p class="text-sm text-warm-700 mt-1">Kelola berita, program donasi, video kajian, dan konten website</p>
        </div>

        <div class="bg-white rounded-2xl border border-warm-200 p-6 space-y-6">
            <div>
                <h3 class="font-bold text-warm-900 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-newspaper text-antique-600"></i>
                    Berita & Artikel
                </h3>
                <p class="text-sm text-warm-700 mb-3">Menu: <strong>"Warta & Berita"</strong></p>
                <ul class="text-sm text-warm-700 space-y-1 ml-4">
                    <li>• Klik <strong>"+ Tambah Berita"</strong> untuk artikel baru</li>
                    <li>• Upload thumbnail (rekomendasi: 1200x630px)</li>
                    <li>• Tulis konten lengkap dengan editor</li>
                    <li>• Pilih kategori & status publikasi</li>
                    <li>• Klik "Publikasikan" untuk tampilkan di website</li>
                </ul>
            </div>

            <hr class="border-warm-200">

            <div>
                <h3 class="font-bold text-warm-900 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-hand-holding-dollar text-antique-600"></i>
                    Program Donasi
                </h3>
                <p class="text-sm text-warm-700 mb-3">Menu: <strong>"Program Donasi"</strong></p>
                <ul class="text-sm text-warm-700 space-y-1 ml-4">
                    <li>• Buat program baru dengan target donasi</li>
                    <li>• Upload gambar program (1200x800px)</li>
                    <li>• Set tanggal mulai & selesai (opsional)</li>
                    <li>• Sistem akan otomatis hitung progress bar</li>
                    <li>• Edit/nonaktifkan program kapan saja</li>
                </ul>
            </div>

            <hr class="border-warm-200">

            <div>
                <h3 class="font-bold text-warm-900 mb-3 flex items-center gap-2">
                    <i class="fa-brands fa-youtube text-antique-600"></i>
                    Video Kajian
                </h3>
                <p class="text-sm text-warm-700 mb-3">Menu: <strong>"Integrasi YouTube"</strong></p>
                <ul class="text-sm text-warm-700 space-y-1 ml-4">
                    <li>• Copy <strong>Video ID</strong> dari URL YouTube (setelah <code>v=</code>)</li>
                    <li>• Contoh: <code>https://youtube.com/watch?v=<strong>dQw4w9WgXcQ</strong></code></li>
                    <li>• Paste Video ID ke form</li>
                    <li>• Pilih kategori (Kajian/Live Streaming/Dokumentasi)</li>
                    <li>• Video akan auto-embed di halaman Kajian</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Section 5: Laporan -->
    <div id="laporan" class="space-y-6">
        <div class="border-l-4 border-antique-500 pl-4">
            <h2 class="text-2xl font-bold text-warm-900 font-classic">Laporan Keuangan</h2>
            <p class="text-sm text-warm-700 mt-1">Buat dan cetak laporan keuangan periode tertentu</p>
        </div>

        <div class="bg-white rounded-2xl border border-warm-200 p-6 space-y-4">
            <p class="text-sm text-warm-700">Menu: <strong>"Laporan Keuangan"</strong></p>
            
            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <span class="text-antique-600 font-bold">1.</span>
                    <p class="text-sm text-warm-700">Pilih <strong>Tanggal Mulai</strong> dan <strong>Tanggal Akhir</strong> periode laporan</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-antique-600 font-bold">2.</span>
                    <p class="text-sm text-warm-700">Klik <strong>"Tampilkan Laporan"</strong> untuk preview</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-antique-600 font-bold">3.</span>
                    <p class="text-sm text-warm-700">Klik <strong>"Cetak Laporan"</strong> untuk print atau save PDF</p>
                </div>
            </div>

            <div class="bg-warm-50 border border-warm-200 rounded-lg p-4 mt-4">
                <p class="text-xs font-bold text-warm-800 mb-2">Laporan berisi:</p>
                <ul class="text-xs text-warm-700 space-y-1">
                    <li>• Total pemasukan per kategori</li>
                    <li>• Total pengeluaran per kategori</li>
                    <li>• Saldo awal & saldo akhir periode</li>
                    <li>• Detail transaksi lengkap</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Section 6: Profil -->
    <div id="profil" class="space-y-6">
        <div class="border-l-4 border-antique-500 pl-4">
            <h2 class="text-2xl font-bold text-warm-900 font-classic">Pengaturan Profil</h2>
            <p class="text-sm text-warm-700 mt-1">Edit profil pribadi dan pengaturan masjid</p>
        </div>

        <div class="bg-white rounded-2xl border border-warm-200 p-6 space-y-6">
            <div>
                <h3 class="font-bold text-warm-900 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-user-gear text-antique-600"></i>
                    Profil Saya
                </h3>
                <p class="text-sm text-warm-700 mb-3">Klik <strong>nama Anda di sidebar bawah</strong> untuk edit profil pribadi:</p>
                <ul class="text-sm text-warm-700 space-y-1 ml-4">
                    <li>• Update nama, email, no HP</li>
                    <li>• Upload foto profil</li>
                    <li>• Tulis bio singkat</li>
                    <li>• Ganti password</li>
                    <li>• Set visibilitas profil publik (untuk halaman Tim Kami)</li>
                </ul>
            </div>

            <hr class="border-warm-200">

            <div>
                <h3 class="font-bold text-warm-900 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-mosque text-antique-600"></i>
                    Profil & DKM
                </h3>
                <p class="text-sm text-warm-700 mb-3">Menu: <strong>"Profil & DKM"</strong> (khusus Admin/Content Admin)</p>
                <ul class="text-sm text-warm-700 space-y-1 ml-4">
                    <li>• Edit profil masjid (nama, sejarah, visi misi)</li>
                    <li>• Update kontak (alamat, WhatsApp, email)</li>
                    <li>• Kelola akun sosial media</li>
                    <li>• Manajemen pengurus DKM (assign user admin ke posisi pengurus)</li>
                    <li>• Set saldo awal kas</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="bg-gradient-to-br from-cypress-50 to-antique-50 rounded-2xl border border-antique-200 p-8">
        <h2 class="text-xl font-bold text-warm-900 mb-6 flex items-center gap-2">
            <i class="fa-solid fa-circle-question text-antique-600"></i>
            FAQ (Frequently Asked Questions)
        </h2>
        
        <div class="space-y-3">
            <details class="bg-white rounded-xl border border-warm-200 p-4 group">
                <summary class="font-bold text-warm-900 cursor-pointer flex justify-between items-center">
                    <span>Bagaimana cara mengganti password?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-antique-600 transition-transform group-open:rotate-180"></i>
                </summary>
                <p class="text-sm text-warm-700 mt-3 pt-3 border-t border-warm-100">Klik nama Anda di sidebar bawah → Profil Saya → Isi "Kata Sandi Baru" → Simpan.</p>
            </details>

            <details class="bg-white rounded-xl border border-warm-200 p-4 group">
                <summary class="font-bold text-warm-900 cursor-pointer flex justify-between items-center">
                    <span>Kenapa donasi tidak bisa diverifikasi?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-antique-600 transition-transform group-open:rotate-180"></i>
                </summary>
                <p class="text-sm text-warm-700 mt-3 pt-3 border-t border-warm-100">Pastikan role Anda adalah Admin atau Bendahara. Content Admin tidak memiliki akses verifikasi donasi.</p>
            </details>

            <details class="bg-white rounded-xl border border-warm-200 p-4 group">
                <summary class="font-bold text-warm-900 cursor-pointer flex justify-between items-center">
                    <span>Bagaimana cara export laporan CSV?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-antique-600 transition-transform group-open:rotate-180"></i>
                </summary>
                <p class="text-sm text-warm-700 mt-3 pt-3 border-t border-warm-100">Di halaman Buku Kas Transaksi, klik tombol "Export CSV" di bagian atas tabel. File CSV akan otomatis terdownload.</p>
            </details>

            <details class="bg-white rounded-xl border border-warm-200 p-4 group">
                <summary class="font-bold text-warm-900 cursor-pointer flex justify-between items-center">
                    <span>Apakah bisa menghapus transaksi kas?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-antique-600 transition-transform group-open:rotate-180"></i>
                </summary>
                <p class="text-sm text-warm-700 mt-3 pt-3 border-t border-warm-100">Ya, hanya Admin yang bisa menghapus transaksi. Klik ikon "Hapus" di baris transaksi yang ingin dihapus.</p>
            </details>

            <details class="bg-white rounded-xl border border-warm-200 p-4 group">
                <summary class="font-bold text-warm-900 cursor-pointer flex justify-between items-center">
                    <span>Bagaimana cara mengubah kategori transaksi?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-antique-600 transition-transform group-open:rotate-180"></i>
                </summary>
                <p class="text-sm text-warm-700 mt-3 pt-3 border-t border-warm-100">Kategori transaksi dapat diubah hanya oleh Admin melalui database. Hubungi developer untuk custom kategori.</p>
            </details>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>
