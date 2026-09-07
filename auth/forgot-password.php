<?php
// forgot-password.php - Permintaan Reset Kata Sandi via OTP
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pesan = '';
$tipe = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $pesan = 'Mohon masukkan alamat email Anda.';
        $tipe = 'error';
    } else {
        $stmt = $pdo->prepare("SELECT id, nama_lengkap FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            try {
                // Generate OTP dan simpan email di session
                $otp = generate_otp($email, 'forgot_password');
                send_otp_email($email, $user['nama_lengkap'], $otp, 'forgot_password');

                $_SESSION['pending_forgot'] = ['email' => $email, 'nama' => $user['nama_lengkap']];

                header('Location: verify-otp.php?purpose=forgot_password');
                exit;
            } catch (\Exception $e) {
                $pesan = 'Gagal mengirim kode OTP ke email Anda. Pastikan email benar dan coba lagi.';
                $tipe  = 'error';
            }
        } else {
            $pesan = 'Alamat email tidak ditemukan dalam pangkalan data kami.';
            $tipe = 'error';
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
    <title>Lupa Kata Sandi · <?= e($profil['nama_masjid']) ?></title>
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

        <!-- Kartu Utama Klasik — mirip login -->
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
                            إِنَّ مَعَ الْعُسْرِ يُسْرًا
                        </p>
                        <span class="text-xs text-antique-300/80 block mt-1">"Sesungguhnya bersama kesulitan ada kemudahan."</span>
                    </div>

                    <div class="rounded-xl bg-cypress-900/60 border border-white/10 p-4 text-xs leading-relaxed text-emerald-100/90 backdrop-blur-sm">
                        <p class="italic">
                            Masukkan alamat email yang terdaftar. Kode OTP 6 digit akan dikirimkan untuk memverifikasi identitas Anda.
                        </p>
                        <span class="block mt-2 font-medium text-antique-300 text-[11px]">⏱ Kode berlaku selama 10 menit</span>
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

            <!-- Sisi Kanan: Formulir Lupa Sandi -->
            <div class="md:col-span-7 p-8 sm:p-10 lg:p-12 flex flex-col justify-center bg-white">
                <div class="max-w-md mx-auto w-full">

                    <div class="mb-8">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-antique-50 border border-antique-300/60 text-antique-700 text-xs font-semibold uppercase tracking-wider mb-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-cypress-700"></span>
                            Pemulihan Kata Sandi
                        </div>
                        <h2 class="text-2xl font-bold text-warm-900 tracking-tight">Lupa Kata Sandi?</h2>
                        <p class="text-sm text-warm-800/70 mt-1.5">Masukkan email Anda dan kami akan mengirimkan kode OTP untuk mereset kata sandi.</p>
                    </div>

                    <?php if ($pesan): ?>
                    <div class="mb-6 p-4 rounded-xl <?= $tipe === 'success' ? 'bg-emerald-50/90 border-emerald-200/80 text-emerald-900' : 'bg-amber-50/90 border-amber-200/80 text-amber-900' ?> border flex items-start gap-3 text-sm shadow-sm">
                        <svg class="w-5 h-5 <?= $tipe === 'success' ? 'text-emerald-600' : 'text-amber-600' ?> shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="leading-snug">
                            <strong class="font-semibold block text-xs uppercase tracking-wide <?= $tipe === 'success' ? 'text-emerald-800' : 'text-amber-800' ?>">
                                <?= $tipe === 'success' ? 'Berhasil' : 'Perhatian' ?>
                            </strong>
                            <?= e($pesan) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="forgot-password.php" class="space-y-5">
                        <div>
                            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-2">
                                Alamat Email Terdaftar
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-warm-800/40">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required
                                       placeholder="nama@email.com" autofocus
                                       class="w-full pl-11 pr-4 py-3 rounded-xl border border-warm-200 bg-warm-50/60 text-warm-900 placeholder-warm-800/40 text-sm focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full py-3.5 px-6 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-semibold text-sm tracking-wide shadow-md shadow-cypress-900/15 active:scale-[0.99] transition-luxury flex items-center justify-center gap-2">
                            <span>Kirim Kode OTP</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </form>

                    <div class="mt-6 pt-5 border-t border-warm-100 text-center text-xs text-warm-800/70">
                        Ingat kata sandi Anda? <a href="login.php" class="text-cypress-700 font-bold hover:underline">Kembali ke Login</a>
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
