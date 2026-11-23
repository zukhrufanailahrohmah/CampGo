<?php
require_once __DIR__ . '/kontak_functions.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
    require_once __DIR__ . '/../config/database.php';
}
$kontakFooter = kontakAktif($conn);
?>
<footer class="footer" id="kontak">
    <div class="container footer-container">

        <!-- Informasi CampGo -->
        <div class="footer-section">
            <h2>Camp<span>Go.</span></h2>
            <p>
                Temukan pengalaman camping terbaik
                bersama perlengkapan berkualitas dari CampGo.
            </p>
        </div>

        <!-- Navigasi -->
        <div class="footer-section">
            <h3>Menu</h3>
            <ul>
                <li><a href="<?= BASE_URL ?>/index.php">Beranda</a></li>
                <li><a href="<?= BASE_URL ?>/customer/katalog.php">Katalog</a></li>
                <li><a href="<?= BASE_URL ?>/customer/artikel.php">Artikel</a></li>
            </ul>
        </div>

        <!-- Kontak -->
        <div class="footer-section">
            <h3>Hubungi Kami</h3>
            <?php if ($kontakFooter): ?>
                <?php foreach ($kontakFooter as $daftar): foreach ($daftar as $k): $tautan = kontakLink($k); ?>
                    <p><?= h($k['label']) ?>:
                        <?php if ($tautan): ?>
                            <a href="<?= h($tautan) ?>"<?= strpos($tautan, 'http') === 0 ? ' target="_blank" rel="noopener"' : '' ?>><?= h($k['contact_value']) ?></a>
                        <?php else: ?>
                            <?= h($k['contact_value']) ?>
                        <?php endif; ?>
                    </p>
                <?php endforeach; endforeach; ?>
            <?php else: ?>
                <!-- Cadangan sampai kontak diisi lewat CRUD Kontak -->
                <p>WhatsApp: 08xxxxxxxxxx</p>
                <p>Email: campgo@gmail.com</p>
                <p>Instagram: @campgo</p>
            <?php endif; ?>
        </div>

        <!-- Layanan -->
        <div class="footer-section">
            <h3>Layanan</h3>
            <p>Reservasi alat camping</p>
            <p>Pengambilan di toko</p>
            <p>Delivery oleh staf CampGo</p>
        </div>

    </div>

    <div class="footer-bottom">
        <p>
            &copy; <?php echo date("Y"); ?> CampGo.
            All Rights Reserved.
        </p>
    </div>
</footer>
</body>
</html>
