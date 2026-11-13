<?php
// admin/visitors.php - Halaman Log Pengunjung Terpadu (Lengkap dengan filter & pagination)
require_once __DIR__ . '/../config/database.php';
cek_login();

$profil = get_profil_masjid();
$activeMenu = 'visitors';

// Filter & pagination
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = min(max(10, (int)($_GET['perPage'] ?? 25)), 100);
$search     = trim($_GET['search'] ?? '');
$deviceF    = $_GET['device'] ?? '';
$countryF   = $_GET['country'] ?? '';
$dateFrom   = $_GET['date_from'] ?? '';
$dateTo     = $_GET['date_to'] ?? '';

// Build WHERE clause
$where   = [];
$params  = [];
if ($search !== '') {
    $where[] = "(ip LIKE ? OR city LIKE ? OR country LIKE ? OR isp LIKE ? OR browser LIKE ? OR os LIKE ? OR user_agent LIKE ?)";
    $like  = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like, $like, $like, $like]);
}
if ($deviceF !== '') { $where[] = "device_type = ?"; $params[] = $deviceF; }
if ($countryF !== '') { $where[] = "country = ?"; $params[] = $countryF; }
if ($dateFrom !== '') { $where[] = "DATE(created_at) >= ?"; $params[] = $dateFrom; }
if ($dateTo !== '')   { $where[] = "DATE(created_at) <= ?"; $params[] = $dateTo; }

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM visitors $whereSql");
$stmtCount->execute($params);
$total      = (int)$stmtCount->fetchColumn();
$totalPages = (int)ceil($total / $perPage);
$totalPages = max(1, $totalPages);
$offset     = ($page - 1) * $perPage;

