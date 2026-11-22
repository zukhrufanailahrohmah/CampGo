<?php
require_once __DIR__ . '/../includes/auth.php';

require_post_csrf('customer/register.php');

// Mengambil data dari form
$nama = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$telepon = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$alamat = trim($_POST['address'] ?? '');

$errors = [];

if ($nama === '' || $email === '' || $telepon === '' || $password === '' || $alamat === '') {
    $errors[] = 'Semua kolom wajib diisi!';
} else {
    if (panjangTeks($nama) < 2 || panjangTeks($nama) > 120) {
        $errors[] = 'Nama harus 2-120 karakter.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors[] = 'Format email tidak valid!';
    }
    // 08xx / +62xx, 9-15 digit
    if (!preg_match('/^(\+62|62|0)[0-9]{8,13}$/', preg_replace('/[\s\-]/', '', $telepon))) {
        $errors[] = 'Nomor telepon tidak valid (contoh: 081234567890).';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    } elseif (strlen($password) > 72) {
        $errors[] = 'Password maksimal 72 karakter.';
    }
}

if (!$errors) {
    $ada = dbFetchOne($conn, 'SELECT user_id FROM users WHERE email = ?', 's', [$email]);
    if ($ada) {
        $errors[] = 'Email sudah terdaftar! Silakan gunakan email lain.';
    }
}

if ($errors) {
    foreach ($errors as $e) {
        flash('error', $e);
    }
    set_old(['full_name' => $nama, 'email' => $email, 'phone' => $telepon, 'address' => $alamat]);
    redirect('customer/register.php');
}

// Mengamankan password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Menyimpan akun ke database (role selalu customer; Staff/Admin dibuat oleh Admin)
try {
    dbExecute(
        $conn,
        "INSERT INTO users (full_name, email, phone, password_hash, address, role, account_status)
         VALUES (?, ?, ?, ?, ?, 'customer', 'active')",
        'sssss',
        [$nama, $email, $telepon, $password_hash, $alamat]
    );
} catch (RuntimeException $e) {
    if ($e->getCode() === 1062) { // dua orang daftar dengan email sama di saat bersamaan
        flash('error', 'Email sudah terdaftar! Silakan gunakan email lain.');
    } else {
        error_log('Register gagal: ' . $e->getMessage());
        flash('error', 'Registrasi gagal. Silakan coba lagi.');
    }
    set_old(['full_name' => $nama, 'email' => $email, 'phone' => $telepon, 'address' => $alamat]);
    redirect('customer/register.php');
}

flash('success', 'Registrasi berhasil! Akun Customer berhasil dibuat, silakan login.');
redirect('customer/login.php');
