<?php
// users-admin.php - Manajemen Pengguna & Hak Akses Multi-Role RBAC (Scope 26)
require_once __DIR__ . '/../config/database.php';
cek_role(['admin']); // Khusus Administrator

$profil = get_profil_masjid();
$user   = $_SESSION['user'];
$pesan  = '';
$tipe   = '';

// 1. TAMBAH USER BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $username = trim($_POST['username'] ?? '');
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $noHp     = trim($_POST['no_hp'] ?? '');
    $role     = $_POST['role'] ?? 'donatur';
    $password = $_POST['password'] ?? '';
    $status   = $_POST['status'] ?? 'aktif';

    if (empty($username) || empty($nama) || empty($password)) {
        $pesan = 'Username, nama lengkap, dan kata sandi wajib diisi.';
        $tipe  = 'error';
    } else {
        $stmtCek = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmtCek->execute([$username, $email]);
        if ($stmtCek->fetch()) {
            $pesan = 'Username atau email tersebut sudah terdaftar.';
            $tipe  = 'error';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, no_hp, password, nama_lengkap, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $noHp, $hash, $nama, $role, $status]);

            $pesan = "Pengguna '{$username}' dengan peran '{$role}' berhasil ditambahkan!";
            $tipe  = 'success';
        }
    }
}

// 2. EDIT USER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id       = (int)($_POST['id'] ?? 0);
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $noHp     = trim($_POST['no_hp'] ?? '');
    $role     = $_POST['role'] ?? 'donatur';
    $status   = $_POST['status'] ?? 'aktif';
    $password = $_POST['password'] ?? '';

    if ($id > 0 && !empty($nama)) {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ?, no_hp = ?, role = ?, status = ?, password = ? WHERE id = ?");
            $stmt->execute([$nama, $email, $noHp, $role, $status, $hash, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ?, no_hp = ?, role = ?, status = ? WHERE id = ?");
            $stmt->execute([$nama, $email, $noHp, $role, $status, $id]);
        }

        $pesan = "Data pengguna '{$nama}' berhasil diperbarui!";
        $tipe  = 'success';
    }
}

// 3. HAPUS USER (Kecuali diri sendiri)
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    if ($idHapus === $user['id']) {
        $pesan = 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.';
        $tipe  = 'error';
    } else {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$idHapus]);
        header('Location: users-admin.php?msg=deleted');
        exit;
    }
}

$pag = paginate_data($pdo, "SELECT * FROM users ORDER BY id ASC", [], 10);
$usersList = $pag['items'];

$pageTitle    = 'Manajemen Pengguna & Role · ' . $profil['nama_masjid'];
$activeMenu   = 'users-admin';
$pageSubtitle = 'Akses Kontrol Pengguna & Peran Sistem (RBAC)';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold text-warm-900 font-classic">
            Manajemen Pengguna &amp; Peran (RBAC)
        </h2>
        <p class="text-xs sm:text-sm text-warm-800/70 mt-1">
            Kelola hak akses Administrator, Bendahara Keuangan, Content Administrator, dan Donatur.
        </p>
    </div>

    <button type="button" onclick="bukaModalUser()" class="px-4 py-2.5 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold text-xs shadow-xs transition flex items-center gap-2">
        <i class="fa-solid fa-user-plus"></i>
        <span>Tambah Pengguna Baru</span>
    </button>
</div>

<?php if ($pesan): ?>
    <div class="p-4 rounded-2xl <?= $tipe === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?> text-xs font-semibold flex items-center gap-2">
        <i class="fa-solid <?= $tipe === 'success' ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-red-600' ?>"></i>
        <span><?= e($pesan) ?></span>
    </div>
<?php endif; ?>

