
<?php

session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Mengambil data login
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Memeriksa kolom kosong
    if (empty($email) || empty($password)) {
        die("Email dan password wajib diisi!");
    }

    // Mencari akun berdasarkan email
    $query = mysqli_prepare(
        $conn,
        "SELECT user_id, full_name, email, password_hash, role, account_status
         FROM users
         WHERE email = ?"
    );

    if (!$query) {
        die("Query gagal: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($query, "s", $email);
    mysqli_stmt_execute($query);

    $hasil = mysqli_stmt_get_result($query);
    $user = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($query);

    // Memeriksa akun dan password
    if (!$user || !password_verify($password, $user['password_hash'])) {
        die("Email atau password salah!");
    }

    // Memeriksa status akun
    if ($user['account_status'] !== 'active') {
        die("Akun kamu tidak aktif!");
    }

    // Memastikan role akun dikenali
    $daftar_role = ['customer', 'staff', 'admin'];

    if (!in_array($user['role'], $daftar_role, true)) {
        die("Role akun tidak dikenali!");
    }

    // Membuat session login
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    // Mengarahkan pengguna berdasarkan role database
    if ($user['role'] === 'customer') {

        header("Location: index.php");

    } elseif ($user['role'] === 'staff') {

        header("Location: ../staff/index.php");

    } elseif ($user['role'] === 'admin') {

        header("Location: ../admin/index.php");

    }

    exit;

} else {

    header("Location: login.php");
    exit;

}

?>
