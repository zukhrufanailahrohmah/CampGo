<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/booking_service.php';

function campgoFail(string $message): void { throw new DomainException($message); }
function campgoPost(string $back): void { require_post_csrf($back); }
function campgoId($value): int { $n = filter_var($value,FILTER_VALIDATE_INT); if ($n === false || $n < 1) campgoFail('ID tidak valid.'); return (int)$n; }
function campgoText($value, int $max=255, bool $required=true): string {
  $s=trim((string)$value);
  if (($required && $s==='') || panjangTeks($s)>$max) campgoFail('Teks wajib diisi dan maksimal '.$max.' karakter.');
  return $s;
}
function campgoAmount($value): float { if (!is_numeric($value) || $value<0 || $value>9999999999) campgoFail('Nominal tidak valid.'); return round((float)$value,2); }
function campgoQty($value, bool $zero=false): int { $n=filter_var($value,FILTER_VALIDATE_INT); if ($n===false || $n<($zero?0:1) || $n>1000000) campgoFail('Jumlah tidak valid.'); return (int)$n; }
function campgoEnum($v, array $allowed): string { if (!is_string($v) || !in_array($v,$allowed,true)) campgoFail('Pilihan tidak valid.'); return $v; }
function campgoDate($s): string { if (!is_string($s) || !parseTanggal($s)) campgoFail('Tanggal tidak valid.'); return $s; }
function campgoLog(mysqli $conn,string $action,string $table,int $id,string $notes=''): void { logActivity($conn,currentUserId(),$action,$table,$id,$notes); }
function campgoBack(string $target,callable $callback): void {
 try { $msg=$callback(); flash('success', $msg?:'Data berhasil disimpan.'); }
 catch (DomainException|BookingException $e) { flash('error',$e->getMessage()); }
 catch (Throwable $e) { error_log('CampGo Backend 1: '.$e->getMessage()); flash('error','Operasi gagal. Periksa data terkait atau hubungi pengembang.'); }
 redirect($target);
}
function campgoBookingNeeds(mysqli $conn,int $bookingId): array {
 $rows=dbFetchAll($conn,'SELECT equipment_id, SUM(quantity) qty FROM booking_details WHERE booking_id=? GROUP BY equipment_id','i',[$bookingId]);
 $out=[];foreach($rows as $r) $out[(int)$r['equipment_id']]=(int)$r['qty'];return $out;
}
function campgoCheckStock(mysqli $conn,int $bookingId,string $start,string $end): void {
 $needs=campgoBookingNeeds($conn,$bookingId);
 $stock=cekKetersediaan($conn,$needs,$start,$end,$bookingId,true);
 foreach($needs as $id=>$qty) if(!isset($stock[$id]) || ($stock[$id]['tersedia']<$qty || $stock[$id]['status']!=='available')) campgoFail('Stok tidak cukup untuk '.($stock[$id]['nama']??'barang #'.$id).' pada periode tersebut.');
}
function campgoTransact(mysqli $conn,callable $fn) {
 mysqli_begin_transaction($conn);
 try { $result=$fn(); mysqli_commit($conn);return $result; }
 catch(Throwable $e){mysqli_rollback($conn);throw $e;}
}
function campgoUpload(?array $file,string $folder): ?string {
 if(!$file || ($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)return null;
 if($file['error']!==UPLOAD_ERR_OK || $file['size']>2*1024*1024 || $file['size']<=0)campgoFail('Foto maksimal 2 MB.');
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
 $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;
 if(!$ext || !getimagesize($file['tmp_name'])) campgoFail('Foto harus JPG, PNG, atau WEBP.');
 $dir=dirname(__DIR__).'/uploads/'.$folder;
 if(!is_dir($dir) && !mkdir($dir,0755,true))campgoFail('Folder foto tidak tersedia.');
 $name=bin2hex(random_bytes(16)).'.'.$ext;
 if(!move_uploaded_file($file['tmp_name'],$dir.'/'.$name))campgoFail('Gagal menyimpan foto.');
 return 'uploads/'.$folder.'/'.$name;
}
function campgoCrudConfig(): array { return [
 'categories'=>['name'=>'Kategori','pk'=>'category_id','fields'=>['category_name'=>['Nama kategori','text',100,true],'description'=>['Deskripsi','textarea',2000,false]]],
 'equipment'=>['name'=>'Perlengkapan','pk'=>'equipment_id','fields'=>['category_id'=>['Kategori','category',0,true],'equipment_name'=>['Nama barang','text',150,true],'description'=>['Deskripsi','textarea',3000,false],'specifications'=>['Spesifikasi','textarea',3000,false],'price_per_day'=>['Harga per hari','amount',0,true],'item_condition'=>['Kondisi','enum:excellent,good,fair,maintenance',0,true],'item_status'=>['Status','enum:available,inactive',0,true]]],
 'packages'=>['name'=>'Paket toko','pk'=>'package_id','fields'=>['package_name'=>['Nama paket','text',150,true],'description'=>['Deskripsi','textarea',3000,false],'price_per_day'=>['Harga per hari','amount',0,true],'package_status'=>['Status','enum:active,inactive',0,true]]],
 'contacts'=>['name'=>'Kontak toko','pk'=>'contact_id','fields'=>['contact_type'=>['Jenis kontak','enum:whatsapp,instagram,tiktok,email,address,other',0,true],'label'=>['Label','text',100,true],'contact_value'=>['Isi kontak','textarea',1000,true],'is_active'=>['Aktif','enum:0,1',0,true],'display_order'=>['Urutan tampil','integer',0,true]]],
 'articles'=>['name'=>'Artikel','pk'=>'article_id','fields'=>['title'=>['Judul','text',200,true],'slug'=>['Slug URL','text',220,true],'excerpt'=>['Ringkasan','textarea',2000,false],'content'=>['Isi artikel','textarea',50000,true],'article_status'=>['Status','enum:draft,published',0,true]]],
 ];}
function campgoCrudValue(string $field,array $spec,$raw) {
 [$label,$type,$max,$required]=$spec;
 if($type==='category')return campgoId($raw);
 if($type==='amount')return campgoAmount($raw);
 if($type==='integer') { $v=filter_var($raw,FILTER_VALIDATE_INT);if($v===false || $v<0 || $v>10000) campgoFail('Urutan tidak valid.');return (int)$v; }
 if(str_starts_with($type,'enum:'))return campgoEnum((string)$raw,explode(',',substr($type,5)));
 return campgoText($raw,(int)$max,(bool)$required);
}

/**
 * Ringkasan ketersediaan inventaris pada periode tertentu.
 * Sumber alokasi: booking_details + bookings melalui cekKetersediaan() Backend 2.
 * Tidak mengubah stock_total, stock_unavailable, atau stock_movements.
 * "sedang_disewa" = puncak unit yang terikat booking aktif pada tanggal yang dipilih.
 */
function campgoInventoryAvailability(mysqli $conn, array $equipmentRows, string $startDate, string $endDate): array {
    $start = parseTanggal($startDate);
    $end = parseTanggal($endDate);
    if (!$start || !$end || $end < $start || $start->diff($end)->days > 30) {
        campgoFail('Periode stok tidak valid. Pilih rentang tanggal maksimal 31 hari.');
    }
    $needs = [];
    foreach ($equipmentRows as $row) {
        $needs[(int)$row['equipment_id']] = 1;
    }
    if (!$needs) return [];
    $result = cekKetersediaan($conn, $needs, $startDate, $endDate);
    $summary = [];
    foreach ($equipmentRows as $row) {
        $id = (int)$row['equipment_id'];
        $entry = $result[$id] ?? null;
        $summary[$id] = [
            'stock_total' => (int)$row['stock_total'],
            'stock_unavailable' => (int)$row['stock_unavailable'],
            'sedang_disewa' => (int)($entry['terpakai'] ?? 0),
            'sisa_tersedia' => $row['item_status'] === 'available' ? (int)($entry['tersedia'] ?? 0) : 0,
        ];
    }
    return $summary;
}
