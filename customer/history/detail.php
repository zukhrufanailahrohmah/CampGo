<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/booking_service.php';

requireRole('customer');

$d = detailBooking($conn, trim($_GET['code'] ?? ''), currentUserId()); // hanya milik sendiri
if (!$d) {
    http_response_code(404);
    die('Booking tidak ditemukan.');
}
$b = $d['booking'];
$bayar = $d['payments'][0] ?? null; // pembayaran awal

$pageTitle = 'Detail Booking ' . $b['booking_code'];
$extraCss = ['app-pages.css'];
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<main class="page-wrap">
    <h1>Booking <?= h($b['booking_code']); ?></h1>
    <?= flash_render(); ?>

    <div class="card">
        <p>Status booking: <?= badge('booking', $b['booking_status']); ?></p>
        <p>Periode: <?= tgl($b['start_date']); ?> s/d <?= tgl($b['end_date']); ?> |
           Pengambilan: <?= h(label_status('pickup', $b['pickup_method'])); ?></p>
        <?php if ($b['delivery_address']): ?><p>Alamat antar: <?= h($b['delivery_address']); ?></p><?php endif; ?>
        <?php if ($d['delivery'] && $d['delivery']['delivery_schedule']): ?>
            <p>Jadwal: <?= h($d['delivery']['delivery_schedule']); ?></p><?php endif; ?>
    </div>

    <h2>Rincian</h2>
    <div class="table-scroll"><table class="tbl">
        <tr><th>Item</th><th>Jumlah</th><th>Harga/hari</th><th>Hari</th><th>Subtotal</th></tr>
        <?php foreach ($d['paket'] as $p): ?>
        <tr><td><strong>Paket: <?= h($p['package_name']); ?></strong>
            <?php foreach ($d['isi_paket'] as $ip) { if ($ip['booking_package_id'] == $p['booking_package_id']) {
                echo '<br><span class="muted">- ' . h($ip['equipment_name']) . ' x' . (int)$ip['quantity'] . '</span>'; } } ?></td>
            <td><?= (int)$p['quantity']; ?></td><td><?= rupiah($p['unit_price']); ?></td>
            <td><?= (int)$p['rental_days']; ?></td><td><?= rupiah($p['subtotal']); ?></td></tr>
        <?php endforeach; ?>
        <?php foreach ($d['barang_satuan'] as $it): ?>
        <tr><td><?= h($it['equipment_name']); ?></td><td><?= (int)$it['quantity']; ?></td>
            <td><?= rupiah($it['unit_price']); ?></td><td><?= (int)$it['rental_days']; ?></td><td><?= rupiah($it['subtotal']); ?></td></tr>
        <?php endforeach; ?>
        <tr><td colspan="4" style="text-align:right">Biaya antar</td><td><?= rupiah($b['delivery_fee']); ?></td></tr>
        <tr><td colspan="4" style="text-align:right"><strong>Total</strong></td><td><strong><?= rupiah($b['total_amount']); ?></strong></td></tr>
    </table></div>

    <h2>Pembayaran</h2>
    <?php if ($bayar): ?>
    <div class="card">
        <p>Metode: <?= h(label_status('method', $bayar['payment_method'])); ?> | Status: <?= badge('payment', $bayar['payment_status']); ?></p>
        <?php if ($bayar['payment_status'] === 'rejected' && $bayar['verification_notes']): ?>
            <div class="flash flash-error">Bukti ditolak: <?= h($bayar['verification_notes']); ?></div><?php endif; ?>

        <?php if ($bayar['payment_method'] === 'transfer' && $b['booking_status'] === 'pending_payment'): ?>
            <p class="muted">Transfer sebesar <?= rupiah($bayar['amount']); ?> lalu unggah bukti (JPG/PNG/WEBP, maks. 2 MB).
               Nomor rekening toko belum ditentukan kelompok.</p>
            <form action="../booking/proses_upload_bukti.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="payment_id" value="<?= (int)$bayar['payment_id']; ?>">
                <input type="hidden" name="booking_code" value="<?= h($b['booking_code']); ?>">
                <div class="form-row"><input type="file" name="proof" accept="image/jpeg,image/png,image/webp" required></div>
                <button class="btn" type="submit">Unggah Bukti</button>
            </form>
        <?php elseif ($bayar['payment_method'] === 'cash' && $bayar['payment_status'] === 'unpaid' && $b['booking_status'] === 'waiting_verification'): ?>
            <p class="muted">Bayar tunai sesuai prosedur pengambilan barang. Staff akan mencatat penerimaan uang.</p>
        <?php elseif ($bayar['payment_status'] === 'waiting_verification'): ?>
            <p class="muted">Bukti sudah terkirim, menunggu verifikasi Staff.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (in_array($b['booking_status'], ['confirmed', 'ongoing'], true)): ?>
        <p><a class="btn" href="../pass.php?code=<?= urlencode($b['booking_code']); ?>">Lihat Digital Rental Pass</a></p>
    <?php endif; ?>
    <p><a href="index.php">&larr; Kembali ke riwayat</a></p>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
