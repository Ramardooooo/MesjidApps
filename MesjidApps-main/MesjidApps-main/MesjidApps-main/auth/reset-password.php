<?php
// reset-password.php - Eksekusi Reset Kata Sandi dengan Token (Scope 13)
require_once __DIR__ . '/../config/database.php';
mulai_session();

$profil = get_profil_masjid();
$token = trim($_GET['token'] ?? '');
$pesan = '';
$tipe = '';

if (empty($token)) {
    header('Location: login.php');
    exit;
}

// Validasi Token
$stmt = $pdo->prepare("SELECT id, nama_lengkap, email FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    $pesan = 'Tautan reset kata sandi tidak valid atau telah kadaluarsa (lebih dari 1 jam). Silakan minta tautan baru.';
    $tipe = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'] ?? '';
    $konfirm  = $_POST['password_confirm'] ?? '';

    if (empty($password) || strlen($password) < 6) {
        $pesan = 'Kata sandi baru minimal 6 karakter.';
        $tipe = 'error';
    } elseif ($password !== $konfirm) {
        $pesan = 'Konfirmasi kata sandi tidak cocok.';
        $tipe = 'error';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $update->execute([$hash, $user['id']]);

        header('Location: login.php?reset=success');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Kata Sandi · <?= e($profil['nama_masjid']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/classic-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="pattern-arabesque-light min-h-screen flex items-center justify-center p-4 antialiased text-warm-900">

    <div class="max-w-md w-full my-8">
        
        <div class="text-center mb-6 space-y-2">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-cypress-900 border border-antique-500/50 flex items-center justify-center text-antique-400 shadow-md">
                <i class="fa-solid fa-lock-open text-xl"></i>
            </div>
            <h2 class="font-classic text-xl font-bold text-warm-900">Atur Kata Sandi Baru</h2>
            <p class="text-xs text-warm-800/60">Untuk akun: <?= e($user['email'] ?? '') ?></p>
        </div>

        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-antique-300/50 shadow-xl space-y-5">
            
            <?php if ($pesan): ?>
                <div class="p-3.5 rounded-xl bg-red-50 text-red-800 border border-red-200 text-xs font-medium flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-sm text-red-600"></i>
                    <span><?= e($pesan) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($user): ?>
                <form method="POST" action="reset-password.php?token=<?= e($token) ?>" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-warm-800 mb-1">Kata Sandi Baru <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:ring-2 focus:ring-antique-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-warm-800 mb-1">Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirm" required placeholder="Ulangi sandi baru" class="w-full px-3.5 py-2.5 rounded-xl bg-warm-50 border border-antique-300 text-xs focus:ring-2 focus:ring-antique-500 focus:outline-none">
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs shadow-md transition">
                        Simpan Kata Sandi Baru
                    </button>
                </form>
            <?php else: ?>
                <div class="pt-2 text-center">
                    <a href="forgot-password.php" class="px-4 py-2 rounded-xl bg-cypress-700 text-white text-xs font-semibold inline-block">
                        Minta Tautan Reset Baru
                    </a>
                </div>
            <?php endif; ?>

            <div class="pt-3 border-t border-antique-100 text-center text-xs text-warm-800/70">
                <a href="login.php" class="text-cypress-700 font-bold hover:underline">Kembali ke Halaman Login</a>
            </div>
        </div>

    </div>

</body>
</html>
