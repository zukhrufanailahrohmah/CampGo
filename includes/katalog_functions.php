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
