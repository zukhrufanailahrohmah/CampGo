<?php
// Menampilkan bukti transfer. File di uploads/payments/ tidak bisa dibuka langsung lewat URL;
// hanya Staff/Admin yang login yang bisa melihatnya lewat skrip ini.
require_once __DIR__ . '/../../includes/auth.php';

requireRole('staff');

$p = dbFetchOne($conn, 'SELECT proof_image FROM payments WHERE payment_id = ?', 'i', [(int)($_GET['payment_id'] ?? 0)]);
$file = $p && $p['proof_image'] ? UPLOAD_DIR_PAYMENTS . '/' . basename($p['proof_image']) : null;

if (!$file || !is_file($file)) {
    http_response_code(404);
    die('Bukti pembayaran tidak ditemukan.');
}

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($file);
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    http_response_code(415);
    die('Tipe file tidak didukung.');
}

header('Content-Type: ' . $mime);
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . filesize($file));
readfile($file);