// Fetch rows
$stmt = $pdo->prepare("SELECT * FROM visitors $whereSql ORDER BY created_at DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset);
$stmt->execute($params);
$visitors = $stmt->fetchAll();

// Kartu statistik singkat
$cardStats = $pdo->query("SELECT COUNT(*) AS total,
    COUNT(DISTINCT ip) AS unique_ips,
    SUM(device_type = 'mobile') AS mobile,
    SUM(device_type = 'desktop') AS desktop,
    SUM(device_type = 'tablet') AS tablet,
    SUM(device_type = 'bot') AS bots,
    SUM(latitude IS NOT NULL AND longitude IS NOT NULL) AS with_maps,
    COUNT(DISTINCT country) AS countries
FROM visitors")->fetch();

// Opsi filter: unik device & country dari data
$deviceOpts = $pdo->query("SELECT DISTINCT device_type FROM visitors WHERE device_type IS NOT NULL ORDER BY device_type")->fetchAll(PDO::FETCH_COLUMN);
$countryOpts = $pdo->query("SELECT DISTINCT country FROM visitors WHERE country IS NOT NULL ORDER BY country LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);

// Base URL untuk pagination
$baseUrl = base_url() . '/admin/visitors?' . http_build_query(array_merge($_GET, ['page' => '{page}']));

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="mt-6">
    <!-- Header halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-antique-200 pb-6 mb-6">
        <div>
            <h1 class="font-classic text-2xl font-bold text-warm-900 tracking-tight">
                Log Pengunjung Website
            </h1>
            <p class="text-xs text-warm-800/60 mt-1">
                Data pengunjung yang datang ke situs (termasuk lokasi, IP, user agent & device).
            </p>
        </div>
        <a href="dashboard" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white border border-antique-300 text-xs text-cypress-700 hover:bg-cypress-50 hover:text-cypress-900 transition">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>Kembali Dashboard</span>
        </a>
    </div>

    <!-- Statistik Visitor -->
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 mb-8">
        <div class="p-4 bg-white rounded-2xl border border-antique-200 shadow-sm">
            <div class="text-2xl font-bold text-warm-900 font-classic"><?= (int)$cardStats['total'] ?></div>
            <div class="text-[10px] text-stone-500 uppercase tracking-wider">Total Hits</div>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-antique-200 shadow-sm">
            <div class="text-2xl font-bold text-cypress-700 font-classic"><?= (int)$cardStats['unique_ips'] ?></div>
            <div class="text-[10px] text-stone-500 uppercase tracking-wider">Unique IP</div>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-antique-200 shadow-sm">
            <div class="text-2xl font-bold text-blue-600 font-classic"><?= (int)$cardStats['mobile'] ?></div>
            <div class="text-[10px] text-stone-500 uppercase tracking-wider">Mobile</div>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-antique-200 shadow-sm">
            <div class="text-2xl font-bold text-amber-600 font-classic"><?= (int)$cardStats['desktop'] ?></div>
            <div class="text-[10px] text-stone-500 uppercase tracking-wider">Desktop</div>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-antique-200 shadow-sm">
            <div class="text-2xl font-bold text-purple-600 font-classic"><?= (int)$cardStats['tablet'] ?></div>
            <div class="text-[10px] text-stone-500 uppercase tracking-wider">Tablet</div>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-antique-200 shadow-sm">
            <div class="text-2xl font-bold text-emerald-600 font-classic"><?= (int)$cardStats['with_maps'] ?></div>
            <div class="text-[10px] text-stone-500 uppercase tracking-wider">Punya Maps</div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-2xl border border-antique-200 shadow-sm p-5 sm:p-7 mb-8 flex flex-wrap gap-3 items-center">
        <form method="GET" action="visitors" class="flex flex-wrap gap-3 items-center min-w-full sm:w-auto">
            <input type="text" name="search" placeholder="Cari IP / Kota / Negara / Browser..." value="<?= e($search) ?>" class="px-3 py-2 rounded-lg border border-antique-300 bg-warm-50 text-[11px] focus:ring-1 focus:ring-cypress-500 outline-none min-w-[180px]">

            <select name="device" class="px-3 py-2 rounded-lg border border-antique-300 bg-warm-50 text-[11px] focus:ring-1 focus:ring-cypress-500 outline-none">
                <option value="">Semua Device</option>
                <?php foreach ($deviceOpts as $d): ?>
                    <option value="<?= e($d) ?>" <?= $deviceF === $d ? 'selected' : '' ?>><?= ucfirst(e($d)) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="country" class="px-3 py-2 rounded-lg border border-antique-300 bg-warm-50 text-[11px] focus:ring-1 focus:ring-cypress-500 outline-none">
                <option value="">Semua Negara</option>
                <?php foreach ($countryOpts as $c): ?>
                    <option value="<?= e($c) ?>" <?= $countryF === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>

            <input type="date" name="date_from" value="<?= e($dateFrom) ?>" class="px-3 py-2 rounded-lg border border-antique-300 bg-warm-50 text-[11px] focus:ring-1 focus:ring-cypress-500 outline-none">
            <input type="date" name="date_to" value="<?= e($dateTo) ?>" class="px-3 py-2 rounded-lg border border-antique-300 bg-warm-50 text-[11px] focus:ring-1 focus:ring-cypress-500 outline-none">
            <select name="perPage" class="px-3 py-2 rounded-lg border border-antique-300 bg-warm-50 text-[11px] focus:ring-1 focus:ring-cypress-500 outline-none">
                <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 / hal</option>
                <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 / hal</option>
                <option value="100" <?= $perPage === 100 ? 'selected' : '' ?>>100 / hal</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-lg bg-cypress-700 hover:bg-cypress-800 text-white text-xs font-bold transition">
                Filter
            </button>
            <?php if ($search || $deviceF || $countryF || $dateFrom || $dateTo): ?>
                <a href="visitors" class="px-4 py-2 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-600 text-xs font-bold transition">
                    Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabel Data Visitor -->
    <div class="bg-white rounded-2xl border border-antique-200 shadow-sm overflow-hidden mb-8">
        <div class="overflow-x-auto -mx-5 px-5">
            <table class="w-full text-left text-[11px] border-collapse min-w-[700px]">
                <thead>
                    <tr class="border-b border-antique-200 bg-warm-50 text-warm-800/70 uppercase tracking-wider text-[10px]">
                        <th class="px-4 py-3">IP Address</th>
                        <th class="px-4 py-3">Lokasi</th>
                        <th class="px-4 py-3">Device / OS</th>
                        <th class="px-4 py-3">Browser</th>
                        <th class="px-4 py-3">ISP / Provider</th>
                        <th class="px-4 py-3 text-right">Waktu</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-antique-100">
                    <?php if (empty($visitors)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-stone-400 italic">
                                Belum ada data pengunjung terekam.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($visitors as $v): ?>
                        <tr class="hover:bg-warm-50/50 transition">
                            <td class="px-4 py-2.5 font-mono font-bold text-warm-900"><?= e($v['ip']) ?></td>
                            <td class="px-4 py-2.5">
                                <div class="text-sm font-semibold"><?= e($v['city'] ?? '-') ?></div>
                                <div class="text-stone-500 text-[10px] uppercase">
                                    <?= $v['region'] ? e($v['region']) . ', ' : '' ?><?= e($v['country'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-1.5 text-xs font-medium">
                                    <i class="fa-solid fa-<?= $v['device_type'] === 'mobile' ? 'mobile-screen' : ($v['device_type'] === 'tablet' ? 'tablet' : ($v['device_type'] === 'bot' ? 'robot' : 'desktop')) ?> text-[10px] text-cypress-600"></i>
                                    <?= e($v['device_type']) ?>
                                </div>
                                <div class="text-stone-500 text-[10px]">
                                    <?= e($v['os']) ?>
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-stone-50 text-stone-700 border border-stone-200 text-[10px] font-bold">
                                    <?= e($v['browser']) ?>
                                    <?php if ($v['browser_version']): ?>
                                        <span class="text-stone-400/70">v<?= e($v['browser_version']) ?></span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-stone-600 truncate max-w-xs" title="<?= e($v['isp']) ?>">
                                <?= e($v['isp'] ?? '-') ?><?= $v['org'] && $v['org'] !== $v['isp'] ? ' / ' . e($v['org']) : '' ?>
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono text-stone-500">
                                <?= date('d/m/Y H:i', strtotime($v['created_at'])) ?>
                            </td>
                            <td class="px-4 py-2.5 text-right">
                                <?php if ($v['latitude'] && $v['longitude']): ?>
                                    <a href="https://www.google.com/maps?q=<?= $v['latitude'] ?>,<?= $v['longitude'] ?>" target="_blank" rel="noopener noreferrer" class="text-xs text-cypress-700 hover:underline font-bold flex items-center gap-1 justify-center">
                                        <i class="fa-solid fa-location-dot text-[10px]"></i>
                                        Maps
                                    </a>
                                <?php else: ?>
                                    <span class="text-xs text-stone-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total > 0): ?>
        <!-- Pagination -->
        <div class="bg-warm-50 border-t border-antique-200 px-4 py-3 flex items-center justify-between">
            <div class="text-[10px] text-stone-500">
                Menampilkan <b class="text-warm-900"><?= ($page - 1) * $perPage + 1 ?></b>–<b class="text-warm-900"><?= min($page * $perPage, $total) ?></b> dari <b class="text-warm-900"><?= $total ?></b> data
            </div>
            <nav class="flex items-center gap-1">
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => max(1, $page - 1)])) ?>" class="w-7 h-7 rounded-lg bg-white border border-antique-300 flex items-center justify-center text-[10px] text-stone-600 hover:bg-antique-100 transition">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="w-7 h-7 rounded-lg bg-<?= $i === $page ? 'cypress-700 text-white' : 'white' ?> border border-<?= $i === $page ? 'cypress-700' : 'antique-300' ?> flex items-center justify-center text-[10px] text-warm-900 font-bold hover:bg-antique-100 transition">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => min($totalPages, $page + 1)])) ?>" class="w-7 h-7 rounded-lg bg-white border border-antique-300 flex items-center justify-center text-[10px] text-stone-600 hover:bg-antique-100 transition">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
