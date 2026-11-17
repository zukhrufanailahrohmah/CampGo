
<?php
session_start();

// Jika sudah login, arahkan ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {

    if ($_SESSION['role'] === 'customer') {
        header("Location: index.php");
    } elseif ($_SESSION['role'] === 'staff') {
        header("Location: ../staff/index.php");
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/index.php");
    } else {
        session_unset();
        session_destroy();
        header("Location: login.php");
    }

    exit;
}

$pesan = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CampGo</title>
</head>
<body>

    <h2>Login CampGo</h2>
    <p>Silakan masuk ke akun CampGo kamu.</p>

    <?php if (!empty($pesan)): ?>
        <p style="color: red;">
            <?= htmlspecialchars($pesan); ?>
        </p>
    <?php endif; ?>

    <form action="proses_login.php" method="POST">

        <label>Email:</label><br>
        <input type="email" name="email" required>
        <br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required>
        <br><br>

        <button type="submit">Login</button>

    </form>

    <p>Belum punya akun?
        <a href="register.php">Daftar di sini</a>
    </p>

</body>
</html>
