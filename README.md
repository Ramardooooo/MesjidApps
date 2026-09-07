# Masjid Jami' Nurul Iman

Integrated information system, cash bookkeeping, and digital donation platform for Masjid Jami' Nurul Iman. Built with pure PHP (no framework), MySQL, and Tailwind CSS. Designed for transparent financial management, easier administration, and a modern experience for congregants.

## Tech Stack

- PHP 8.x
- MySQL 8.x / MariaDB 10.5+
- Tailwind CSS (CDN)
- PHPMailer (email / OTP)
- Apache (Laragon / XAMPP)

## Features

- Public website - homepage, mosque profile, donation programs, news, video kajian, financial transparency, contact form
- Admin panel - financial dashboard, news, donation programs, users, banners, donation accounts, transaction reports
- Cash bookkeeping - income/expense records, transaction categories, upload proof of payment, period filters, printable reports
- Online donations - QRIS and bank transfer, admin verification, automatic e-receipt, donor notifications
- Donor portal - dashboard, donation history, profile updates, self-service receipt printing
- Authentication - multi-role login (admin, bendahara, content_admin, donatur), registration, forgot/reset password via OTP
- Cash transparency - public financial reports, open cash balance, income/expense charts
- YouTube integration - embed kajian videos, live streaming, and activity documentation

## Project Structure

```
mesjid-website/
|
|-- admin/              Admin panel (dashboard, news, programs, transactions, users, reports)
|-- assets/             Static assets (CSS, JS, images)
|-- auth/               Login, register, logout, forgot/reset password
|-- config/             App configuration and PDO connection + helper functions
|-- db/                 Database schema, seeders, and migration scripts
|-- donatur/            Donor portal and e-receipt
|-- home/               Public pages (profile, programs, donations, transparency, news, kajian, contact)
|-- layouts/            Reusable layout components (header, footer, sidebar)
|-- uploads/            User uploads (banner, news, proofs, programs, QRIS, board members)
|-- vendor/             Composer dependencies (PHPMailer)
|
|-- index.php           Public homepage
|-- donasi.php          Quick donation endpoint
|-- kegiatan.php        Mosque activities page
|-- 404.php             Custom 404 page
|-- .htaccess           Apache configuration
```

## Database

The system uses 13 relational tables with the InnoDB engine and `utf8mb4` charset:

| Table | Purpose |
|-------|---------|
| `users` | Users and multi-role RBAC (admin, bendahara, content_admin, donatur) |
| `profil_masjid` | Mosque profile, history, vision/mission, contact, social media |
| `pengurus_masjid` | DKM board members |
| `rekening_donasi` | Donation bank accounts and QRIS |
| `program_donasi` | Donation programs (target, collected, status, category) |
| `kategori_transaksi` | Master income/expense categories |
| `transaksi_keuangan` | Cash bookkeeping transactions |
| `donasi_online` | Incoming donations (pending, verified, rejected) |
| `berita` | News and activities |
| `banners` | Homepage sliders and promotion banners |
| `youtube_videos` | YouTube videos (kajian, live, documentation) |
| `notifikasi` | User and donor notifications |
| `pesan_kontak` | Messages from the contact form |

### Default Accounts

| Role | Username | Password |
|------|----------|----------|
| Administrator | `admin` | `admin123` |
| Bendahara | `bendahara` | `bendahara123` |
| Content Admin | `konten` | `konten123` |
| Donatur | `donatur` | `donatur123` |

> Change all default passwords before deploying to production.

## Requirements

- PHP >= 8.0 (PDO MySQL, mbstring, json, openssl)
- MySQL >= 8.0 / MariaDB >= 10.5
- Apache with `mod_rewrite` enabled (or Laragon / XAMPP)
- Composer (only required to install PHPMailer)

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/username/mesjid-website.git
cd mesjid-website
```

### 2. Configure your web server

If you use Laragon, copy the project folder to `C:\laragon\www\` and access it at `http://localhost/mesjid-website/`. Make sure the Apache `rewrite` module is enabled for `.htaccess`.

### 3. Create the database

Run the schema file:

```bash
mysql -u root -p mesjid_website < db/database_schema_v2.sql
```

You can also import `db/database_schema_v2.sql` through phpMyAdmin. The file creates the database, all tables, and seeds realistic starter data.

### 4. Configure the connection

Edit `config/database.php` to match your environment:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mesjid_website');
```

### 5. Install Composer dependencies

```bash
composer install
```

This installs PHPMailer, which is required for OTP email delivery. Then configure the SMTP settings in `config/mail.php`.

### 6. Access the application

| URL | Description |
|-----|-------------|
| `http://localhost/mesjid-website/` | Public homepage |
| `http://localhost/mesjid-website/auth/login.php` | Login page |
| `http://localhost/mesjid-website/admin/dashboard.php` | Admin dashboard |

## Design

The UI uses a classic Islamic theme with:

- Deep Cypress Green (#183728) as the primary color
- Antique Brass Gold (#c5a059) as an accent
- Warm Alabaster (#fbf9f5) as the body background

Fonts: Plus Jakarta Sans (body), Amiri and Cinzel (classic Islamic headings).

## License

This project is licensed under the MIT License. You are free to use, modify, and distribute it.

---

"Memakmurkan Masjid, Mensejahterakan Ummat"

Masjid Jami' Nurul Iman, Pusat Dakwah, Ibadah, dan Pemberdayaan Ummat
