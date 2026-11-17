
<?php

session_start();
include '../config/database.php';

// Memastikan data dikirim melalui POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Mengambil data dari form
    $nama = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $alamat = trim($_POST['address'] ?? '');

    // Memastikan semua data terisi
    if (
        empty($nama) ||
        empty($email) ||
        empty($telepon) ||
        empty($password) ||
        empty($alamat)
    ) {
        die("Semua kolom wajib diisi!");
    }

    // Memeriksa format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Format email tidak valid!");
    }

    // Memeriksa apakah email sudah terdaftar
    $query = mysqli_prepare(
        $conn,
        "SELECT user_id FROM users WHERE email = ?"
    );

    mysqli_stmt_bind_param($query, "s", $email);
    mysqli_stmt_execute($query);

    $hasil = mysqli_stmt_get_result($query);

    if (mysqli_num_rows($hasil) > 0) {
        mysqli_stmt_close($query);
        die("Email sudah terdaftar! Silakan gunakan email lain.");
    }

    mysqli_stmt_close($query);

    // Mengamankan password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Menyimpan akun ke database
    $query = mysqli_prepare(
        $conn,
        "INSERT INTO users
        (full_name, email, phone, password_hash, address, role, account_status)
        VALUES (?, ?, ?, ?, ?, 'customer', 'active')"
    );

    if (!$query) {
        die("Query gagal disiapkan: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $query,
        "sssss",
        $nama,
        $email,
        $telepon,
        $password_hash,
        $alamat
    );

    // Menjalankan penyimpanan
    if (mysqli_stmt_execute($query)) {

        echo "Registrasi berhasil!<br>";
        echo "Akun Customer berhasil dibuat.";

    } else {

        echo "Registrasi gagal: " . mysqli_stmt_error($query);

    }

    mysqli_stmt_close($query);

} else {

    echo "Silakan kirim data melalui form Register.";

}

?>
