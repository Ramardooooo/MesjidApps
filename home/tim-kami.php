<?php
require_once __DIR__ . '/../config/database.php';

$profil = get_profil_masjid();

$stmt = $pdo->query("
    SELECT 
        id, username, nama_lengkap, avatar, bio, jabatan, social_links,
        profile_views, created_at, role
    FROM users
    WHERE is_public_profile = 1 AND status = 'aktif'
    ORDER BY 
        CASE role 
            WHEN 'admin' THEN 1 
            WHEN 'bendahara' THEN 2 
            WHEN 'content_admin' THEN 3 
            ELSE 4 
        END,
        nama_lengkap ASC
");

$staffList = $stmt->fetchAll();

$pageTitle = 'Tim Kami · ' . $profil['nama_masjid'];
$activeNav = 'tim-kami';

require_once __DIR__ . '/../layouts/public_header.php';
?>

<style>
.team-hero {
    background: linear-gradient(135deg, #0d1d15 0%, #07100b 100%);
    position: relative;
    overflow: hidden;
}
.team-hero::before {
    content: '';
    position: absolute;
    top: 0; right: 0; bottom: 0; left: 0;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'%3E%3Cg fill='none' stroke='%23c5a059' stroke-opacity='.08' stroke-width='1'%3E%3Cpath d='M100 5L195 100 100 195 5 100Z'/%3E%3Cpath d='M100 25L175 100 100 175 25 100Z'/%3E%3Cpath d='M100 45L155 100 100 155 45 100Z'/%3E%3Ccircle cx='100' cy='100' r='8'/%3E%3Cpath d='M100 5V195M5 100H195M21 21L179 179M179 21L21 179'/%3E%3C/g%3E%3C/svg%3E");
    background-size: 200px;
    pointer-events: none;
}
.gold-gradient-text {
    background: linear-gradient(135deg, #dfc896 0%, #c5a059 50%, #b08a42 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>

<div class="team-hero pt-16 pb-12 text-white relative">
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-antique-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 right-10 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center space-y-4">
<h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
            Tim Pengurus Kami<br class="hidden sm:inline">
            <span class="gold-gradient-text font-classic">Memakmurkan Masjid</span>
        </h1>
        <p class="text-stone-300 text-sm sm:text-base max-w-2xl mx-auto">
            Kenali tim profesional yang mengabdikan diri untuk kemajuan masjid dan pelayanan jamaah
        </p>
    </div>
</div>

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <?php if (empty($staffList)): ?>
        <div class="text-center py-16">
            <div class="w-20 h-20 bg-warm-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-users text-3xl text-warm-400"></i>
            </div>
            <h2 class="text-xl font-bold text-warm-900 mb-2">Belum ada profil tim yang ditampilkan</h2>
            <p class="text-warm-600">Profil pengurus akan muncul di halaman ini setelah diaktifkan</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($staffList as $staff): 
                $avatarPath = trim((string)($staff['avatar'] ?? ''));
                $avatarUrl = $avatarPath !== '' ? upload_url($avatarPath) : '';
                
                $namaBersih = preg_replace('/[^A-Za-z0-9]/', '', $staff['nama_lengkap']);
                $initials = strtoupper(substr($namaBersih, 0, 2));
                if ($initials === '') {
                    $initials = 'S';
                }
                
                $roleLabel = [
                    'admin' => 'Administrator',
                    'bendahara' => 'Bendahara',
                    'content_admin' => 'Pengelola Konten'
                ][$staff['role']] ?? $staff['role'];
            ?>
                <a href="<?= base_url() ?>/home/profil-staff?id=<?= $staff['id'] ?>" class="group bg-white rounded-2xl shadow-soft border border-warm-200 hover:shadow-luxury hover:border-antique-300 transition-all overflow-hidden">
                    <div class="p-6 text-center">
                        <div class="relative inline-block mb-4">
                            <?php if ($avatarUrl): ?>
                                <img src="<?= e($avatarUrl) ?>" alt="<?= e($staff['nama_lengkap']) ?>" class="w-24 h-24 rounded-full object-cover border-4 border-antique-100 group-hover:border-antique-300 transition-colors">
                            <?php else: ?>
                                <div class="w-24 h-24 rounded-full bg-antique-100 flex items-center justify-center text-antique-700 text-3xl font-bold border-4 border-antique-50 group-hover:border-antique-200 transition-colors">
                                    <?= e($initials) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h3 class="text-lg font-bold text-warm-900 mb-1 group-hover:text-cypress-900 transition-colors">
                            <?= e($staff['nama_lengkap']) ?>
                        </h3>
                        <?php if (!empty($staff['jabatan'])): ?>
                            <p class="text-sm text-antique-600 font-semibold mb-2"><?= e($staff['jabatan']) ?></p>
                        <?php else: ?>
                            <p class="text-sm text-warm-500 mb-2"><?= e($roleLabel) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($staff['bio'])): ?>
                            <p class="text-xs text-warm-600 line-clamp-2 mb-3">
                                <?= e(substr($staff['bio'], 0, 100)) ?><?= strlen($staff['bio']) > 100 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                        <div class="flex items-center justify-center gap-4 text-xs text-warm-500">
                            <span class="flex items-center gap-1">
                                <i class="fa-solid fa-eye"></i>
                                <?= number_format($staff['profile_views'], 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                    <div class="px-6 py-3 bg-warm-50 border-t border-warm-200 text-center">
                        <span class="text-xs font-semibold text-cypress-700 group-hover:text-cypress-900">
                            Lihat Profil <i class="fa-solid fa-arrow-right ml-1 group-hover:translate-x-1 transition-transform"></i>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="mt-12 text-center">
        <a href="<?= base_url() ?>/kontak" class="inline-flex items-center gap-2 px-6 py-3 bg-cypress-900 hover:bg-cypress-800 text-white rounded-lg font-semibold text-sm transition">
            <i class="fa-solid fa-envelope"></i>
            Hubungi Tim Kami
        </a>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>