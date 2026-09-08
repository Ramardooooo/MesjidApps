<?php
require_once __DIR__ . '/../config/database.php';

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
    ORDER BY
        d.tanggal_donasi DESC,
        d.id DESC
");

$stmtDonasi->execute([
    $user['id']
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

?>
<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= e($pageTitle) ?>
    </title>


    <!-- =====================================================
         TAILWIND
    ====================================================== -->

    <script src="https://cdn.tailwindcss.com"></script>

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
                            700: '#183728',
                            800: '#11271d',
                            900: '#0d1d15',
                            950: '#07130d'

                        },

                        antique: {

                            50: '#fdfbf7',
                            100: '#f8f4ec',
                            200: '#ebe1cc',
                            300: '#dfc896',
                            500: '#c5a059',
                            600: '#b08a42',
                            700: '#8c6b2d',
                            800: '#6d521f'

                        },

                        warm: {

                            50: '#fbf9f5',
                            100: '#f5f1e8',
                            200: '#ebe4d3',
                            800: '#242b26',
                            900: '#191f1b'

                        }

                    },

                    boxShadow: {

                        soft:
                            '0 10px 30px rgba(13,29,21,.07)',

                        luxury:
                            '0 16px 40px rgba(13,29,21,.13)'

                    }

                }

            }

        };

    </script>


    <!-- =====================================================
         CLASSIC THEME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="../assets/css/classic-theme.css"
    >


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >


    <style>

        /*
        ==========================================================
        BACKGROUND UTAMA
        ISLAMIC GEOMETRIC ORNAMENT
        ==========================================================
        */

        body {

            background-color:
                #fbf9f5;

            background-image:

                radial-gradient(
                    circle at 50% 0%,
                    rgba(
                        197,
                        160,
                        89,
                        .12
                    ),
                    transparent 38%
                ),

                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' viewBox='0 0 120 120'%3E%3Cg fill='none' stroke='%23183728' stroke-opacity='.075' stroke-width='1'%3E%3Cpath d='M60 4L116 60 60 116 4 60Z'/%3E%3Cpath d='M60 22L98 60 60 98 22 60Z'/%3E%3Cpath d='M60 40L80 60 60 80 40 60Z'/%3E%3Ccircle cx='60' cy='60' r='16'/%3E%3Cpath d='M60 4V116M4 60H116M20 20L100 100M100 20L20 100'/%3E%3C/g%3E%3Cg fill='none' stroke='%23c5a059' stroke-opacity='.055' stroke-width='1'%3E%3Cpath d='M0 0L120 120M120 0L0 120'/%3E%3C/g%3E%3C/svg%3E");

            background-size:
                auto,
                120px 120px;

            background-attachment:
                fixed;

        }


        /*
        ==========================================================
        SIDEBAR
        ==========================================================
        */

        .sidebar-pattern {

            background-color:
                #07130d;

            background-image:

                radial-gradient(
                    circle at 20% 10%,
                    rgba(
                        197,
                        160,
                        89,
                        .08
                    ),
                    transparent 30%
                ),

                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='none' stroke='%23c5a059' stroke-opacity='.045'%3E%3Cpath d='M40 0L80 40 40 80 0 40Z'/%3E%3Cpath d='M40 15L65 40 40 65 15 40Z'/%3E%3Ccircle cx='40' cy='40' r='10'/%3E%3C/g%3E%3C/svg%3E");

            background-size:
                auto,
                80px 80px;

        }


        /*
        ==========================================================
        ISLAMIC CARD
        ==========================================================
        */

        .islamic-card {

            background-color:
                #0d1d15;

            background-image:

                linear-gradient(
                    135deg,
                    rgba(
                        197,
                        160,
                        89,
                        .08
                    ),
                    transparent 35%
                ),

                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill='none' stroke='%23c5a059' stroke-opacity='.075' stroke-width='1'%3E%3Cpath d='M50 3L97 50 50 97 3 50Z'/%3E%3Cpath d='M50 18L82 50 50 82 18 50Z'/%3E%3Cpath d='M50 32L68 50 50 68 32 50Z'/%3E%3Ccircle cx='50' cy='50' r='8'/%3E%3Cpath d='M50 3V97M3 50H97M15 15L85 85M85 15L15 85'/%3E%3C/g%3E%3C/svg%3E");

            background-size:
                auto,
                100px 100px;

        }


        /*
        ==========================================================
        PROFILE HERO
        ==========================================================
        */

        .profile-hero {

            background-color:
                #0d1d15;

            background-image:

                radial-gradient(
                    circle at 80% 20%,
                    rgba(
                        197,
                        160,
                        89,
                        .15
                    ),
                    transparent 35%
                ),

                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140' viewBox='0 0 140 140'%3E%3Cg fill='none' stroke='%23c5a059' stroke-opacity='.08' stroke-width='1'%3E%3Cpath d='M70 3L137 70 70 137 3 70Z'/%3E%3Cpath d='M70 25L115 70 70 115 25 70Z'/%3E%3Cpath d='M70 45L95 70 70 95 45 70Z'/%3E%3Ccircle cx='70' cy='70' r='12'/%3E%3Cpath d='M70 3V137M3 70H137M21 21L119 119M119 21L21 119'/%3E%3C/g%3E%3C/svg%3E");

            background-size:
                auto,
                140px 140px;

        }


        /*
        ==========================================================
        PROFILE IMAGE
        ==========================================================
        */

        .profile-photo {

            box-shadow:
                0 0 0 4px
                rgba(
                    197,
                    160,
                    89,
                    .18
                ),

                0 12px 30px
                rgba(
                    0,
                    0,
                    0,
                    .25
                );

        }


        /*
        ==========================================================
        UPLOAD PHOTO BUTTON
        ==========================================================
        */

        .photo-upload-button {

            position:
                absolute;

            right:
                -4px;

            bottom:
                -4px;

            width:
                32px;

            height:
                32px;

            border-radius:
                9999px;

            background:
                #c5a059;

            color:
                #11271d;

            border:
                3px solid
                #0d1d15;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            cursor:
                pointer;

            transition:
                all .2s ease;

        }

        .photo-upload-button:hover {

            transform:
                scale(1.08);

            filter:
                brightness(1.08);

        }


        /*
        ==========================================================
        FILE INPUT
        ==========================================================
        */

        #foto_profil {

            display:
                none;

        }


        /*
        ==========================================================
        MODAL
        ==========================================================
        */

        .modal-backdrop {

            background:
                rgba(
                    7,
                    19,
                    13,
                    .72
                );

            backdrop-filter:
                blur(5px);

        }


        /*
        ==========================================================
        MOBILE SIDEBAR
        ==========================================================
        */

        .mobile-sidebar-open
        .sidebar-mobile {

            transform:
                translateX(0);

        }

        .mobile-sidebar-open
        .sidebar-overlay {

            display:
                block;

        }

    </style>

