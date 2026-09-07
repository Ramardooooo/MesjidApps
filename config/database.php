<?php
// config/database.php
// Konfigurasi Database & Helper Global Sistem Informasi & Pembukuan Masjid

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mesjid_website');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}

// Pastikan zona waktu PHP & MySQL konsisten agar OTP tidak dianggap kedaluwarsa
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Asia/Makassar');
}
try {
    $pdo->exec("SET time_zone = '+08:00'");
} catch (Throwable $e) {
    // Abaikan jika gagal set time_zone
}

// Pastikan folder uploads tersedia
$uploadDirs = [
    __DIR__ . '/../uploads',
    __DIR__ . '/../uploads/bukti',
    __DIR__ . '/../uploads/program',
    __DIR__ . '/../uploads/berita',
    __DIR__ . '/../uploads/banner',
    __DIR__ . '/../uploads/qris',
    __DIR__ . '/../uploads/pengurus',
];
foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

function base_url() {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $pos = strrpos($scriptName, '/');
    $dir = $pos === false ? '' : substr($scriptName, 0, $pos);
    $last = strtolower(substr($dir, strrpos($dir, '/') + 1));
    $subdirs = ['admin', 'auth', 'home', 'donatur'];
    if (in_array($last, $subdirs)) {
        $up = strrpos($dir, '/');
        $dir = $up === false ? '' : substr($dir, 0, $up);
    }
    return rtrim($dir, '/');
}

function root_prefix() {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $dir = $pos = strrpos($scriptName, '/') === false ? '' : substr($scriptName, 0, strrpos($scriptName, '/'));
    $last = strtolower(substr($dir, strrpos($dir, '/') + 1));
    $subdirs = ['admin', 'auth', 'home', 'donatur'];
    return in_array($last, $subdirs) ? '../' : '';
}

function upload_url($path) {
    $path = (string)($path ?? '');
    if ($path === '') return '';
    // Path upload relatif (disimpan sebagai "uploads/...") di-resolve dari akar proyek
    if (strpos($path, 'uploads/') === 0) {
        return base_url() . '/' . $path;
    }
    // Biarkan URL absolut/eksternal apa adanya
    if (preg_match('#^(https?:)?//#i', $path) || strpos($path, '/') === 0) {
        return $path;
    }
    return base_url() . '/' . $path;
}

function mulai_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function cek_login() {
    mulai_session();
    if (!isset($_SESSION['user'])) {
        header('Location: ' . base_url() . '/auth/login.php');
        exit;
    }
}

// Cek hak akses role tertentu
function cek_role(array $allowed_roles) {
    cek_login();
    $userRole = $_SESSION['user']['role'] ?? '';
    if (!in_array($userRole, $allowed_roles)) {
        header('Location: ' . base_url() . '/404.php?err=unauthorized');
        exit;
    }
}

function e($text) {
    return htmlspecialchars((string)($text ?? ''), ENT_QUOTES, 'UTF-8');
}

function format_rupiah($nominal) {
    return 'Rp ' . number_format((float)$nominal, 0, ',', '.');
}

function tanggal_indo($tanggal, $cetak_hari = false) {
    if (empty($tanggal) || $tanggal === '0000-00-00') return '-';
    
    $hari = [
        'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
    ];
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($tanggal);
    if (!$timestamp) return $tanggal;
    
    $tgl = date('j', $timestamp);
    $bln = $bulan[(int)date('n', $timestamp)];
    $thn = date('Y', $timestamp);
    
    if ($cetak_hari) {
        $nama_hari = $hari[(int)date('w', $timestamp)];
        return $nama_hari . ', ' . $tgl . ' ' . $bln . ' ' . $thn;
    }
    
    return $tgl . ' ' . $bln . ' ' . $thn;
}

function get_profil_masjid() {
    global $pdo;
    static $profil = null;
    if ($profil === null) {
        $stmt = $pdo->query("SELECT * FROM profil_masjid WHERE id = 1 LIMIT 1");
        $profil = $stmt->fetch();
        if (!$profil) {
                $profil = [
                    'nama_masjid' => 'Masjid Jami\' Nurul Iman',
                    'sebutan' => 'Pusat Dakwah & Ibadah',
                    'slogan' => 'Memakmurkan Masjid, Mensejahterakan Ummat',
                    'alamat' => 'Jl. Mesjid Raya No. 45, Banjarmasin',
                    'kota' => 'Banjarmasin',
                    'whatsapp' => '6281255557890',
                    'email' => 'info@masjidnuruliman.id',
                    'saldo_awal_kas' => 18500000,
                ];
        }
    }
    return $profil;
}

