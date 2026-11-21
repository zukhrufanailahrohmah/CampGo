
<nav class="navbar">
    <div class="container navbar-container">

        <!-- Logo -->
        <a href="<?= BASE_URL ?>/index.php" class="logo">
            Camp<span>Go.</span>
        </a>

        <!-- Menu Navigasi -->
        <ul class="nav-menu">
            <li>
                <a href="<?= BASE_URL ?>/index.php">Beranda</a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/customer/katalog.php">Katalog</a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/customer/artikel.php">Artikel</a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/index.php#tentang">Tentang Kami</a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/index.php#kontak">Kontak</a>
            </li>
        </ul>

        <!-- Tombol Login / Akun -->
        <div class="nav-button">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>/<?= h(($_SESSION['role'] ?? '') === 'staff' ? 'staff/index.php' : (($_SESSION['role'] ?? '') === 'admin' ? 'admin/index.php' : 'customer/index.php')) ?>" class="btn-login">
                    <?= h($_SESSION['full_name'] ?? 'Akun Saya') ?>
                </a>
                <a href="<?= BASE_URL ?>/customer/logout.php" class="btn-login">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/customer/login.php" class="btn-login">
                    Login
                </a>
            <?php endif; ?>
        </div>

    </div>
</nav>
