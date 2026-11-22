<?php
require_once __DIR__ . '/../includes/inventory_service.php';
requireRole($campgoRequiredRole ?? 'staff');
$back = $campgoCurrentPath ?? 'staff/package_items.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    campgoPost($back);
    campgoBack($back, function () use ($conn) {
        $action = (string) ($_POST['action'] ?? 'save');
        if ($action === 'delete') {
            $id = campgoId($_POST['package_item_id'] ?? 0);
            dbExecute($conn, 'DELETE FROM package_items WHERE package_item_id=?', 'i', [$id]);
            campgoLog($conn, 'remove_item', 'package_items', $id);
            return 'Barang berhasil dihapus dari paket.';
        }

        $packageId = campgoId($_POST['package_id'] ?? 0);
        $equipmentId = campgoId($_POST['equipment_id'] ?? 0);
        $quantity = campgoQty($_POST['quantity'] ?? null);
        if (!dbFetchOne($conn, 'SELECT package_id FROM packages WHERE package_id=?', 'i', [$packageId])) {
            campgoFail('Paket tidak ditemukan.');
        }
        if (!dbFetchOne($conn, 'SELECT equipment_id FROM equipment WHERE equipment_id=?', 'i', [$equipmentId])) {
            campgoFail('Barang tidak ditemukan.');
        }

        if ($action === 'update') {
            $id = campgoId($_POST['package_item_id'] ?? 0);
            if (!dbFetchOne($conn, 'SELECT package_item_id FROM package_items WHERE package_item_id=?', 'i', [$id])) {
                campgoFail('Isi paket yang ingin diedit tidak ditemukan.');
            }
            if (dbFetchOne($conn, 'SELECT package_item_id FROM package_items WHERE package_id=? AND equipment_id=? AND package_item_id<>?', 'iii', [$packageId, $equipmentId, $id])) {
                campgoFail('Barang tersebut sudah terdaftar dalam paket yang dipilih. Edit jumlah pada baris yang sudah ada.');
            }
            dbExecute($conn, 'UPDATE package_items SET package_id=?, equipment_id=?, quantity=? WHERE package_item_id=?', 'iiii', [$packageId, $equipmentId, $quantity, $id]);
            campgoLog($conn, 'update_item', 'package_items', $id);
            return 'Isi paket berhasil diperbarui.';
        }
        if ($action !== 'save') campgoFail('Aksi tidak dikenal.');
        $existing = dbFetchOne($conn, 'SELECT package_item_id FROM package_items WHERE package_id=? AND equipment_id=?', 'ii', [$packageId, $equipmentId]);
        if ($existing) {
            campgoFail('Barang sudah ada di paket ini. Gunakan tombol Edit pada tabel untuk mengubahnya.');
        }
        dbExecute($conn, 'INSERT INTO package_items (package_id,equipment_id,quantity) VALUES (?,?,?)', 'iii', [$packageId, $equipmentId, $quantity]);
        $saved = dbFetchOne($conn, 'SELECT package_item_id FROM package_items WHERE package_id=? AND equipment_id=?', 'ii', [$packageId, $equipmentId]);
        campgoLog($conn, 'add_item', 'package_items', (int) ($saved['package_item_id'] ?? 0));
        return 'Barang berhasil ditambahkan ke paket.';
    });
}

