<?php
require_once __DIR__ . '/../includes/auth.php';

requireRole('customer');

$user = dbFetchOne($conn, 'SELECT full_name, email, phone, address, created_at FROM users WHERE user_id = ?', 'i', [currentUserId()]);
$old = pull_old();

$pageTitle = 'Profil - CampGo';
$extraCss = ['app-pages.css'];
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>
<main class="page-wrap">
    <h1>Profil Saya</h1>
    <?= flash_render(); ?>

    <form class="card" action="proses_profile.php" method="POST">
        <?= csrf_field(); ?>
        <div class="form-row"><label>Email (tidak dapat diubah)</label>
            <input type="email" value="<?= h($user['email']); ?>" disabled></div>
        <div class="form-row"><label>Nama Lengkap</label>
            <input type="text" name="full_name" maxlength="120" value="<?= h($old['full_name'] ?? $user['full_name']); ?>" required></div>
        <div class="form-row"><label>Nomor Telepon</label>
            <input type="text" name="phone" maxlength="25" value="<?= h($old['phone'] ?? $user['phone']); ?>" required></div>
        <div class="form-row"><label>Alamat</label>
            <textarea name="address" required><?= h($old['address'] ?? $user['address']); ?></textarea></div>
        <button class="btn" type="submit">Simpan Perubahan</button>
    </form>

    <h2>Ganti Password</h2>
    <form class="card" action="proses_ganti_password.php" method="POST">
        <?= csrf_field(); ?>
        <div class="form-row"><label>Password Lama</label>
            <input type="password" name="password_lama" required></div>
        <div class="form-row"><label>Password Baru (minimal 8 karakter)</label>
            <input type="password" name="password_baru" minlength="8" maxlength="72" required></div>
        <div class="form-row"><label>Ulangi Password Baru</label>
            <input type="password" name="password_ulang" minlength="8" maxlength="72" required></div>
        <button class="btn" type="submit">Ganti Password</button>
    </form>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
