<?php
// =================================
// CAMPGO - HEADER + SIDEBAR PANEL STAFF/ADMIN
// Halaman yang memakai file ini harus mengisi dulu:
//   $panelTitle = "Judul Halaman";
//   $activeMenu = "dashboard";
//   $extraCss   = ['app-pages.css'];   (opsional)
// =================================

require_once __DIR__ . '/panel_functions.php';

$panelTitle = $panelTitle ?? 'Panel CampGo';
$activeMenu = $activeMenu ?? '';
$extraCss   = $extraCss ?? [];
$role       = $_SESSION['role'] ?? '';
$namaUser   = $_SESSION['full_name'] ?? 'Pengguna';

// Daftar menu: [key, label, path relatif BASE_URL]
$menuOperasional = [
    ['dashboard',    'Dashboard',    $role === 'admin' ? 'admin/index.php' : 'staff/index.php'],
    ['transaksi',    'Transaksi',    'staff/transactions/index.php'],
    ['pembatalan',   'Pembatalan',   'staff/cancellations/index.php'],
    ['perpanjangan', 'Perpanjangan', 'staff/extensions/index.php'],
    ['reschedule',   'Reschedule',   'staff/reschedules/index.php'],
    ['antar-jemput', 'Antar-Jemput', 'staff/deliveries/index.php'],
    ['pengembalian', 'Pengembalian', 'staff/returns/index.php'],
    ['inventaris',   'Inventaris',   'staff/equipment/index.php'],
    ['artikel',      'Artikel',      'staff/articles/index.php'],
];

$menuAdmin = [
    ['laporan', 'Laporan',        'admin/reports/index.php'],
    ['akun',    'Manajemen Akun', 'admin/users/index.php'],
    ['log',     'Log Aktivitas',  'admin/logs/index.php'],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($panelTitle) ?> - CampGo</title>

    <!-- CSS Naila dimuat dulu agar font/gaya dasar ikut sama -->
    <link rel="stylesheet" href="<?= h(url('assets/css/style.css')) ?>">
    <!-- CSS tambahan milik halaman (mis. app-pages.css dari Mba Irul) -->
    <?php foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="<?= h(url('assets/css/' . $css)) ?>">
    <?php endforeach; ?>
    <!-- CSS panel (Nisa), dimuat terakhir -->
    <link rel="stylesheet" href="<?= h(url('assets/css/panel.css')) ?>">
</head>

<body class="pn-body">

    <div class="pn-layout">

        <!-- SIDEBAR -->
        <aside class="pn-sidebar" id="pnSidebar">
            <a href="<?= h(url('index.php')) ?>" class="pn-logo">Camp<span>Go.</span></a>
            <p class="pn-role-label"><?= $role === 'admin' ? 'Panel Admin' : 'Panel Staff' ?></p>

            <nav class="pn-menu">
                <?php foreach ($menuOperasional as [$key, $label, $path]): ?>
                    <a href="<?= h(url($path)) ?>"
                        class="<?= $activeMenu === $key ? 'active' : '' ?>"><?= h($label) ?></a>
                <?php endforeach; ?>

                <?php if ($role === 'admin'): ?>
                    <p class="pn-menu-title">Khusus Admin</p>
                    <?php foreach ($menuAdmin as [$key, $label, $path]): ?>
                        <a href="<?= h(url($path)) ?>"
                            class="<?= $activeMenu === $key ? 'active' : '' ?>"><?= h($label) ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>

            <a href="<?= h(url('customer/logout.php')) ?>" class="pn-logout">Keluar</a>
        </aside>

        <div class="pn-overlay" id="pnOverlay"></div>

        <!-- KONTEN -->
        <div class="pn-main">

            <header class="pn-topbar">
                <button class="pn-burger" id="pnBurger" aria-label="Buka menu">☰</button>
                <h1 class="pn-page-title"><?= h($panelTitle) ?></h1>
                <div class="pn-user">
                    <span class="pn-user-name"><?= h($namaUser) ?></span>
                    <span class="pn-user-role"><?= h(ucfirst($role)) ?></span>
                </div>
            </header>

            <main class="pn-content">