<?php
// =================================
// CAMPGO - AUTENTIKASI & PROTEKSI ROLE
// =================================
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function currentUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

// Path dashboard sesuai role (relatif BASE_URL)
function dashboardPath(string $role): string
{
    return match ($role) {
        'staff' => 'staff/index.php',
        'admin' => 'admin/index.php',
        default => 'customer/index.php',
    };
}

// Hanya menerima alamat di dalam aplikasi ini (cegah open redirect)
function safeRedirectTarget(?string $target): ?string
{
    if ($target === null || $target === '') {
        return null;
    }
    $prefix = BASE_URL . '/';
    if (strncmp($target, $prefix, strlen($prefix)) === 0 && strpos($target, '//', 1) === false && strpos($target, "\n") === false) {
        return substr($target, strlen(BASE_URL) + 1);
    }
    return null;
}

// Memeriksa apakah pengguna sudah login (dan akunnya masih aktif)
function requireLogin()
{
    global $conn;

    if (!isLoggedIn()) {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
            $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? null;
        }
        flash('error', 'Silakan login terlebih dahulu.');
        redirect('customer/login.php');
    }

    // Cek ulang ke database: akun yang dinonaktifkan Admin langsung tidak bisa dipakai
    $u = dbFetchOne($conn, 'SELECT full_name, role, account_status FROM users WHERE user_id = ?', 'i', [$_SESSION['user_id']]);

    if (!$u || $u['account_status'] !== 'active') {
        unset($_SESSION['user_id'], $_SESSION['full_name'], $_SESSION['email'], $_SESSION['role']);
        flash('error', 'Akun kamu tidak aktif atau tidak ditemukan.');
        redirect('customer/login.php');
    }

    $_SESSION['role'] = $u['role'];
    $_SESSION['full_name'] = $u['full_name'];
}

// Memeriksa apakah pengguna memiliki role yang sesuai.
// $roles boleh string ('staff') atau array (['staff','admin']).
// Admin otomatis boleh membuka halaman Staff (Master Doc 2.3).
function requireRole($roles)
{
    requireLogin();

    $roles = (array)$roles;
    if (in_array('staff', $roles, true) && !in_array('admin', $roles, true)) {
        $roles[] = 'admin';
    }

    if (!in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403);
        die("Akses ditolak! Kamu tidak memiliki izin membuka halaman ini.");
    }
}
