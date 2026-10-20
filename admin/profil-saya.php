<?php
require_once __DIR__ . '/../config/database.php';

cek_login();

$user = $_SESSION['user'];

if (!in_array($user['role'] ?? '', ['admin', 'bendahara', 'content_admin'])) {
    header('Location: ' . base_url() . '/home');
    exit;
}

$profil = get_profil_masjid();
$pageTitle = 'Profil Saya · ' . $profil['nama_masjid'];

$pesan = '';
$tipe = '';

$stmtUser = $pdo->prepare("
    SELECT
        id,
        username,
        email,
        no_hp,
        nama_lengkap,
        avatar,
        bio,
        jabatan,
        social_links,
        is_public_profile,
        created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmtUser->execute([$user['id']]);
$userDb = $stmtUser->fetch();

if (!$userDb) {
    session_destroy();
    header('Location: ' . base_url() . '/auth/login');
    exit;
}

function upload_foto_profil($inputName = 'foto_profil')
{
    if (
        !isset($_FILES[$inputName]) ||
        $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $file = $_FILES[$inputName];

    if ($file['size'] > 3 * 1024 * 1024) {
        return false;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions)) {
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mimeType, $allowedMimes)) {
        return false;
    }

    $fileName = 'profile_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
    $targetDir = __DIR__ . '/../uploads/profil';

    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0777, true);
    }

    $targetPath = $targetDir . '/' . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/profil/' . $fileName;
    }

    return false;
}

function hapus_foto_lama($avatar)
{
    if (empty($avatar)) {
        return;
    }

    if (strpos($avatar, 'uploads/profil/') !== 0) {
        return;
    }

    $filePath = __DIR__ . '/../' . $avatar;

    if (is_file($filePath)) {
        @unlink($filePath);
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['aksi'] ?? '') === 'update_profil'
) {

    $nama = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $noHp = trim($_POST['no_hp'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $pass = $_POST['password_baru'] ?? '';
    $isPublic = isset($_POST['is_public_profile']) ? 1 : 0;

    $socialLinks = [
        'whatsapp' => trim($_POST['social_whatsapp'] ?? ''),
        'instagram' => trim($_POST['social_instagram'] ?? ''),
        'facebook' => trim($_POST['social_facebook'] ?? ''),
        'linkedin' => trim($_POST['social_linkedin'] ?? ''),
    ];

    if ($nama === '') {
        $pesan = 'Nama lengkap tidak boleh kosong.';
        $tipe = 'error';
    } elseif ($pass !== '' && strlen($pass) < 6) {
        $pesan = 'Kata sandi baru minimal 6 karakter.';
        $tipe = 'error';
    } else {

        $fotoBaru = upload_foto_profil('foto_profil');

        if ($fotoBaru === false) {
            $pesan = 'Foto gagal diupload. Gunakan JPG, PNG, atau WEBP dengan ukuran maksimal 3MB.';
            $tipe = 'error';
        } else {

            if ($fotoBaru !== null && !empty($userDb['avatar']) && $userDb['avatar'] !== $fotoBaru) {
                hapus_foto_lama($userDb['avatar']);
            }

        $socialJson = json_encode($socialLinks);

        // Cek email duplikat (kecuali email sama dengan yang lama)
        $stmtCekEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmtCekEmail->execute([$email, $user['id']]);
        $existingUser = $stmtCekEmail->fetch();

        if ($existingUser) {
            $pesan = 'Email ' . e($email) . ' sudah digunakan oleh pengguna lain.';
            $tipe = 'error';
        } else {
            if ($pass !== '') {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET nama_lengkap = ?, email = ?, no_hp = ?, bio = ?, jabatan = ?, 
                        social_links = ?, is_public_profile = ?, avatar = ?, password = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nama, $email, $noHp, $bio, $jabatan, $socialJson, $isPublic, $fotoBaru, $hash, $user['id']]);
                $pesan = 'Profil dan kata sandi berhasil diperbarui.';
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET nama_lengkap = ?, email = ?, no_hp = ?, bio = ?, jabatan = ?, 
                        social_links = ?, is_public_profile = ?, avatar = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nama, $email, $noHp, $bio, $jabatan, $socialJson, $isPublic, $fotoBaru, $user['id']]);
                $pesan = 'Profil berhasil diperbarui.';
            }
        }

            if ($fotoBaru !== null) {
                $_SESSION['user']['avatar'] = $fotoBaru;
            }

            $_SESSION['user']['nama'] = $nama;
            $_SESSION['user']['no_hp'] = $noHp;

            $tipe = 'success';

            $stmtUser->execute([$user['id']]);
            $userDb = $stmtUser->fetch();
        }
    }
}

