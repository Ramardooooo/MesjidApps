<?php
// Layouts: Komponen Pengumuman (Header Ticker + Banner)
// Pastikan tabel 'pengumuman' sudah ada di DB dengan kolom: id, isi, tipe (ticker/banner), is_active
$pengumumanTicker = $pdo->query("SELECT isi FROM pengumuman WHERE tipe = 'ticker' AND is_active = 1 ORDER BY id DESC LIMIT 1")->fetchColumn();
$pengumumanBanner = $pdo->query("SELECT isi FROM pengumuman WHERE tipe = 'banner' AND is_active = 1 ORDER BY id DESC LIMIT 1")->fetch();
?>

<!-- Ticker (Running Text) -->
<?php if ($pengumumanTicker): ?>
<div class="bg-cypress-900 text-white py-1.5 overflow-hidden flex items-center no-print">
    <div class="bg-antique-500 px-3 py-0.5 mx-2 rounded text-[9px] font-bold text-cypress-950 uppercase shrink-0">Info</div>
    <marquee class="text-xs font-medium" scrollamount="4">
        <?= e($pengumumanTicker) ?>
    </marquee>
</div>
<?php endif; ?>

<!-- Banner (Popup) -->
<?php if ($pengumumanBanner): ?>
<div id="pengumumanBanner" class="max-w-7xl mx-auto px-4 mt-4 no-print">
    <div class="bg-amber-50 border-l-4 border-amber-500 rounded-lg p-4 shadow-sm flex items-start gap-4">
        <div class="text-amber-600 text-lg mt-0.5">
            <i class="fa-solid fa-bullhorn"></i>
        </div>
        <div class="flex-1">
            <h4 class="font-bold text-amber-900 text-xs uppercase tracking-wider">Pengumuman</h4>
            <p class="text-xs text-amber-800/80 mt-1 leading-relaxed">
                <?= e($pengumumanBanner['isi']) ?>
            </p>
        </div>
        <button onclick="document.getElementById('pengumumanBanner').remove()" class="text-amber-400 hover:text-amber-600">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>
<?php endif; ?>