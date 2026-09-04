<?php
// Redirect ke lokasi baru (struktur folder dirapikan)
$qs = empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING'];
header('Location: home/donasi-online.php' . $qs);
exit;
