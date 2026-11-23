<?php
// =================================
// CAMPGO - DETAIL BARANG / PAKET (publik, tanpa login)
// Frontend 1 (Naila). URL: detail.php?jenis=barang|paket&id=...
// =================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/katalog_functions.php';

$jenis = ($_GET['jenis'] ?? '') === 'paket' ? 'paket' : 'barang';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

$item = null;
$isiPaket = [];
if ($id) {
    if ($jenis === 'paket') {
        $item = dbFetchOne($conn, "SELECT package_id, package_name, description, image_path, price_per_day
                                   FROM packages WHERE package_id = ? AND package_status = 'active'", 'i', [$id]);
        if ($item) {
            $isiPaket = dbFetchAll($conn, "SELECT e.equipment_id, e.equipment_name, pi.quantity
                                           FROM package_items pi
                                           JOIN equipment e ON e.equipment_id = pi.equipment_id
                                           WHERE pi.package_id = ? ORDER BY e.equipment_name", 'i', [$id]);
        }
    } else {
        $item = dbFetchOne($conn, "SELECT e.equipment_id, e.equipment_name, e.description, e.specifications, e.image_path,
                                          e.price_per_day, e.stock_total, e.stock_unavailable, e.item_condition,
                                          e.category_id, c.category_name
                                   FROM equipment e JOIN categories c ON c.category_id = e.category_id
                                   WHERE e.equipment_id = ? AND e.item_status = 'available'", 'i', [$id]);
    }
}

if (!$item) {
    flash('error', 'Barang atau paket yang kamu cari tidak ditemukan.');
    redirect('customer/katalog.php');
}

$nama = $jenis === 'paket' ? $item['package_name'] : $item['equipment_name'];
$sisa = $jenis === 'barang' ? ktSisaStok($item) : null;
$linkSewa = url('customer/booking/form.php');

$pageTitle = $nama . ' - CampGo';
$extraCss = ['katalog.css'];
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>
<main class="kt-page">
    <div class="container kt-body">

        <nav class="kt-crumb" aria-label="Navigasi halaman">
            <a href="<?= h(url('customer/katalog.php')) ?>">Katalog</a>
            <span>/</span>
            <?php if ($jenis === 'barang'): ?>
                <a href="<?= h(url('customer/katalog.php?kategori=' . (int)$item['category_id'])) ?>"><?= h($item['category_name']) ?></a>
                <span>/</span>
            <?php endif; ?>
            <strong><?= h($nama) ?></strong>
        </nav>

        <div class="kt-detail">
            <div class="kt-detail-foto"><?= ktFotoHtml($item['image_path'], $nama) ?></div>

            <div class="kt-detail-info">
                <span class="kt-label"><?= $jenis === 'paket' ? 'Paket toko' : h($item['category_name']) ?></span>
                <h1><?= h($nama) ?></h1>

                <div class="kt-harga kt-harga-besar"><?= h(rupiah($item['price_per_day'])) ?> <small>/ hari</small></div>

                <?php if ($jenis === 'barang'): ?>
                    <dl class="kt-fakta">
                        <div><dt>Ketersediaan</dt>
                            <dd><?php if ($sisa > 0): ?><span class="kt-stok kt-stok-ada">Stok <?= $sisa ?> unit</span>
                                <?php else: ?><span class="kt-stok kt-stok-habis">Habis</span><?php endif; ?></dd></div>
                        <div><dt>Kondisi</dt><dd><?= h(ktKondisi($item['item_condition'])) ?></dd></div>
                        <div><dt>Kategori</dt><dd><?= h($item['category_name']) ?></dd></div>
                    </dl>
                <?php endif; ?>

                <div class="kt-aksi">
                    <?php if ($jenis === 'paket' || $sisa > 0): ?>
                        <a class="btn-primary" href="<?= h($linkSewa) ?>">Sewa sekarang</a>
                    <?php else: ?>
                        <span class="kt-btn kt-btn-mati">Stok habis</span>
                    <?php endif; ?>
                    <a class="kt-btn kt-btn-outline" href="<?= h(url('customer/katalog.php')) ?>">Kembali ke katalog</a>
                </div>
                <p class="kt-catatan">Tanggal sewa dan jumlah unit dipilih di halaman reservasi. Ketersediaan dicek sesuai tanggal yang kamu pilih.</p>

                <?php if (trim((string)$item['description']) !== ''): ?>
                    <h2>Deskripsi</h2>
                    <p><?= nl2br(h($item['description'])) ?></p>
                <?php endif; ?>

                <?php if ($jenis === 'barang' && trim((string)$item['specifications']) !== ''): ?>
                    <h2>Spesifikasi</h2>
                    <ul class="kt-spek">
                        <?php foreach (preg_split('/\R/', trim($item['specifications'])) as $baris): ?>
                            <?php
                            $baris = trim($baris);
                            if ($baris === '') continue;
                            $pecah = explode(':', $baris, 2);
                            ?>
                            <li>
                                <?php if (count($pecah) === 2): ?>
                                    <span><?= h(trim($pecah[0])) ?></span><strong><?= h(trim($pecah[1])) ?></strong>
                                <?php else: ?>
                                    <?= h($baris) ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($jenis === 'paket'): ?>
                    <h2>Isi paket</h2>
                    <?php if ($isiPaket): ?>
                        <ul class="kt-isi">
                            <?php foreach ($isiPaket as $i): ?>
                                <li>
                                    <a href="<?= h(url('customer/detail.php?jenis=barang&id=' . (int)$i['equipment_id'])) ?>"><?= h($i['equipment_name']) ?></a>
                                    <span><?= (int)$i['quantity'] ?> unit</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="kt-catatan">Harga paket sudah termasuk semua barang di atas, tidak dihitung lagi per barang.</p>
                    <?php else: ?>
                        <p class="kt-sub">Isi paket belum diatur.</p>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>