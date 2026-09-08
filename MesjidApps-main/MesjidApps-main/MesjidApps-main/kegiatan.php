<?php
// Redirect ke lokasi baru (struktur folder dirapikan)
$qs = empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING'];
header('Location: home/berita.php' . $qs);
exit;
