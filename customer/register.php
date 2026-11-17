
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
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CampGo</title>
</head>
<body>

    <h2>Daftar Akun CampGo</h2>
    <p>Buat akun untuk mulai menggunakan CampGo.</p>

    <form action="proses_register.php" method="POST">

        <label>Nama Lengkap:</label><br>
        <input type="text" name="full_name" required>
        <br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required>
        <br><br>

        <label>Nomor Telepon:</label><br>
        <input type="text" name="phone" required>
        <br><br>

        <label>Password:</label><br>
        <input type="password" name="password" minlength="8" required>
        <br><br>

        <label>Alamat:</label><br>
        <textarea name="address" required></textarea>
        <br><br>

        <button type="submit">Daftar</button>

    </form>

    <p>Sudah punya akun?
        <a href="login.php">Login di sini</a>
    </p>

</body>
</html>
