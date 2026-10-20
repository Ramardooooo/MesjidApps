<?php
// home/profil-staff-dkm.php - Tim Pengelola (Admin/Staff) untuk halaman Profil & DKM
require_once __DIR__ . '/../config/database.php';

$staffList = $pdo->query("
    SELECT id, nama_lengkap, avatar, jabatan, role, bio
    FROM users
    WHERE is_public_profile = 1 AND status = 'aktif' AND role != 'donatur'
    ORDER BY 
        CASE role 
            WHEN 'admin' THEN 1 
            WHEN 'bendahara' THEN 2 
            WHEN 'content_admin' THEN 3 
            ELSE 4 
        END,
        nama_lengkap ASC
")->fetchAll();

if (empty($staffList)) {
    return;
}
?>

<div class="space-y-8">
    <div class="text-center max-w-2xl mx-auto space-y-2">
<h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">
            Pengurus Harian &amp; Tim Teknis
        </h2>
        <p class="text-xs sm:text-sm text-warm-800/70">
            Tim profesional yang mengelola operasional harian, teknis website, dan pembukuan masjid.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($staffList as $s): 
            $avatarPath = trim((string)($s['avatar'] ?? ''));
            $avatarUrl = $avatarPath !== '' ? upload_url($avatarPath) : '';
            
            $namaBersih = preg_replace('/[^A-Za-z0-9]/', '', $s['nama_lengkap']);
            $initials = strtoupper(substr($namaBersih, 0, 2));
            if ($initials === '') {
                $initials = strtoupper(substr($s['nama_lengkap'], 0, 1));
            }
            
            $roleLabel = [
                'admin' => 'Administrator',
                'bendahara' => 'Bendahara',
                'content_admin' => 'Pengelola Konten'
            ][$s['role']] ?? ucfirst($s['role']);
        ?>
            <div class="bg-white rounded-2xl p-6 border border-antique-300/40 shadow-xs hover:shadow-md transition flex flex-col items-center text-center">
                <?php if ($avatarUrl): ?>
                    <img src="<?= e($avatarUrl) ?>" alt="<?= e($s['nama_lengkap']) ?>" class="w-16 h-16 rounded-full object-cover border-2 border-antique-300 mb-4 shadow-sm">
                <?php else: ?>
                    <div class="w-16 h-16 rounded-full bg-cypress-900 border-2 border-antique-300 flex items-center justify-center text-antique-300 font-bold text-xl mb-4 shadow-sm">
                        <?= e($initials) ?>
                    </div>
                <?php endif; ?>
                <span class="text-[10px] font-bold uppercase tracking-wider text-antique-700 bg-antique-50 px-3 py-1 rounded-full border border-antique-200 w-fit mb-2">
                    <?= e($roleLabel) ?>
                </span>
                <h4 class="font-bold text-sm text-warm-900 leading-snug mb-1"><?= e($s['nama_lengkap']) ?></h4>
                <?php if (!empty($s['jabatan'])): ?>
                    <span class="text-xs text-warm-800/60 block mb-2"><?= e($s['jabatan']) ?></span>
                <?php endif; ?>
                <?php if (!empty($s['bio'])): ?>
                    <p class="text-xs text-warm-700/60 line-clamp-2 mb-2 italic">"<?= e(substr($s['bio'], 0, 60)) ?>..."</p>
                <?php endif; ?>
                <a href="<?= base_url() ?>/home/profil-staff?id=<?= $s['id'] ?>" class="inline-flex items-center gap-1 text-xs font-semibold text-antique-600 hover:text-antique-700 mt-auto">
                    <i class="fa-solid fa-user"></i>
                    <span>Profile Lengkap</span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
