<?php
// =================================
// CAMPGO - FUNGSI BANTU KATALOG (Frontend 1 / Naila)
// Dipakai customer/katalog.php dan customer/detail.php.
// Semua fungsi diawali "kt" supaya tidak bentrok dengan fungsi lain.
// =================================

// URL foto dari kolom image_path. Mengembalikan null kalau kosong/tidak valid.
function ktFotoUrl(?string $path): ?string
{
    if (!$path || !preg_match('#^uploads/[a-z]+/[A-Za-z0-9._-]+$#', $path)) {
        return null;
    }
    return url($path);
}

// Stok yang bisa disewakan (belum memperhitungkan tanggal booking).
function ktSisaStok(array $row): int
{
    return max(0, (int)$row['stock_total'] - (int)$row['stock_unavailable']);
}

function ktKondisi(?string $v): string
{
    static $map = [
        'excellent'   => 'Sangat baik',
        'good'        => 'Baik',
        'fair'        => 'Cukup',
        'maintenance' => 'Sedang perawatan',
    ];
    return $map[$v ?? ''] ?? '-';
}

// Potong teks untuk ringkasan kartu.
function ktRingkas(?string $teks, int $max = 90): string
{
    $teks = trim(preg_replace('/\s+/', ' ', (string)$teks));
    if (panjangTeks($teks) <= $max) {
        return $teks;
    }
    $potong = function_exists('mb_substr') ? mb_substr($teks, 0, $max, 'UTF-8') : substr($teks, 0, $max);
    return rtrim($potong) . '...';
}

// Kotak foto: gambar kalau ada, kalau tidak tampil kotak polos.
function ktFotoHtml(?string $path, string $alt): string
{
    $src = ktFotoUrl($path);
    if ($src) {
        return '<div class="kt-foto"><img src="' . h($src) . '" alt="' . h($alt) . '" loading="lazy"></div>';
    }
    return '<div class="kt-foto kt-foto-kosong"><span>Foto belum tersedia</span></div>';
}

// ---------------------------------
// Kartu siap pakai (dipakai homepage). Markup sama dengan katalog.php.
// ---------------------------------

// $b butuh: equipment_id, equipment_name, description, image_path, price_per_day,
//           stock_total, stock_unavailable, category_name
function ktKartuBarangHtml(array $b): string
{
    $detail = url('customer/detail.php?jenis=barang&id=' . (int)$b['equipment_id']);
    $sisa = ktSisaStok($b);
    ob_start(); ?>
    <article class="kt-card">
        <a href="<?= h($detail) ?>" tabindex="-1" aria-hidden="true"><?= ktFotoHtml($b['image_path'], $b['equipment_name']) ?></a>
        <div class="kt-card-body">
            <span class="kt-label"><?= h($b['category_name']) ?></span>
            <h3><a href="<?= h($detail) ?>"><?= h($b['equipment_name']) ?></a></h3>
            <p class="kt-desc"><?= h(ktRingkas($b['description'])) ?></p>
            <div class="kt-card-foot">
                <div class="kt-harga"><?= h(rupiah($b['price_per_day'])) ?> <small>/ hari</small></div>
                <?php if ($sisa > 0): ?>
                    <span class="kt-stok kt-stok-ada">Stok <?= $sisa ?></span>
                <?php else: ?>
                    <span class="kt-stok kt-stok-habis">Habis</span>
                <?php endif; ?>
            </div>
            <a class="kt-btn kt-btn-outline" href="<?= h($detail) ?>">Lihat detail</a>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

// $p butuh: package_id, package_name, description, image_path, price_per_day
function ktKartuPaketHtml(array $p): string
{
    $detail = url('customer/detail.php?jenis=paket&id=' . (int)$p['package_id']);
    ob_start(); ?>
    <article class="kt-card">
        <a href="<?= h($detail) ?>" tabindex="-1" aria-hidden="true"><?= ktFotoHtml($p['image_path'], $p['package_name']) ?></a>
        <div class="kt-card-body">
            <span class="kt-label">Paket toko</span>
            <h3><a href="<?= h($detail) ?>"><?= h($p['package_name']) ?></a></h3>
            <p class="kt-desc"><?= h(ktRingkas($p['description'])) ?></p>
            <div class="kt-card-foot">
                <div class="kt-harga"><?= h(rupiah($p['price_per_day'])) ?> <small>/ hari</small></div>
            </div>
            <a class="kt-btn kt-btn-outline" href="<?= h($detail) ?>">Lihat isi paket</a>
        </div>
    </article>
    <?php
    return ob_get_clean();
}