$packages = dbFetchAll($conn, 'SELECT package_id,package_name FROM packages ORDER BY package_name');
$equipment = dbFetchAll($conn, 'SELECT equipment_id,equipment_name FROM equipment ORDER BY equipment_name');
$rows = dbFetchAll($conn, 'SELECT pi.*,p.package_name,e.equipment_name FROM package_items pi JOIN packages p ON p.package_id=pi.package_id JOIN equipment e ON e.equipment_id=pi.equipment_id ORDER BY p.package_name,e.equipment_name');
$editItem = null;
if (isset($_GET['edit'])) {
    $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($editId && $editId > 0) {
        $editItem = dbFetchOne($conn, 'SELECT * FROM package_items WHERE package_item_id=?', 'i', [$editId]);
    }
}
$isEditing = $editItem !== null;
$panelTitle = 'Isi Paket';
$activeMenu = 'inventaris';
$extraCss = ['app-pages.css'];
include __DIR__ . '/../includes/panel_header.php';
?>
<div class="page-wrap">
    <h1>Isi Paket Camping</h1>
    <?= flash_render() ?>
    <?php $inventoryActive = 'package_items'; include __DIR__ . '/../includes/inventory_navigation.php'; ?>

    <?php $showPackageForm = $isEditing || (($_GET['form'] ?? '') === 'add'); ?>
    <?php if (!$showPackageForm): ?>
    <div style="margin: 14px 0 18px;">
        <a class="btn" href="<?= h(url($back . '?form=add')) ?>">Tambah Isi Paket</a>
    </div>
    <?php endif; ?>
    <?php if ($showPackageForm): ?>
    <div class="card">
        <h2><?= $isEditing ? 'Edit Isi Paket' : 'Tambah Isi Paket' ?></h2>
        <?php if (!$packages || !$equipment): ?>
            <p>Buat paket dan tambahkan barang terlebih dahulu melalui menu Paket dan Barang.</p>
        <?php else: ?>
            <form method="post" action="<?= h(url($back)) ?>">
                <?= csrf_field() ?>
                <?php if ($isEditing): ?>
                    <input type="hidden" name="package_item_id" value="<?= (int) $editItem['package_item_id'] ?>">
                <?php endif; ?>
                <div class="form-row">
                    <label for="package_id">Paket</label>
                    <select id="package_id" name="package_id" required>
                        <option value="">-- Pilih paket camping --</option>
                        <?php foreach ($packages as $p): ?>
                            <option value="<?= (int) $p['package_id'] ?>" <?= $isEditing && (int)$editItem['package_id'] === (int)$p['package_id'] ? 'selected' : '' ?>><?= h($p['package_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <label for="equipment_id">Barang</label>
                    <select id="equipment_id" name="equipment_id" required>
                        <option value="">-- Pilih perlengkapan --</option>
                        <?php foreach ($equipment as $e): ?>
                            <option value="<?= (int) $e['equipment_id'] ?>" <?= $isEditing && (int)$editItem['equipment_id'] === (int)$e['equipment_id'] ? 'selected' : '' ?>><?= h($e['equipment_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <label for="quantity">Jumlah unit per paket</label>
                    <input id="quantity" type="number" name="quantity" min="1" value="<?= $isEditing ? (int)$editItem['quantity'] : 1 ?>" required>
                </div>
                <button class="btn" type="submit" name="action" value="<?= $isEditing ? 'update' : 'save' ?>"><?= $isEditing ? 'Simpan Perubahan' : 'Tambah ke Paket' ?></button>
            </form>
        <?php endif; ?>
    </div>
    <p style="margin: 16px 0;"><a href="<?= h(url($back)) ?>">← Kembali ke Daftar Isi Paket</a></p>
    <?php else: ?>

    <h2>Daftar Isi Paket</h2>
    <div class="table-scroll">
        <table class="tbl">
            <thead><tr><th>Paket</th><th>Barang</th><th>Jumlah</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="4">Belum ada barang dalam paket.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= h($r['package_name']) ?></td>
                    <td><?= h($r['equipment_name']) ?></td>
                    <td><?= (int) $r['quantity'] ?> unit</td>
                    <td>
                        <a href="<?= h(url($back . '?edit=' . (int)$r['package_item_id'])) ?>">Edit</a>
                        <form method="post" action="<?= h(url($back)) ?>" style="display:inline" onsubmit="return confirm('Hapus barang ini dari paket?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="package_item_id" value="<?= (int) $r['package_item_id'] ?>">
                            <button type="submit" name="action" value="delete">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; // Daftar tidak ditampilkan bersamaan dengan form. ?>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
