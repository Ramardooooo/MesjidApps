<?php
// register.php - Pendaftaran Akun Donatur Baru (Scope 11)
require_once __DIR__ . '/../config/database.php';
mulai_session();

if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'donatur') {
        header('Location: ../donatur/portal-donatur.php');
    } else {
        header('Location: ../admin/dashboard.php');
    }
    exit;
}

$profil = get_profil_masjid();
$pesan = '';
$tipe  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirm  = $_POST['password_confirm'] ?? '';

    if (empty($nama) || empty($email) || empty($password)) {
        $pesan = 'Nama lengkap, email, dan kata sandi wajib diisi.';
        $tipe  = 'error';
    } elseif ($password !== $konfirm) {
        $pesan = 'Konfirmasi kata sandi tidak cocok.';
        $tipe  = 'error';
    } elseif (strlen($password) < 6) {
        $pesan = 'Kata sandi minimal 6 karakter demi keamanan.';
        $tipe  = 'error';
    } else {
        // Buat username jika kosong
        if (empty($username)) {
            $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0])) . rand(10, 99);
        }

        // Cek apakah username atau email sudah terdaftar
        $stmtCek = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmtCek->execute([$username, $email]);
        if ($stmtCek->fetch()) {
            $pesan = 'Username atau email tersebut sudah terdaftar. Silakan gunakan akun lain atau login.';
            $tipe  = 'error';
        } else {
            // Simpan data pendaftaran sementara di session
            $_SESSION['pending_register'] = [
                'nama'     => $nama,
                'email'    => $email,
                'no_hp'    => $no_hp,
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ];

            // Generate & kirim OTP
            try {
                $otp = generate_otp($email, 'register');
                send_otp_email($email, $nama, $otp, 'register');
                header('Location: verify-otp.php?purpose=register');
                exit;
            } catch (\Exception $e) {
                unset($_SESSION['pending_register']);
                $pesan = 'Gagal mengirim kode OTP ke email Anda. Pastikan email benar dan coba lagi.';
                $tipe  = 'error';
            }
        }
    }
}

