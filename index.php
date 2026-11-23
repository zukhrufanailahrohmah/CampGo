<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/katalog_functions.php';
require_once __DIR__ . '/includes/artikel_functions.php';
require_once __DIR__ . '/includes/kontak_functions.php';

// ---------- Data homepage (hanya membaca) ----------
// Kategori yang punya barang tersedia
$kategori = dbFetchAll($conn, "SELECT c.category_id, c.category_name, COUNT(e.equipment_id) AS jumlah
                               FROM categories c
                               JOIN equipment e ON e.category_id = c.category_id AND e.item_status = 'available'
                               GROUP BY c.category_id, c.category_name
                               ORDER BY c.category_name");

// Rekomendasi: barang yang paling sering dipesan (kalau belum ada booking, urut nama)
$rekomendasi = dbFetchAll($conn, "SELECT e.equipment_id, e.equipment_name, e.description, e.image_path, e.price_per_day,
                                         e.stock_total, e.stock_unavailable, c.category_name
                                  FROM equipment e
                                  JOIN categories c ON c.category_id = e.category_id
                                  WHERE e.item_status = 'available' AND e.stock_total > e.stock_unavailable
                                  ORDER BY (SELECT COUNT(*) FROM booking_details bd WHERE bd.equipment_id = e.equipment_id) DESC,
                                           e.equipment_name
                                  LIMIT 4");

$paket = dbFetchAll($conn, "SELECT package_id, package_name, description, image_path, price_per_day
                            FROM packages WHERE package_status = 'active'
                            ORDER BY package_name LIMIT 3");

$artikel = dbFetchAll($conn, "SELECT title, slug, excerpt, content, image_path,
                                     COALESCE(published_at, created_at) AS tanggal
                              FROM articles WHERE article_status = 'published'
                              ORDER BY COALESCE(published_at, created_at) DESC, article_id DESC
                              LIMIT 3");

$linkWhatsApp = kontakWhatsApp($conn);

$pageTitle = "CampGo - Sewa Alat Camping";
$extraCss = ['katalog.css', 'artikel.css', 'beranda.css'];
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="container hero-content">
            <div class="hero-text">
                <span class="hero-label">
                    EXPLORE THE OUTDOORS
                </span>

                <h1>
                    Petualangan Seru
                    Dimulai Bersama
                    <span>CampGo.</span>
                </h1>

                <p>
                    Temukan perlengkapan camping berkualitas
                    untuk menemani setiap petualanganmu.
                    Sewa lebih mudah, praktis, dan terpercaya.
                </p>

                <a href="<?= BASE_URL ?>/customer/katalog.php"
                   class="btn-primary">
                    Jelajahi Katalog
                </a>
            </div>
        </div>
    </section>

    <!-- KATEGORI -->
    <?php if ($kategori): ?>
    <section class="hm-section">
        <div class="container">
            <div class="section-heading">
                <span>KATEGORI</span>
                <h2>Cari Berdasarkan Kebutuhan</h2>
                <p>Pilih jenis perlengkapan yang kamu perlukan untuk camping.</p>
            </div>

            <div class="hm-kategori-grid">
                <?php foreach ($kategori as $k): ?>
                    <a class="hm-kategori" href="<?= h(url('customer/katalog.php?kategori=' . (int)$k['category_id'])) ?>">
                        <strong><?= h($k['category_name']) ?></strong>
                        <span><?= (int)$k['jumlah'] ?> perlengkapan</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- REKOMENDASI PERLENGKAPAN -->
    <?php if ($rekomendasi): ?>
    <section class="hm-section">
        <div class="container">
            <div class="section-heading">
                <span>REKOMENDASI</span>
                <h2>Perlengkapan Pilihan</h2>
                <p>Perlengkapan yang siap disewa untuk petualanganmu.</p>
            </div>

            <div class="kt-grid">
                <?php foreach ($rekomendasi as $b): ?>
                    <?= ktKartuBarangHtml($b) ?>
                <?php endforeach; ?>
            </div>

            <div class="hm-more">
                <a class="kt-btn kt-btn-outline" href="<?= h(url('customer/katalog.php')) ?>">Lihat semua perlengkapan</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- PAKET SEWA -->
    <?php if ($paket): ?>
    <section class="hm-section">
        <div class="container">
            <div class="section-heading">
                <span>PAKET SEWA</span>
                <h2>Paket Camping Hemat</h2>
                <p>Perlengkapan sudah dipaketkan dengan harga khusus per hari.</p>
            </div>

            <div class="kt-grid">
                <?php foreach ($paket as $p): ?>
                    <?= ktKartuPaketHtml($p) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- KEUNGGULAN -->
    <section class="advantages">
        <div class="container">
            <div class="section-heading">
                <span>KENAPA CAMPGO?</span>
                <h2>Camping Jadi Lebih Mudah</h2>
                <p>
                    Kami menyediakan perlengkapan camping
                    untuk pengalaman petualangan yang nyaman.
                </p>
            </div>

            <div class="advantage-grid">
                <div class="advantage-card">
                    <h3>Peralatan Berkualitas</h3>
                    <p>
                        Perlengkapan camping dirawat dan
                        diperiksa sebelum disewakan.
                    </p>
                </div>

                <div class="advantage-card">
                    <h3>Reservasi Mudah</h3>
                    <p>
                        Pesan alat camping sesuai tanggal
                        dan kebutuhanmu.
                    </p>
                </div>

                <div class="advantage-card">
                    <h3>Layanan Delivery</h3>
                    <p>
                        Pilih pengambilan di toko atau
                        pengantaran langsung oleh staf CampGo.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CARA RESERVASI -->
    <section class="how-to">
        <div class="container">
            <div class="section-heading">
                <span>CARA RESERVASI</span>
                <h2>Mulai Petualanganmu</h2>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <h3>01. Pilih Peralatan</h3>
                    <p>Pilih perlengkapan camping yang dibutuhkan.</p>
                </div>

                <div class="step-card">
                    <h3>02. Tentukan Jadwal</h3>
                    <p>Tentukan tanggal dan jumlah alat yang disewa.</p>
                </div>

                <div class="step-card">
                    <h3>03. Konfirmasi Pesanan</h3>
                    <p>Lakukan pembayaran sesuai metode yang tersedia.</p>
                </div>

                <div class="step-card">
                    <h3>04. Siap Berpetualang!</h3>
                    <p>Ambil sendiri atau pilih layanan delivery CampGo.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ARTIKEL TERBARU -->
    <?php if ($artikel): ?>
    <section class="hm-section">
        <div class="container">
            <div class="section-heading">
                <span>ARTIKEL &amp; TIPS</span>
                <h2>Siap Sebelum Berangkat</h2>
                <p>Tips dan panduan singkat untuk camping yang lebih nyaman.</p>
            </div>

            <div class="kt-grid">
                <?php foreach ($artikel as $a): ?>
                    <?= atKartuArtikelHtml($a) ?>
                <?php endforeach; ?>
            </div>

            <div class="hm-more">
                <a class="kt-btn kt-btn-outline" href="<?= h(url('customer/artikel.php')) ?>">Lihat semua artikel</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- AJAKAN & KONTAK -->
    <section class="hm-cta">
        <div class="container">
            <h2>Siap Mulai Petualanganmu?</h2>
            <?php if ($linkWhatsApp): ?>
                <p>Pilih perlengkapan di katalog, atau tanyakan dulu ke kami lewat WhatsApp kalau masih bingung.</p>
            <?php else: ?>
                <p>Pilih perlengkapan di katalog, tentukan tanggal sewa, dan petualanganmu siap dimulai.</p>
            <?php endif; ?>
            <div class="hm-cta-aksi">
                <a class="btn-primary" href="<?= h(url('customer/katalog.php')) ?>">Lihat Katalog</a>
                <?php if ($linkWhatsApp): ?>
                    <a class="kt-btn kt-btn-outline" href="<?= h($linkWhatsApp) ?>" target="_blank" rel="noopener">Chat WhatsApp</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>