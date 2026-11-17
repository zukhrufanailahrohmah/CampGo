
<?php

// Memulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memeriksa apakah pengguna sudah login
function requireLogin() {

    if (!isset($_SESSION['user_id'])) {
        header("Location: /CampGo/customer/test_login.php");
        exit;
    }

}

// Memeriksa apakah pengguna memiliki role yang sesuai
function requireRole($role) {

    requireLogin();

    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        die("Akses ditolak! Kamu tidak memiliki izin membuka halaman ini.");
    }

}

?>
