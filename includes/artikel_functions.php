<?php
// =================================
// CAMPGO - FUNGSI BANTU ARTIKEL (Frontend 1 / Naila)
// Dipakai customer/artikel.php dan customer/artikel_detail.php.
// Semua fungsi diawali "at" supaya tidak bentrok dengan fungsi lain.
// =================================

// Tanggal dalam bahasa Indonesia, contoh: 23 November 2026
function atTanggal(?string $tanggal): string
{
    if (!$tanggal || !($t = strtotime($tanggal))) {
        return '';
    }
    static $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                     'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return date('j', $t) . ' ' . $bulan[(int)date('n', $t)] . ' ' . date('Y', $t);
}

// Isi artikel disimpan sebagai teks biasa. Format sederhana untuk penulis:
//   - paragraf dipisah satu baris kosong
//   - baris diawali "## " menjadi subjudul
//   - baris diawali "- " menjadi daftar poin
// Semua teks di-escape, jadi aman dari HTML/script yang disisipkan.
function atRenderIsi(string $teks): string
{
    $teks = str_replace(["\r\n", "\r"], "\n", trim($teks));
    if ($teks === '') {
        return '';
    }
    $html = '';
    foreach (preg_split('/\n\s*\n/', $teks) as $blok) {
        $baris = array_values(array_filter(array_map('trim', explode("\n", $blok)), fn($b) => $b !== ''));
        if (!$baris) {
            continue;
        }
        if (count($baris) === 1 && strpos($baris[0], '## ') === 0) {
            $html .= '<h2>' . h(substr($baris[0], 3)) . '</h2>';
            continue;
        }
        $semuaPoin = true;
        foreach ($baris as $b) {
            if (strpos($b, '- ') !== 0) {
                $semuaPoin = false;
                break;
            }
        }
        if ($semuaPoin) {
            $html .= '<ul>';
            foreach ($baris as $b) {
                $html .= '<li>' . h(substr($b, 2)) . '</li>';
            }
            $html .= '</ul>';
            continue;
        }
        $html .= '<p>' . implode('<br>', array_map('h', $baris)) . '</p>';
    }
    return $html;
}

// Kartu artikel siap pakai (dipakai homepage). Butuh katalog_functions.php.
// $a butuh: title, slug, excerpt, content, image_path, tanggal
function atKartuArtikelHtml(array $a): string
{
    $link = url('customer/artikel_detail.php?slug=' . urlencode($a['slug']));
    $ringkas = trim((string)$a['excerpt']) !== '' ? $a['excerpt'] : preg_replace('/^(## |- )/m', '', (string)$a['content']);
    ob_start(); ?>
    <article class="kt-card">
        <a href="<?= h($link) ?>" tabindex="-1" aria-hidden="true"><?= ktFotoHtml($a['image_path'], $a['title']) ?></a>
        <div class="kt-card-body">
            <span class="kt-label"><?= h(atTanggal($a['tanggal'])) ?></span>
            <h3><a href="<?= h($link) ?>"><?= h($a['title']) ?></a></h3>
            <p class="kt-desc"><?= h(ktRingkas($ringkas, 110)) ?></p>
            <a class="kt-btn kt-btn-outline" href="<?= h($link) ?>">Baca artikel</a>
        </div>
    </article>
    <?php
    return ob_get_clean();
}
