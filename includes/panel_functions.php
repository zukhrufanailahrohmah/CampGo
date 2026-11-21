<?php
// =================================
// CAMPGO - FUNGSI BANTU PANEL STAFF/ADMIN
// Dibuat oleh: Nisa (Frontend 2)
// =================================

require_once __DIR__ . '/auth.php';

// Hanya role yang diizinkan yang boleh membuka halaman panel.
// Default: staff dan admin. Halaman khusus admin: requirePanelAccess(['admin']);
function requirePanelAccess(array $roles = ['staff', 'admin'])
{

    // Belum login -> kembali ke halaman login
    if (!isset($_SESSION['user_id'])) {
        header("Location: /CampGo/customer/login.php");
        exit;
    }

    // Sudah login tapi role tidak cocok -> tolak
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        http_response_code(403);
        die("Akses ditolak! Kamu tidak memiliki izin membuka halaman ini.");
    }
}

// Singkatan htmlspecialchars, supaya teks dari database aman ditampilkan
function e($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}
