<?php
/**
 * 404.php
 * Minimal Modern Error Page
 * Masjid Jami' Nurul Iman
 */

http_response_code(404);

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Base URL otomatis
$scriptDir = str_replace(
    '\\',
    '/',
    dirname($_SERVER['SCRIPT_NAME'] ?? '')
);

$base = rtrim($scriptDir, '/');

if ($base === '/' || $base === '.') {
    $base = '';
}

$homeUrl = $base . '/';
$namaMasjid = "Masjid Jami' Nurul Iman";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <title>404 - Halaman Tidak Ditemukan | <?= htmlspecialchars($namaMasjid, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #174b35;
            --primary-dark: #103725;
            --text: #202a24;
            --muted: #89928c;
            --border: #e9eee9;
            --background: #fafcfb;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--background);
            color: var(--text);
        }

        .container {
            width: 100%;
            max-width: 520px;
            text-align: center;
            animation: appear .7s ease both;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 65px;
            color: var(--primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .brand-icon {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            color: white;
            background: var(--primary);
            border-radius: 10px;
        }

        .brand-icon svg {
            width: 19px;
            height: 19px;
        }

        .error-code {
            font-size: clamp(110px, 25vw, 170px);
            font-weight: 800;
            line-height: .95;
            letter-spacing: -12px;
            color: var(--primary);
            user-select: none;
        }

        .error-label {
            display: inline-block;
            margin-top: 24px;
            padding: 7px 13px;
            border: 1px solid #dce9df;
            border-radius: 50px;
            background: #f0f6f1;
            color: var(--primary);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        h1 {
            margin-top: 25px;
            font-size: clamp(21px, 5vw, 27px);
            font-weight: 700;
            letter-spacing: -.8px;
        }

        .description {
            max-width: 370px;
            margin: 13px auto 30px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.9;
        }

        .home-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-width: 190px;
            padding: 14px 25px;
            border-radius: 10px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 7px 20px rgba(23, 75, 53, .13);
            transition: .25s ease;
        }

        .home-button:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(23, 75, 53, .2);
        }

        .home-button svg {
            width: 16px;
            height: 16px;
        }

        .footer {
            margin-top: 75px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            color: #a0aaa3;
            font-size: 10px;
            letter-spacing: .3px;
        }

        @keyframes appear {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .brand {
                margin-bottom: 55px;
            }

            .error-code {
                letter-spacing: -8px;
            }

            .footer {
                margin-top: 60px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>

<body>

    <main class="container">

        <!-- Brand -->
        <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="brand">
            <span class="brand-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18M5 21V11l7-7 7 7v10"/>
                    <path d="M9 21v-6h6v6"/>
                    <path d="M12 4V2"/>
                </svg>
            </span>
            <span><?= htmlspecialchars($namaMasjid, ENT_QUOTES, 'UTF-8') ?></span>
        </a>

        <!-- Error -->
        <div class="error-code">404</div>

        <span class="error-label">Page Not Found</span>

        <h1>Oops! Halaman tidak ditemukan.</h1>

        <p class="description">
            Maaf, halaman yang Anda cari mungkin telah dipindahkan,
            dihapus, atau alamat yang dimasukkan tidak tersedia.
        </p>

        <!-- Home Button -->
        <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>"
           class="home-button">

            <svg viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 10 9-7 9 7"/>
                <path d="M5 9v12h14V9"/>
                <path d="M9 21v-7h6v7"/>
            </svg>

            Kembali ke Home
        </a>

        <!-- Footer -->
        <footer class="footer">
            &copy; <?= date('Y') ?>
            <?= htmlspecialchars($namaMasjid, ENT_QUOTES, 'UTF-8') ?>
            &nbsp;·&nbsp; All Rights Reserved
        </footer>

    </main>

</body>
</html>