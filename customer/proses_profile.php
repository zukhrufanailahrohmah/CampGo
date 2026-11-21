<?php
require_once __DIR__ . '/../includes/auth.php';

requireRole('customer');
require_post_csrf('customer/profile.php');

$nama = trim($_POST['full_name'] ?? '');
$telepon = trim($_POST['phone'] ?? '');
$alamat = trim($_POST['address'] ?? '');

$errors = [];
if ($nama === '' || $telepon === '' || $alamat === '') {
    $errors[] = 'Semua kolom wajib diisi!';
} else {
    if (panjangTeks($nama) < 2 || panjangTeks($nama) > 120) {
        $errors[] = 'Nama harus 2-120 karakter.';
    }
    if (!preg_match('/^(\+62|62|0)[0-9]{8,13}$/', preg_replace('/[\s\-]/', '', $telepon))) {
        $errors[] = 'Nomor telepon tidak valid (contoh: 081234567890).';
    }
}

if ($errors) {
    foreach ($errors as $e) {
        flash('error', $e);
    }
    set_old(['full_name' => $nama, 'phone' => $telepon, 'address' => $alamat]);
    redirect('customer/profile.php');
}

dbExecute($conn, 'UPDATE users SET full_name = ?, phone = ?, address = ? WHERE user_id = ?', 'sssi', [$nama, $telepon, $alamat, currentUserId()]);
$_SESSION['full_name'] = $nama;

flash('success', 'Profil berhasil diperbarui.');
redirect('customer/profile.php');
