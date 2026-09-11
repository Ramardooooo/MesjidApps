<?php
require_once __DIR__ . '/../config/database.php';

cek_login();

$user = $_SESSION['user'];

if (($user['role'] ?? '') !== 'donatur') {
    header('Location: ../admin/dashboard.php');
    exit;
}

$profil = get_profil_masjid();
$pageTitle = 'Profil Donatur · ' . $profil['nama_masjid'];

require_once __DIR__ . '/includes/portal-controller.php';
?>
<?php require __DIR__ . '/includes/portal-header.php'; ?>
<?php require __DIR__ . '/includes/portal-profile.php'; ?>
<?php require __DIR__ . '/includes/portal-account.php'; ?>
<?php require __DIR__ . '/includes/portal-status.php'; ?>
<?php require __DIR__ . '/includes/portal-history.php'; ?>
<?php require __DIR__ . '/includes/portal-footer.php'; ?>
<?php require __DIR__ . '/includes/portal-modal.php'; ?>
<?php require __DIR__ . '/includes/portal-scripts.php'; ?>
