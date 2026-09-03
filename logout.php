<?php
require_once __DIR__ . '/config/database.php';
mulai_session();
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
