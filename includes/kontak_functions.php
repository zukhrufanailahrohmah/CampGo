<?php
// =================================
// CAMPGO - FUNGSI BANTU KONTAK (Frontend 1 / Naila)
// Membaca tabel contacts (diisi lewat CRUD Kontak oleh Staff/Admin).
// Dipakai footer dan homepage. Semua fungsi diawali "kontak".
// =================================

// Kontak aktif, dikelompokkan per jenis: ['whatsapp' => [baris, ...], 'email' => [...], ...]
function kontakAktif(mysqli $conn): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    try {
        $rows = dbFetchAll($conn, "SELECT contact_type, label, contact_value
                                   FROM contacts WHERE is_active = 1
                                   ORDER BY display_order, contact_id");
        foreach ($rows as $r) {
            $cache[$r['contact_type']][] = $r;
        }
    } catch (Throwable $e) {
        $cache = []; // tabel belum ada / error: footer memakai teks cadangan
    }
    return $cache;
}

// Tautan yang bisa diklik untuk satu kontak, atau null kalau tidak ada.
function kontakLink(array $k): ?string
{
    $nilai = trim((string)$k['contact_value']);
    if ($nilai === '') {
        return null;
    }
    switch ($k['contact_type']) {
        case 'whatsapp':
            $angka = preg_replace('/\D+/', '', $nilai);
            if ($angka === '' || strlen($angka) < 8) {
                return null;
            }
            if ($angka[0] === '0') {
                $angka = '62' . substr($angka, 1);
            }
            return 'https://wa.me/' . $angka;
        case 'instagram':
        case 'tiktok':
            if (preg_match('#^https?://#i', $nilai)) {
                return filter_var($nilai, FILTER_VALIDATE_URL) ? $nilai : null;
            }
            $akun = ltrim($nilai, '@');
            if (!preg_match('/^[A-Za-z0-9._]{1,60}$/', $akun)) {
                return null;
            }
            return $k['contact_type'] === 'instagram'
                ? 'https://www.instagram.com/' . $akun
                : 'https://www.tiktok.com/@' . $akun;
        case 'email':
            return filter_var($nilai, FILTER_VALIDATE_EMAIL) ? 'mailto:' . $nilai : null;
    }
    return null;
}

// Tautan WhatsApp pertama yang aktif, atau null.
function kontakWhatsApp(mysqli $conn): ?string
{
    foreach (kontakAktif($conn)['whatsapp'] ?? [] as $k) {
        $t = kontakLink($k);
        if ($t) {
            return $t;
        }
    }
    return null;
}
