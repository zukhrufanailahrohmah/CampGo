<?php
require_once __DIR__ . '/../includes/auth.php';

require_post_csrf('customer/login.php');

// Pembatasan percobaan login (per session): 5x gagal = tunggu 5 menit
if (($_SESSION['login_lock_until'] ?? 0) > time()) {
    $menit = (int)ceil(($_SESSION['login_lock_until'] - time()) / 60);
    flash('error', "Terlalu banyak percobaan login. Coba lagi dalam {$menit} menit.");
    redirect('customer/login.php');
}

// Mengambil data login
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Memeriksa kolom kosong
if ($email === '' || $password === '') {
    flash('error', 'Email dan password wajib diisi!');
    set_old(['email' => $email]);
    redirect('customer/login.php');
}

// Mencari akun berdasarkan email
$user = dbFetchOne(
    $conn,
    'SELECT user_id, full_name, email, password_hash, role, account_status
     FROM users
     WHERE email = ?',
    's',
    [$email]
);

// Hash palsu supaya waktu proses sama baik email ada maupun tidak
$hash = $user['password_hash'] ?? '$2y$10$usesomesillystringforsaleaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

// Memeriksa akun dan password
if (!$user || !password_verify($password, $hash)) {
    $_SESSION['login_fail'] = ($_SESSION['login_fail'] ?? 0) + 1;
    if ($_SESSION['login_fail'] >= 5) {
        $_SESSION['login_lock_until'] = time() + 300;
        $_SESSION['login_fail'] = 0;
    }
    flash('error', 'Email atau password salah!');
    set_old(['email' => $email]);
    redirect('customer/login.php');
}

// Memeriksa status akun
if ($user['account_status'] !== 'active') {
    flash('error', 'Akun kamu tidak aktif!');
    redirect('customer/login.php');
}

// Memastikan role akun dikenali
if (!in_array($user['role'], ['customer', 'staff', 'admin'], true)) {
    flash('error', 'Role akun tidak dikenali!');
    redirect('customer/login.php');
}

// Perbarui hash kalau algoritma default PHP sudah berganti
if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    dbExecute($conn, 'UPDATE users SET password_hash = ? WHERE user_id = ?', 'si', [password_hash($password, PASSWORD_DEFAULT), $user['user_id']]);
}

// Membuat session login
session_regenerate_id(true);

$_SESSION['user_id'] = (int)$user['user_id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
unset($_SESSION['login_fail'], $_SESSION['login_lock_until']);

// Kembali ke halaman yang tadi dituju (kalau ada), selain itu ke dashboard role
$tujuan = safeRedirectTarget($_SESSION['after_login'] ?? null);
unset($_SESSION['after_login']);

if ($tujuan !== null && $user['role'] === 'customer' && strpos($tujuan, 'customer/') === 0) {
    redirect($tujuan);
}

redirect(dashboardPath($user['role']));
