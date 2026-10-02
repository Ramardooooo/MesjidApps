<?php
// otp/send_donasi_notification.php
// Email notification untuk status donasi (diverifikasi/ditolak)

require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

function kirim_notif_donasi($email, $nama, $status, $noDonasi, $nominal, $program = '', $keterangan = '') {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_SECURE;
        $mail->Port = MAIL_PORT;
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($email, $nama);
        
        $baseUrl = base_url();
        
        if ($status === 'diverifikasi') {
            $mail->Subject = "✅ Donasi Anda Telah Diverifikasi - $noDonasi";
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9;'>
                <div style='background: #1b3a2b; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h2 style='margin: 0;'>Donasi Diverifikasi</h2>
                </div>
                <div style='background: white; padding: 30px; border-radius: 0 0 10px 10px;'>
                    <p style='font-size: 16px;'>Assalamu'alaikum <strong>$nama</strong>,</p>
                    <p style='font-size: 14px; color: #555;'>Alhamdulillah, donasi Anda telah diverifikasi oleh pengurus masjid:</p>
                    <table style='width: 100%; margin: 20px 0; border-collapse: collapse;'>
                        <tr style='background: #f5f5f5;'>
                            <td style='padding: 10px; border: 1px solid #ddd;'><strong>No. Donasi</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>$noDonasi</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd;'><strong>Program</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>$program</td>
                        </tr>
                        <tr style='background: #f5f5f5;'>
                            <td style='padding: 10px; border: 1px solid #ddd;'><strong>Nominal</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'><strong style='color: #16a34a;'>$nominal</strong></td>
                        </tr>
                    </table>
                    <p style='font-size: 14px; color: #555;'>Jazakumullahu khairan atas kepercayaan dan dukungan Anda dalam memakmurkan masjid.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$baseUrl/kwitansi?no=$noDonasi' style='display: inline-block; padding: 12px 30px; background: #1b3a2b; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>Lihat e-Kwitansi Resmi</a>
                    </div>
                    <p style='font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;'>
                        Email ini dikirim otomatis oleh sistem. Jika ada pertanyaan, hubungi pengurus masjid.
                    </p>
                </div>
            </div>
            ";
        } else {
            $mail->Subject = "❌ Pemberitahuan Status Donasi - $noDonasi";
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9;'>
                <div style='background: #dc2626; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h2 style='margin: 0;'>Donasi Ditolak</h2>
                </div>
                <div style='background: white; padding: 30px; border-radius: 0 0 10px 10px;'>
                    <p style='font-size: 16px;'>Assalamu'alaikum <strong>$nama</strong>,</p>
                    <p style='font-size: 14px; color: #555;'>Mohon maaf, donasi Anda tidak dapat diverifikasi:</p>
                    <table style='width: 100%; margin: 20px 0; border-collapse: collapse;'>
                        <tr style='background: #f5f5f5;'>
                            <td style='padding: 10px; border: 1px solid #ddd;'><strong>No. Donasi</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>$noDonasi</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd;'><strong>Alasan</strong></td>
                            <td style='padding: 10px; border: 1px solid #ddd; color: #dc2626;'>$keterangan</td>
                        </tr>
                    </table>
                    <p style='font-size: 14px; color: #555;'>Jika ada pertanyaan atau ingin melakukan donasi ulang, silakan hubungi pengurus masjid.</p>
                    <p style='font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;'>
                        Email ini dikirim otomatis oleh sistem. Jika ada pertanyaan, hubungi pengurus masjid.
                    </p>
                </div>
            </div>
            ";
        }
        
        $mail->isHTML(true);
        $mail->Body = $body;
        $mail->send();
        
        return true;
    } catch (Exception $e) {
        error_log("Email notification gagal: " . $mail->ErrorInfo);
        return false;
    }
}
