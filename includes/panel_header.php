<?php
// =================================
// CAMPGO - HEADER + SIDEBAR PANEL STAFF/ADMIN
// Halaman yang memakai file ini harus mengisi dulu:
//   $panelTitle = "Judul Halaman";
//   $activeMenu = "dashboard";
// =================================

require_once __DIR__ . '/panel_functions.php';

$panelTitle = $panelTitle ?? 'Panel CampGo';
$activeMenu = $activeMenu ?? '';
$role       = $_SESSION['role'] ?? '';
$namaUser   = $_SESSION['full_name'] ?? 'Pengguna';

// Daftar menu: [key, label, link]
// Pola folder mengikuti Master Document & Mba Irul: staff/<fitur>/index.php
$menuOperasional = [
    ['dashboard',    'Dashboard',    $role === 'admin' ? '/CampGo/admin/index.php' : '/CampGo/staff/index.php'],
    ['transaksi',    'Transaksi',    '/CampGo/staff/transactions/index.php'],
    ['pembatalan',   'Pembatalan',   '/CampGo/staff/cancellations/index.php'],
    ['perpanjangan', 'Perpanjangan', '/CampGo/staff/extensions/index.php'],
    ['reschedule',   'Reschedule',   '/CampGo/staff/reschedules/index.php'],
    ['antar-jemput', 'Antar-Jemput', '/CampGo/staff/deliveries/index.php'],
    ['pengembalian', 'Pengembalian', '/CampGo/staff/returns/index.php'],
    ['inventaris',   'Inventaris',   '/CampGo/staff/equipment/index.php'],
    ['artikel',      'Artikel',      '/CampGo/staff/articles/index.php'],
];

$menuAdmin = [
    ['laporan', 'Laporan',        '/CampGo/admin/reports/index.php'],
    ['akun',    'Manajemen Akun', '/CampGo/admin/users/index.php'],
    ['log',     'Log Aktivitas',  '/CampGo/admin/logs/index.php'],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($panelTitle) ?> - CampGo</title>

    <!-- CSS Naila dimuat dulu agar font/gaya dasar ikut sama -->
    <link rel="stylesheet" href="/CampGo/assets/css/style.css">
    <!-- CSS panel (Nisa) -->
    <link rel="stylesheet" href="/CampGo/assets/css/panel.css">
</head>

<body class="pn-body">

    <div class="pn-layout">

        <!-- SIDEBAR -->
        <aside class="pn-sidebar" id="pnSidebar">
            <a href="/CampGo/index.php" class="pn-logo">Camp<span>Go.</span></a>
            <p class="pn-role-label"><?= $role === 'admin' ? 'Panel Admin' : 'Panel Staff' ?></p>

            <nav class="pn-menu">
                <?php foreach ($menuOperasional as [$key, $label, $link]): ?>
                    <a href="<?= e($link) ?>"
                        class="<?= $activeMenu === $key ? 'active' : '' ?>"><?= e($label) ?></a>
                <?php endforeach; ?>

                <?php if ($role === 'admin'): ?>
                    <p class="pn-menu-title">Khusus Admin</p>
                    <?php foreach ($menuAdmin as [$key, $label, $link]): ?>
                        <a href="<?= e($link) ?>"
                            class="<?= $activeMenu === $key ? 'active' : '' ?>"><?= e($label) ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>

            <a href="/CampGo/customer/logout.php" class="pn-logout">Keluar</a>
        </aside>

        <div class="pn-overlay" id="pnOverlay"></div>

        <!-- KONTEN -->
        <div class="pn-main">

            <header class="pn-topbar">
                <button class="pn-burger" id="pnBurger" aria-label="Buka menu">☰</button>
                <h1 class="pn-page-title"><?= e($panelTitle) ?></h1>
                <div class="pn-user">
                    <span class="pn-user-name"><?= e($namaUser) ?></span>
                    <span class="pn-user-role"><?= e(ucfirst($role)) ?></span>
                </div>
            </header>

            <main class="pn-content">