// Konfigurasi Tanggal
$namaHari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w')];
$namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][date('n')];
$tanggalMasehi = $namaHari . ', ' . date('j') . ' ' . $namaBulan . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Donatur · <?= e($profil['nama_masjid']) ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts & Custom Classic Theme -->
    <link rel="stylesheet" href="../assets/css/classic-theme.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cypress: {
                            50: '#f2f7f4',
                            100: '#e1ede6',
                            200: '#c3dbcd',
                            600: '#234b38',
                            700: '#1b3a2b',
                            800: '#142a1f',
                            900: '#0d1d15',
                        },
                        antique: {
                            50: '#fdfbf7',
                            100: '#f8f4ec',
                            300: '#dfc896',
                            500: '#c5a059',
                            600: '#b08a42',
                            700: '#8c6b2d',
                        },
                        warm: {
                            50: '#fbf9f5',
                            100: '#f5f1e8',
                            200: '#ebe4d3',
                            800: '#242b26',
                            900: '#191f1b',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui'],
                        classic: ['Cinzel', 'serif'],
                        arabic: ['Amiri', 'serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="pattern-arabesque-light min-h-screen flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8 text-warm-800">

    <div class="w-full max-w-4xl mx-auto my-6">

        <!-- Kartu Utama Klasik -->
        <div class="bg-white rounded-2xl shadow-xl shadow-stone-900/5 border border-antique-300/40 overflow-hidden grid grid-cols-1 md:grid-cols-12 transition-all">

            <!-- Sisi Kiri: Visual Klasik & Identitas Masjid -->
            <div class="md:col-span-5 pattern-arabesque-dark p-8 md:p-10 text-white flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 pointer-events-none opacity-20">
                    <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="100" cy="0" r="80" stroke="#c5a059" stroke-width="1.5" />
                        <circle cx="100" cy="0" r="60" stroke="#c5a059" stroke-width="0.8" stroke-dasharray="3 3" />
                    </svg>
                </div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3.5 mb-6">
                        <div class="w-14 h-14 p-2.5 rounded-xl bg-cypress-900/80 border border-antique-500/40 shadow-inner flex items-center justify-center text-antique-300 shrink-0">
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 0c-3 3-5 6-5 9a5 5 0 0010 0c0-3-2-6-5-9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 21h16M7 21v-4m10 4v-4M9 13h6"/>
                                <circle cx="12" cy="2" r="0.75" fill="currentColor"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-widest text-antique-300 font-semibold block"><?= e($profil['sebutan']) ?></span>
                            <h1 class="font-classic text-xl font-bold tracking-wide text-white"><?= strtoupper(e($profil['nama_masjid'])) ?></h1>
                        </div>
                    </div>

                    <div class="my-6 py-3 border-y border-white/10 text-center">
                        <p class="font-arabic text-xl md:text-2xl text-antique-100 tracking-wider">
                            أَهْلًا وَسَهْلًا
                        </p>
                    </div>

                    <div class="rounded-xl bg-cypress-900/60 border border-white/10 p-4 text-xs leading-relaxed text-emerald-100/90 backdrop-blur-sm">
                        <p class="italic">
                            "Sebaik-baik manusia adalah yang paling bermanfaat bagi manusia lainnya."
                        </p>
                        <span class="block mt-2 font-medium text-antique-300 text-[11px]">— HR. Ahmad &amp; ath-Thabrani</span>
                    </div>
                </div>

                <div class="relative z-10 pt-6 mt-6 border-t border-white/10 text-xs text-emerald-200/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-antique-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span><?= e($tanggalMasehi) ?></span>
                    </div>
                    <a href="../index.php" class="px-2 py-0.5 rounded bg-cypress-900/80 border border-antique-500/30 text-[10px] text-antique-300 hover:bg-cypress-800 transition">
                        ← Beranda
                    </a>
                </div>
            </div>

            <!-- Sisi Kanan: Formulir Pendaftaran Donatur -->
            <div class="md:col-span-7 p-8 sm:p-10 lg:p-12 flex flex-col justify-center bg-white">
                <div class="max-w-md mx-auto w-full">
                    <div class="mb-8">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-antique-50 border border-antique-300/60 text-antique-700 text-xs font-semibold uppercase tracking-wider mb-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-cypress-700"></span>
                            Daftar Akun Donatur
                        </div>
                        <h2 class="text-2xl font-bold text-warm-900 tracking-tight">Buat Akun Baru</h2>
                        <p class="text-sm text-warm-800/70 mt-1.5">Pantau riwayat infaq Anda &amp; unduh kwitansi resmi digital.</p>
                    </div>

                    <?php if ($pesan): ?>
                    <div class="mb-6 p-4 rounded-xl bg-amber-50/90 border border-amber-200/80 text-amber-900 flex items-start gap-3 text-sm shadow-sm">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="leading-snug">
                            <strong class="font-semibold block text-xs uppercase tracking-wide text-amber-800">Perhatian</strong>
                            <?= e($pesan) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="register.php" class="space-y-4">
                        <div>
                            <label for="nama_lengkap" class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-2">Nama Lengkap</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-warm-800/40">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= e($_POST['nama_lengkap'] ?? '') ?>" required placeholder="Contoh: H. Bambang Subagyo"
                                       class="w-full pl-11 pr-4 py-3 rounded-xl border border-warm-200 bg-warm-50/60 text-warm-900 placeholder-warm-800/40 text-sm focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury" autofocus>
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-2">Alamat Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-warm-800/40">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required placeholder="nama@email.com"
                                       class="w-full pl-11 pr-4 py-3 rounded-xl border border-warm-200 bg-warm-50/60 text-warm-900 placeholder-warm-800/40 text-sm focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
                            </div>
                        </div>

                        <div>
                            <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-2">Nomor WhatsApp / HP</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-warm-800/40">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                                    </svg>
                                </div>
                                <input type="text" id="no_hp" name="no_hp" value="<?= e($_POST['no_hp'] ?? '') ?>" placeholder="Contoh: 081234567890"
                                       class="w-full pl-11 pr-4 py-3 rounded-xl border border-warm-200 bg-warm-50/60 text-warm-900 placeholder-warm-800/40 text-sm focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-2">Kata Sandi</label>
                                <input type="password" id="password" name="password" required placeholder="Minimal 6 digit"
                                       class="w-full px-4 py-3 rounded-xl border border-warm-200 bg-warm-50/60 text-warm-900 placeholder-warm-800/40 text-sm focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
                            </div>
                            <div>
                                <label for="password_confirm" class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-2">Konfirmasi Sandi</label>
                                <input type="password" id="password_confirm" name="password_confirm" required placeholder="Ulangi sandi"
                                       class="w-full px-4 py-3 rounded-xl border border-warm-200 bg-warm-50/60 text-warm-900 placeholder-warm-800/40 text-sm focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full py-3.5 px-6 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-semibold text-sm tracking-wide shadow-md shadow-cypress-900/15 active:scale-[0.99] transition-luxury flex items-center justify-center gap-2">
                            <span>Daftar Sekarang</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </form>

                    <div class="mt-6 pt-5 border-t border-warm-100 text-center text-xs text-warm-800/70">
                        Sudah memiliki akun? <a href="login.php" class="text-cypress-700 font-bold hover:underline">Masuk di Sini</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-warm-800/60">
            <p>© <?= date('Y') ?> <?= e($profil['nama_masjid']) ?> · Menjaga Amanah, Memakmurkan Masjid</p>
        </div>
    </div>

</body>
</html>