</head>


<body
    class="
        min-h-screen
        text-warm-900
        antialiased
    "
>


<div
    id="appShell"
    class="min-h-screen"
>


    <!-- =====================================================
         SIDEBAR OVERLAY
    ====================================================== -->

    <div
        class="
            sidebar-overlay

            hidden

            fixed
            inset-0

            z-40

            bg-black/50

            lg:hidden
        "

        onclick="closeSidebar()"
    ></div>



    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside
        class="
            sidebar-mobile

            fixed
            inset-y-0
            left-0

            z-50

            w-[250px]

            sidebar-pattern

            text-white

            border-r
            border-antique-500/15

            flex
            flex-col

            -translate-x-full

            lg:translate-x-0

            transition-transform
            duration-300
        "
    >


        <!-- BRAND -->

        <div
            class="
                h-[69px]

                px-4

                border-b
                border-white/10

                flex
                items-center
                gap-3
            "
        >

            <a
                href="../index.php"

                class="
                    w-10
                    h-10

                    rounded-xl

                    bg-cypress-900

                    border
                    border-antique-500/25

                    flex
                    items-center
                    justify-center

                    text-antique-500
                "
            >

                <i
                    class="
                        fa-solid
                        fa-hand-holding-heart
                    "
                ></i>

            </a>


            <div>

                <div
                    class="
                        text-[15px]
                        font-bold
                    "
                >
                    Masjid Donasi
                </div>

                <div
                    class="
                        text-[10px]
                        text-antique-500
                    "
                >
                    Donatur Area
                </div>

            </div>

        </div>



        <!-- USER MINI PROFILE -->

        <div
            class="
                px-2.5
                pt-[18px]
            "
        >

            <div
                class="
                    rounded-2xl

                    bg-[#1b2822]

                    border
                    border-white/5

                    px-3.5
                    py-3

                    flex
                    items-center
                    gap-3
                "
            >

                <?php if ($avatarUrl): ?>

                    <img
                        src="<?= e($avatarUrl) ?>"

                        alt="Foto profil"

                        class="
                            w-10
                            h-10

                            rounded-xl

                            object-cover

                            border
                            border-antique-500/30
                        "
                    >

                <?php else: ?>

                    <div
                        class="
                            w-10
                            h-10

                            rounded-xl

                            bg-cypress-800

                            border
                            border-antique-500/30

                            flex
                            items-center
                            justify-center

                            text-antique-300

                            font-bold
                            text-xs
                        "
                    >
                        <?= e($initials) ?>
                    </div>

                <?php endif; ?>


                <div
                    class="min-w-0"
                >

                    <div
                        class="
                            text-xs
                            font-bold
                            truncate
                        "
                    >
                        <?= e($namaDonatur) ?>
                    </div>

                    <div
                        class="
                            text-[10px]
                            text-antique-500
                            mt-1
                        "
                    >
                        Donatur
                    </div>

                </div>

            </div>

        </div>



        <!-- MENU -->

        <nav
            class="
                px-2.5
                pt-5

                space-y-1

                flex-1
            "
        >

            <div
                class="
                    px-3.5
                    mb-2

                    text-[10px]

                    uppercase
                    tracking-widest

                    text-antique-500/70

                    font-semibold
                "
            >
                Menu
            </div>


            <a
                href="portal-donatur.php"

                class="
                    flex
                    items-center
                    gap-3

                    px-3.5
                    py-3

                    rounded-xl

                    bg-[#d9ad2f]

                    text-[#142a1f]

                    font-semibold

                    text-xs
                "
            >

                <i
                    class="
                        fa-regular
                        fa-user

                        w-4
                    "
                ></i>

                Profil Saya

            </a>


            <a
                href="#informasi-akun"

                class="
                    flex
                    items-center
                    gap-3

                    px-3.5
                    py-3

                    rounded-xl

                    text-emerald-100/80

                    hover:text-white
                    hover:bg-white/5

                    text-xs

                    transition
                "
            >

                <i
                    class="
                        fa-solid
                        fa-sliders

                        w-4

                        text-antique-300/80
                    "
                ></i>

                Informasi Akun

            </a>

        </nav>



        <!-- QUOTE -->

        <div
            class="
                p-2.5
                pb-4
            "
        >

            <div
                class="
                    rounded-2xl

                    border
                    border-antique-500/20

                    bg-[#10251b]

                    px-4
                    py-4
                "
            >

                <div
                    class="
                        flex
                        items-center
                        gap-2

                        text-antique-300

                        mb-2
                    "
                >

                    <i
                        class="
                            fa-regular
                            fa-star
                        "
                    ></i>

                    <span
                        class="
                            text-xs
                            font-bold
                        "
                    >
                        Terus Berbagi
                    </span>

                </div>


                <p
                    class="
                        text-[10px]
                        leading-relaxed

                        text-emerald-100/80
                    "
                >
                    Jadikan kebaikan sebagai bagian dari perjalanan hidup.
                </p>

            </div>

        </div>

         <!-- ==========================================
         LOGOUT
    =========================================== -->

    <div class="px-3 pb-5">

        <a
            href="../auth/logout.php"
            onclick="return confirm('Apakah kamu yakin ingin keluar?')"
            class="
                w-full
                flex
                items-center
                gap-3
                px-4
                py-3
                rounded-xl
                border
                border-red-400/20
                bg-red-950/30
                text-red-200
                hover:bg-red-900/50
                hover:text-white
                transition
                duration-200
                text-sm
                font-semibold
            "
        >

            <i
                class="
                    fa-solid
                    fa-arrow-right-from-bracket
                    w-5
                    text-center
                "
            ></i>

            <span>
                Logout
            </span>

        </a>

    </div>


    </aside>



    <!-- =====================================================
         MAIN
    ====================================================== -->

    <div
        class="
            lg:pl-[250px]

            min-h-screen
        "
    >


        <!-- =================================================
             TOPBAR
        ================================================== -->

        <header
            class="
                h-[69px]

                bg-[#0d1d15]

                text-white

                border-b
                border-antique-500/20

                sticky
                top-0

                z-30

                px-4
                sm:px-6
                lg:px-7

                flex
                items-center
                justify-between

                shadow-md
            "
        >

            <div
                class="
                    flex
                    items-center
                    gap-3
                "
            >

                <button
                    type="button"

                    onclick="openSidebar()"

                    class="
                        lg:hidden

                        w-9
                        h-9

                        rounded-lg

                        bg-white/5

                        border
                        border-white/10

                        text-antique-300
                    "
                >

                    <i
                        class="
                            fa-solid
                            fa-bars
                        "
                    ></i>

                </button>


                <div>

                    <div
                        class="
                            text-[11px]
                        "
                    >
                        Dashboard Donatur
                    </div>

                    <div
                        class="
                            text-xs

                            text-antique-500

                            font-bold
                        "
                    >
                        Profil Saya
                    </div>

                </div>

            </div>



            <!-- RIGHT -->

            <div
                class="
                    flex
                    items-center
                    gap-3
                "
            >

                <!-- NOTIFICATION -->

                <div
                    class="relative"
                >

                    <?php

                    $unreadCount = 0;

                    foreach (
                        $daftarNotifikasi
                        as $notif
                    ) {

                        if (
                            (int)$notif['is_read'] === 0
                        ) {

                            $unreadCount++;

                        }

                    }

                    ?>


                    <button
                        type="button"

                        onclick="toggleNotification()"

                        class="
                            relative

                            w-9
                            h-9

                            rounded-xl

                            bg-[#d9ad2f]

                            text-[#142a1f]

                            flex
                            items-center
                            justify-center
                        "
                    >

                        <i
                            class="
                                fa-regular
                                fa-bell
                            "
                        ></i>


                        <?php if ($unreadCount > 0): ?>

                            <span
                                class="
                                    absolute

                                    -top-1
                                    -right-1

                                    min-w-[16px]
                                    h-4

                                    rounded-full

                                    bg-red-500

                                    text-white

                                    text-[9px]

                                    font-bold

                                    flex
                                    items-center
                                    justify-center

                                    border-2
                                    border-[#0d1d15]
                                "
                            >
                                <?= $unreadCount > 9
                                    ? '9+'
                                    : $unreadCount ?>
                            </span>

                        <?php endif; ?>

                    </button>



                    <!-- NOTIFICATION DROPDOWN -->

                    <div
                        id="notificationPanel"

                        class="
                            hidden

                            absolute
                            right-0
                            top-12

                            w-[320px]

                            max-w-[calc(100vw-32px)]

                            bg-white

                            text-warm-900

                            rounded-2xl

                            shadow-luxury

                            border
                            border-antique-200

                            overflow-hidden
                        "
                    >

                        <div
                            class="
                                px-4
                                py-3

                                border-b
                                border-antique-200

                                flex
                                items-center
                                justify-between
                            "
                        >

                            <span
                                class="
                                    text-xs
                                    font-bold
                                "
                            >
                                Notifikasi
                            </span>


                            <a
                                href="portal-donatur.php?baca_notif=1"

                                class="
                                    text-[10px]

                                    text-cypress-700

                                    font-semibold
                                "
                            >
                                Tandai terbaca
                            </a>

                        </div>


                        <div
                            class="
                                max-h-80
                                overflow-y-auto
                            "
                        >

                            <?php if (
                                empty(
                                    $daftarNotifikasi
                                )
                            ): ?>

                                <div
                                    class="
                                        px-4
                                        py-8

                                        text-center

                                        text-xs

                                        text-warm-800/50
                                    "
                                >
                                    Belum ada notifikasi.
                                </div>

                            <?php else: ?>

                                <?php foreach (
                                    $daftarNotifikasi
                                    as $n
                                ): ?>

                                    <div
                                        class="
                                            px-4
                                            py-3

                                            border-b
                                            border-antique-100
                                        "
                                    >

                                        <div
                                            class="
                                                flex
                                                gap-2
                                            "
                                        >

                                            <span
                                                class="
                                                    mt-1.5

                                                    w-1.5
                                                    h-1.5

                                                    rounded-full

                                                    shrink-0

                                                    <?= (int)$n['is_read'] === 0
                                                        ? 'bg-antique-500'
                                                        : 'bg-stone-300' ?>
                                                "
                                            ></span>


                                            <div>

                                                <div
                                                    class="
                                                        flex
                                                        justify-between
                                                        gap-3
                                                    "
                                                >

                                                    <strong
                                                        class="
                                                            text-[11px]
                                                        "
                                                    >
                                                        <?= e(
                                                            $n['judul']
                                                        ) ?>
                                                    </strong>

                                                    <span
                                                        class="
                                                            text-[9px]

                                                            text-warm-800/40
                                                        "
                                                    >
                                                        <?= date(
                                                            'd/m',
                                                            strtotime(
                                                                $n['created_at']
                                                            )
                                                        ) ?>
                                                    </span>

                                                </div>


                                                <p
                                                    class="
                                                        text-[10px]

                                                        text-warm-800/65

                                                        mt-1
                                                    "
                                                >
                                                    <?= e(
                                                        $n['pesan']
                                                    ) ?>
                                                </p>

                                            </div>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>



                <!-- TOPBAR AVATAR -->

                <?php if ($avatarUrl): ?>

                    <img
                        src="<?= e($avatarUrl) ?>"

                        alt="Foto profil"

                        class="
                            w-9
                            h-9

                            rounded-xl

                            object-cover

                            border
                            border-antique-500/40
                        "
                    >

                <?php else: ?>

                    <div
                        class="
                            w-9
                            h-9

                            rounded-xl

                            bg-cypress-800

                            border
                            border-antique-500/40

                            flex
                            items-center
                            justify-center

                            text-antique-300

                            text-[10px]

                            font-bold
                        "
                    >
                        <?= e($initials) ?>
                    </div>

                <?php endif; ?>

            </div>

        </header>



        <!-- =====================================================
             CONTENT
        ====================================================== -->

        <main
            class="
                max-w-[1150px]

                mx-auto

                px-4
                sm:px-6
                lg:px-8

                py-7
                lg:py-8
            "
        >


            <!-- ALERT -->

            <?php if ($pesan): ?>

                <div
                    class="
                        mb-5

                        rounded-xl

                        px-4
                        py-3

                        text-xs
                        font-semibold

                        flex
                        items-center
                        gap-2

                        <?= $tipe === 'success'
                            ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                            : 'bg-rose-50 text-rose-800 border border-rose-200' ?>
                    "
                >

                    <i
                        class="
                            fa-solid

                            <?= $tipe === 'success'
                                ? 'fa-circle-check'
                                : 'fa-circle-exclamation' ?>
                        "
                    ></i>

                    <?= e($pesan) ?>

                </div>

            <?php endif; ?>



            <!-- =================================================
                 HERO PROFIL
            ================================================== -->

            <section
                class="
                    profile-hero

                    rounded-[24px]

                    border
                    border-antique-500/30

                    shadow-luxury

                    overflow-hidden

                    px-6
                    sm:px-9

                    py-7
                    sm:py-8
                "
            >

                <div
                    class="
                        flex
                        items-center

                        gap-5
                        sm:gap-6
                    "
                >


                    <!-- FOTO PROFIL -->

                    <div
                        class="
                            relative
                            shrink-0
                        "
                    >

                        <?php if ($avatarUrl): ?>

                            <img
                                id="heroAvatar"

                                src="<?= e($avatarUrl) ?>"

                                alt="Foto profil"

                                class="
                                    profile-photo

                                    w-[88px]
                                    h-[88px]

                                    sm:w-[94px]
                                    sm:h-[94px]

                                    rounded-2xl

                                    object-cover

                                    border-2
                                    border-antique-500/60
                                "
                            >

                        <?php else: ?>

                            <div
                                id="heroAvatar"

                                class="
                                    profile-photo

                                    w-[88px]
                                    h-[88px]

                                    sm:w-[94px]
                                    sm:h-[94px]

                                    rounded-2xl

                                    bg-cypress-800

                                    border-2
                                    border-antique-500/60

                                    flex
                                    items-center
                                    justify-center

                                    text-antique-300

                                    text-xl

                                    font-bold
                                "
                            >
                                <?= e($initials) ?>
                            </div>

                        <?php endif; ?>


                        <!-- CAMERA BUTTON -->

                        <button
                            type="button"

                            onclick="openEditModal(true)"

                            class="
                                photo-upload-button
                            "
                            title="Ganti foto profil"
                        >

                            <i
                                class="
                                    fa-solid
                                    fa-camera

                                    text-xs
                                "
                            ></i>

                        </button>

                    </div>



                    <!-- TEXT -->

                    <div
                        class="min-w-0"
                    >

                        <div
                            class="
                                text-antique-500

                                text-xs
                                sm:text-sm

                                font-medium

                                mb-1.5
                            "
                        >
                            ASSALAMU'ALAIKUM
                        </div>


                        <h1
                            class="
                                text-2xl
                                sm:text-[30px]

                                leading-tight

                                text-white

                                font-bold

                                tracking-wide

                                truncate
                            "
                        >
                            <?= e($namaDonatur) ?>
                        </h1>


                        <div
                            class="
                                flex
                                flex-wrap

                                items-center

                                gap-x-4
                                gap-y-1.5

                                mt-3

                                text-[11px]

                                text-emerald-100/90
                            "
                        >

                            <span
                                class="
                                    inline-flex
                                    items-center
                                    gap-1.5
                                "
                            >

                                <i
                                    class="
                                        fa-regular
                                        fa-heart

                                        text-antique-500
                                    "
                                ></i>

                                Donatur

                            </span>


                            <span
                                class="
                                    text-white/20
                                "
                            >
                                |
                            </span>


                            <span>
                                Member sejak
                                <?= e(
                                    date(
                                        'Y',
                                        strtotime(
                                            $tanggalDaftar
                                        )
                                    )
                                ) ?>
                            </span>

                        </div>

                    </div>

                </div>

            </section>



            <!-- =================================================
                 INFORMASI PRIBADI
            ================================================== -->

            <section
                id="informasi-akun"

                class="
                    mt-5
                    sm:mt-6

                    islamic-card

                    rounded-[22px]

                    border
                    border-antique-500/25

                    shadow-soft

                    overflow-hidden
                "
            >


                <!-- HEADER -->

                <div
                    class="
                        px-5
                        sm:px-6

                        py-5

                        border-b
                        border-antique-500/25

                        flex
                        items-center
                        justify-between

                        gap-4
                    "
                >

                    <div>

                        <h2
                            class="
                                text-sm
                                sm:text-base

                                font-bold

                                text-antique-500
                            "
                        >
                            Informasi Pribadi
                        </h2>


                        <p
                            class="
                                text-[11px]

                                text-white/80

                                mt-1
                            "
                        >
                            Kelola informasi pribadi kamu.
                        </p>

                    </div>


                    <button
                        type="button"

                        onclick="openEditModal(false)"

                        class="
                            shrink-0

                            px-4
                            sm:px-5

                            py-2.5

                            rounded-xl

                            bg-[#d9ad2f]

                            text-[#142a1f]

                            text-[11px]
                            sm:text-xs

                            font-bold

                            inline-flex
                            items-center
                            gap-2

                            hover:brightness-105

                            transition
                        "
                    >

                        <i
                            class="
                                fa-solid
                                fa-pen

                                text-[10px]
                            "
                        ></i>

                        Edit Profil

                    </button>

                </div>



                <!-- BODY -->

                <div
                    class="
                        p-5
                        sm:p-6
                    "
                >

                    <div
                        class="
                            grid
                            grid-cols-1
                            lg:grid-cols-2

                            gap-5
                        "
                    >


                        <!-- NAMA -->

                        <div>

                            <label
                                class="
                                    block

                                    text-[11px]

                                    font-bold

                                    text-antique-500

                                    mb-2
                                "
                            >
                                Nama Lengkap
                            </label>


                            <div
                                class="
                                    min-h-[47px]

                                    rounded-xl

                                    px-3.5

                                    bg-white

                                    border
                                    border-antique-200

                                    flex
                                    items-center
                                    gap-3

                                    text-xs
                                    text-warm-800
                                "
                            >

                                <i
                                    class="
                                        fa-regular
                                        fa-user

                                        text-antique-500

                                        w-4
                                    "
                                ></i>

                                <?= e($namaDonatur) ?>

                            </div>

                        </div>



                        <!-- EMAIL -->

                        <div>

                            <label
                                class="
                                    block

                                    text-[11px]

                                    font-bold

                                    text-antique-500

                                    mb-2
                                "
                            >
                                Email
                            </label>


                            <div
                                class="
                                    min-h-[47px]

                                    rounded-xl

                                    px-3.5

                                    bg-white

                                    border
                                    border-antique-200

                                    flex
                                    items-center
                                    gap-3

                                    text-xs
                                    text-warm-800
                                "
                            >

                                <i
                                    class="
                                        fa-regular
                                        fa-envelope

                                        text-antique-500

                                        w-4
                                    "
                                ></i>


                                <span
                                    class="truncate"
                                >
                                    <?= e(
                                        $emailDonatur
                                        ?: 'Belum diisi'
                                    ) ?>
                                </span>

                            </div>

                        </div>



                        <!-- WHATSAPP -->

                        <div>

                            <label
                                class="
                                    block

                                    text-[11px]

                                    font-bold

                                    text-antique-500

                                    mb-2
                                "
                            >
                                Nomor WhatsApp
                            </label>


                            <div
                                class="
                                    min-h-[47px]

                                    rounded-xl

                                    px-3.5

                                    bg-white

                                    border
                                    border-antique-200

                                    flex
                                    items-center
                                    gap-3

                                    text-xs
                                    text-warm-800
                                "
                            >

                                <i
                                    class="
                                        fa-brands
                                        fa-whatsapp

                                        text-antique-500

                                        w-4
                                    "
                                ></i>


                                <?= e(
                                    $noHpDonatur
                                    ?: 'Belum diisi'
                                ) ?>

                            </div>

                        </div>



                        <!-- FOTO -->

                        <div>

                            <label
                                class="
                                    block

                                    text-[11px]

                                    font-bold

                                    text-antique-500

                                    mb-2
                                "
                            >
                                Foto Profil
                            </label>


                            <button
                                type="button"

                                onclick="openEditModal(true)"

                                class="
                                    w-full

                                    min-h-[47px]

                                    rounded-xl

                                    px-3.5

                                    bg-white

                                    border
                                    border-antique-200

                                    flex
                                    items-center
                                    gap-3

                                    text-xs
                                    text-warm-800

                                    hover:border-antique-500

                                    transition
                                "
                            >

                                <i
                                    class="
                                        fa-solid
                                        fa-camera

                                        text-antique-500

                                        w-4
                                    "
                                ></i>

                                <?= $avatarUrl
                                    ? 'Ganti Foto Profil'
                                    : 'Tambahkan Foto Profil' ?>

                            </button>

                        </div>

                    </div>

                </div>

            </section>



            <!-- =================================================
                 STATUS AKUN
            ================================================== -->

            <section
                class="
                    mt-5
                    sm:mt-6

                    islamic-card

                    rounded-[22px]

                    border
                    border-antique-500/25

                    shadow-soft

                    overflow-hidden
                "
            >

                <div
                    class="
                        p-5
                        sm:p-6
                    "
                >

                    <div
                        class="
                            flex
                            items-center

                            gap-4

                            mb-5
                        "
                    >

                        <div
                            class="
                                w-11
                                h-11

                                rounded-xl

                                bg-[#d9ad2f]

                                text-[#142a1f]

                                flex
                                items-center
                                justify-center
                            "
                        >

                            <i
                                class="
                                    fa-regular
                                    fa-circle-check
                                "
                            ></i>

                        </div>


                        <div>

                            <h2
                                class="
                                    text-sm
                                    sm:text-base

                                    font-bold

                                    text-antique-500
                                "
                            >
                                Status Akun
                            </h2>


                            <p
                                class="
                                    text-[11px]

                                    text-white/80

                                    mt-1
                                "
                            >
                                Informasi dasar akun donatur.
                            </p>

                        </div>

                    </div>



                    <div
                        class="
                            grid
                            grid-cols-1
                            lg:grid-cols-2

                            gap-3
                        "
                    >

                        <!-- STATUS -->

                        <div
                            class="
                                rounded-xl

                                bg-white/95

                                px-4
                                py-3.5
                            "
                        >

                            <div
                                class="
                                    text-[10px]

                                    uppercase
                                    tracking-wide

                                    text-warm-800/45

                                    mb-2
                                "
                            >
                                Status Akun
                            </div>


                            <div
                                class="
                                    flex
                                    items-center
                                    gap-2

                                    text-xs
                                    font-semibold

                                    <?= e($statusText) ?>
                                "
                            >

                                <span
                                    class="
                                        w-2
                                        h-2

                                        rounded-full

                                        <?= e($statusDot) ?>
                                    "
                                ></span>

                                <?= e($statusLabel) ?>

                            </div>

                        </div>



                        <!-- DAFTAR -->

                        <div
                            class="
                                rounded-xl

                                bg-white/95

                                px-4
                                py-3.5
                            "
                        >

                            <div
                                class="
                                    text-[10px]

                                    uppercase
                                    tracking-wide

                                    text-warm-800/45

                                    mb-2
                                "
                            >
                                Terdaftar Sejak
                            </div>


                            <div
                                class="
                                    text-xs

                                    font-semibold

                                    text-warm-900
                                "
                            >
                                <?= e(
                                    tanggal_indo(
                                        $tanggalDaftar
                                    )
                                ) ?>
                            </div>

                        </div>

                    </div>

                </div>

            </section>



            <!-- =================================================
                 RIWAYAT DONASI
            ================================================== -->

            <section
                class="
                    mt-5
                    sm:mt-6

                    bg-white/95

                    rounded-[22px]

                    border
                    border-antique-300/50

                    shadow-soft

                    overflow-hidden
                "
            >

                <div
                    class="
                        px-5
                        sm:px-6

                        py-5

                        border-b
                        border-antique-200

                        flex
                        items-center
                        justify-between
                    "
                >

                    <div>

                        <h2
                            class="
                                text-sm
                                sm:text-base

                                font-bold

                                text-cypress-800
                            "
                        >
                            Riwayat Infaq
                        </h2>


                        <p
                            class="
                                text-[11px]

                                text-warm-800/55

                                mt-1
                            "
                        >
                            Catatan donasi yang telah kamu lakukan.
                        </p>

                    </div>


                    <span
                        class="
                            text-[10px]

                            px-3
                            py-1.5

                            rounded-full

                            bg-antique-50

                            border
                            border-antique-200

                            text-antique-700

                            font-bold
                        "
                    >
                        <?= $jumlahTransaksi ?>
                        Donasi
                    </span>

                </div>



                <?php if (
                    empty($riwayatDonasi)
                ): ?>

                    <div
                        class="
                            text-center

                            py-10
                        "
                    >

                        <i
                            class="
                                fa-solid
                                fa-receipt

                                text-3xl

                                text-stone-300
                            "
                        ></i>


                        <p
                            class="
                                text-xs

                                text-warm-800/55

                                mt-3
                            "
                        >
                            Belum ada catatan donasi.
                        </p>


                        <a
                            href="../home/donasi-online.php"

                            class="
                                inline-flex

                                items-center
                                gap-2

                                mt-4

                                px-4
                                py-2.5

                                rounded-xl

                                bg-cypress-700

                                text-white

                                text-xs

                                font-semibold
                            "
                        >

                            <i
                                class="
                                    fa-solid
                                    fa-hand-holding-heart
                                "
                            ></i>

                            Salurkan Infaq

                        </a>

                    </div>

                <?php else: ?>

                    <div
                        class="
                            overflow-x-auto
                        "
                    >

                        <table
                            class="
                                w-full

                                text-left

                                text-xs
                            "
                        >

                            <thead>

                                <tr
                                    class="
                                        border-b
                                        border-antique-200

                                        text-[10px]
                                        uppercase

                                        text-warm-800/55
                                    "
                                >

                                    <th
                                        class="
                                            px-5
                                            py-3
                                        "
                                    >
                                        Donasi
                                    </th>

                                    <th
                                        class="
                                            py-3
                                            pr-4
                                        "
                                    >
                                        Program
                                    </th>

                                    <th
                                        class="
                                            py-3
                                            pr-4
                                        "
                                    >
                                        Nominal
                                    </th>

                                    <th
                                        class="
                                            py-3
                                            pr-5
                                        "
                                    >
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody
                                class="
                                    divide-y
                                    divide-antique-100
                                "
                            >

                                <?php foreach (
                                    $riwayatDonasi
                                    as $r
                                ): ?>

                                    <tr
                                        class="
                                            hover:bg-warm-50/70
                                            transition
                                        "
                                    >

                                        <td
                                            class="
                                                px-5
                                                py-4
                                            "
                                        >

                                            <span
                                                class="
                                                    block

                                                    font-mono
                                                    font-bold

                                                    text-cypress-800
                                                "
                                            >
                                                <?= e(
                                                    $r['no_donasi']
                                                ) ?>
                                            </span>


                                            <span
                                                class="
                                                    block

                                                    text-[10px]

                                                    text-warm-800/45

                                                    mt-1
                                                "
                                            >
                                                <?= tanggal_indo(
                                                    $r['tanggal_donasi']
                                                ) ?>
                                            </span>

                                        </td>


                                        <td
                                            class="
                                                py-4
                                                pr-4
                                            "
                                        >

                                            <span
                                                class="
                                                    block

                                                    font-semibold

                                                    text-warm-900
                                                "
                                            >
                                                <?= e(
                                                    $r['nama_program']
                                                ) ?>
                                            </span>


                                            <span
                                                class="
                                                    text-[10px]

                                                    text-warm-800/50
                                                "
                                            >
                                                <?= e(
                                                    $r['metode_pembayaran']
                                                ) ?>
                                            </span>

                                        </td>


                                        <td
                                            class="
                                                py-4
                                                pr-4

                                                whitespace-nowrap
                                            "
                                        >

                                            <span
                                                class="
                                                    font-bold

                                                    text-cypress-700
                                                "
                                            >
                                                <?= format_rupiah(
                                                    $r['nominal']
                                                ) ?>
                                            </span>

                                        </td>


                                        <td
                                            class="
                                                py-4
                                                pr-5
                                            "
                                        >

                                            <?php if (
                                                $r['status'] ===
                                                'diverifikasi'
                                            ): ?>

                                                <span
                                                    class="
                                                        px-2.5
                                                        py-1

                                                        rounded-full

                                                        text-[10px]

                                                        font-bold

                                                        bg-emerald-100

                                                        text-emerald-800
                                                    "
                                                >
                                                    Diterima
                                                </span>

                                            <?php elseif (
                                                $r['status'] ===
                                                'ditolak'
                                            ): ?>

                                                <span
                                                    class="
                                                        px-2.5
                                                        py-1

                                                        rounded-full

                                                        text-[10px]

                                                        font-bold

                                                        bg-red-100

                                                        text-red-800
                                                    "
                                                >
                                                    Ditolak
                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="
                                                        px-2.5
                                                        py-1

                                                        rounded-full

                                                        text-[10px]

                                                        font-bold

                                                        bg-amber-100

                                                        text-amber-800
                                                    "
                                                >
                                                    Menunggu
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </section>



            <!-- =================================================
                 FOOTER
            ================================================== -->

            <footer
                class="
                    pt-10
                    pb-4

                    text-center
                "
            >

                <div
                    class="
                        text-[11px]

                        text-warm-800/50
                    "
                >

                    © <?= date('Y') ?>

                    <?= e(
                        $profil['nama_masjid']
                    ) ?>

                </div>


                <div
                    class="
                        text-[10px]

                        text-warm-800/40

                        mt-1
                    "
                >
                    Bersama Menebar Kebaikan
                </div>

            </footer>

        </main>

    </div>

</div>



<!-- =============================================================
     MODAL EDIT PROFIL
============================================================== -->

<div
    id="editModal"

    class="
        hidden

        fixed
        inset-0

        z-[100]

        items-center
        justify-center

        p-4
    "
>

    <div
        class="
            absolute
            inset-0

            modal-backdrop
        "

        onclick="closeEditModal()"
    ></div>



    <div
        class="
            relative

            w-full
            max-w-lg

            bg-white

            rounded-2xl

            border
            border-antique-200

            shadow-luxury

            overflow-hidden
        "
    >


        <!-- HEADER -->

        <div
            class="
                px-5
                py-4

                bg-cypress-900

                text-white

                flex
                items-center
                justify-between
            "
        >

            <div>

                <h3
                    class="
                        text-sm

                        font-bold

                        text-antique-500
                    "
                >
                    Edit Profil
                </h3>


                <p
                    class="
                        text-[10px]

                        text-white/70

                        mt-1
                    "
                >
                    Perbarui informasi akun donatur.
                </p>

            </div>


            <button
                type="button"

                onclick="closeEditModal()"

                class="
                    w-8
                    h-8

                    rounded-lg

                    bg-white/5

                    text-white/70

                    hover:text-white
                "
            >

                <i
                    class="
                        fa-solid
                        fa-xmark
                    "
                ></i>

            </button>

        </div>



        <!-- FORM -->

        <form
            method="POST"

            action="portal-donatur.php"

            enctype="multipart/form-data"

            class="
                p-5

                space-y-4
            "
        >

            <input
                type="hidden"

                name="aksi"

                value="update_profil"
            >


            <!-- FOTO -->

            <div
                class="
                    flex
                    flex-col
                    items-center

                    pb-3
                "
            >

                <div
                    class="
                        relative
                    "
                >

                    <?php if ($avatarUrl): ?>

                        <img
                            id="modalAvatar"

                            src="<?= e($avatarUrl) ?>"

                            class="
                                w-24
                                h-24

                                rounded-2xl

                                object-cover

                                border-2
                                border-antique-500/50
                            "
                        >

                    <?php else: ?>

                        <div
                            id="modalAvatar"

                            class="
                                w-24
                                h-24

                                rounded-2xl

                                bg-cypress-800

                                border-2
                                border-antique-500/50

                                flex
                                items-center
                                justify-center

                                text-antique-300

                                text-xl
                                font-bold
                            "
                        >
                            <?= e($initials) ?>
                        </div>

                    <?php endif; ?>

                </div>


                <label
                    for="foto_profil"

                    class="
                        mt-3

                        cursor-pointer

                        px-4
                        py-2

                        rounded-xl

                        bg-antique-50

                        border
                        border-antique-300

                        text-antique-700

                        text-xs

                        font-bold

                        hover:bg-antique-100

                        transition
                    "
                >

                    <i
                        class="
                            fa-solid
                            fa-camera

                            mr-1
                        "
                    ></i>

                    Pilih Foto

                </label>


                <input
                    id="foto_profil"

                    type="file"

                    name="foto_profil"

                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"

                    onchange="previewPhoto(this)"
                >


                <p
                    class="
                        text-[10px]

                        text-warm-800/45

                        mt-2
                    "
                >
                    JPG, PNG, atau WEBP · Maksimal 3MB
                </p>

            </div>



            <!-- NAMA -->

            <div>

                <label
                    for="nama_lengkap"

                    class="
                        block

                        text-[11px]

                        font-bold

                        text-warm-800

                        mb-1.5
                    "
                >
                    Nama Lengkap
                </label>


                <input
                    id="nama_lengkap"

                    type="text"

                    name="nama_lengkap"

                    value="<?= e($namaDonatur) ?>"

                    required

                    class="
                        w-full

                        px-3.5
                        py-3

                        rounded-xl

                        bg-warm-50

                        border
                        border-antique-200

                        text-xs

                        outline-none

                        focus:border-antique-500

                        focus:ring-2
                        focus:ring-antique-500/20
                    "
                >

            </div>



            <!-- EMAIL -->

            <div>

                <label
                    class="
                        block

                        text-[11px]

                        font-bold

                        text-warm-800

                        mb-1.5
                    "
                >
                    Email
                </label>


                <input
                    type="email"

                    value="<?= e($emailDonatur) ?>"

                    disabled

                    class="
                        w-full

                        px-3.5
                        py-3

                        rounded-xl

                        bg-stone-100

                        border
                        border-stone-200

                        text-xs

                        text-warm-800/50

                        cursor-not-allowed
                    "
                >

            </div>



            <!-- WHATSAPP -->

            <div>

                <label
                    for="no_hp"

                    class="
                        block

                        text-[11px]

                        font-bold

                        text-warm-800

                        mb-1.5
                    "
                >
                    Nomor WhatsApp
                </label>


                <input
                    id="no_hp"

                    type="text"

                    name="no_hp"

                    value="<?= e($noHpDonatur) ?>"

                    placeholder="08xxxxxxxxxx"

                    class="
                        w-full

                        px-3.5
                        py-3

                        rounded-xl

                        bg-warm-50

                        border
                        border-antique-200

                        text-xs

                        outline-none

                        focus:border-antique-500

                        focus:ring-2
                        focus:ring-antique-500/20
                    "
                >

            </div>



            <!-- PASSWORD -->

            <div>

                <label
                    for="password_baru"

                    class="
                        block

                        text-[11px]

                        font-bold

                        text-warm-800

                        mb-1.5
                    "
                >
                    Kata Sandi Baru

                    <span
                        class="
                            font-normal

                            text-warm-800/45
                        "
                    >
                        (opsional)
                    </span>

                </label>


                <input
                    id="password_baru"

                    type="password"

                    name="password_baru"

                    placeholder="Minimal 6 karakter"

                    class="
                        w-full

                        px-3.5
                        py-3

                        rounded-xl

                        bg-warm-50

                        border
                        border-antique-200

                        text-xs

                        outline-none

                        focus:border-antique-500

                        focus:ring-2
                        focus:ring-antique-500/20
                    "
                >

            </div>



            <!-- BUTTON -->

            <div
                class="
                    flex
                    justify-end

                    gap-2

                    pt-2
                "
            >

                <button
                    type="button"

                    onclick="closeEditModal()"

                    class="
                        px-4
                        py-2.5

                        rounded-xl

                        border
                        border-antique-200

                        text-xs

                        font-semibold

                        text-warm-800

                        hover:bg-warm-50
                    "
                >
                    Batal
                </button>


                <button
                    type="submit"

                    class="
                        px-5
                        py-2.5

                        rounded-xl

                        bg-[#d9ad2f]

                        text-[#142a1f]

                        text-xs

                        font-bold

                        hover:brightness-105
                    "
                >

                    <i
                        class="
                            fa-solid
                            fa-check

                            mr-1
                        "
                    ></i>

                    Simpan Perubahan

                </button>

            </div>

        </form>

    </div>

</div>



<!-- =============================================================
     JAVASCRIPT
============================================================== -->

<script>

/*
|--------------------------------------------------------------------------
| ELEMENT
|--------------------------------------------------------------------------
*/

const appShell =
    document.getElementById(
        'appShell'
    );

const editModal =
    document.getElementById(
        'editModal'
    );

const notificationPanel =
    document.getElementById(
        'notificationPanel'
    );


/*
|--------------------------------------------------------------------------
| SIDEBAR
|--------------------------------------------------------------------------
*/

function openSidebar()
{
    appShell.classList.add(
        'mobile-sidebar-open'
    );
}


function closeSidebar()
{
    appShell.classList.remove(
        'mobile-sidebar-open'
    );
}


/*
|--------------------------------------------------------------------------
| EDIT MODAL
|--------------------------------------------------------------------------
*/

function openEditModal(focusPhoto = false)
{
    editModal.classList.remove(
        'hidden'
    );

    editModal.classList.add(
        'flex'
    );

    document.body.classList.add(
        'overflow-hidden'
    );

    if (focusPhoto) {

        setTimeout(
            function()
            {
                document
                    .getElementById(
                        'foto_profil'
                    )
                    ?.click();
            },
            150
        );

    } else {

        setTimeout(
            function()
            {
                document
                    .getElementById(
                        'nama_lengkap'
                    )
                    ?.focus();
            },
            100
        );
    }
}


function closeEditModal()
{
    editModal.classList.add(
        'hidden'
    );

    editModal.classList.remove(
        'flex'
    );

    document.body.classList.remove(
        'overflow-hidden'
    );
}


/*
|--------------------------------------------------------------------------
| PREVIEW FOTO
|--------------------------------------------------------------------------
*/

function previewPhoto(input)
{
    if (
        !input.files ||
        !input.files[0]
    ) {
        return;
    }

    const file =
        input.files[0];

    /*
    | Cek ukuran di browser
    */
    if (
        file.size >
        3 * 1024 * 1024
    ) {

        alert(
            'Ukuran foto maksimal 3MB.'
        );

        input.value = '';

        return;
    }


    /*
    | Preview
    */
    const reader =
        new FileReader();

    reader.onload =
        function(event)
        {

            const modalAvatar =
                document.getElementById(
                    'modalAvatar'
                );

            const heroAvatar =
                document.getElementById(
                    'heroAvatar'
                );


            /*
            | Modal
            */
            if (
                modalAvatar
            ) {

                if (
                    modalAvatar.tagName
                    .toLowerCase()
                    === 'img'
                ) {

                    modalAvatar.src =
                        event.target.result;

                } else {

                    const img =
                        document.createElement(
                            'img'
                        );

                    img.id =
                        'modalAvatar';

                    img.src =
                        event.target.result;

                    img.className =
                        'w-24 h-24 rounded-2xl object-cover border-2 border-antique-500/50';

                    modalAvatar.replaceWith(
                        img
                    );
                }
            }


            /*
            | Hero
            */
            if (
                heroAvatar
            ) {

                if (
                    heroAvatar.tagName
                    .toLowerCase()
                    === 'img'
                ) {

                    heroAvatar.src =
                        event.target.result;

                }
            }

        };


    reader.readAsDataURL(file);
}


/*
|--------------------------------------------------------------------------
| NOTIFICATION
|--------------------------------------------------------------------------
*/

function toggleNotification()
{
    notificationPanel.classList.toggle(
        'hidden'
    );
}


/*
|--------------------------------------------------------------------------
| CLICK OUTSIDE NOTIFICATION
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'click',
    function(event)
    {

        const button =
            event.target.closest(
                'button'
            );

        const panel =
            event.target.closest(
                '#notificationPanel'
            );


        if (
            !panel &&
            !button?.onclick
        ) {

            notificationPanel.classList.add(
                'hidden'
            );

        }

    }
);


/*
|--------------------------------------------------------------------------
| ESC
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'keydown',
    function(event)
    {

        if (
            event.key === 'Escape'
        ) {

            closeEditModal();

            notificationPanel.classList.add(
                'hidden'
            );

            closeSidebar();

        }

    }
);

</script>


</body>

</html>