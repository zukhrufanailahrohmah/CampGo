<?php
// =================================
// CAMPGO - BACA ARTIKEL (publik, tanpa login)
// Frontend 1 (Naila). URL: artikel_detail.php?slug=...
// =================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/katalog_functions.php';
require_once __DIR__ . '/../includes/artikel_functions.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$a = null;
if ($slug !== '' && panjangTeks($slug) <= 220) {
    $a = dbFetchOne($conn, "SELECT article_id, title, slug, excerpt, content, image_path,
                                   COALESCE(published_at, created_at) AS tanggal
                            FROM articles
                            WHERE slug = ? AND article_status = 'published'", 's', [$slug]);
}

if (!$a) {
    flash('error', 'Artikel yang kamu cari tidak ditemukan.');
    redirect('customer/artikel.php');
}

$lain = dbFetchAll($conn, "SELECT article_id, title, slug, excerpt, content, image_path,
                                  COALESCE(published_at, created_at) AS tanggal
                           FROM articles
                           WHERE article_status = 'published' AND article_id <> ?
                           ORDER BY COALESCE(published_at, created_at) DESC, article_id DESC
                           LIMIT 3", 'i', [(int)$a['article_id']]);

$pageTitle = $a['title'] . ' - CampGo';
$extraCss = ['katalog.css', 'artikel.css'];
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>
<main class="kt-page">
    <div class="container kt-body">

        <nav class="kt-crumb" aria-label="Navigasi halaman">
            <a href="<?= h(url('customer/artikel.php')) ?>">Artikel</a>
            <span>/</span>
            <strong><?= h($a['title']) ?></strong>
        </nav>

        <article class="at-baca">
            <h1><?= h($a['title']) ?></h1>
            <p class="at-meta"><?= h(atTanggal($a['tanggal'])) ?> &middot; Tim CampGo</p>

            <?php if (ktFotoUrl($a['image_path'])): ?>
                <div class="at-hero"><?= ktFotoHtml($a['image_path'], $a['title']) ?></div>
            <?php endif; ?>

            <div class="at-isi"><?= atRenderIsi($a['content']) ?></div>

            <div class="kt-aksi">
                <a class="btn-primary" href="<?= h(url('customer/katalog.php')) ?>">Lihat katalog perlengkapan</a>
                <a class="kt-btn kt-btn-outline" href="<?= h(url('customer/artikel.php')) ?>">Semua artikel</a>
            </div>
        </article>

        <?php if ($lain): ?>
            <section class="kt-section at-lain">
                <h2>Artikel lainnya</h2>
                <div class="kt-grid">
                    <?php foreach ($lain as $l): ?>
                        <?php
                        $link = url('customer/artikel_detail.php?slug=' . urlencode($l['slug']));
                        $ringkas = trim((string)$l['excerpt']) !== '' ? $l['excerpt'] : preg_replace('/^(## |- )/m', '', $l['content']);
                        ?>
                        <article class="kt-card">
                            <a href="<?= h($link) ?>" tabindex="-1" aria-hidden="true"><?= ktFotoHtml($l['image_path'], $l['title']) ?></a>
                            <div class="kt-card-body">
                                <span class="kt-label"><?= h(atTanggal($l['tanggal'])) ?></span>
                                <h3><a href="<?= h($link) ?>"><?= h($l['title']) ?></a></h3>
                                <p class="kt-desc"><?= h(ktRingkas($ringkas, 100)) ?></p>
                                <a class="kt-btn kt-btn-outline" href="<?= h($link) ?>">Baca artikel</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
