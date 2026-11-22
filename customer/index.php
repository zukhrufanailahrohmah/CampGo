<?php
require_once __DIR__ . '/../includes/auth.php';

requireRole('customer');

$pageTitle = 'Akun Saya - CampGo';
$extraCss = ['app-pages.css'];
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>
<main class="page-wrap">
    <h1>Halo, <?= h($_SESSION['full_name']); ?>!</h1>
    <p>Selamat datang di layanan persewaan alat camping!</p>

    <?= flash_render(); ?>

    <div class="grid-2">
        <div class="card"><h3>Sewa Alat</h3>
            <p class="muted">Pilih perlengkapan atau paket dan buat reservasi.</p>
            <a class="btn" href="booking/form.php">Buat Reservasi</a></div>
        <div class="card"><h3>Riwayat Sewa</h3>
            <p class="muted">Lihat status booking, pembayaran, dan Digital Rental Pass.</p>
            <a class="btn" href="history/index.php">Lihat Riwayat</a></div>
        <div class="card"><h3>Profil</h3>
            <p class="muted">Ubah data pribadi dan password.</p>
            <a class="btn" href="profile.php">Edit Profil</a></div>
        <div class="card"><h3>Digital Rental Pass</h3>
            <p class="muted">Tunjukkan kode booking saat pengambilan barang.</p>
            <a class="btn" href="pass.php">Buka Pass</a></div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