function upload_berkas($fileInput, $subfolder = 'bukti') {
    if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$fileInput];
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return false;
    }

    // Batasi ukuran file max 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetDir = __DIR__ . '/../uploads/' . $subfolder;
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0777, true);
    }
    $targetPath = $targetDir . '/' . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/' . $subfolder . '/' . $fileName;
    }

    return false;
}

// =========================================================
// OTP (One-Time Password) Helpers
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
 */
function send_otp_email(string $email, string $nama, string $otp, string $purpose): void {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/mail.php';

    // Gunakan fully qualified class name karena 'use' tidak boleh di dalam function

    $label    = $purpose === 'register' ? 'Aktivasi Akun' : 'Reset Kata Sandi';
    $keterangan = $purpose === 'register'
        ? 'Untuk menyelesaikan pendaftaran akun donatur Anda, masukkan kode OTP berikut:'
        : 'Anda telah meminta reset kata sandi. Masukkan kode OTP berikut untuk melanjutkan:';

    $tahun = date('Y');

    // Bangun 6 kotak digit OTP satu per satu agar tampilan mewah & mudah dibaca
    $otpDigits = str_split($otp);
    $otpBoxes  = '';
    foreach ($otpDigits as $d) {
        $otpBoxes .= '<td align="center" valign="middle" style="padding:0 4px;">'
            . '<div style="display:inline-block;min-width:46px;height:62px;line-height:62px;background:#ffffff;'
            . 'border:2px solid #c5a059;border-radius:12px;box-shadow:0 4px 12px rgba(197,160,89,0.20);'
            . 'font-size:32px;font-weight:800;color:#11271d;font-family:Consolas,Monaco,\'Courier New\',monospace;'
            . 'text-align:center;letter-spacing:0;">' . $d . '</div></td>';
    }

    // Template HTML email — gaya klasik Islami premium (Deep Cypress + Antique Gold)
    $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kode OTP — {$label}</title>
</head>
<body style="margin:0;padding:0;background-color:#ede7d8;background-image:linear-gradient(180deg,#f7f3ea 0%,#ede7d8 100%);font-family:'Segoe UI',Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ede7d8;padding:32px 12px;">
  <tr><td align="center" style="padding:0;">
    <table role="presentation" width="620" cellpadding="0" cellspacing="0" style="max-width:620px;width:100%;">

      <!-- Pre-header -->
      <tr>
        <td style="padding:0 0 14px;text-align:center;">
          <p style="margin:0;font-size:11px;color:#a49678;letter-spacing:3px;text-transform:uppercase;font-weight:600;">Portal Digital Masjid Jami' Nurul Iman</p>
        </td>
      </tr>

      <!-- ===== KARTU UTAMA ===== -->
      <tr>
        <td width="100%" align="center" style="background:#ffffff;border-radius:20px;box-shadow:0 18px 50px rgba(29,42,34,0.16);border:1px solid #e3d5b4;overflow:hidden;">

          <!-- HEADER -->
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="padding:0;background:linear-gradient(135deg,#07100b 0%,#142a1f 55%,#1b3a2b 100%);">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="padding:36px 44px 32px;">
                      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                          <td align="left" style="vertical-align:middle;">
                            <div style="vertical-align:middle;">
                              <p style="margin:0;font-size:17px;color:#ffffff;font-weight:700;letter-spacing:0.5px;">Masjid Jami' Nurul Iman</p>
                              <p style="margin:3px 0 0;font-size:10px;color:#c5a059;letter-spacing:3px;text-transform:uppercase;font-weight:600;">Pusat Dakwah &amp; Ibadah Ummat</p>
                            </div>
                          </td>
                          <td align="right" style="vertical-align:middle;">
                            <div style="display:inline-block;padding:6px 14px;border:1px solid rgba(197,160,89,0.55);border-radius:999px;background:rgba(197,160,89,0.12);font-size:10px;color:#dfc896;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Kode Keamanan</div>
                          </td>
                        </tr>
                      </table>
                      <h1 style="margin:26px 0 0;font-size:24px;color:#ffffff;font-weight:800;letter-spacing:0.3px;">{$label}</h1>
                      <p style="margin:6px 0 0;font-size:13px;color:#c8d6cd;line-height:1.6;">Kode verifikasi sekali pakai untuk mengamankan akun Anda.</p>
                      <div style="margin:18px 0 0;height:3px;width:76px;background:linear-gradient(90deg,#dfc896,#c5a059,#8c6b2d);border-radius:3px;"></div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>

          <!-- BODY -->
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="padding:32px 44px 26px;">
                <p style="margin:0 0 10px;font-size:16px;color:#1b2620;font-weight:700;">Assalamu'alaikum, {$nama}!</p>
                <p style="margin:0 0 24px;font-size:13px;color:#5e6d64;line-height:1.75;">{$keterangan}</p>

                <!-- Panel OTP -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fbf7ef;border:2px dashed #d8bd7e;border-radius:18px;">
                  <tr>
                    <td style="padding:26px 20px 30px;text-align:center;">
                      <p style="margin:0 0 20px;font-size:11px;color:#69541f;font-weight:700;letter-spacing:3px;text-transform:uppercase;">Kode Verifikasi Anda</p>
                      <table role="presentation" align="center" cellpadding="0" cellspacing="0">
                        <tr>
                          {$otpBoxes}
                        </tr>
                      </table>
                      <p style="margin:20px 0 0;font-size:12px;color:#8a7a55;line-height:1.6;">
                        Kode ini berlaku <strong style="color:#234b38;">10 menit</strong> sejak dikirimkan
                      </p>
                    </td>
                  </tr>
                </table>

                <!-- Info chips: 2 kolom -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0 0;">
                  <tr>
                    <td width="50%" style="padding:0 8px 0 0;vertical-align:top;">
                      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f7f4;border:1px solid #dfe9e1;border-radius:14px;">
                        <tr>
                          <td style="padding:14px 16px;text-align:center;">
                            <p style="margin:0 0 4px;font-size:11px;color:#1b3a2b;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Sekali Pakai</p>
                            <p style="margin:0;font-size:11px;color:#5e7a6c;line-height:1.5;">Setelah diverifikasi, kode otomatis tidak berlaku kembali.</p>
                          </td>
                        </tr>
                      </table>
                    </td>
                    <td width="50%" style="padding:0 0 0 8px;vertical-align:top;">
                      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fbf7ef;border:1px solid #ede0c4;border-radius:14px;">
                        <tr>
                          <td style="padding:14px 16px;text-align:center;">
                            <p style="margin:0 0 4px;font-size:11px;color:#69541f;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Rahasia Anda</p>
                            <p style="margin:0;font-size:11px;color:#8a7a55;line-height:1.5;">Jangan bagikan kode ini kepada siapapun, termasuk pihak yang mengaku pengurus.</p>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>

                <!-- Peringatan keamanan -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0 0;background:#fdf8ef;border:1px solid #f0dcae;border-left:4px solid #c5a059;border-radius:12px;">
                  <tr>
                    <td style="padding:14px 18px;">
                      <p style="margin:0;font-size:12px;color:#7a5c1e;line-height:1.7;">
                        <strong>Tidak merasa meminta kode ini?</strong> Anda cukup mengabaikan email ini
                        dan tidak perlu melakukan tindakan apa pun. Keamanan akun Anda tetap terjaga.
                      </p>
                    </td>
                  </tr>
                </table>

                <!-- Salam penutup -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0 0;">
                  <tr>
                    <td style="padding:0;">
                      <p style="margin:0;font-size:12px;color:#9aa49e;line-height:1.7;">
                        Jazakallahu khairan atas kepercayaan Anda kepada kami.<br>
                        <span style="color:#5e6d64;">— Tim Portal Masjid</span>
                      </p>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>

          <!-- FOOTER -->
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="background:#f6f1e5;border-top:1px solid #e6dcc0;padding:22px 44px;text-align:center;">
                <p style="margin:0;font-size:11px;color:#9a8b64;line-height:1.7;">Email ini dikirim otomatis dari sistem, mohon tidak membalas.</p>
                <p style="margin:8px 0 0;font-size:11px;color:#9a8b64;">&copy; {$tahun} Masjid Jami' Nurul Iman &middot; Menjaga Amanah, Memakmurkan Ummat</p>
              </td>
            </tr>
          </table>

        </td>
      </tr>

      <!-- Post-footer -->
      <tr>
        <td style="padding:16px 0 0;text-align:center;">
          <p style="margin:0;font-size:10px;color:#aa9c7e;letter-spacing:1px;">Masjid Jami' Nurul Iman &middot; Banjarmasin</p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;

    $textBody = "Assalamu'alaikum, {$nama}!\n\n{$keterangan}\n\nKode OTP {$label} Anda: {$otp}\n\nBerlaku selama 10 menit.\n\nJangan bagikan kode ini kepada siapapun, termasuk pihak yang mengaku sebagai admin masjid.\n\nJika Anda tidak merasa meminta kode ini, abaikan email ini.\n\n— Tim Portal Masjid";

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_SECURE;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

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
