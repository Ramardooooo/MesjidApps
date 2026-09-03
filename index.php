<?php
require_once __DIR__ . '/config/database.php';

mulai_session();

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
