<?php
require_once __DIR__ . '/../includes/auth.php';

requireRole('customer');
require_post_csrf('customer/profile.php');

$lama = $_POST['password_lama'] ?? '';
$baru = $_POST['password_baru'] ?? '';
$ulang = $_POST['password_ulang'] ?? '';

$user = dbFetchOne($conn, 'SELECT password_hash FROM users WHERE user_id = ?', 'i', [currentUserId()]);

if (!password_verify($lama, $user['password_hash'])) {
    flash('error', 'Password lama salah.');
} elseif (strlen($baru) < 8 || strlen($baru) > 72) {
    flash('error', 'Password baru harus 8-72 karakter.');
} elseif ($baru !== $ulang) {
    flash('error', 'Ulangi password baru tidak sama.');
} else {
    dbExecute($conn, 'UPDATE users SET password_hash = ? WHERE user_id = ?', 'si', [password_hash($baru, PASSWORD_DEFAULT), currentUserId()]);
    session_regenerate_id(true);
    flash('success', 'Password berhasil diganti.');
}

redirect('customer/profile.php');
