<?php
require_once __DIR__ . '/config/database.php';

mulai_session();

// Jika sudah login langsung ke dashboard
if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'donatur') {
        header('Location: donatur/portal-donatur.php');
    } else {
        header('Location: admin/dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Mohon masukkan username dan password Anda.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u LIMIT 1');
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id'       => $user['id'],
                'username' => $user['username'],
                'nama'     => $user['nama_lengkap'],
                'role'     => $user['role'],
            ];
            if ($user['role'] === 'donatur') {
                header('Location: donatur/portal-donatur.php');
            } else {
                header('Location: admin/dashboard.php');
            }
            exit;
        } else {
            $error = 'Username atau kata sandi tidak cocok. Silakan periksa kembali.';
        }
    }
}

// Konfigurasi Tanggal Hijriyah & Masehi
$namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w')];
$namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][date('n')];
$tanggalMasehi = $namaHari . ', ' . date('j') . ' ' . $namaBulan . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Pengurus · Masjid Nurul Iman</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts & Custom Classic Theme -->
    <link rel="stylesheet" href="assets/css/classic-theme.css">
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

    <!-- Container Utama -->
    <div class="w-full max-w-4xl mx-auto">
        
        <!-- Kartu Utama Klasik -->
        <div class="bg-white rounded-2xl shadow-xl shadow-stone-900/5 border border-antique-300/40 overflow-hidden grid grid-cols-1 md:grid-cols-12 transition-all">
            
            <!-- Sisi Kiri: Visual Klasik & Identitas Masjid (5 Kolom di Desktop) -->
            <div class="md:col-span-5 pattern-arabesque-dark p-8 md:p-10 text-white flex flex-col justify-between relative overflow-hidden">
                <!-- Ornamen Aksen Sudut Emas -->
                <div class="absolute top-0 right-0 w-24 h-24 pointer-events-none opacity-20">
                    <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="100" cy="0" r="80" stroke="#c5a059" stroke-width="1.5" />
                        <circle cx="100" cy="0" r="60" stroke="#c5a059" stroke-width="0.8" stroke-dasharray="3 3" />
                    </svg>
                </div>

                <!-- Bagian Atas: Logo & Nama Masjid -->
                <div class="relative z-10">
                    <div class="flex items-center gap-3.5 mb-6">
                        <!-- Emblem Kubah Emas Klasik -->
                        <div class="w-13 h-13 p-2.5 rounded-xl bg-cypress-900/80 border border-antique-500/40 shadow-inner flex items-center justify-center text-antique-300 shrink-0">
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 0c-3 3-5 6-5 9a5 5 0 0010 0c0-3-2-6-5-9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 21h16M7 21v-4m10 4v-4M9 13h6"/>
                                <circle cx="12" cy="2" r="0.75" fill="currentColor"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-widest text-antique-300 font-semibold block">Masjid Jami'</span>
                            <h1 class="font-classic text-xl font-bold tracking-wide text-white">NURUL IMAN</h1>
                        </div>
                    </div>

                    <!-- Kaligrafi Bismillah Halus -->
                    <div class="my-6 py-3 border-y border-white/10 text-center">
                        <p class="font-arabic text-xl md:text-2xl text-antique-100 tracking-wider">
                            بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
                        </p>
                    </div>

                    <!-- Kutipan Hadits Bijak -->
                    <div class="rounded-xl bg-cypress-900/60 border border-white/10 p-4 text-xs leading-relaxed text-emerald-100/90 backdrop-blur-sm">
                        <p class="italic">
                            "Barangsiapa membangun masjid karena Allah, niscaya Allah bangunkan baginya rumah di surga."
                        </p>
                        <span class="block mt-2 font-medium text-antique-300 text-[11px]">— HR. Bukhari &amp; Muslim</span>
                    </div>
                </div>

                <!-- Bagian Bawah: Penanda Waktu & Tanggal -->
                <div class="relative z-10 pt-6 mt-6 border-t border-white/10 text-xs text-emerald-200/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-antique-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span><?= e($tanggalMasehi) ?></span>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-cypress-900/80 border border-antique-500/30 text-[10px] text-antique-300">
                        Hijriyah
                    </span>
                </div>
            </div>

            <!-- Sisi Kanan: Formulir Login Simpel & Elegan (7 Kolom di Desktop) -->
            <div class="md:col-span-7 p-8 sm:p-10 lg:p-12 flex flex-col justify-center bg-white">
                
                <div class="max-w-md mx-auto w-full">
                    <!-- Heading Form -->
                    <div class="mb-8">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-antique-50 border border-antique-300/60 text-antique-700 text-xs font-semibold uppercase tracking-wider mb-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-cypress-700"></span>
                            Portal Khusus Pengurus
                        </div>
                        <h2 class="text-2xl font-bold text-warm-900 tracking-tight">Selamat Datang</h2>
                        <p class="text-sm text-warm-800/70 mt-1.5">Silakan masuk untuk mengelola data donasi &amp; kegiatan masjid.</p>
                    </div>

                    <!-- Notifikasi Pesan Error yang Lembut -->
                    <?php if ($error): ?>
                    <div class="mb-6 p-4 rounded-xl bg-amber-50/90 border border-amber-200/80 text-amber-900 flex items-start gap-3 text-sm shadow-sm animate-pulse">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="leading-snug">
                            <strong class="font-semibold block text-xs uppercase tracking-wide text-amber-800">Akses Ditolak</strong>
                            <?= e($error) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Form Login -->
                    <form method="POST" action="login.php" class="space-y-5" id="loginForm">
                        
                        <!-- Input Username -->
                        <div>
                            <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80 mb-2">
                                Username Pengurus
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-warm-800/40">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input type="text" id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>"
                                       class="w-full pl-11 pr-4 py-3 rounded-xl border border-warm-200 bg-warm-50/60 text-warm-900 placeholder-warm-800/40 text-sm focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury"
                                       placeholder="Ketik username Anda" required autofocus>
                            </div>
                        </div>

                        <!-- Input Kata Sandi -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-warm-800/80">
                                    Kata Sandi
                                </label>
                                <a href="javascript:void(0)" onclick="bukaBantuan()" class="text-xs text-antique-600 hover:text-antique-700 hover:underline transition">
                                    Lupa sandi?
                                </a>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-warm-800/40">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input type="password" id="password" name="password"
                                       class="w-full pl-11 pr-12 py-3 rounded-xl border border-warm-200 bg-warm-50/60 text-warm-900 placeholder-warm-800/40 text-sm focus:outline-none focus:bg-white focus:border-cypress-700 focus:ring-2 focus:ring-cypress-700/15 transition-luxury"
                                       placeholder="Masukkan kata sandi" required>
                                
                                <!-- Tombol Toggle Password -->
                                <button type="button" id="togglePass" aria-label="Lihat kata sandi"
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-warm-800/40 hover:text-warm-800/80 transition-luxury">
                                    <svg id="eyeOn" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg id="eyeOff" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Checkbox Ingat Saya -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2.5 cursor-pointer text-xs text-warm-800/80 select-none">
                                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-warm-300 text-cypress-700 focus:ring-cypress-700/30 accent-[#1b3a2b]">
                                <span>Ingat saya di perangkat ini</span>
                            </label>
                        </div>

                        <!-- Tombol Masuk Utama -->
                        <button type="submit"
                                class="w-full py-3.5 px-6 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-semibold text-sm tracking-wide shadow-md shadow-cypress-900/15 active:scale-[0.99] transition-luxury flex items-center justify-center gap-2">
                            <span>Masuk ke Portal</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </form>

                    <!-- Tombol Cepat Isi Akun Demo (Laragon Testing) -->
                    <div class="mt-6 pt-5 border-t border-warm-100 text-center">
                        <button type="button" onclick="isiAkunDemo()"
                                class="w-full py-2.5 px-4 rounded-lg bg-antique-50 hover:bg-antique-100 border border-antique-300/60 text-antique-700 text-xs font-medium flex items-center justify-center gap-2 transition-luxury">
                            <svg class="w-4 h-4 text-antique-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Klik untuk isi demo (admin / admin123)</span>
                        </button>
                    </div>

                </div>

            </div>

        </div>

        <!-- Footer Bersih & Kalem -->
        <div class="text-center mt-6 text-xs text-warm-800/60 space-y-1">
            <p>© <?= date('Y') ?> Masjid Nurul Iman · Menjaga Amanah, Memakmurkan Masjid</p>
        </div>

    </div>

    <!-- Modal Bantuan Sederhana -->
    <div id="modalBantuan" class="fixed inset-0 bg-stone-900/40 backdrop-blur-xs hidden items-center justify-center p-4 z-50 transition-opacity">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-warm-200">
            <div class="flex items-center gap-3 text-cypress-700 mb-3">
                <div class="w-9 h-9 rounded-lg bg-cypress-50 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-base text-warm-900">Bantuan Akses Akun</h3>
            </div>
            <p class="text-xs text-warm-800/80 leading-relaxed">
                Untuk reset kata sandi atau pendaftaran pengurus baru, silakan hubungi Sekretaris/Ketua Takmir Masjid Nurul Iman atau gunakan akun demo yang tersedia.
            </p>
            <div class="mt-5 text-right">
                <button type="button" onclick="tutupBantuan()"
                        class="px-4 py-2 rounded-lg bg-cypress-700 text-white text-xs font-semibold hover:bg-cypress-800 transition">
                    Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- Script Interaktif -->
    <script>
        // Toggle Show/Hide Password
        const toggle = document.getElementById('togglePass');
        const pass = document.getElementById('password');
        const on = document.getElementById('eyeOn');
        const off = document.getElementById('eyeOff');

        toggle.addEventListener('click', () => {
            const show = pass.type === 'password';
            pass.type = show ? 'text' : 'password';
            on.classList.toggle('hidden', show);
            off.classList.toggle('hidden', !show);
        });

        // Pengisian Akun Demo Otomatis
        function isiAkunDemo() {
            document.getElementById('username').value = 'admin';
            document.getElementById('password').value = 'admin123';
            
            // Efek visual highlight pada input
            const u = document.getElementById('username');
            const p = document.getElementById('password');
            u.classList.add('ring-2', 'ring-antique-500');
            p.classList.add('ring-2', 'ring-antique-500');
            setTimeout(() => {
                u.classList.remove('ring-2', 'ring-antique-500');
                p.classList.remove('ring-2', 'ring-antique-500');
            }, 600);
        }

        // Modal Bantuan
        function bukaBantuan() {
            const m = document.getElementById('modalBantuan');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function tutupBantuan() {
            const m = document.getElementById('modalBantuan');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }
    </script>
</body>
</html>
