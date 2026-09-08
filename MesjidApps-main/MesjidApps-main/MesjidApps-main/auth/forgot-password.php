<?php
// forgot-password.php - Permintaan Reset Kata Sandi (Scope 13)
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$pesan = '';
$tipe = '';
$tokenSimulasi = '';

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
            // Generate token acak yang aman
            $token = bin2hex(random_bytes(24));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $update->execute([$token, $expires, $user['id']]);

            $tokenSimulasi = $token;
            $pesan = 'Tautan reset kata sandi berhasil dibuat! Silakan klik tombol di bawah untuk melanjutkan penggantian kata sandi.';
            $tipe = 'success';
        } else {
            $pesan = 'Alamat email tidak ditemukan dalam pangkalan data kami.';
            $tipe = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi · <?= e($profil['nama_masjid']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/classic-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="pattern-arabesque-light min-h-screen flex items-center justify-center p-4 antialiased text-warm-900">

    <div class="max-w-md w-full my-8">
        
        <div class="text-center mb-6 space-y-2">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-cypress-900 border border-antique-500/50 flex items-center justify-center text-antique-400 shadow-md">
                <i class="fa-solid fa-key text-xl"></i>
            </div>
            <h2 class="font-classic text-xl font-bold text-warm-900">Pemulihan Kata Sandi</h2>
            <p class="text-xs text-warm-800/60">Masukkan email akun Anda untuk mendapatkan tautan pembaruan kata sandi.</p>
        </div>

        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-xl space-y-5">
            
            <?php if ($pesan): ?>
                <div class="p-3.5 rounded-xl <?= $tipe === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?> text-xs font-medium space-y-2">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid <?= $tipe === 'success' ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-red-600' ?>"></i>
                        <span><?= e($pesan) ?></span>
                    </div>

                    <?php if ($tokenSimulasi): ?>
                        <div class="pt-2">
                            <a href="reset-password.php?token=<?= e($tokenSimulasi) ?>" class="block w-full text-center py-2 px-3 rounded-lg bg-emerald-700 text-white font-bold text-xs hover:bg-emerald-800 transition">
                                <i class="fa-solid fa-arrow-right mr-1"></i> Buka Halaman Reset Password Sekarang
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="forgot-password.php" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-warm-800 mb-1">Alamat Email Terdaftar</label>
                    <input type="email" name="email" required placeholder="nama@email.com" class="w-full px-3.5 py-2.5 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:ring-2 focus:ring-antique-500 focus:outline-none">
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs shadow-md transition">
                    Kirim Tautan Reset
                </button>
            </form>

            <div class="pt-3 border-t border-antique-100 text-center text-xs text-warm-800/70">
                Ingat kata sandi Anda? <a href="login.php" class="text-cypress-700 font-bold hover:underline">Kembali ke Login</a>
            </div>
        </div>

    </div>

</body>
</html>
