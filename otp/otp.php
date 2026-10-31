<?php
function generate_otp(string $email, string $purpose): string {
    global $pdo;
    $email   = trim($email);
    $purpose = trim($purpose);
    
    // Hasilkan OTP 6 digit
    $otp     = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    // Hash OTP untuk disimpan di DB
    $hashed  = hash('sha256', $otp);
    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Hapus data lama agar satu email+purpose hanya punya satu data (tidak menumpuk)
    $pdo->prepare("DELETE FROM otp_codes WHERE email = ? AND purpose = ?")->execute([$email, $purpose]);
    
    // Masukkan data baru
    $pdo->prepare("INSERT INTO otp_codes (email, otp_code, purpose, expires_at, used) VALUES (?, ?, ?, ?, 0)")
        ->execute([$email, $hashed, $purpose, $expires]);

    return $otp;
}

function verify_otp(string $email, string $otp, string $purpose): bool {
    global $pdo;
    $email  = trim($email);
    $otp    = trim($otp);
    $purpose= trim($purpose);
    $now    = date('Y-m-d H:i:s');
    
    // Hash input OTP untuk dibandingkan dengan hash di database
    $hashedInput = hash('sha256', $otp);

    $stmt = $pdo->prepare(
        "SELECT id FROM otp_codes
         WHERE email = ? AND otp_code = ? AND purpose = ? AND used = 0 AND expires_at > ?
         LIMIT 1"
    );
    $stmt->execute([$email, $hashedInput, $purpose, $now]);
    $row = $stmt->fetch();
    
    if (!$row) return false;

    $pdo->prepare("UPDATE otp_codes SET used = 1 WHERE id = ?")->execute([$row['id']]);
    return true;
}

function send_otp_email(string $email, string $nama, string $otp, string $purpose): void {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../config/mail.php';

    $label    = $purpose === 'register' ? 'Aktivasi Akun' : 'Reset Kata Sandi';
    
    $konten = require __DIR__ . '/email-template.php';
    $htmlBody = $konten['html'];
    $textBody = $konten['text'];

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host         = MAIL_HOST;
        $mail->SMTPAuth     = true;
        $mail->Username     = MAIL_USERNAME;
        $mail->Password     = MAIL_PASSWORD;
        $mail->SMTPSecure   = MAIL_SECURE;
        $mail->Port         = MAIL_PORT;
        $mail->CharSet      = 'UTF-8';
        $mail->SMTPDebug    = 0;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($email, $nama);
        $mail->isHTML(true);
        $mail->Subject = "[OTP] {$label} — Kode Verifikasi Portal Masjid";
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody;

        $mail->send();
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log('[OTP Mail Error] ' . $mail->ErrorInfo);
        throw new \RuntimeException('Gagal mengirim email OTP: ' . $mail->ErrorInfo);
    }
}
