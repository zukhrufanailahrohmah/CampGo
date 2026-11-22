<?php
require_once __DIR__ . '/../includes/auth.php';

// Jika sudah login, arahkan ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    redirect(dashboardPath($_SESSION['role'] ?? 'customer'));
}

$old = pull_old();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CampGo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app-pages.css">
</head>
<body>
<div class="auth-box">

    <h2>Daftar Akun CampGo</h2>
    <p>Buat akun untuk mulai menggunakan CampGo.</p>

    <?= flash_render(); ?>

    <form action="proses_register.php" method="POST">
        <?= csrf_field(); ?>

        <div class="form-row">
            <label>Nama Lengkap:</label>
            <input type="text" name="full_name" maxlength="120" value="<?= h($old['full_name'] ?? ''); ?>" required>
        </div>

        <div class="form-row">
            <label>Email:</label>
            <input type="email" name="email" maxlength="150" value="<?= h($old['email'] ?? ''); ?>" required>
        </div>

        <div class="form-row">
            <label>Nomor Telepon:</label>
            <input type="text" name="phone" maxlength="25" placeholder="08xxxxxxxxxx" value="<?= h($old['phone'] ?? ''); ?>" required>
        </div>

        <div class="form-row">
            <label>Password (minimal 8 karakter):</label>
            <input type="password" name="password" minlength="8" maxlength="72" required>
        </div>

        <div class="form-row">
            <label>Alamat:</label>
            <textarea name="address" required><?= h($old['address'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn">Daftar</button>
    </form>

    <p>Sudah punya akun?
        <a href="login.php">Login di sini</a>
    </p>

</div>
</body>
</html>
