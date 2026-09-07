<?php
// verify-otp.php — Verifikasi Kode OTP (Register & Forgot Password)
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil  = get_profil_masjid();
$purpose = $_GET['purpose'] ?? '';

// Validasi purpose
if (!in_array($purpose, ['register', 'forgot_password'])) {
    header('Location: login.php');
    exit;
}

// Pastikan ada session pendukung
if ($purpose === 'register' && empty($_SESSION['pending_register'])) {
    header('Location: register.php');
    exit;
}
if ($purpose === 'forgot_password' && empty($_SESSION['pending_forgot'])) {
    header('Location: forgot-password.php');
    exit;
}

$email     = $purpose === 'register'
    ? ($_SESSION['pending_register']['email'] ?? '')
    : ($_SESSION['pending_forgot']['email'] ?? '');
$nama      = $purpose === 'register'
    ? ($_SESSION['pending_register']['nama'] ?? '')
    : ($_SESSION['pending_forgot']['nama'] ?? '');
$otpSimulasi = $_SESSION['otp_simulasi'] ?? null;

$pesan = '';
$tipe  = '';

// ── Kirim ulang OTP ─────────────────────────────────────────
if (isset($_GET['resend']) && $_GET['resend'] === '1') {
    try {
        $otp = generate_otp($email, $purpose);
        send_otp_email($email, $nama, $otp, $purpose);
        $pesan = 'Kode OTP baru telah dikirimkan ke email Anda. Cek inbox atau folder spam.';
        $tipe  = 'info';
    } catch (\Exception $e) {
        $pesan = 'Gagal mengirim ulang email OTP. Pastikan email valid dan coba lagi.';
        $tipe  = 'error';
    }
}

// ── Handle POST verifikasi ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gabungkan 6 kotak digit menjadi satu kode
    $digits = [];
    for ($i = 1; $i <= 6; $i++) {
        $digits[] = trim($_POST["otp_$i"] ?? '');
    }
    $otpInput = implode('', $digits);

    if (strlen($otpInput) !== 6 || !ctype_digit($otpInput)) {
        $pesan = 'Masukkan 6 digit kode OTP yang valid.';
        $tipe  = 'error';
    } elseif (!verify_otp($email, $otpInput, $purpose)) {
        $pesan = 'Kode OTP tidak valid atau sudah kedaluwarsa. Silakan minta kode baru.';
        $tipe  = 'error';
    } else {
        // OTP Benar ✓
        if ($purpose === 'register') {
            // Buat akun dari session
            $data = $_SESSION['pending_register'];
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO users (username, email, no_hp, password, nama_lengkap, role, status)
                     VALUES (?, ?, ?, ?, ?, 'donatur', 'aktif')"
                );
                $stmt->execute([
                    $data['username'], $data['email'], $data['no_hp'],
                    $data['password'], $data['nama'],
                ]);
                $newId = $pdo->lastInsertId();

                // Notifikasi sambutan
                $pdo->prepare(
                    "INSERT INTO notifikasi (user_id, judul, pesan, tipe) VALUES (?, ?, ?, 'sistem')"
                )->execute([
                    $newId,
                    'Selamat Datang di Portal Donatur!',
                    'Ahlan wa sahlan! Akun donatur Anda telah aktif. Anda dapat memantau riwayat donasi dan mengunduh kwitansi resmi di sini.',
                ]);
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $pesan = 'Terjadi kesalahan saat membuat akun. Silakan coba lagi.';
                $tipe  = 'error';
                goto render;
            }

            // Auto-login
            $_SESSION['user'] = [
                'id'       => $newId,
                'username' => $data['username'],
                'nama'     => $data['nama'],
                'role'     => 'donatur',
                'email'    => $data['email'],
            ];
            unset($_SESSION['pending_register'], $_SESSION['otp_simulasi']);

            header('Location: ../donatur/portal-donatur.php?welcome=1');
            exit;

        } else {
            // forgot_password — simpan otorisasi reset ke session
            $_SESSION['otp_verified_email'] = $email;
            unset($_SESSION['pending_forgot'], $_SESSION['otp_simulasi']);

            header('Location: reset-password.php');
            exit;
        }
    }
}

