<?php
// config/mail.php
// Konfigurasi SMTP untuk pengiriman email OTP
// =============================================
// CARA BUAT GMAIL APP PASSWORD:
// 1. Buka https://myaccount.google.com/security
// 2. Aktifkan "2-Step Verification"
// 3. Buka https://myaccount.google.com/apppasswords
// 4. Pilih App: Mail, Device: Windows Computer
// 5. Klik "Generate" — salin 16 karakter (tanpa spasi) ke MAIL_PASS di bawah
// =============================================

define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);
define('MAIL_USERNAME', 'ramaxkont1@gmail.com'); // ← GANTI INI
define('MAIL_PASSWORD', 'kwkf ttcb roet avnm');               // ← GANTI INI (App Password Gmail)
define('MAIL_FROM',     'ramaxkont1@gmail.com'); // ← GANTI INI (sama dgn USERNAME)
define('MAIL_FROM_NAME', 'Ramardo Ganteng Pro Player');                    // Nama pengirim email
define('MAIL_SECURE',   'tls');
