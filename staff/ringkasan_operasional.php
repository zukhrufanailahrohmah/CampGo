<?php
// Halaman pengujian data operasional Staff tanpa mengubah staff/index.php milik Nisa.
require_once __DIR__ . '/../includes/inventory_service.php';
requireRole('staff');
$panelTitle = 'Ringkasan Operasional Staff';
$activeMenu = 'dashboard';
$extraCss = ['app-pages.css'];
$stats = [
 'Booking berlangsung' => (int)(dbFetchOne($conn, "SELECT COUNT(*) total FROM bookings WHERE booking_status='ongoing'")['total'] ?? 0),
 'Pembatalan pending' => (int)(dbFetchOne($conn, "SELECT COUNT(*) total FROM cancellations WHERE cancellation_status='pending'")['total'] ?? 0),
 'Perpanjangan pending' => (int)(dbFetchOne($conn, "SELECT COUNT(*) total FROM extensions WHERE extension_status='pending'")['total'] ?? 0),
 'Reschedule pending' => (int)(dbFetchOne($conn, "SELECT COUNT(*) total FROM reschedules WHERE reschedule_status='pending'")['total'] ?? 0),
];
include __DIR__ . '/../includes/panel_header.php';
?>
<div class="page-wrap"><h1>Ringkasan Operasional Staff</h1>
<p>Halaman tambahan pengujian backend. Dashboard asli tetap tidak diubah.</p>
<div class="table-scroll"><table class="tbl"><tr><th>Indikator</th><th>Jumlah</th></tr>
<?php foreach ($stats as $label => $jumlah): ?><tr><td><?= h($label) ?></td><td><?= $jumlah ?></td></tr><?php endforeach; ?>
</table></div></div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
