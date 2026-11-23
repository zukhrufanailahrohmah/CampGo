<?php
require_once __DIR__ . '/../includes/auth.php';

// Jika sudah login, arahkan ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? '';

    if (in_array($role, ['customer', 'staff', 'admin'], true)) {
        redirect(dashboardPath($role));
    }

    // Role tidak dikenali: bersihkan session
    session_unset();
    session_destroy();
    header('Location: ' . url('customer/login.php'));
    exit;
}

$old = pull_old();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CampGo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body class="au-body">
<div class="au-wrap">

    <?php include __DIR__ . '/../includes/auth_side.php'; ?>

    <main class="au-main">
        <div class="au-card">

            <a class="au-back" href="<?= BASE_URL ?>/index.php">&larr; Kembali ke beranda</a>

            <h1>Login CampGo</h1>
            <p class="au-sub">Silakan masuk ke akun CampGo kamu.</p>

            <?= flash_render(); ?>

            <form action="proses_login.php" method="POST">
                <?= csrf_field(); ?>

                <div class="au-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" autocomplete="email" value="<?= h($old['email'] ?? ''); ?>" required>
                </div>

                <div class="au-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>

                <button type="submit" class="au-btn">Login</button>
            </form>

            <p class="au-alt">Belum punya akun?
                <a href="register.php">Daftar di sini</a>
            </p>

        </div>
    </main>

</div>
</body>
</html>