render:
// Label UI berdasarkan purpose
$labelBadge  = $purpose === 'register' ? 'Verifikasi Akun Baru' : 'Verifikasi Lupa Sandi';
$labelJudul  = $purpose === 'register' ? 'Verifikasi Email Anda' : 'Konfirmasi Identitas';
$labelSub    = $purpose === 'register'
    ? 'Masukkan 6 digit kode OTP yang dikirim ke <strong>' . e($email) . '</strong> untuk mengaktifkan akun.'
    : 'Masukkan 6 digit kode OTP yang dikirim ke <strong>' . e($email) . '</strong> untuk mereset kata sandi.';
$labelArabic = $purpose === 'register' ? 'أَهْلًا وَسَهْلًا' : 'إِنَّ مَعَ الْعُسْرِ يُسْرًا';
$labelArabicSub = $purpose === 'register'
    ? '"Selamat datang, semoga Allah meridhoi."'
    : '"Sesungguhnya bersama kesulitan ada kemudahan."';

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
    <title><?= e($labelJudul) ?> · <?= e($profil['nama_masjid']) ?></title>
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
    <style>
        /* Animasi masuk OTP */
        @keyframes otp-pop {
            0%   { transform: scale(0.85); opacity: 0; }
            60%  { transform: scale(1.04); }
            100% { transform: scale(1);    opacity: 1; }
        }
        .otp-box { animation: otp-pop 0.25s ease forwards; }
        .otp-box:nth-child(1) { animation-delay: 0.05s; }
        .otp-box:nth-child(2) { animation-delay: 0.10s; }
        .otp-box:nth-child(3) { animation-delay: 0.15s; }
        .otp-box:nth-child(4) { animation-delay: 0.20s; }
        .otp-box:nth-child(5) { animation-delay: 0.25s; }
        .otp-box:nth-child(6) { animation-delay: 0.30s; }

        /* Countdown timer */
        #countdown-bar { transition: width 1s linear; }
    </style>
