<?php
require_once __DIR__ . '/../config/database.php';

$profil = get_profil_masjid();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . base_url() . '/home');
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        id, username, nama_lengkap, avatar, bio, jabatan, social_links,
        is_public_profile, profile_views, created_at, role
    FROM users
    WHERE id = ? AND is_public_profile = 1 AND status = 'aktif'
    LIMIT 1
");
$stmt->execute([$id]);
$staff = $stmt->fetch();

if (!$staff) {
    header('Location: ' . base_url() . '/home');
    exit;
}

$pdo->prepare("UPDATE users SET profile_views = profile_views + 1 WHERE id = ?")->execute([$id]);

$socialData = [];
if (!empty($staff['social_links'])) {
    $socialData = json_decode($staff['social_links'], true) ?? [];
}

$avatarPath = trim((string)($staff['avatar'] ?? ''));
$avatarUrl = $avatarPath !== '' ? upload_url($avatarPath) : '';

$namaBersih = preg_replace('/[^A-Za-z0-9]/', '', $staff['nama_lengkap']);
$initials = strtoupper(substr($namaBersih, 0, 2));
if ($initials === '') {
    $initials = 'S';
}

$pageTitle = $staff['nama_lengkap'] . ' · Tim ' . $profil['nama_masjid'];
$activeNav = 'tim-kami';

require_once __DIR__ . '/../layouts/public_header.php';
?>

<style>
.staff-hero {
    background: linear-gradient(135deg, #0d1d15 0%, #07100b 100%);
    position: relative;
    overflow: hidden;
}
.staff-hero::before {
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

<div class="staff-hero pt-16 pb-12 text-white relative">
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-antique-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <a href="<?= base_url() ?>/tim-kami" class="inline-flex items-center gap-2 text-antique-300 hover:text-white text-sm font-medium mb-6 transition">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali ke Tim Kami
        </a>
        <div class="flex flex-col lg:flex-row items-center lg:items-start gap-10">
            <div class="flex-shrink-0">
                <?php if ($avatarUrl): ?>
                    <img src="<?= e($avatarUrl) ?>" alt="<?= e($staff['nama_lengkap']) ?>" class="w-40 h-40 lg:w-48 lg:h-48 rounded-3xl object-cover border-4 border-antique-500/40 shadow-2xl">
                <?php else: ?>
                    <div class="w-40 h-40 lg:w-48 lg:h-48 rounded-3xl bg-antique-600 flex items-center justify-center text-white text-5xl font-bold border-4 border-antique-500/40 shadow-2xl">
                        <?= e($initials) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div>
<h1 class="text-3xl lg:text-4xl font-bold mb-2"><?= e($staff['nama_lengkap']) ?></h1>
                <?php if (!empty($staff['jabatan'])): ?>
                    <p class="text-xl text-antique-300 mb-4 font-semibold"><?= e($staff['jabatan']) ?></p>
                <?php endif; ?>
                <div class="flex flex-wrap gap-4 text-sm text-white/80">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-eye"></i>
                        <span><?= number_format($staff['profile_views'], 0, ',', '.') ?> dilihat</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-calendar"></i>
                        <span>Bergabung <?= date('d M Y', strtotime($staff['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <?php if (!empty($staff['bio'])): ?>
                <div class="bg-white rounded-xl shadow-soft border border-warm-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-warm-900 mb-4">Tentang</h2>
                    <div class="prose max-w-none text-warm-800">
                        <?= nl2br(e($staff['bio'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty(array_filter($socialData))): ?>
                <div class="bg-white rounded-xl shadow-soft border border-warm-200 p-6">
                    <h2 class="text-xl font-bold text-warm-900 mb-4">Media Sosial</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php if (!empty($socialData['whatsapp'])): ?>
                            <a href="https://wa.me/<?= e(str_replace(['+', ' ', '-'], '', $socialData['whatsapp'])) ?>" target="_blank" class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-xl border border-green-200 transition-colors">
                                <i class="fa-brands fa-whatsapp text-green-600 text-2xl mb-2"></i>
                                <span class="text-xs font-semibold text-green-800">WhatsApp</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($socialData['instagram'])): ?>
                            <a href="https://instagram.com/<?= e(ltrim($socialData['instagram'], '@')) ?>" target="_blank" class="flex flex-col items-center p-4 bg-pink-50 hover:bg-pink-100 rounded-xl border border-pink-200 transition-colors">
                                <i class="fa-brands fa-instagram text-pink-600 text-2xl mb-2"></i>
                                <span class="text-xs font-semibold text-pink-800">Instagram</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($socialData['facebook'])): ?>
                            <a href="<?= e($socialData['facebook']) ?>" target="_blank" class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-xl border border-blue-200 transition-colors">
                                <i class="fa-brands fa-facebook text-blue-600 text-2xl mb-2"></i>
                                <span class="text-xs font-semibold text-blue-800">Facebook</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($socialData['linkedin'])): ?>
                            <a href="<?= e($socialData['linkedin']) ?>" target="_blank" class="flex flex-col items-center p-4 bg-blue-100 hover:bg-blue-200 rounded-xl border border-blue-300 transition-colors">
                                <i class="fa-brands fa-linkedin text-blue-700 text-2xl mb-2"></i>
                                <span class="text-xs font-semibold text-blue-900">LinkedIn</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-soft border border-warm-200 p-6">
                <h2 class="text-xl font-bold text-warm-900 mb-4">Informasi Kontak</h2>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-user text-antique-600 mt-1"></i>
                        <div>
                            <p class="text-xs text-warm-600">Username</p>
                            <p class="text-sm font-semibold text-warm-900"><?= e($staff['username']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-calendar text-antique-600 mt-1"></i>
                        <div>
                            <p class="text-xs text-warm-600">Bergabung sejak</p>
                            <p class="text-sm font-semibold text-warm-900"><?= date('d M Y', strtotime($staff['created_at'])) ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-eye text-antique-600 mt-1"></i>
                        <div>
                            <p class="text-xs text-warm-600">Profil dilihat</p>
                            <p class="text-sm font-semibold text-warm-900"><?= number_format($staff['profile_views'], 0, ',', '.') ?> kali</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-cypress-50 border border-cypress-200 rounded-xl p-6">
                <h3 class="font-bold text-cypress-900 mb-2">Butuh bantuan?</h3>
                <p class="text-sm text-cypress-800 mb-4">Silakan hubungi kami melalui menu kontak di website</p>
                <a href="<?= base_url() ?>/kontak" class="block w-full py-2 px-4 bg-cypress-900 hover:bg-cypress-800 text-white rounded-lg text-center font-semibold text-sm transition">
                    <i class="fa-solid fa-envelope mr-2"></i> Hubungi Sekarang
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/public_footer.php'; ?>