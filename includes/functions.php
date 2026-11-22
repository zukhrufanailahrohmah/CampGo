<?php
// =================================
// CAMPGO - FUNGSI BANTU BERSAMA
// =================================
require_once __DIR__ . '/session.php';

// ---------- Tampilan & navigasi ----------
function h($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

// Redirect ke path relatif aplikasi, contoh: redirect('customer/login.php')
function redirect(string $path)
{
    header('Location: ' . url($path));
    exit;
}

function rupiah($angka): string
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function tgl($tanggal): string
{
    return $tanggal ? date('d/m/Y', strtotime($tanggal)) : '-';
}

// ---------- Flash message & old input ----------
function flash(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function flash_render(): string
{
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $html .= '<div class="flash flash-' . h($f['type']) . '">' . h($f['msg']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

function set_old(array $data): void
{
    $_SESSION['old_input'] = $data;
}

function pull_old(): array
{
    $old = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);
    return $old;
}

// ---------- CSRF ----------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $t = $_POST['csrf_token'] ?? '';
    return is_string($t) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

// Dipakai di awal setiap handler proses_*.php
function require_post_csrf(string $backTo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect($backTo);
    }
    if (!csrf_valid()) {
        flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
        redirect($backTo);
    }
}

// ---------- Database (prepared statement, wajib sesuai Master Doc Bab VIII) ----------
// Semua fungsi melempar RuntimeException kalau query gagal.
function dbStmt(mysqli $conn, string $sql, string $types = '', array $params = []): mysqli_stmt
{
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new RuntimeException('Query gagal disiapkan: ' . mysqli_error($conn), mysqli_errno($conn));
    }
    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    if (!mysqli_stmt_execute($stmt)) {
        $msg = mysqli_stmt_error($stmt);
        $no  = mysqli_stmt_errno($stmt);
        mysqli_stmt_close($stmt);
        throw new RuntimeException($msg, $no);
    }
    return $stmt;
}

function dbFetchAll(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    $stmt = dbStmt($conn, $sql, $types, $params);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function dbFetchOne(mysqli $conn, string $sql, string $types = '', array $params = []): ?array
{
    $rows = dbFetchAll($conn, $sql, $types, $params);
    return $rows[0] ?? null;
}

// INSERT/UPDATE/DELETE. Mengembalikan jumlah baris terpengaruh.
function dbExecute(mysqli $conn, string $sql, string $types = '', array $params = []): int
{
    $stmt = dbStmt($conn, $sql, $types, $params);
    $n = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $n;
}

// INSERT dan kembalikan ID baru.
function dbInsert(mysqli $conn, string $sql, string $types = '', array $params = []): int
{
    $stmt = dbStmt($conn, $sql, $types, $params);
    $id = mysqli_stmt_insert_id($stmt);
    mysqli_stmt_close($stmt);
    return (int)$id;
}

// "IN (?,?,?)" + string tipe untuk bind
function dbIn(array $ids): array
{
    return [implode(',', array_fill(0, count($ids), '?')), str_repeat('i', count($ids))];
}

// ---------- Catatan aktivitas Staff/Admin (tabel activity_logs) ----------
function logActivity(mysqli $conn, ?int $userId, string $action, ?string $table = null, ?int $targetId = null, ?string $desc = null): void
{
    try {
        dbExecute(
            $conn,
            'INSERT INTO activity_logs (user_id, action, target_table, target_id, description, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)',
            'ississ',
            [$userId, $action, $table, $targetId, $desc, $_SERVER['REMOTE_ADDR'] ?? null]
        );
    } catch (Throwable $e) {
        error_log('logActivity gagal: ' . $e->getMessage()); // log gagal tidak boleh menggagalkan transaksi utama
    }
}

// ---------- Label status (Bahasa Indonesia) ----------
function label_status(string $jenis, ?string $nilai): string
{
    static $map = [
        'booking' => [
            'pending_payment' => 'Menunggu pembayaran', 'waiting_verification' => 'Menunggu verifikasi',
            'confirmed' => 'Terkonfirmasi', 'ongoing' => 'Sedang disewa', 'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan', 'rejected' => 'Ditolak',
        ],
        'payment' => [
            'unpaid' => 'Belum dibayar', 'waiting_verification' => 'Menunggu verifikasi',
            'paid' => 'Lunas', 'rejected' => 'Ditolak', 'refunded' => 'Dikembalikan',
        ],
        'pickup' => ['pickup_at_store' => 'Ambil di toko', 'doorstep_delivery' => 'Diantar staf CampGo'],
        'method' => ['transfer' => 'Transfer bank', 'cash' => 'Tunai'],
    ];
    return $map[$jenis][$nilai ?? ''] ?? ($nilai ?? '-');
}

function badge(string $jenis, ?string $nilai): string
{
    return '<span class="badge badge-' . h($nilai ?? '') . '">' . h(label_status($jenis, $nilai)) . '</span>';
}

// Panjang teks dalam karakter (aman untuk huruf non-ASCII, tidak wajib ekstensi mbstring)
function panjangTeks(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : (int)preg_match_all('/./us', $s);
}
