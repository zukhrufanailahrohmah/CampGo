<?php
// =================================
// CAMPGO - DATABASE CONFIGURATION
// =================================

// Konfigurasi database
$host = "localhost";
$username = "root";
$password = "";
$database = "campgo_db";

// Membuat koneksi
$conn = mysqli_connect(
    $host,
    $username,
    $password,
    $database
);

// Memeriksa koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
