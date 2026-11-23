<?php
// =================================
// CAMPGO - DAFTAR ARTIKEL (publik, tanpa login)
// Frontend 1 (Naila). Hanya membaca tabel articles berstatus published.
// =================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/katalog_functions.php';
require_once __DIR__ . '/../includes/artikel_functions.php';

$artikel = dbFetchAll($conn, "SELECT article_id, title, slug, excerpt, content, image_path,
                                     COALESCE(published_at, created_at) AS tanggal
                              FROM articles
                              WHERE article_status = 'published'
                              ORDER BY COALESCE(published_at, created_at) DESC, article_id DESC");

$pageTitle = 'Artikel & Tips Camping - CampGo';
$extraCss = ['katalog.css', 'artikel.css'];
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>
<main class="kt-page">

    <section class="kt-head">
        <div class="container">
            <h1>Artikel &amp; tips camping</h1>
            <p>Panduan singkat untuk memilih perlengkapan dan menyiapkan perjalananmu, dari pemula sampai yang sudah sering camping.</p>
        </div>
    </section>

    <div class="container kt-body">
        <?= flash_render(); ?>

        <?php if ($artikel): ?>
            <div class="kt-grid">
                <?php foreach ($artikel as $a): ?>
                    <?php
                    $link = url('customer/artikel_detail.php?slug=' . urlencode($a['slug']));
                    $ringkas = trim((string)$a['excerpt']) !== '' ? $a['excerpt'] : preg_replace('/^(## |- )/m', '', $a['content']);
                    ?>
                    <article class="kt-card">
                        <a href="<?= h($link) ?>" tabindex="-1" aria-hidden="true"><?= ktFotoHtml($a['image_path'], $a['title']) ?></a>
                        <div class="kt-card-body">
                            <span class="kt-label"><?= h(atTanggal($a['tanggal'])) ?></span>
                            <h3><a href="<?= h($link) ?>"><?= h($a['title']) ?></a></h3>
                            <p class="kt-desc"><?= h(ktRingkas($ringkas, 130)) ?></p>
                            <a class="kt-btn kt-btn-outline" href="<?= h($link) ?>">Baca artikel</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="kt-kosong">
                <p>Belum ada artikel yang terbit. Artikel akan muncul setelah staf menerbitkannya.</p>
                <a class="kt-btn" href="<?= h(url('customer/katalog.php')) ?>">Lihat katalog</a>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
