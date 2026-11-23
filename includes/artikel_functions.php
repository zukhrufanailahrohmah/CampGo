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