<!-- Tabel Daftar User -->
<div class="bg-white rounded-3xl border border-antique-300/50 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-antique-200 flex items-center justify-between">
        <h3 class="font-classic text-base font-bold text-warm-900">
            Daftar Akun Pengguna Sistem
        </h3>
        <span class="text-xs text-warm-800/60 font-semibold"><?= number_format($pag['total']) ?> Pengguna</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-antique-200 bg-warm-50/70 text-warm-800/70 uppercase text-[10px] font-bold">
                    <th class="py-3 px-4">Nama Lengkap &amp; Username</th>
                    <th class="py-3 px-4">Email &amp; No. WhatsApp</th>
                    <th class="py-3 px-4 text-center">Hak Akses (Role)</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4">Terdaftar</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-antique-100">
                <?php foreach ($usersList as $u): ?>
                    <tr class="hover:bg-warm-50/60 transition">
                        <td class="py-3.5 px-4 align-top">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-cypress-900 text-antique-300 font-bold flex items-center justify-center text-xs shrink-0">
                                    <?= strtoupper(substr($u['nama_lengkap'], 0, 1)) ?>
                                </div>
                                <div>
                                    <strong class="font-bold text-sm text-warm-900 block leading-tight"><?= e($u['nama_lengkap']) ?></strong>
                                    <span class="text-[11px] font-mono text-warm-800/60 block mt-0.5">@<?= e($u['username']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 align-top">
                            <span class="text-warm-900 block font-medium"><?= e($u['email'] ?: '-') ?></span>
                            <span class="text-[11px] text-warm-800/60 block mt-0.5"><?= e($u['no_hp'] ?: '-') ?></span>
                        </td>
                        <td class="py-3.5 px-4 align-top text-center whitespace-nowrap">
                            <?php 
                            $badgeClass = 'bg-stone-100 text-stone-700';
                            if ($u['role'] === 'admin') $badgeClass = 'bg-purple-100 text-purple-900 border border-purple-300';
                            elseif ($u['role'] === 'bendahara') $badgeClass = 'bg-emerald-100 text-emerald-900 border border-emerald-300';
                            elseif ($u['role'] === 'content_admin') $badgeClass = 'bg-blue-100 text-blue-900 border border-blue-300';
                            elseif ($u['role'] === 'donatur') $badgeClass = 'bg-amber-100 text-amber-900 border border-amber-300';
                            ?>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $badgeClass ?>">
                                <?= str_replace('_', ' ', e($u['role'])) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 align-top text-center whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $u['status'] === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' ?>">
                                <?= e($u['status']) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 align-top whitespace-nowrap text-warm-800/60">
                            <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick='editUser(<?= json_encode($u) ?>)' class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <?php if ($u['id'] !== $user['id']): ?>
                                    <a href="users-admin.php?hapus=<?= $u['id'] ?>" data-hapus data-judul="Hapus Pengguna" data-pesan="Akses pengguna '<?= e($u['nama_lengkap'] ?? '') ?>' akan dinonaktifkan permanen. Lanjutkan?" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50" title="Hapus">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php render_pagination($pag['totalHalaman'], $pag['halaman'], $pag['total'], $pag['dari'], $pag['sampai']); ?>
</div>

<!-- Modal Tambah/Edit User -->
<div id="modalUser" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 border border-antique-300 shadow-2xl space-y-4 text-xs">
        <div class="flex items-center justify-between border-b border-antique-200 pb-3">
            <h3 id="modalUserTitle" class="font-classic text-lg font-bold text-warm-900">Tambah Pengguna Baru</h3>
            <button type="button" onclick="tutupModalUser()" class="text-stone-400 hover:text-stone-700 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="users-admin.php" class="space-y-3.5">
            <input type="hidden" id="userAksi" name="aksi" value="tambah">
            <input type="hidden" id="userId" name="id" value="0">

            <div id="boxUsername">
                <label class="block font-bold text-warm-800 mb-1">Username Login <span class="text-red-500">*</span></label>
                <input type="text" id="inputUsername" name="username" required placeholder="Contoh: bendahara2" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-warm-800 mb-1">Nama Lengkap &amp; Gelar <span class="text-red-500">*</span></label>
                <input type="text" id="inputNama" name="nama_lengkap" required placeholder="Nama lengkap..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Alamat Email</label>
                    <input type="email" id="inputEmail" name="email" placeholder="email@user.com" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">No. WhatsApp</label>
                    <input type="text" id="inputHp" name="no_hp" placeholder="08..." class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Peran Akses (Role)</label>
                    <select id="selectRole" name="role" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-semibold focus:outline-none">
                        <option value="admin">Administrator (Penuh)</option>
                        <option value="bendahara">Bendahara (Keuangan)</option>
                        <option value="content_admin">Content Admin (Media)</option>
                        <option value="donatur">Donatur (Portal)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-warm-800 mb-1">Status Akun</label>
                    <select id="selectStatus" name="status" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 font-semibold focus:outline-none">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label id="labelPassword" class="block font-bold text-warm-800 mb-1">Kata Sandi <span class="text-red-500">*</span></label>
                <input type="password" id="inputPassword" name="password" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 rounded-xl bg-warm-50 border border-antique-300 focus:outline-none">
                <span id="hintPassword" class="text-[10px] text-warm-800/60 hidden block mt-1">Kosongkan jika tidak ingin mengubah kata sandi.</span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="tutupModalUser()" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-700 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-cypress-700 hover:bg-cypress-800 text-white font-bold shadow-xs">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalUser() {
    document.getElementById('userAksi').value = 'tambah';
    document.getElementById('userId').value = '0';
    document.getElementById('modalUserTitle').textContent = 'Tambah Pengguna Baru';
    document.getElementById('boxUsername').classList.remove('hidden');
    document.getElementById('inputUsername').required = true;
    document.getElementById('inputUsername').value = '';
    document.getElementById('inputNama').value = '';
    document.getElementById('inputEmail').value = '';
    document.getElementById('inputHp').value = '';
    document.getElementById('inputPassword').required = true;
    document.getElementById('inputPassword').value = '';
    document.getElementById('labelPassword').innerHTML = 'Kata Sandi <span class="text-red-500">*</span>';
    document.getElementById('hintPassword').classList.add('hidden');
    document.getElementById('modalUser').classList.remove('hidden');
}

function editUser(u) {
    document.getElementById('userAksi').value = 'edit';
    document.getElementById('userId').value = u.id;
    document.getElementById('modalUserTitle').textContent = 'Edit Pengguna @' + u.username;
    document.getElementById('boxUsername').classList.add('hidden');
    document.getElementById('inputUsername').required = false;
    document.getElementById('inputNama').value = u.nama_lengkap;
    document.getElementById('inputEmail').value = u.email || '';
    document.getElementById('inputHp').value = u.no_hp || '';
    document.getElementById('selectRole').value = u.role;
    document.getElementById('selectStatus').value = u.status;
    document.getElementById('inputPassword').required = false;
    document.getElementById('inputPassword').value = '';
    document.getElementById('labelPassword').textContent = 'Ubah Kata Sandi (Opsional)';
    document.getElementById('hintPassword').classList.remove('hidden');
    document.getElementById('modalUser').classList.remove('hidden');
}

function tutupModalUser() {
    document.getElementById('modalUser').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
