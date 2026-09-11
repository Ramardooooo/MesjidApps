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
                    gap-2
                    sm:gap-3
                "
            >

                <!-- KEMBALI KE BERANDA -->
                <a
                    href="../index.php"
                    class="
                        inline-flex
                        items-center
                        gap-2
                        px-3
                        sm:px-4
                        py-2
                        rounded-xl
                        bg-white/5
                        border
                        border-antique-500/30
                        text-antique-300
                        hover:bg-white/10
                        hover:text-white
                        transition
                        text-[10px]
                        sm:text-xs
                        font-semibold
                    "
                    title="Kembali ke Beranda"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    <span class="hidden sm:inline">Kembali ke Beranda</span>
                    <span class="sm:hidden">Beranda</span>
                </a>

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

