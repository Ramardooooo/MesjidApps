<?php
require_once __DIR__ . '/config/database.php';

try {
    // Untuk MySQL gunakan INFORMATION_SCHEMA
    $result = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'profil_masjid' AND TABLE_SCHEMA = DATABASE()")->fetchAll();
    $columns = array_column($result, 'COLUMN_NAME');

    if (!in_array('tiktok', $columns)) {
        $pdo->exec('ALTER TABLE profil_masjid ADD COLUMN tiktok VARCHAR(255)');
        echo "✓ Added tiktok column<br>";
    } else {
        echo "✓ tiktok column sudah ada<br>";
    }

    if (!in_array('twitter', $columns)) {
        $pdo->exec('ALTER TABLE profil_masjid ADD COLUMN twitter VARCHAR(255)');
        echo "✓ Added twitter column<br>";
    } else {
        echo "✓ twitter column sudah ada<br>";
    }

    if (!in_array('telegram', $columns)) {
        $pdo->exec('ALTER TABLE profil_masjid ADD COLUMN telegram VARCHAR(255)');
        echo "✓ Added telegram column<br>";
    } else {
        echo "✓ telegram column sudah ada<br>";
    }

    echo "<br><strong>✅ Database migration selesai!</strong>";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