$namaBersih = preg_replace('/[^A-Za-z0-9]/', '', $userDb['nama_lengkap']);
$initials = strtoupper(substr($namaBersih, 0, 2));
if ($initials === '') {
    $initials = 'S';
}

$socialData = [];
if (!empty($userDb['social_links'])) {
    $socialData = json_decode($userDb['social_links'], true) ?? [];
}

$avatarPath = trim((string)($userDb['avatar'] ?? ''));
$avatarUrl = $avatarPath !== '' ? upload_url($avatarPath) : '';

$pageSubtitle = 'Kelola profil pribadi dan visibilitas publik Anda';
$activeMenu = 'profil-saya';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">Profil Saya</h2>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">Kelola informasi pribadi, foto, bio & visibilitas publik</p>
    </div>
</div>

<?php if ($pesan): ?>
    <div class="mb-6 p-4 rounded-lg border <?= $tipe === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800' ?>">
        <div class="flex items-start gap-3">
            <i class="fa-solid <?= $tipe === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> mt-0.5"></i>
            <p class="text-sm font-medium"><?= e($pesan) ?></p>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="lg:col-span-1">
        <div class="bg-gradient-to-br from-cypress-900 to-cypress-950 rounded-2xl p-6 text-white shadow-luxury border border-antique-500/20">
            <div class="flex flex-col items-center text-center">
                <div class="relative mb-4">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= e($avatarUrl) ?>" alt="<?= e($userDb['nama_lengkap']) ?>" class="w-24 h-24 rounded-full object-cover border-4 border-antique-500/30">
                    <?php else: ?>
                        <div class="w-24 h-24 rounded-full bg-antique-600 flex items-center justify-center text-white text-3xl font-bold border-4 border-antique-500/30">
                            <?= e($initials) ?>
                        </div>
                    <?php endif; ?>
                    <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-antique-500 rounded-full flex items-center justify-center border-4 border-cypress-900">
                        <i class="fa-solid fa-user text-xs text-cypress-950"></i>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-white mb-1"><?= e($userDb['nama_lengkap']) ?></h3>
                <p class="text-xs text-antique-300 uppercase tracking-widest font-semibold mb-1"><?= e(str_replace('_', ' ', $user['role'])) ?></p>
                <?php if (!empty($userDb['jabatan'])): ?>
                    <p class="text-xs text-emerald-300 mb-3"><?= e($userDb['jabatan']) ?></p>
                <?php endif; ?>
                <div class="w-full pt-4 border-t border-white/10 space-y-2 text-left">
                    <div class="flex items-center gap-2 text-xs">
                        <i class="fa-solid fa-envelope w-4 text-antique-400"></i>
                        <span class="text-white/80 truncate"><?= e($userDb['email'] ?: '-') ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <i class="fa-solid fa-phone w-4 text-antique-400"></i>
                        <span class="text-white/80"><?= e($userDb['no_hp'] ?: '-') ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <i class="fa-solid fa-calendar w-4 text-antique-400"></i>
                        <span class="text-white/80">Bergabung <?= date('d M Y', strtotime($userDb['created_at'])) ?></span>
                    </div>
                </div>
                <?php if ($userDb['is_public_profile']): ?>
                    <div class="mt-4 w-full">
                        <a href="<?= base_url() ?>/home/profil-staff?id=<?= $userDb['id'] ?>" target="_blank" class="block w-full py-2 px-3 bg-antique-600 hover:bg-antique-500 rounded-lg text-xs font-semibold text-white transition">
                            <i class="fa-solid fa-external-link-alt mr-1"></i> Lihat Profil Publik
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2">
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-soft border border-warm-200 p-6 space-y-6">
            <input type="hidden" name="aksi" value="update_profil">
            
            <div class="border-b border-warm-200 pb-4">
                <h3 class="text-lg font-bold text-warm-900 flex items-center gap-2">
                    <i class="fa-solid fa-id-card text-antique-600"></i>
                    Informasi Dasar
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-warm-800 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_lengkap" value="<?= e($userDb['nama_lengkap']) ?>" required class="w-full px-4 py-2.5 border border-warm-300 rounded-lg focus:ring-2 focus:ring-cypress-600 focus:border-transparent text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-warm-800 mb-2">Jabatan/Posisi</label>
                    <input type="text" name="jabatan" value="<?= e($userDb['jabatan'] ?? '') ?>" placeholder="Contoh: Ketua DKM" class="w-full px-4 py-2.5 border border-warm-300 rounded-lg focus:ring-2 focus:ring-cypress-600 focus:border-transparent text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-warm-800 mb-2">Email</label>
                    <input type="email" name="email" value="<?= e($userDb['email'] ?? '') ?>" class="w-full px-4 py-2.5 border border-warm-300 rounded-lg focus:ring-2 focus:ring-cypress-600 focus:border-transparent text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-warm-800 mb-2">No. HP/WhatsApp</label>
                    <input type="text" name="no_hp" value="<?= e($userDb['no_hp'] ?? '') ?>" placeholder="08xx" class="w-full px-4 py-2.5 border border-warm-300 rounded-lg focus:ring-2 focus:ring-cypress-600 focus:border-transparent text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-warm-800 mb-2">Bio/Deskripsi Singkat</label>
                <textarea name="bio" rows="3" placeholder="Ceritakan sedikit tentang Anda dan kontribusi Anda di masjid..." class="w-full px-4 py-2.5 border border-warm-300 rounded-lg focus:ring-2 focus:ring-cypress-600 focus:border-transparent text-sm resize-none"><?= e($userDb['bio'] ?? '') ?></textarea>
                <p class="text-xs text-warm-600 mt-1">Bio akan ditampilkan pada profil publik jika diaktifkan</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-warm-800 mb-2">Foto Profil</label>
                <input type="file" name="foto_profil" accept="image/jpeg,image/png,image/webp" class="w-full px-4 py-2.5 border border-warm-300 rounded-lg focus:ring-2 focus:ring-cypress-600 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cypress-900 file:text-white file:text-xs file:font-semibold hover:file:bg-cypress-800">
                <p class="text-xs text-warm-600 mt-1">JPG, PNG, atau WEBP. Maksimal 3MB.</p>
            </div>

            <div class="border-b border-warm-200 pb-4 pt-2">
                <h3 class="text-lg font-bold text-warm-900 flex items-center gap-2">
                    <i class="fa-solid fa-share-nodes text-antique-600"></i>
                    Media Sosial
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-warm-800 mb-2"><i class="fa-brands fa-whatsapp text-green-600"></i> WhatsApp</label>
                    <input type="text" name="social_whatsapp" value="<?= e($socialData['whatsapp'] ?? '') ?>" placeholder="08xx" class="w-full px-4 py-2.5 border border-warm-300 rounded-lg focus:ring-2 focus:ring-cypress-600 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-warm-800 mb-2"><i class="fa-brands fa-instagram text-pink-600"></i> Instagram</label>
                    <input type="text" name="social_instagram" value="<?= e($socialData['instagram'] ?? '') ?>" placeholder="@username" class="w-full px-4 py-2.5 border border-warm-300 rounded-lg focus:ring-2 focus:ring-cypress-600 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-warm-800 mb-2"><i class="fa-brands fa-facebook text-blue-600"></i> Facebook</label>
                    <input type="text" name="social_facebook" value="<?= e($socialData['facebook'] ?? '') ?>" placeholder="URL profil" class="w-full px-4 py-2.5 border border-warm-300 rounded-lg focus:ring-2 focus:ring-cypress-600 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-warm-800 mb-2"><i class="fa-brands fa-linkedin text-blue-700"></i> LinkedIn</label>
                    <input type="text" name="social_linkedin" value="<?= e($socialData['linkedin'] ?? '') ?>" placeholder="URL profil" class="w-full px-4 py-2.5 border border-warm-300 rounded-lg focus:ring-2 focus:ring-cypress-600 text-sm">
                </div>
            </div>

            <div class="border-b border-warm-200 pb-4 pt-2">
                <h3 class="text-lg font-bold text-warm-900 flex items-center gap-2">
                    <i class="fa-solid fa-lock text-antique-600"></i>
                    Keamanan & Visibilitas
                </h3>
            </div>

            <div>
                <label class="block text-sm font-semibold text-warm-800 mb-2">Kata Sandi Baru (opsional)</label>
                <input type="password" name="password_baru" placeholder="Kosongkan jika tidak ingin mengubah" class="w-full px-4 py-2.5 border border-warm-300 rounded-lg focus:ring-2 focus:ring-cypress-600 text-sm">
                <p class="text-xs text-warm-600 mt-1">Minimal 6 karakter</p>
            </div>

            <div class="bg-antique-50 border border-antique-200 rounded-lg p-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="is_public_profile" value="1" <?= $userDb['is_public_profile'] ? 'checked' : '' ?> class="mt-1 w-5 h-5 text-cypress-600 border-warm-300 rounded focus:ring-2 focus:ring-cypress-600">
                    <div>
                        <span class="text-sm font-semibold text-warm-900 block">Tampilkan profil saya di halaman publik</span>
                        <p class="text-xs text-warm-700 mt-1">Profil Anda akan muncul di halaman "Tim Kami" dan dapat diakses oleh jamaah</p>
                    </div>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="px-6 py-2.5 bg-cypress-900 hover:bg-cypress-800 text-white rounded-lg font-semibold text-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Perubahan
                </button>
                <a href="dashboard" class="px-6 py-2.5 bg-warm-200 hover:bg-warm-300 text-warm-900 rounded-lg font-semibold text-sm transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
