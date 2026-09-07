<?php
// reset-password.php - Eksekusi Reset Kata Sandi (via OTP verification)
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pesan  = '';
$tipe   = '';

// Pastikan user sudah verifikasi OTP sebelumnya
$email = $_SESSION['otp_verified_email'] ?? '';
if (empty($email)) {
    header('Location: forgot-password.php');
    exit;
}

// Ambil data user
$stmt = $pdo->prepare("SELECT id, nama_lengkap, email FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    unset($_SESSION['otp_verified_email']);
    header('Location: forgot-password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password']         ?? '';
    $konfirm  = $_POST['password_confirm'] ?? '';

    if (empty($password) || strlen($password) < 6) {
        $pesan = 'Kata sandi baru minimal 6 karakter.';
        $tipe  = 'error';
    } elseif ($password !== $konfirm) {
        $pesan = 'Konfirmasi kata sandi tidak cocok.';
        $tipe  = 'error';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?")
            ->execute([$hash, $user['id']]);

        // Hapus session otorisasi
        unset($_SESSION['otp_verified_email']);

        header('Location: login.php?reset=success');
        exit;
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
    <title>Atur Kata Sandi Baru · <?= e($profil['nama_masjid']) ?></title>
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
                            50: '#f2f7f4', 100: '#e1ede6', 200: '#c3dbcd',
                            600: '#234b38', 700: '#1b3a2b', 800: '#142a1f', 900: '#0d1d15',
                        },
                        antique: {
                            50: '#fdfbf7', 100: '#f8f4ec', 300: '#dfc896',
                            500: '#c5a059', 600: '#b08a42', 700: '#8c6b2d',
                        },
                        warm: {
                            50: '#fbf9f5', 100: '#f5f1e8', 200: '#ebe4d3',
                            800: '#242b26', 900: '#191f1b',
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

        <!-- Kartu Utama — layout sama dengan login -->
        <div class="bg-white rounded-2xl shadow-xl shadow-stone-900/5 border border-antique-300/40 overflow-hidden grid grid-cols-1 md:grid-cols-12 transition-all">

            <!-- Sisi Kiri: Visual Klasik -->
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
                            وَاللَّهُ غَفُورٌ رَّحِيمٌ
                        </p>
                        <span class="text-[11px] text-antique-300/80 block mt-1">"Dan Allah Maha Pengampun lagi Maha Penyayang."</span>
                    </div>

                    <div class="rounded-xl bg-cypress-900/60 border border-white/10 p-4 text-xs leading-relaxed text-emerald-100/90 backdrop-blur-sm space-y-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span class="text-emerald-300 font-semibold">OTP Terverifikasi ✓</span>
                        </div>
                        <p class="text-emerald-100/80">Identitas Anda telah dikonfirmasi. Silakan buat kata sandi baru yang kuat untuk akun:</p>
                        <p class="font-semibold text-antique-300"><?= e($user['email']) ?></p>
                        <p class="text-[11px] text-emerald-200/60 mt-1">Gunakan minimal 6 karakter, kombinasi huruf dan angka.</p>
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

            <!-- Sisi Kanan: Form Reset Password -->
            <div class="md:col-span-7 p-8 sm:p-10 lg:p-12 flex flex-col justify-center bg-white">
                <div class="max-w-md mx-auto w-full">

                    <div class="mb-8">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-antique-50 border border-antique-300/60 text-antique-700 text-xs font-semibold uppercase tracking-wider mb-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-cypress-700"></span>
                            Atur Kata Sandi Baru
                        </div>
                        <h2 class="text-2xl font-bold text-warm-900 tracking-tight">Buat Kata Sandi Baru</h2>
                        <p class="text-sm text-warm-800/70 mt-1.5">Untuk akun <strong><?= e($user['nama_lengkap']) ?></strong> · <?= e($user['email']) ?></p>
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

                    <form method="POST" action="reset-password.php" class="space-y-5">
                        <div>
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-2">
                                Kata Sandi Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-warm-800/40">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input type="password" id="password" name="password" required
                                       placeholder="Minimal 6 karakter" autofocus
                                       class="w-full pl-11 pr-12 py-3 rounded-xl border border-warm-200 bg-warm-50/60 text-warm-900 placeholder-warm-800/40 text-sm focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
                                <button type="button" id="togglePass1"
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-warm-800/40 hover:text-warm-800/80 transition-luxury">
                                    <svg id="eye1On" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg id="eye1Off" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="password_confirm" class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-2">
                                Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-warm-800/40">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <input type="password" id="password_confirm" name="password_confirm" required
                                       placeholder="Ulangi kata sandi baru"
                                       class="w-full pl-11 pr-4 py-3 rounded-xl border border-warm-200 bg-warm-50/60 text-warm-900 placeholder-warm-800/40 text-sm focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury">
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full py-3.5 px-6 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-semibold text-sm tracking-wide shadow-md shadow-cypress-900/15 active:scale-[0.99] transition-luxury flex items-center justify-center gap-2">
                            <span>Simpan Kata Sandi Baru</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </form>

                    <div class="mt-6 pt-5 border-t border-warm-100 text-center text-xs text-warm-800/70">
                        <a href="login.php" class="text-cypress-700 font-bold hover:underline">← Kembali ke Halaman Login</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-warm-800/60">
            <p>© <?= date('Y') ?> <?= e($profil['nama_masjid']) ?> · Menjaga Amanah, Memakmurkan Masjid</p>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(btnId, inputId, onId, offId) {
            document.getElementById(btnId).addEventListener('click', () => {
                const inp = document.getElementById(inputId);
                const show = inp.type === 'password';
                inp.type = show ? 'text' : 'password';
                document.getElementById(onId).classList.toggle('hidden', show);
                document.getElementById(offId).classList.toggle('hidden', !show);
            });
        }
        togglePassword('togglePass1', 'password', 'eye1On', 'eye1Off');
    </script>
</body>
</html>
