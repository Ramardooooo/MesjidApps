<?php
// otp/email-template.php
// Template HTML email OTP — hanya di-require saat send_otp_email() dipanggil.
// Variabel yang tersedia: $label, $nama, $keterangan, $otpBoxes, $tahun

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

return ['html' => $htmlBody, 'text' => $textBody];