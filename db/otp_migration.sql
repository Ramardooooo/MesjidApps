-- =============================================
-- OTP Migration — mesjid-website
-- Jalankan query ini di phpMyAdmin / HeidiSQL
-- =============================================

CREATE TABLE IF NOT EXISTS `otp_codes` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(180) NOT NULL,
    `otp_code`   CHAR(6)      NOT NULL,
    `purpose`    ENUM('register','forgot_password') NOT NULL,
    `expires_at` DATETIME     NOT NULL,
    `used`       TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_email_purpose` (`email`, `purpose`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
