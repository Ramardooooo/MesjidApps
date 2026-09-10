<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Portal Pengurus & Pembukuan · Masjid Nurul Iman';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/classic-theme.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cypress: {
                            50: '#f2f7f4',
                            100: '#e1ede6',
                            200: '#c3dbcd',
                            500: '#2d6148',
                            600: '#234b38',
                            700: '#1b3a2b',
                            800: '#142a1f',
                            900: '#0d1d15',
                            950: '#07100b',
                        },
                        antique: {
                            50: '#fdfbf7',
                            100: '#f8f4ec',
                            200: '#eedfbe',
                            300: '#dfc896',
                            400: '#d2b474',
                            500: '#c5a059',
                            600: '#b08a42',
                            700: '#8c6b2d',
                            800: '#694f20',
                            900: '#463414',
                        },
                        warm: {
                            50: '#fbf9f5',
                            100: '#f5f1e8',
                            200: '#ebe4d3',
                            800: '#242b26',
                            900: '#191f1b',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui'],
                        classic: ['Cinzel', 'serif'],
                        arabic: ['Amiri', 'serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="pattern-arabesque-light min-h-screen text-warm-900 flex flex-col justify-between antialiased selection:bg-antique-500 selection:text-white">
