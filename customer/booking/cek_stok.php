<?php
// Endpoint JSON: cek ketersediaan & estimasi biaya (tanpa menyimpan apa pun).
// GET: start_date, end_date, items[id]=qty, packages[id]=qty, pickup_method
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/booking_service.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $items = bersihkanJumlah($_GET['items'] ?? [], 'barang');
    $pkgs = bersihkanJumlah($_GET['packages'] ?? [], 'paket');
    $r = susunRincian($conn, $items, $pkgs, trim($_GET['start_date'] ?? ''), trim($_GET['end_date'] ?? ''), false, false);

    $fee = ($_GET['pickup_method'] ?? '') === 'doorstep_delivery' ? (float)DELIVERY_FEE : 0.0;
    echo json_encode([
        'success' => true,
        'ok'      => $r['ok'],
        'hari'    => $r['hari'],
        'subtotal' => $r['subtotal'],
        'delivery_fee' => $fee,
        'total'   => $r['subtotal'] + $fee,
        'barang'  => array_values(array_map(fn($c) => [
            'equipment_id' => $c['equipment_id'], 'nama' => $c['nama'], 'tersedia' => $c['tersedia'],
            'dibutuhkan' => $c['dibutuhkan'], 'cukup' => $c['cukup'],
        ], $r['cek'])),
    ]);
} catch (BookingException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('cek_stok: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada server.']);
}
