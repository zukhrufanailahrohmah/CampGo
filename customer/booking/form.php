<?php
require_once __DIR__ . '/../../includes/auth.php';

requireRole('customer');

// Data katalog (hanya baca). Katalog lengkap dikerjakan Frontend 1; ini form fungsional untuk uji jalur booking.
$barang = dbFetchAll($conn, "SELECT e.equipment_id, e.equipment_name, e.price_per_day, c.category_name
                             FROM equipment e JOIN categories c ON c.category_id = e.category_id
                             WHERE e.item_status = 'available' ORDER BY c.category_name, e.equipment_name");
$paket = dbFetchAll($conn, "SELECT package_id, package_name, description, price_per_day FROM packages
                            WHERE package_status = 'active' ORDER BY package_name");

$user = dbFetchOne($conn, 'SELECT full_name, phone, address FROM users WHERE user_id = ?', 'i', [currentUserId()]);
$old = pull_old();
$val = fn($k, $d = '') => h($old[$k] ?? $d);

$pageTitle = 'Reservasi - CampGo';
$extraCss = ['app-pages.css'];
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<main class="page-wrap">
    <h1>Buat Reservasi</h1>
    <p class="muted">Harga dihitung ulang oleh server saat reservasi dikirim. Tanggal mulai dan selesai sama-sama dihitung (10-12 = 3 hari).</p>
    <?= flash_render(); ?>

    <form id="form-booking" action="proses_booking.php" method="POST">
        <?= csrf_field(); ?>

        <h2>1. Tanggal sewa</h2>
        <div class="grid-2">
            <div class="form-row"><label>Mulai</label>
                <input type="date" name="start_date" min="<?= date('Y-m-d'); ?>" value="<?= $val('start_date'); ?>" required></div>
            <div class="form-row"><label>Selesai</label>
                <input type="date" name="end_date" min="<?= date('Y-m-d'); ?>" value="<?= $val('end_date'); ?>" required></div>
        </div>

        <h2>2. Pilih paket / perlengkapan</h2>
        <?php if (!$paket && !$barang): ?>
            <p class="muted">Belum ada data barang. Jalankan <code>database/seed_demo.php</code> untuk data contoh.</p>
        <?php endif; ?>

        <?php if ($paket): ?>
        <div class="table-scroll"><table class="tbl">
            <tr><th>Paket toko</th><th>Harga/hari</th><th style="width:90px">Jumlah</th></tr>
            <?php foreach ($paket as $p): ?>
            <tr><td><strong><?= h($p['package_name']); ?></strong><br><span class="muted"><?= h($p['description']); ?></span></td>
                <td><?= rupiah($p['price_per_day']); ?></td>
                <td><input type="number" min="0" max="<?= MAX_QTY_PER_ITEM; ?>" name="packages[<?= (int)$p['package_id']; ?>]"
                           value="<?= h($old['packages'][$p['package_id']] ?? 0); ?>"></td></tr>
            <?php endforeach; ?>
        </table></div>
        <?php endif; ?>

        <?php if ($barang): ?>
        <div class="table-scroll"><table class="tbl" style="margin-top:14px">
            <tr><th>Perlengkapan satuan</th><th>Kategori</th><th>Harga/hari</th><th style="width:90px">Jumlah</th></tr>
            <?php foreach ($barang as $b): ?>
            <tr><td><?= h($b['equipment_name']); ?></td><td><?= h($b['category_name']); ?></td>
                <td><?= rupiah($b['price_per_day']); ?></td>
                <td><input type="number" min="0" max="<?= MAX_QTY_PER_ITEM; ?>" name="items[<?= (int)$b['equipment_id']; ?>]"
                           value="<?= h($old['items'][$b['equipment_id']] ?? 0); ?>"></td></tr>
            <?php endforeach; ?>
        </table></div>
        <?php endif; ?>

        <p><button type="button" class="btn btn-light" id="btn-cek-stok">Cek ketersediaan &amp; estimasi biaya</button></p>
        <div id="hasil-stok" class="stok-hasil"></div>

        <h2>3. Data pemesan &amp; pengambilan</h2>
        <div class="grid-2">
            <div class="form-row"><label>Nama pemesan</label>
                <input type="text" name="customer_name" maxlength="120" value="<?= $val('customer_name', $user['full_name']); ?>" required></div>
            <div class="form-row"><label>Nomor telepon</label>
                <input type="text" name="customer_phone" maxlength="25" value="<?= $val('customer_phone', $user['phone']); ?>" required></div>
        </div>
        <div class="form-row"><label>Metode pengambilan</label>
            <select name="pickup_method" id="pickup_method" required>
                <option value="pickup_at_store" <?= ($old['pickup_method'] ?? '') === 'pickup_at_store' ? 'selected' : ''; ?>>Ambil di toko</option>
                <option value="doorstep_delivery" <?= ($old['pickup_method'] ?? '') === 'doorstep_delivery' ? 'selected' : ''; ?>>Diantar staf CampGo<?= DELIVERY_FEE > 0 ? ' (+' . rupiah(DELIVERY_FEE) . ')' : ''; ?></option>
            </select></div>
        <div class="form-row" id="row-alamat"><label>Alamat pengantaran</label>
            <textarea name="delivery_address"><?= $val('delivery_address', $user['address']); ?></textarea></div>
        <div class="form-row"><label>Metode pembayaran</label>
            <select name="payment_method" required>
                <option value="transfer" <?= ($old['payment_method'] ?? '') === 'transfer' ? 'selected' : ''; ?>>Transfer bank (unggah bukti setelah reservasi)</option>
                <option value="cash" <?= ($old['payment_method'] ?? '') === 'cash' ? 'selected' : ''; ?>>Tunai</option>
            </select></div>
        <div class="form-row"><label>Catatan (opsional)</label>
            <textarea name="customer_notes"><?= $val('customer_notes'); ?></textarea></div>

        <button class="btn" type="submit">Kirim Reservasi</button>
    </form>
</main>
<script>window.CAMPGO_CEK_URL = "<?= BASE_URL; ?>/customer/booking/cek_stok.php";</script>
<script src="<?= BASE_URL; ?>/assets/js/booking.js"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