</head>
<body class="pattern-arabesque-light min-h-screen flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8 text-warm-800">

    <div class="w-full max-w-4xl mx-auto my-6">

        <!-- Kartu Utama — layout sama persis dengan login -->
        <div class="bg-white rounded-2xl shadow-xl shadow-stone-900/5 border border-antique-300/40 overflow-hidden grid grid-cols-1 md:grid-cols-12 transition-all">

            <!-- ══════════════════════════════════════════════════ -->
            <!-- Sisi Kiri: Visual Klasik & Identitas Masjid       -->
            <!-- ══════════════════════════════════════════════════ -->
            <div class="md:col-span-5 pattern-arabesque-dark p-8 md:p-10 text-white flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 pointer-events-none opacity-20">
                    <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="100" cy="0" r="80" stroke="#c5a059" stroke-width="1.5" />
                        <circle cx="100" cy="0" r="60" stroke="#c5a059" stroke-width="0.8" stroke-dasharray="3 3" />
                    </svg>
                </div>

                <div class="relative z-10">
                    <!-- Logo & Nama Masjid -->
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

                    <!-- Ayat / Kalimat Arab -->
                    <div class="my-6 py-3 border-y border-white/10 text-center">
                        <p class="font-arabic text-xl md:text-2xl text-antique-100 tracking-wider">
                            <?= e($labelArabic) ?>
                        </p>
                        <span class="text-[11px] text-antique-300/80 block mt-1"><?= $labelArabicSub ?></span>
                    </div>

                    <!-- Panduan OTP -->
                    <div class="rounded-xl bg-cypress-900/60 border border-white/10 p-4 text-xs leading-relaxed text-emerald-100/90 backdrop-blur-sm space-y-2">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-antique-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span>Kode OTP telah dikirimkan ke email <strong class="text-antique-300"><?= e($email) ?></strong></span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-antique-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Kode berlaku <strong class="text-antique-300">10 menit</strong> sejak dikirimkan</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-antique-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span>Jangan bagikan kode kepada siapapun</span>
                        </div>
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

            <!-- ══════════════════════════════════════════════════ -->
            <!-- Sisi Kanan: Form OTP                              -->
            <!-- ══════════════════════════════════════════════════ -->
            <div class="md:col-span-7 p-8 sm:p-10 lg:p-12 flex flex-col justify-center bg-white">
                <div class="max-w-md mx-auto w-full">

                    <!-- Header Section -->
                    <div class="mb-8">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-antique-50 border border-antique-300/60 text-antique-700 text-xs font-semibold uppercase tracking-wider mb-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-cypress-700"></span>
                            <?= e($labelBadge) ?>
                        </div>
                        <h2 class="text-2xl font-bold text-warm-900 tracking-tight"><?= e($labelJudul) ?></h2>
                        <p class="text-sm text-warm-800/70 mt-1.5"><?= $labelSub ?></p>
                    </div>

                    <!-- Alert Pesan -->
                    <?php if ($pesan): ?>
                    <div class="mb-6 p-4 rounded-xl
                        <?php if ($tipe === 'error') echo 'bg-amber-50/90 border-amber-200/80 text-amber-900';
                              elseif ($tipe === 'info') echo 'bg-blue-50/90 border-blue-200/80 text-blue-900';
                              else echo 'bg-emerald-50/90 border-emerald-200/80 text-emerald-900'; ?>
                        border flex items-start gap-3 text-sm shadow-sm">
                        <svg class="w-5 h-5 shrink-0 mt-0.5
                            <?php if ($tipe === 'error') echo 'text-amber-600';
                                  elseif ($tipe === 'info') echo 'text-blue-500';
                                  else echo 'text-emerald-600'; ?>"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="<?= $tipe === 'error'
                                    ? 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
                                    : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' ?>"/>
                        </svg>
                        <div class="leading-snug">
                            <strong class="font-semibold block text-xs uppercase tracking-wide
                                <?php if ($tipe === 'error') echo 'text-amber-800';
                                      elseif ($tipe === 'info') echo 'text-blue-800';
                                      else echo 'text-emerald-800'; ?>">
                                <?= $tipe === 'error' ? 'Perhatian' : ($tipe === 'info' ? 'Informasi' : 'Berhasil') ?>
                            </strong>
                            <?= e($pesan) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Form OTP — 6 Kotak Digit -->
                    <form method="POST" action="verify-otp.php?purpose=<?= e($purpose) ?>" id="otpForm" class="space-y-6">

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-4 text-center">
                                Masukkan Kode OTP
                            </label>

                            <!-- 6 Kotak Input Digit -->
                            <div class="flex justify-center gap-2 sm:gap-3" id="otpInputs">
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                <input type="text"
                                       id="otp_<?= $i ?>"
                                       name="otp_<?= $i ?>"
                                       maxlength="1"
                                       inputmode="numeric"
                                       pattern="[0-9]"
                                       autocomplete="one-time-code"
                                       class="otp-box w-11 h-14 sm:w-13 sm:h-16 text-center text-xl font-bold rounded-xl border-2 border-warm-200 bg-warm-50/60 text-warm-900 focus:outline-none focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 focus:bg-white transition-luxury caret-transparent"
                                       style="opacity:0;"
                                       <?= $i === 1 ? 'autofocus' : '' ?>
                                       required>
                                <?php endfor; ?>
                            </div>

                            <!-- Countdown Bar -->
                            <div class="mt-4">
                                <div class="flex justify-between text-[11px] text-warm-800/50 mb-1">
                                    <span>Waktu tersisa:</span>
                                    <span id="countdown-text" class="font-mono font-semibold text-cypress-700">10:00</span>
                                </div>
                                <div class="w-full h-1.5 bg-warm-100 rounded-full overflow-hidden">
                                    <div id="countdown-bar" class="h-full bg-cypress-700 rounded-full" style="width:100%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Verifikasi -->
                        <button type="submit" id="submitBtn"
                                class="w-full py-3.5 px-6 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-semibold text-sm tracking-wide shadow-md shadow-cypress-900/15 active:scale-[0.99] transition-luxury flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span>Verifikasi Kode OTP</span>
                        </button>
                    </form>

                    <!-- Kirim Ulang & Kembali -->
                    <div class="mt-6 pt-5 border-t border-warm-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-warm-800/70">
                        <span>Tidak menerima kode?
                            <a href="verify-otp.php?purpose=<?= e($purpose) ?>&resend=1"
                               class="text-cypress-700 font-bold hover:underline" id="resendLink">
                                Kirim Ulang OTP
                            </a>
                        </span>
                        <a href="<?= $purpose === 'register' ? 'register.php' : 'forgot-password.php' ?>"
                           class="text-antique-600 hover:text-antique-700 hover:underline">
                            ← Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-warm-800/60">
            <p>© <?= date('Y') ?> <?= e($profil['nama_masjid']) ?> · Menjaga Amanah, Memakmurkan Masjid</p>
        </div>
    </div>

    <script>
    // ── OTP Box Auto-focus & Navigation ─────────────────────────
    const inputs = document.querySelectorAll('#otpInputs input');

    inputs.forEach((inp, idx) => {
        inp.addEventListener('input', (e) => {
            const val = e.target.value.replace(/\D/g, '');
            e.target.value = val.slice(-1); // hanya 1 digit terakhir
            if (val && idx < inputs.length - 1) {
                inputs[idx + 1].focus();
            }
            checkAllFilled();
        });

        inp.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !inp.value && idx > 0) {
                inputs[idx - 1].focus();
                inputs[idx - 1].value = '';
            }
            if (e.key === 'ArrowLeft' && idx > 0) inputs[idx - 1].focus();
            if (e.key === 'ArrowRight' && idx < inputs.length - 1) inputs[idx + 1].focus();
        });

        // Paste handler — distribusikan digit ke kotak
        inp.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            pasted.split('').slice(0, 6).forEach((ch, i) => {
                if (inputs[i]) inputs[i].value = ch;
            });
            const next = Math.min(pasted.length, inputs.length - 1);
            inputs[next].focus();
            checkAllFilled();
        });
    });

    function checkAllFilled() {
        const allFilled = [...inputs].every(i => i.value.length === 1);
        document.getElementById('submitBtn').classList.toggle('opacity-60', !allFilled);
        document.getElementById('submitBtn').classList.toggle('cursor-not-allowed', !allFilled);
    }
    checkAllFilled();

    // ── Countdown Timer (10 menit = 600 detik) ──────────────────
    let total = 600;
    const txt  = document.getElementById('countdown-text');
    const bar  = document.getElementById('countdown-bar');
    const resend = document.getElementById('resendLink');

    const timer = setInterval(() => {
        total--;
        if (total <= 0) {
            clearInterval(timer);
            txt.textContent = '00:00';
            txt.classList.replace('text-cypress-700', 'text-red-600');
            bar.style.width = '0%';
            bar.classList.replace('bg-cypress-700', 'bg-red-500');
            return;
        }
        const m = String(Math.floor(total / 60)).padStart(2, '0');
        const s = String(total % 60).padStart(2, '0');
        txt.textContent = `${m}:${s}`;
        bar.style.width = (total / 600 * 100) + '%';

        // Warna merah di 60 detik terakhir
        if (total <= 60) {
            txt.classList.replace('text-cypress-700', 'text-red-600');
            bar.classList.replace('bg-cypress-700', 'bg-red-500');
        }
    }, 1000);

    // Submit on enter dari input terakhir
    inputs[inputs.length - 1].addEventListener('keydown', (e) => {
        if (e.key === 'Enter') document.getElementById('otpForm').submit();
    });
    </script>
</body>
</html>
