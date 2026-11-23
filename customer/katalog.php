<?php
// =================================
// CAMPGO - KATALOG PERLENGKAPAN (publik, tanpa login)
// Frontend 1 (Naila). Hanya membaca data: categories, equipment, packages.
// =================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/katalog_functions.php';

// ---------- Baca filter dari URL ----------
$q = trim((string)($_GET['q'] ?? ''));
$q = function_exists('mb_substr') ? mb_substr($q, 0, 60, 'UTF-8') : substr($q, 0, 60);
$like = '%' . addcslashes($q, '%_\\') . '%';

$kategoriId = filter_var($_GET['kategori'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$kategoriId = $kategoriId === false ? null : $kategoriId;

// ---------- Data ----------
$kategori = dbFetchAll($conn, "SELECT c.category_id, c.category_name, COUNT(e.equipment_id) AS jumlah
                               FROM categories c
                               LEFT JOIN equipment e ON e.category_id = c.category_id AND e.item_status = 'available'
                               GROUP BY c.category_id, c.category_name
                               ORDER BY c.category_name");

$sql = "SELECT e.equipment_id, e.equipment_name, e.description, e.image_path, e.price_per_day,
               e.stock_total, e.stock_unavailable, e.item_condition, c.category_name
        FROM equipment e
        JOIN categories c ON c.category_id = e.category_id
        WHERE e.item_status = 'available'";
$types = '';
$params = [];
if ($kategoriId) {
    $sql .= ' AND e.category_id = ?';
    $types .= 'i';
    $params[] = $kategoriId;
}
if ($q !== '') {
    $sql .= ' AND e.equipment_name LIKE ?';
    $types .= 's';
    $params[] = $like;
}
$sql .= ' ORDER BY c.category_name, e.equipment_name';
$barang = dbFetchAll($conn, $sql, $types, $params);

// Paket toko tidak punya kategori, jadi hanya tampil kalau filter kategori tidak dipakai.
$paket = [];
if (!$kategoriId) {
    $sqlPaket = "SELECT package_id, package_name, description, image_path, price_per_day
                 FROM packages WHERE package_status = 'active'";
    $typesPaket = '';
    $paramsPaket = [];
    if ($q !== '') {
        $sqlPaket .= ' AND package_name LIKE ?';
        $typesPaket = 's';
        $paramsPaket[] = $like;
    }
    $paket = dbFetchAll($conn, $sqlPaket . ' ORDER BY package_name', $typesPaket, $paramsPaket);
}

$adaFilter = ($q !== '' || $kategoriId);
$totalBarang = array_sum(array_map(fn($k) => (int)$k['jumlah'], $kategori));

$pageTitle = 'Katalog Perlengkapan - CampGo';
$extraCss = ['katalog.css'];
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>
<main class="kt-page">

    <section class="kt-head">
        <div class="container">
            <h1>Katalog perlengkapan</h1>
            <p>Pilih perlengkapan atau paket camping yang kamu butuhkan. Harga per hari, tanggal dipilih saat reservasi.</p>

            <form class="kt-search" action="<?= h(url('customer/katalog.php')) ?>" method="GET">
                <?php if ($kategoriId): ?>
                    <input type="hidden" name="kategori" value="<?= (int)$kategoriId ?>">
                <?php endif; ?>
                <input type="search" name="q" value="<?= h($q) ?>" placeholder="Cari tenda, sleeping bag, kompor..." maxlength="60" aria-label="Cari perlengkapan">
                <button type="submit" class="kt-btn">Cari</button>
            </form>
        </div>
    </section>

    <div class="container kt-body">

        <?= flash_render(); ?>

        <nav class="kt-chips" aria-label="Kategori">
            <a href="<?= h(url('customer/katalog.php' . ($q !== '' ? '?q=' . urlencode($q) : ''))) ?>"
               class="kt-chip<?= $kategoriId ? '' : ' aktif' ?>">Semua <span><?= (int)$totalBarang ?></span></a>
            <?php foreach ($kategori as $k): ?>
                <?php $qs = 'kategori=' . (int)$k['category_id'] . ($q !== '' ? '&q=' . urlencode($q) : ''); ?>
                <a href="<?= h(url('customer/katalog.php?' . $qs)) ?>"
                   class="kt-chip<?= (int)$kategoriId === (int)$k['category_id'] ? ' aktif' : '' ?>">
                    <?= h($k['category_name']) ?> <span><?= (int)$k['jumlah'] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php if ($paket): ?>
            <section class="kt-section">
                <h2>Paket camping</h2>
                <p class="kt-sub">Perlengkapan sudah dipaketkan dengan harga khusus per hari.</p>
                <div class="kt-grid">
                    <?php foreach ($paket as $p): ?>
                        <?php $detail = url('customer/detail.php?jenis=paket&id=' . (int)$p['package_id']); ?>
                        <article class="kt-card">
                            <a href="<?= h($detail) ?>" tabindex="-1" aria-hidden="true"><?= ktFotoHtml($p['image_path'], $p['package_name']) ?></a>
                            <div class="kt-card-body">
                                <span class="kt-label">Paket toko</span>
                                <h3><a href="<?= h($detail) ?>"><?= h($p['package_name']) ?></a></h3>
                                <p class="kt-desc"><?= h(ktRingkas($p['description'])) ?></p>
                                <div class="kt-card-foot">
                                    <div class="kt-harga"><?= h(rupiah($p['price_per_day'])) ?> <small>/ hari</small></div>
                                </div>
                                <a class="kt-btn kt-btn-outline" href="<?= h($detail) ?>">Lihat isi paket</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="kt-section">
            <h2>Perlengkapan satuan</h2>
            <?php if ($barang): ?>
                <p class="kt-sub"><?= count($barang) ?> perlengkapan<?= $adaFilter ? ' sesuai pencarianmu' : '' ?>.</p>
                <div class="kt-grid">
                    <?php foreach ($barang as $b): ?>
                        <?php
                        $detail = url('customer/detail.php?jenis=barang&id=' . (int)$b['equipment_id']);
                        $sisa = ktSisaStok($b);
                        ?>
                        <article class="kt-card">
                            <a href="<?= h($detail) ?>" tabindex="-1" aria-hidden="true"><?= ktFotoHtml($b['image_path'], $b['equipment_name']) ?></a>
                            <div class="kt-card-body">
                                <span class="kt-label"><?= h($b['category_name']) ?></span>
                                <h3><a href="<?= h($detail) ?>"><?= h($b['equipment_name']) ?></a></h3>
                                <p class="kt-desc"><?= h(ktRingkas($b['description'])) ?></p>
                                <div class="kt-card-foot">
                                    <div class="kt-harga"><?= h(rupiah($b['price_per_day'])) ?> <small>/ hari</small></div>
                                    <?php if ($sisa > 0): ?>
                                        <span class="kt-stok kt-stok-ada">Stok <?= $sisa ?></span>
                                    <?php else: ?>
                                        <span class="kt-stok kt-stok-habis">Habis</span>
                                    <?php endif; ?>
                                </div>
                                <a class="kt-btn kt-btn-outline" href="<?= h($detail) ?>">Lihat detail</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="kt-kosong">
                    <?php if ($adaFilter): ?>
                        <p>Tidak ada perlengkapan yang cocok dengan pencarianmu.</p>
                        <a class="kt-btn" href="<?= h(url('customer/katalog.php')) ?>">Tampilkan semua</a>
                    <?php else: ?>
                        <p>Katalog masih kosong. Perlengkapan akan muncul setelah staf menambahkannya.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <p class="kt-catatan">Stok yang tampil adalah jumlah unit yang bisa disewakan. Ketersediaan pada tanggal tertentu dicek saat kamu membuat reservasi.</p>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
