<?php
// Ringkasan data untuk dashboard Admin tanpa mengubah admin/index.php milik tim.
require_once __DIR__ . '/../includes/inventory_service.php';
requireRole('admin');
$panelTitle = 'Ringkasan Operasional Admin';
$activeMenu = 'dashboard';
$extraCss = ['app-pages.css'];
$stats = [
 'Pengguna' => (int)(dbFetchOne($conn, 'SELECT COUNT(*) total FROM users')['total'] ?? 0),
 'Staff' => (int)(dbFetchOne($conn, "SELECT COUNT(*) total FROM users WHERE role='staff'")['total'] ?? 0),
 'Jenis Perlengkapan' => (int)(dbFetchOne($conn, 'SELECT COUNT(*) total FROM equipment')['total'] ?? 0),
 'Booking' => (int)(dbFetchOne($conn, 'SELECT COUNT(*) total FROM bookings')['total'] ?? 0),
 'Pembatalan Pending' => (int)(dbFetchOne($conn, "SELECT COUNT(*) total FROM cancellations WHERE cancellation_status='pending'")['total'] ?? 0),
];
include __DIR__ . '/../includes/panel_header.php';
?>
<div class="page-wrap">
<h1>Ringkasan Operasional Admin</h1>
<p>Halaman tambahan untuk menguji data dashboard tanpa mengganti dashboard asli tim.</p>
<div class="table-scroll"><table class="tbl"><tr><th>Indikator</th><th>Jumlah</th></tr>
<?php foreach ($stats as $label => $jumlah): ?><tr><td><?= h($label) ?></td><td><?= $jumlah ?></td></tr><?php endforeach; ?>
</table></div>
<p><a href="<?= h(url('admin/reports/index.php')) ?>">Lihat laporan transaksi dan inventaris</a></p>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
