<?php
// otp/otp.php
// OTP (One-Time Password) Helpers — Generate, Verifikasi, dan Kirim Email OTP
// =========================================================

/**
 * Buat OTP 6 digit, simpan ke tabel otp_codes, return kode OTP.
 * OTP valid selama 10 menit.
 */
function generate_otp(string $email, string $purpose): string {
    global $pdo;
    $email   = trim($email);
    $purpose = trim($purpose);
    // Hapus OTP lama untuk email + purpose yang sama
    $pdo->prepare("DELETE FROM otp_codes WHERE email = ? AND purpose = ?")->execute([$email, $purpose]);

    $otp     = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $pdo->prepare("INSERT INTO otp_codes (email, otp_code, purpose, expires_at) VALUES (?, ?, ?, ?)")
        ->execute([$email, $otp, $purpose, $expires]);

    return $otp;
}

/**
 * Verifikasi OTP — return true jika valid, false jika tidak/expired/sudah dipakai.
 * Jika valid, tandai sebagai used = 1.
 */
function verify_otp(string $email, string $otp, string $purpose): bool {
    global $pdo;
    $email  = trim($email);
    $otp    = trim($otp);
    $purpose= trim($purpose);

    // Bandingkan terhadap waktu PHP yang sama dengan generate_otp,
    // bukan NOW() MySQL, agar tidak terpengaruh perbedaan zona waktu.
    $now = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare(
        "SELECT id FROM otp_codes
         WHERE email = ? AND otp_code = ? AND purpose = ? AND used = 0 AND expires_at > ?
         LIMIT 1"
    );
    $stmt->execute([$email, $otp, $purpose, $now]);
    $row = $stmt->fetch();
    if (!$row) return false;

    // Tandai OTP sudah digunakan
    $pdo->prepare("UPDATE otp_codes SET used = 1 WHERE id = ?")->execute([$row['id']]);
    return true;
}

/**
 * Kirim OTP ke email via Gmail SMTP (PHPMailer).
 * Konfigurasi SMTP ada di config/mail.php
 * Throws Exception jika gagal kirim.
 *
 * Optimasi kecepatan:
 *  - Template HTML dipisah di otp/email-template.php → hanya diproses saat kirim
 *  - SMTPKeepAlive dipakai supaya koneksi SMTP dipertahankan (tidak reconnect tiap kirim)
 *  - SMTPDebug dimatikan untuk menghindari output tambahan
 */
function send_otp_email(string $email, string $nama, string $otp, string $purpose): void {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../config/mail.php';

    $label    = $purpose === 'register' ? 'Aktivasi Akun' : 'Reset Kata Sandi';
    $keterangan = $purpose === 'register'
        ? 'Untuk menyelesaikan pendaftaran akun donatur Anda, masukkan kode OTP berikut:'
        : 'Anda telah meminta reset kata sandi. Masukkan kode OTP berikut untuk melanjutkan:';

    $tahun = date('Y');

    // Render template email dari file terpisah (hanya dimuat saat dibutuhkan)
    $konten = require __DIR__ . '/email-template.php';
    $htmlBody = $konten['html'];
    $textBody = $konten['text'];

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host         = MAIL_HOST;
        $mail->SMTPAuth     = true;
        $mail->Username     = MAIL_USERNAME;
        $mail->Password     = MAIL_PASSWORD;
        $mail->SMTPSecure   = MAIL_SECURE;
        $mail->Port         = MAIL_PORT;
        $mail->CharSet      = 'UTF-8';
        $mail->SMTPKeepAlive = true;
        $mail->SMTPDebug    = 0;

        // Pengirim & penerima
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($email, $nama);
        $mail->addReplyTo(MAIL_FROM, MAIL_FROM_NAME);

        // Konten
        $mail->isHTML(true);
        $mail->Subject = "[OTP] {$label} — Kode Verifikasi Portal Masjid";
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody;

        $mail->send();
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        // Log error tapi jangan crash halaman
        error_log('[OTP Mail Error] ' . $mail->ErrorInfo);
        throw new \RuntimeException('Gagal mengirim email OTP: ' . $mail->ErrorInfo);
    }
}