<?php
require_once __DIR__ . '/../../config/database.php';

cek_login();

$user = $_SESSION['user'];

if (($user['role'] ?? '') !== 'donatur') {
    header('Location: ../admin/dashboard.php');
    exit;
}

$profil = get_profil_masjid();

$pageTitle = 'Profil Donatur · ' . $profil['nama_masjid'];

$pesan = '';
$tipe = '';

/*
|--------------------------------------------------------------------------
| AMBIL DATA USER TERBARU
|--------------------------------------------------------------------------
*/

$stmtUser = $pdo->prepare("
    SELECT
        id,
        username,
        email,
        no_hp,
        nama_lengkap,
        role,
        avatar,
        status,
        created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmtUser->execute([$user['id']]);

$userDb = $stmtUser->fetch();

if (!$userDb) {
    session_destroy();

    header('Location: ../auth/login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| HELPER UPLOAD FOTO PROFIL
|--------------------------------------------------------------------------
*/

function upload_foto_profil($inputName = 'foto_profil')
{
    if (
        !isset($_FILES[$inputName]) ||
        $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    if (
        $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK
    ) {
        return false;
    }

    $file = $_FILES[$inputName];

    /*
    | Maksimal 3MB
    */
    if ($file['size'] > 3 * 1024 * 1024) {
        return false;
    }

    /*
    | Cek ekstensi
    */
    $allowedExtensions = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    $extension = strtolower(
        pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        )
    );

    if (
        !in_array(
            $extension,
            $allowedExtensions,
            true
        )
    ) {
        return false;
    }

    /*
    | Cek MIME TYPE
    */
    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    $mime = finfo_file(
        $finfo,
        $file['tmp_name']
    );

    finfo_close($finfo);

    $allowedMime = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    if (
        !in_array(
            $mime,
            $allowedMime,
            true
        )
    ) {
        return false;
    }

    /*
    | Nama file baru
    */
    $fileName =
        'profile_' .
        time() .
        '_' .
        bin2hex(random_bytes(5)) .
        '.' .
        $extension;

    /*
    | Folder
    */
    $targetDir =
        __DIR__ .
        '/../uploads/profil';

    if (!is_dir($targetDir)) {
        @mkdir(
            $targetDir,
            0777,
            true
        );
    }

    $targetPath =
        $targetDir .
        '/' .
        $fileName;

    /*
    | Pindahkan file
    */
    if (
        move_uploaded_file(
            $file['tmp_name'],
            $targetPath
        )
    ) {
        return 'uploads/profil/' . $fileName;
    }

    return false;
}


/*
|--------------------------------------------------------------------------
| HAPUS FOTO LAMA
|--------------------------------------------------------------------------
*/

function hapus_foto_lama($avatar)
{
    if (empty($avatar)) {
        return;
    }

    /*
    | Hanya hapus file yang berada
    | di uploads/profil/
    */
    if (
        strpos(
            $avatar,
            'uploads/profil/'
        ) !== 0
    ) {
        return;
    }

    $filePath =
        __DIR__ .
        '/../' .
        $avatar;

    if (
        is_file($filePath)
    ) {
        @unlink($filePath);
    }
}


/*
|--------------------------------------------------------------------------
| UPDATE PROFIL
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['aksi'] ?? '') === 'update_profil'
) {

    $nama =
        trim(
            $_POST['nama_lengkap'] ?? ''
        );

    $noHp =
        trim(
            $_POST['no_hp'] ?? ''
        );

    $pass =
        $_POST['password_baru'] ?? '';

    /*
    | Validasi nama
    */
    if ($nama === '') {

        $pesan =
            'Nama lengkap tidak boleh kosong.';

        $tipe =
            'error';

    }

    /*
    | Validasi password
    */
    elseif (
        $pass !== '' &&
        strlen($pass) < 6
    ) {

        $pesan =
            'Kata sandi baru minimal 6 karakter.';

        $tipe =
            'error';

    }

    else {

        /*
        | Cek apakah ada foto baru
        */
        $fotoBaru =
            upload_foto_profil(
                'foto_profil'
            );

        if (
            $fotoBaru === false
        ) {

            $pesan =
                'Foto gagal diupload. Gunakan JPG, PNG, atau WEBP dengan ukuran maksimal 3MB.';

            $tipe =
                'error';

        }

        else {

            /*
            | Jika password diubah
            */
            if ($pass !== '') {

                $hash =
                    password_hash(
                        $pass,
                        PASSWORD_DEFAULT
                    );

                if (
                    $fotoBaru !== null
                ) {

                    $stmt =
                        $pdo->prepare("
                            UPDATE users
                            SET
                                nama_lengkap = ?,
                                no_hp = ?,
                                password = ?,
                                avatar = ?
                            WHERE id = ?
                        ");

                    $stmt->execute([
                        $nama,
                        $noHp,
                        $hash,
                        $fotoBaru,
                        $user['id']
                    ]);

                } else {

                    $stmt =
                        $pdo->prepare("
                            UPDATE users
                            SET
                                nama_lengkap = ?,
                                no_hp = ?,
                                password = ?
                            WHERE id = ?
                        ");

                    $stmt->execute([
                        $nama,
                        $noHp,
                        $hash,
                        $user['id']
                    ]);
                }

            }

            /*
            | Jika password tidak diubah
            */
            else {

                if (
                    $fotoBaru !== null
                ) {

                    $stmt =
                        $pdo->prepare("
                            UPDATE users
                            SET
                                nama_lengkap = ?,
                                no_hp = ?,
                                avatar = ?
                            WHERE id = ?
                        ");

                    $stmt->execute([
                        $nama,
                        $noHp,
                        $fotoBaru,
                        $user['id']
                    ]);

                } else {

                    $stmt =
                        $pdo->prepare("
                            UPDATE users
                            SET
                                nama_lengkap = ?,
                                no_hp = ?
                            WHERE id = ?
                        ");

                    $stmt->execute([
                        $nama,
                        $noHp,
                        $user['id']
                    ]);
                }
            }


            /*
            | Hapus foto lama jika berhasil
            | diganti dengan foto baru
            */
            if (
                $fotoBaru !== null &&
                !empty($userDb['avatar']) &&
                $userDb['avatar'] !== $fotoBaru
            ) {

                hapus_foto_lama(
                    $userDb['avatar']
                );
            }


            /*
            | Update SESSION
            */
            $_SESSION['user']['nama'] =
                $nama;

            $_SESSION['user']['no_hp'] =
                $noHp;

            if (
                $fotoBaru !== null
            ) {

                $_SESSION['user']['avatar'] =
                    $fotoBaru;
            }


            /*
            | Pesan
            */
            if ($pass !== '') {

                $pesan =
                    'Profil dan kata sandi berhasil diperbarui.';

            } else {

                $pesan =
                    'Profil berhasil diperbarui.';
            }

            $tipe =
                'success';


            /*
            | Ambil ulang data user
            */
            $stmtUser->execute([
                $user['id']
            ]);

            $userDb =
                $stmtUser->fetch();
        }
    }
}


/*
|--------------------------------------------------------------------------
| NOTIFIKASI - TANDAI TERBACA
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['baca_notif'])
) {

    $pdo
        ->prepare("
            UPDATE notifikasi
            SET is_read = 1
            WHERE user_id = ?
        ")
        ->execute([
            $user['id']
        ]);

    header(
        'Location: portal-donatur.php'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| RIWAYAT DONASI
|--------------------------------------------------------------------------
*/

$stmtDonasi = $pdo->prepare("
    SELECT
        d.*,
        p.nama_program
    FROM donasi_online d
    JOIN program_donasi p
        ON d.program_id = p.id
    WHERE
        d.user_id = ?
        OR d.email = ?
    ORDER BY
        d.tanggal_donasi DESC,
        d.id DESC
");

$stmtDonasi->execute([
    $user['id'],
    $user['email'] ?? ''
]);

$riwayatDonasi =
    $stmtDonasi->fetchAll();


/*
|--------------------------------------------------------------------------
| STATISTIK DONASI
|--------------------------------------------------------------------------
*/

$totalDonasiSaya = 0;
$totalDonasiVerif = 0;
$jumlahTransaksi =
    count($riwayatDonasi);

foreach (
    $riwayatDonasi as $r
) {

    if (
        $r['status'] ===
        'diverifikasi'
    ) {

        $totalDonasiVerif +=
            (float)$r['nominal'];
    }

    $totalDonasiSaya +=
        (float)$r['nominal'];
}


/*
|--------------------------------------------------------------------------
| NOTIFIKASI
|--------------------------------------------------------------------------
*/

$stmtNotif = $pdo->prepare("
    SELECT *
    FROM notifikasi
    WHERE user_id = ?
    ORDER BY id DESC
    LIMIT 5
");

$stmtNotif->execute([
    $user['id']
]);

$daftarNotifikasi =
    $stmtNotif->fetchAll();


/*
|--------------------------------------------------------------------------
| DATA PROFIL
|--------------------------------------------------------------------------
*/

$namaDonatur =
    $userDb['nama_lengkap']
    ?: ($user['nama'] ?? 'Donatur');

$emailDonatur =
    $userDb['email']
    ?? ($user['email'] ?? '');

$noHpDonatur =
    $userDb['no_hp']
    ?? ($user['no_hp'] ?? '');

$statusAkun =
    $userDb['status']
    ?? 'aktif';

$tanggalDaftar =
    $userDb['created_at']
    ?? date('Y-m-d');

$avatarPath =
    trim(
        (string)(
            $userDb['avatar']
            ?? ''
        )
    );

$avatarUrl =
    $avatarPath !== ''
        ? upload_url($avatarPath)
        : '';

/*
|--------------------------------------------------------------------------
| INITIAL
|--------------------------------------------------------------------------
*/

$namaBersih =
    preg_replace(
        '/[^A-Za-z0-9]/',
        '',
        $namaDonatur
    );

$initials =
    strtoupper(
        substr(
            $namaBersih,
            0,
            2
        )
    );

if ($initials === '') {
    $initials = 'D';
}


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

$statusAktif =
    $statusAkun === 'aktif';

$statusLabel =
    $statusAktif
        ? 'Aktif'
        : ucfirst($statusAkun);

$statusDot =
    $statusAktif
        ? 'bg-emerald-500'
        : 'bg-rose-500';

$statusText =
    $statusAktif
        ? 'text-emerald-700'
        : 'text-rose-700';
