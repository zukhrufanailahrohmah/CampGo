<?php
// Digital Rental Pass. Bisa dibuka tanpa login: masukkan kode booking + email/telepon.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/booking_service.php';

$kode = trim($_REQUEST['code'] ?? '');
$kontak = trim($_POST['kontak'] ?? '');
$pass = null;
$dicoba = false;

if ($kode !== '') {
    $loginId = (isLoggedIn() && ($_SESSION['role'] ?? '') === 'customer') ? currentUserId() : null;

    // Pembatasan tebak-tebakan kode: maks. 8 percobaan tamu per 10 menit (per session)
    $_SESSION['pass_tries'] = array_filter($_SESSION['pass_tries'] ?? [], fn($t) => $t > time() - 600);
    $bolehCoba = $loginId !== null || count($_SESSION['pass_tries']) < 8;

    if ($loginId !== null || $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$bolehCoba) {
            flash('error', 'Terlalu banyak percobaan. Coba lagi beberapa menit lagi.');
        } else {
            if ($loginId === null) {
                if (!csrf_valid()) {
                    flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
                    redirect('customer/pass.php');
                }
                $_SESSION['pass_tries'][] = time();
            }
            $dicoba = true;
            $pass = ambilPass($conn, $kode, $kontak, $loginId);
        }
    }
}

$pageTitle = 'Digital Rental Pass - CampGo';
$extraCss = ['app-pages.css'];
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>
<main class="page-wrap">
    <h1>Digital Rental Pass</h1>
    <?= flash_render(); ?>

<?php if ($pass): $b = $pass['booking']; ?>
    <div class="pass">
        <p class="muted">Tunjukkan kartu ini ke staf CampGo saat pengambilan barang</p>
        <div class="kode"><?= h($b['booking_code']); ?></div>
        <p>Kode verifikasi: <span class="verif"><?= h($pass['kode_verifikasi']); ?></span>
           <span class="muted">(pengganti QR, versi dummy)</span></p>
        <p><strong><?= h($b['customer_name']); ?></strong> | <?= h($b['customer_phone']); ?></p>
        <p>Periode sewa: <?= tgl($b['start_date']); ?> s/d <?= tgl($b['end_date']); ?>
           | <?= h(label_status('pickup', $b['pickup_method'])); ?>
           <?php if ($pass['delivery'] && $pass['delivery']['delivery_schedule']): ?>
              | Jadwal: <?= h($pass['delivery']['delivery_schedule']); ?><?php endif; ?></p>

        <table class="tbl">
            <tr><th>Checklist serah terima</th><th>Jumlah</th></tr>
            <?php foreach ($pass['paket'] as $p): ?>
                <tr><td><strong>Paket <?= h($p['package_name']); ?></strong></td><td>x<?= (int)$p['quantity']; ?></td></tr>
                <?php foreach ($pass['isi_paket'] as $ip) { if ($ip['booking_package_id'] == $p['booking_package_id']) { ?>
                <tr><td>&#9744; <?= h($ip['equipment_name']); ?></td><td><?= (int)$ip['quantity']; ?></td></tr>
                <?php } } ?>
            <?php endforeach; ?>
            <?php foreach ($pass['barang_satuan'] as $it): ?>
                <tr><td>&#9744; <?= h($it['equipment_name']); ?></td><td><?= (int)$it['quantity']; ?></td></tr>
            <?php endforeach; ?>
        </table>
        <p class="muted">Bawa kartu identitas asli sebagai jaminan. Kartu dikembalikan setelah barang diperiksa.</p>
    </div>
<?php else: ?>
    <?php if ($dicoba): ?>
        <div class="flash flash-error">Pass tidak ditemukan. Pastikan kode booking dan email/nomor telepon benar, dan booking sudah terkonfirmasi.</div>
    <?php endif; ?>
    <form class="card" method="POST" action="pass.php">
        <?= csrf_field(); ?>
        <div class="form-row"><label>Kode booking</label>
            <input type="text" name="code" value="<?= h($kode); ?>" placeholder="CG-261121-ABCDE" required></div>
        <div class="form-row"><label>Email atau nomor telepon</label>
            <input type="text" name="kontak" value="<?= h($kontak); ?>" required></div>
        <button class="btn" type="submit">Tampilkan Pass</button>
    </form>
<?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
