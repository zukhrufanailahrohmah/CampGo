<?php
require_once __DIR__.'/../../includes/inventory_service.php';requireRole('staff');$back='staff/returns/index.php';
if($_SERVER['REQUEST_METHOD']==='POST'){campgoPost($back);campgoBack($back,function()use($conn){
 $bid=campgoId($_POST['booking_id']??0);$action=$_POST['action']??'';
 return campgoTransact($conn,function()use($conn,$bid,$action){
  $b=dbFetchOne($conn,'SELECT * FROM bookings WHERE booking_id=? FOR UPDATE','i',[$bid]);if(!$b || $b['booking_status']!=='ongoing')campgoFail('Booking tidak dalam masa sewa.');
  $d=dbFetchOne($conn,'SELECT * FROM deliveries WHERE booking_id=? FOR UPDATE','i',[$bid]);if(!$d || !$d['identity_received'])campgoFail('Serah terima dan jaminan belum tercatat.');
  if($action==='pickup'){
   $method=campgoEnum($_POST['method']??'',['customer_returns_to_store','staff_pickup']);
   $schedule=trim($_POST['schedule']??'');if(!strtotime($schedule))campgoFail('Jadwal tidak valid.');
   $address=campgoText($_POST['address']??'',1000,$method==='staff_pickup');
   dbExecute($conn,"UPDATE deliveries SET return_method=?,return_pickup_address=?,return_pickup_schedule=?,return_pickup_status='scheduled',return_handled_by=? WHERE booking_id=?",'sssii',[$method,$address,str_replace('T',' ',$schedule),currentUserId(),$bid]);
  }elseif($action==='complete'){
   $details=dbFetchAll($conn,'SELECT equipment_id,SUM(quantity) qty FROM booking_details WHERE booking_id=? GROUP BY equipment_id','i',[$bid]);if(!$details)campgoFail('Detail barang kosong.');
   $returned=$_POST['good']??[];$damaged=$_POST['damaged']??[];$lost=$_POST['lost']??[];
   $condition='good';$notes=campgoText($_POST['notes']??'',2000,false);
   $stockChanges=[];
   $ret=dbFetchOne($conn,'SELECT * FROM returns WHERE booking_id=? FOR UPDATE','i',[$bid]);
   if(!$ret){$rid=dbInsert($conn,"INSERT INTO returns (booking_id,received_by,return_date,return_status) VALUES (?,?,NOW(),'inspection')",'ii',[$bid,currentUserId()]);}
   else {if($ret['return_status']==='completed')campgoFail('Sudah diproses.');$rid=(int)$ret['return_id'];}
   foreach($details as $it){$id=(int)$it['equipment_id'];$g=campgoQty($returned[$id]??0,true);$dmg=campgoQty($damaged[$id]??0,true);$l=campgoQty($lost[$id]??0,true);
    if($g+$dmg+$l!==(int)$it['qty'])campgoFail('Total kondisi barang harus sesuai jumlah dipinjam.');
    if($l)$condition='lost';
    elseif($dmg && $condition==='good')$condition='damaged';
    // Stok diubah setelah semua detail berhasil divalidasi.
    if($dmg || $l)$stockChanges[$id]=['damaged'=>$dmg,'lost'=>$l];
    $existing=dbFetchOne($conn,'SELECT return_detail_id FROM return_details WHERE return_id=? AND equipment_id=?','ii',[$rid,$id]);
    if($existing)dbExecute($conn,'UPDATE return_details SET quantity_returned=?,quantity_good=?,quantity_damaged=?,quantity_lost=?,checklist_complete=1,condition_notes=? WHERE return_detail_id=?','iiiisi',[$g+$dmg,$g,$dmg,$l,$notes,$existing['return_detail_id']]);
    else dbExecute($conn,'INSERT INTO return_details (return_id,equipment_id,quantity_returned,quantity_good,quantity_damaged,quantity_lost,checklist_complete,condition_notes) VALUES (?,?,?,?,?,?,1,?)','iiiiiis',[$rid,$id,$g+$dmg,$g,$dmg,$l,$notes]);
   }
   // Sinkronisasi inventaris atas barang rusak/hilang, dalam transaksi pengembalian yang sama.
   // Booking yang sudah completed ditolak di awal sehingga stok tidak dapat disesuaikan dua kali.
   ksort($stockChanges, SORT_NUMERIC); // urutan lock konsisten
   foreach($stockChanges as $equipmentId=>$change){
    $eq=dbFetchOne($conn,'SELECT equipment_id,stock_total,stock_unavailable FROM equipment WHERE equipment_id=? FOR UPDATE','i',[$equipmentId]);
    if(!$eq)campgoFail('Perlengkapan pengembalian tidak ditemukan.');
    $oldTotal=(int)$eq['stock_total'];$oldUnavailable=(int)$eq['stock_unavailable'];
    $newTotal=$oldTotal-$change['lost'];
    $newUnavailable=$oldUnavailable+$change['damaged'];
    if($newTotal<0 || $newUnavailable>$newTotal){
     campgoFail('Stok barang #'.$equipmentId.' tidak cukup untuk memproses barang rusak/hilang. Periksa inventaris terlebih dahulu.');
    }
    dbExecute($conn,'UPDATE equipment SET stock_total=?,stock_unavailable=? WHERE equipment_id=?',
      'iii',[$newTotal,$newUnavailable,$equipmentId]);
    $type=$change['lost'] && $change['damaged'] ? 'adjustment' : ($change['lost'] ? 'stock_removed' : 'marked_unavailable');
    $movementNotes='Pengembalian booking '.$b['booking_code'].'; rusak '.$change['damaged'].' unit, hilang '.$change['lost'].' unit. Return ID '.$rid;
    dbInsert($conn,'INSERT INTO stock_movements (equipment_id,movement_type,stock_total_before,stock_total_after,stock_unavailable_before,stock_unavailable_after,movement_notes,changed_by) VALUES (?,?,?,?,?,?,?,?)',
      'isiiiisi',[$equipmentId,$type,$oldTotal,$newTotal,$oldUnavailable,$newUnavailable,$movementNotes,currentUserId()]);
   }
   $late=max(0,(int)(new DateTimeImmutable($b['end_date']))->diff(new DateTimeImmutable('today'))->format('%r%a'));
   dbExecute($conn,"UPDATE returns SET return_status='completed',general_condition=?,late_days=?,inspection_notes=?,completed_at=NOW(),received_by=?,return_date=NOW() WHERE return_id=?",'sisii',[$condition,$late,$notes,currentUserId(),$rid]);
   dbExecute($conn,"UPDATE deliveries SET identity_returned_at=NOW(),return_pickup_status='completed',return_pickup_at=NOW() WHERE booking_id=?",'i',[$bid]);
   dbExecute($conn,"UPDATE bookings SET booking_status='completed' WHERE booking_id=?",'i',[$bid]);
  }else campgoFail('Aksi tidak tersedia.');
  campgoLog($conn,$action,'returns',$bid);return 'Pengembalian berhasil diperbarui.';
 });
});}
$rows=dbFetchAll($conn,"SELECT b.*,d.return_method,d.return_pickup_status,d.identity_received FROM bookings b LEFT JOIN deliveries d ON d.booking_id=b.booking_id WHERE b.booking_status='ongoing' ORDER BY b.end_date LIMIT 100");
$selectedId=filter_input(INPUT_GET,'booking_id',FILTER_VALIDATE_INT) ?: 0;
$selected=null;
foreach($rows as $candidate){if((int)$candidate['booking_id']===$selectedId){$selected=$candidate;break;}}
$panelTitle='Pengembalian';$activeMenu='pengembalian';$extraCss=['app-pages.css'];include __DIR__.'/../../includes/panel_header.php';?>
<div class="page-wrap"><h1>Pengembalian dan Pemeriksaan Barang</h1><?=flash_render()?>
<?php if(!$selected):?>
<div class="card"><h2>Daftar Pengembalian</h2><div class="table-scroll"><table class="tbl"><thead><tr><th>Kode Booking</th><th>Customer</th><th>Batas Kembali</th><th>Metode Kembali</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($rows as $b):?><tr><td><?=h($b['booking_code'])?></td><td><?=h($b['customer_name'])?></td><td><?=tgl($b['end_date'])?></td><td><?=h($b['return_method']??'Belum diatur')?></td><td><?=h($b['return_pickup_status']??'Belum dijadwalkan')?></td><td><a href="?booking_id=<?=(int)$b['booking_id']?>">Periksa</a></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="6">Tidak ada pengembalian aktif.</td></tr><?php endif;?></tbody></table></div></div>
<?php endif;?>
<?php if($selected):$b=$selected;$items=dbFetchAll($conn,'SELECT bd.equipment_id,e.equipment_name,SUM(bd.quantity) qty FROM booking_details bd JOIN equipment e ON e.equipment_id=bd.equipment_id WHERE bd.booking_id=? GROUP BY bd.equipment_id,e.equipment_name','i',[$b['booking_id']]);?>
<div class="card"><h2>Periksa: <?=h($b['booking_code'])?> – <?=h($b['customer_name'])?></h2><p>Batas kembali: <?=tgl($b['end_date'])?> | Pickup: <?=h($b['return_pickup_status']??'belum ada')?></p>
<form method="post"><?=csrf_field()?><input type="hidden" name="booking_id" value="<?=(int)$b['booking_id']?>"><div class="form-row"><label>Metode</label><select name="method"><option value="customer_returns_to_store">Customer kembali ke toko</option><option value="staff_pickup">Staff jemput</option></select></div><div class="form-row"><label>Jadwal</label><input type="datetime-local" name="schedule"></div><div class="form-row"><label>Alamat jemput (jika staf jemput)</label><input name="address" value="<?=h($b['delivery_address']??'')?>"></div><button class="btn" name="action" value="pickup">Atur pengembalian</button></form>
<form method="post"><?=csrf_field()?><input type="hidden" name="booking_id" value="<?=(int)$b['booking_id']?>"><h3>Checklist kondisi kembali</h3><div class="table-scroll"><table class="tbl"><tr><th>Barang</th><th>Baik</th><th>Rusak</th><th>Hilang</th></tr><?php foreach($items as $it):$id=(int)$it['equipment_id'];?><tr><td><?=h($it['equipment_name'])?> (<?=(int)$it['qty']?> unit)</td><td><input type="number" name="good[<?=$id?>]" min="0" max="<?=(int)$it['qty']?>" value="<?=(int)$it['qty']?>"></td><td><input type="number" name="damaged[<?=$id?>]" min="0" value="0"></td><td><input type="number" name="lost[<?=$id?>]" min="0" value="0"></td></tr><?php endforeach;?></table></div><div class="form-row"><label>Catatan pemeriksaan</label><textarea name="notes" placeholder="Detail kerusakan/kehilangan jika ada"></textarea></div><button class="btn" name="action" value="complete" onclick="return confirm('Barang sudah diperiksa dan kartu identitas dikembalikan?')">Selesaikan pengembalian</button></form></div>
<p><a href="index.php">&larr; Kembali ke Daftar Pengembalian</a></p>
<?php endif;?></div><?php include __DIR__.'/../../includes/panel_footer.php';?>
