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
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app-pages.css">
</head>
<body>
<div class="auth-box">

    <h2>Login CampGo</h2>
    <p>Silakan masuk ke akun CampGo kamu.</p>

    <?= flash_render(); ?>

    <form action="proses_login.php" method="POST">
        <?= csrf_field(); ?>

        <div class="form-row">
            <label>Email:</label>
            <input type="email" name="email" value="<?= h($old['email'] ?? ''); ?>" required>
        </div>

        <div class="form-row">
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn">Login</button>
    </form>

    <p>Belum punya akun?
        <a href="register.php">Daftar di sini</a>
    </p>

</div>
</body>
</html>
