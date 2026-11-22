<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/booking_service.php';

requireRole('customer');
$riwayat = riwayatBooking($conn, currentUserId());

$pageTitle = 'Riwayat Sewa - CampGo';
$extraCss = ['app-pages.css'];
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<main class="page-wrap">
    <h1>Riwayat Sewa</h1>
    <?= flash_render(); ?>
    <p><a class="btn" href="../booking/form.php">+ Reservasi Baru</a></p>

    <?php if (!$riwayat): ?>
        <p class="muted">Belum ada reservasi.</p>
    <?php else: ?>
    <div class="table-scroll"><table class="tbl">
        <tr><th>Kode</th><th>Periode</th><th>Pengambilan</th><th>Total</th><th>Booking</th><th>Pembayaran</th><th>Pembatalan</th><th>Perpanjangan</th></tr>
        <?php foreach ($riwayat as $r): ?>
        <tr>
            <td><a href="detail.php?code=<?= urlencode($r['booking_code']); ?>"><?= h($r['booking_code']); ?></a></td>
            <td><?= tgl($r['start_date']); ?> - <?= tgl($r['end_date']); ?></td>
            <td><?= h(label_status('pickup', $r['pickup_method'])); ?></td>
            <td><?= rupiah($r['total_amount']); ?></td>
            <td><?= badge('booking', $r['booking_status']); ?></td>
            <td><?= badge('payment', $r['payment_status']); ?></td>
            <td><?= h($r['cancellation_status'] ?? '-'); ?></td>
            <td><?= h($r['extension_status'] ?? '-'); ?></td>
        </tr>
        <?php endforeach; ?>
    </table></div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
