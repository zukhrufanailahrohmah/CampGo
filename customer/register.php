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
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body class="au-body">
<div class="au-wrap">

    <?php include __DIR__ . '/../includes/auth_side.php'; ?>

    <main class="au-main">
        <div class="au-card">

            <a class="au-back" href="<?= BASE_URL ?>/index.php">&larr; Kembali ke beranda</a>

            <h1>Daftar Akun CampGo</h1>
            <p class="au-sub">Buat akun untuk mulai menggunakan CampGo.</p>

            <?= flash_render(); ?>

            <form action="proses_register.php" method="POST">
                <?= csrf_field(); ?>

                <div class="au-field">
                    <label for="full_name">Nama Lengkap</label>
                    <input type="text" id="full_name" name="full_name" maxlength="120" autocomplete="name" value="<?= h($old['full_name'] ?? ''); ?>" required>
                </div>

                <div class="au-row2">
                    <div class="au-field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" maxlength="150" autocomplete="email" value="<?= h($old['email'] ?? ''); ?>" required>
                    </div>

                    <div class="au-field">
                        <label for="phone">Nomor Telepon</label>
                        <input type="text" id="phone" name="phone" maxlength="25" inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx" value="<?= h($old['phone'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="au-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                    <span class="au-hint">Minimal 8 karakter.</span>
                </div>

                <div class="au-field">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" rows="3" autocomplete="street-address" required><?= h($old['address'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="au-btn">Daftar</button>
            </form>

            <p class="au-alt">Sudah punya akun?
                <a href="login.php">Login di sini</a>
            </p>

        </div>
    </main>

</div>
</body>
</html>
