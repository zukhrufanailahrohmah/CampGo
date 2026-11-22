<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/booking_service.php';

requireRole('staff');

$d = detailBooking($conn, trim($_GET['code'] ?? ''));
if (!$d) {
    http_response_code(404);
    die('Booking tidak ditemukan.');
}
$b = $d['booking'];

$panelTitle = 'Detail ' . $b['booking_code'];
$activeMenu = 'transaksi';
$extraCss = ['app-pages.css'];
include __DIR__ . '/../../includes/panel_header.php';
?>
<div class="page-wrap">
    <h1><?= h($b['booking_code']); ?></h1>
    <?= flash_render(); ?>

    <div class="card">
        <p>Status: <?= badge('booking', $b['booking_status']); ?> | Dibuat: <?= h($b['created_at']); ?></p>
        <p><strong><?= h($b['customer_name']); ?></strong> | <?= h($b['customer_phone']); ?> | <?= h($b['user_email']); ?></p>
        <p>Periode: <?= tgl($b['start_date']); ?> s/d <?= tgl($b['end_date']); ?> | <?= h(label_status('pickup', $b['pickup_method'])); ?></p>
        <?php if ($b['delivery_address']): ?><p>Alamat antar: <?= h($b['delivery_address']); ?></p><?php endif; ?>
        <?php if ($b['customer_notes']): ?><p>Catatan: <?= h($b['customer_notes']); ?></p><?php endif; ?>
    </div>

    <h2>Barang</h2>
    <div class="table-scroll">
        <table class="tbl">
            <tr>
                <th>Item</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
            <?php foreach ($d['paket'] as $p): ?>
                <tr>
                    <td><strong>Paket: <?= h($p['package_name']); ?></strong>
                        <?php foreach ($d['isi_paket'] as $ip) {
                            if ($ip['booking_package_id'] == $p['booking_package_id']) {
                                echo '<br><span class="muted">- ' . h($ip['equipment_name']) . ' x' . (int)$ip['quantity'] . '</span>';
                            }
                        } ?></td>
                    <td><?= (int)$p['quantity']; ?></td>
                    <td><?= rupiah($p['subtotal']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($d['barang_satuan'] as $it): ?>
                <tr>
                    <td><?= h($it['equipment_name']); ?></td>
                    <td><?= (int)$it['quantity']; ?></td>
                    <td><?= rupiah($it['subtotal']); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2" style="text-align:right"><strong>Total (termasuk antar <?= rupiah($b['delivery_fee']); ?>)</strong></td>
                <td><strong><?= rupiah($b['total_amount']); ?></strong></td>
            </tr>
        </table>
    </div>

    <h2>Pembayaran</h2>
    <?php foreach ($d['payments'] as $p): ?>
        <div class="card">
            <p><?= h(label_status('method', $p['payment_method'])); ?> | <?= h($p['payment_type']); ?> |
                <?= rupiah($p['amount']); ?> | <?= badge('payment', $p['payment_status']); ?></p>
            <?php if ($p['proof_image']): ?>
                <p><a href="bukti.php?payment_id=<?= (int)$p['payment_id']; ?>" target="_blank">Lihat bukti transfer</a></p>
            <?php endif; ?>
            <?php if ($p['verification_notes']): ?><p class="muted">Catatan verifikasi: <?= h($p['verification_notes']); ?></p><?php endif; ?>

            <?php if (
                $p['payment_type'] === 'full_payment' && in_array($p['payment_status'], ['unpaid', 'waiting_verification'], true)
                && in_array($b['booking_status'], ['pending_payment', 'waiting_verification'], true)
            ): ?>
                <form action="proses_verifikasi.php" method="POST">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="payment_id" value="<?= (int)$p['payment_id']; ?>">
                    <input type="hidden" name="booking_code" value="<?= h($b['booking_code']); ?>">
                    <div class="form-row"><label>Catatan (wajib kalau menolak)</label>
                        <input type="text" name="catatan" maxlength="300">
                    </div>
                    <?php if ($p['payment_method'] === 'cash' || $p['payment_status'] === 'waiting_verification'): ?>
                        <button class="btn" name="aksi" value="setuju" type="submit">
                            <?= $p['payment_method'] === 'cash' ? 'Catat tunai diterima &amp; konfirmasi' : 'Setujui pembayaran'; ?></button>
                    <?php endif; ?>
                    <?php if ($p['payment_method'] === 'transfer' && $p['payment_status'] === 'waiting_verification'): ?>
                        <button class="btn btn-danger" name="aksi" value="tolak" type="submit">Tolak bukti</button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <p><a href="index.php">&larr; Kembali ke daftar</a></p>
</div>
<?php include __DIR__ . '/../../includes/panel_footer.php'; ?>