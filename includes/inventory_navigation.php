<?php
/** Navigasi khusus inventaris. Tidak digunakan oleh modul artikel. */
$inventoryAdmin = ($campgoRequiredRole ?? 'staff') === 'admin';
$inventoryManagePath = $campgoManagePath ?? ($inventoryAdmin ? 'admin/equipment/index.php' : 'staff/manage.php');
$inventoryPackagePath = $campgoPackagePath ?? ($inventoryAdmin ? 'admin/equipment/packages.php' : 'staff/package_items.php');
$inventoryStockPath = $campgoStockPath ?? ($inventoryAdmin ? 'admin/equipment/stock.php' : 'staff/stock.php');
$inventoryLinks = [
    ['categories', 'Kategori', $inventoryManagePath . '?module=categories'],
    ['equipment', 'Barang', $inventoryManagePath . '?module=equipment'],
    ['packages', 'Paket', $inventoryManagePath . '?module=packages'],
    ['package_items', 'Isi Paket', $inventoryPackagePath],
    ['stock', 'Penyesuaian Stok', $inventoryStockPath],
    ['contacts', 'Kontak', $inventoryManagePath . '?module=contacts'],
];
?>
<nav aria-label="Menu pengelolaan inventaris" style="display:flex;flex-wrap:wrap;gap:8px;margin:0 0 20px">
<?php foreach ($inventoryLinks as [$key, $label, $path]): ?>
    <a class="btn btn-light" href="<?= h(url($path)) ?>"
       <?php if (($inventoryActive ?? '') === $key): ?>aria-current="page"<?php endif; ?>><?= h($label) ?></a>
<?php endforeach; ?>
</nav>
