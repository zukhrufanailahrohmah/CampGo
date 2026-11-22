<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/booking_service.php';

requireRole('staff'); // admin otomatis boleh

$status = $_GET['status'] ?? 'waiting_verification';
$valid = ['pending_payment', 'waiting_verification', 'confirmed', 'ongoing', 'completed', 'cancelled', 'rejected'];

if ($status === 'all') {
    $rows = dbFetchAll($conn, 'SELECT b.*, p.payment_method, p.payment_status FROM bookings b
                               LEFT JOIN payments p ON p.booking_id = b.booking_id AND p.payment_type = \'full_payment\'
                               ORDER BY b.created_at DESC LIMIT 200');
} elseif (in_array($status, $valid, true)) {
    $rows = dbFetchAll($conn, 'SELECT b.*, p.payment_method, p.payment_status FROM bookings b
                               LEFT JOIN payments p ON p.booking_id = b.booking_id AND p.payment_type = \'full_payment\'
                               WHERE b.booking_status = ? ORDER BY b.created_at DESC LIMIT 200', 's', [$status]);
} else {
    $status = 'waiting_verification';
    $rows = [];
}

$pageTitle = 'Transaksi - Staff CampGo';
$panelTitle = 'Transaksi';
$activeMenu = 'transaksi';
$extraCss = ['app-pages.css'];
include __DIR__ . '/../../includes/panel_header.php';
?>
<div class="page-wrap">
    <h1>Manajemen Transaksi</h1>
    <?= flash_render(); ?>
    <p>
        <?php foreach (['all' => 'Semua'] + array_combine($valid, array_map(fn($s) => label_status('booking', $s), $valid)) as $k => $v): ?>
            <a class="btn <?= $status === $k ? '' : 'btn-light'; ?>" style="margin:2px" href="?status=<?= h($k); ?>"><?= h($v); ?></a>
        <?php endforeach; ?>
    </p>

    <div class="table-scroll">
        <table class="tbl">
            <tr>
                <th>Kode</th>
                <th>Customer</th>
                <th>Periode</th>
                <th>Total</th>
                <th>Bayar</th>
                <th>Status booking</th>
                <th>Status bayar</th>
            </tr>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><a href="detail.php?code=<?= urlencode($r['booking_code']); ?>"><?= h($r['booking_code']); ?></a></td>
                    <td><?= h($r['customer_name']); ?><br><span class="muted"><?= h($r['customer_phone']); ?></span></td>
                    <td><?= tgl($r['start_date']); ?> - <?= tgl($r['end_date']); ?></td>
                    <td><?= rupiah($r['total_amount']); ?></td>
                    <td><?= h(label_status('method', $r['payment_method'])); ?></td>
                    <td><?= badge('booking', $r['booking_status']); ?></td>
                    <td><?= badge('payment', $r['payment_status']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr>
                    <td colspan="7" class="muted">Tidak ada data.</td>
                </tr><?php endif; ?>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../../includes/panel_footer.php'; ?>