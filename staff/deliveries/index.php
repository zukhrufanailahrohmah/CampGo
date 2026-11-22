<?php
require_once __DIR__.'/../../includes/inventory_service.php';requireRole('staff');$back='staff/deliveries/index.php';
if($_SERVER['REQUEST_METHOD']==='POST'){campgoPost($back);campgoBack($back,function()use($conn){
 $booking=campgoId($_POST['booking_id']??0);$action=$_POST['action']??'';
 return campgoTransact($conn,function()use($conn,$booking,$action){
  $b=dbFetchOne($conn,'SELECT * FROM bookings WHERE booking_id=? FOR UPDATE','i',[$booking]);if(!$b || !in_array($b['booking_status'],['confirmed','ongoing'],true))campgoFail('Booking tidak siap diserahkan.');
  $d=dbFetchOne($conn,'SELECT * FROM deliveries WHERE booking_id=? FOR UPDATE','i',[$booking]);
  if(!$d){dbExecute($conn,'INSERT INTO deliveries (booking_id,fulfillment_method,delivery_address) VALUES (?,?,?)','iss',[$booking,$b['pickup_method'],$b['delivery_address']]);$d=dbFetchOne($conn,'SELECT * FROM deliveries WHERE booking_id=? FOR UPDATE','i',[$booking]);}
  if($action==='schedule'){
   $date=trim($_POST['schedule']??'');if(!$date || !strtotime($date))campgoFail('Jadwal tidak valid.');
   dbExecute($conn,"UPDATE deliveries SET delivery_schedule=?,delivery_status='scheduled',handled_by=?,operational_notes=? WHERE booking_id=?",'sisi',[str_replace('T',' ',$date),currentUserId(),campgoText($_POST['notes']??'',1000,false),$booking]);
  }elseif($action==='on_the_way'){
   if($d['fulfillment_method']!=='doorstep_delivery' || $d['delivery_status']!=='scheduled')campgoFail('Status pengantaran tidak sesuai.');
   dbExecute($conn,"UPDATE deliveries SET delivery_status='on_the_way',handled_by=? WHERE booking_id=?",'ii',[currentUserId(),$booking]);
  }elseif($action==='handover'){
   if($d['delivery_status']!=='scheduled' && $d['delivery_status']!=='on_the_way')campgoFail('Jadwal serah terima harus ditetapkan dahulu.');
   $details=dbFetchAll($conn,'SELECT * FROM booking_details WHERE booking_id=?','i',[$booking]);if(!$details)campgoFail('Tidak ada detail barang.');
   $quantities=$_POST['quantity']??[];$conds=$_POST['condition']??[];
   foreach($details as $it){$id=(int)$it['booking_detail_id'];$qty=campgoQty($quantities[$id]??null,true);if($qty!==(int)$it['quantity'])campgoFail('Jumlah serah terima harus sesuai jumlah booking.');$cond=campgoEnum($conds[$id]??'', ['excellent','good','fair']);
    // unique check by query, no unique key on booking_detail_id
    $existing=dbFetchOne($conn,'SELECT handover_item_id FROM handover_items WHERE booking_detail_id=?','i',[$id]);
    if($existing)dbExecute($conn,'UPDATE handover_items SET quantity_handed_over=?,condition_at_handover=?,checklist_complete=1,checked_by=?,checked_at=NOW() WHERE handover_item_id=?','isii',[$qty,$cond,currentUserId(),$existing['handover_item_id']]);
    else dbExecute($conn,'INSERT INTO handover_items (booking_detail_id,quantity_handed_over,condition_at_handover,checklist_complete,checked_by,checked_at) VALUES (?,?,?,1,?,NOW())','iisi',[$id,$qty,$cond,currentUserId()]);
   }
   dbExecute($conn,"UPDATE deliveries SET delivery_status=?,handover_at=NOW(),identity_received=1,identity_type=?,identity_received_at=NOW(),handled_by=?,handover_notes=? WHERE booking_id=?",'ssisi',[$d['fulfillment_method']==='pickup_at_store'?'picked_up':'delivered',campgoText($_POST['identity_type']??'',60),currentUserId(),campgoText($_POST['notes']??'',1000,false),$booking]);
   dbExecute($conn,"UPDATE bookings SET booking_status='ongoing' WHERE booking_id=?",'i',[$booking]);
  }else campgoFail('Aksi tidak tersedia.');
  campgoLog($conn,$action,'deliveries',(int)$d['delivery_id']);return 'Operasional serah terima diperbarui.';
 });
});}
$rows=dbFetchAll($conn,"SELECT b.*,d.delivery_status,d.delivery_schedule,d.identity_received FROM bookings b LEFT JOIN deliveries d ON d.booking_id=b.booking_id WHERE b.booking_status IN ('confirmed','ongoing') ORDER BY b.start_date DESC LIMIT 100");
$selectedId=filter_input(INPUT_GET,'booking_id',FILTER_VALIDATE_INT) ?: 0;
$selected=null;
foreach($rows as $candidate){if((int)$candidate['booking_id']===$selectedId){$selected=$candidate;break;}}
$panelTitle='Antar Jemput & Serah Terima';$activeMenu='antar-jemput';$extraCss=['app-pages.css'];include __DIR__.'/../../includes/panel_header.php';?>
<div class="page-wrap"><h1>Antar Jemput & Serah Terima</h1><?=flash_render()?>
<?php if(!$selected):?>
<div class="card"><h2>Daftar Pengantaran dan Serah Terima</h2><div class="table-scroll"><table class="tbl"><thead><tr><th>Kode Booking</th><th>Customer</th><th>Metode</th><th>Tanggal Sewa</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($rows as $b):?><tr><td><?=h($b['booking_code'])?></td><td><?=h($b['customer_name'])?></td><td><?=h(label_status('pickup',$b['pickup_method']))?></td><td><?=tgl($b['start_date'])?> – <?=tgl($b['end_date'])?></td><td><?=h($b['delivery_status']??'Belum dijadwalkan')?></td><td><a href="?booking_id=<?=(int)$b['booking_id']?>">Detail</a></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="6">Belum ada booking yang siap diserahkan.</td></tr><?php endif;?></tbody></table></div></div>
<?php endif;?>
<?php if($selected):$b=$selected;$details=dbFetchAll($conn,'SELECT bd.*,e.equipment_name FROM booking_details bd JOIN equipment e ON e.equipment_id=bd.equipment_id WHERE bd.booking_id=?','i',[$b['booking_id']]);?>
<div class="card"><h2>Detail: <?=h($b['booking_code'])?> – <?=h($b['customer_name'])?></h2><p><?=h(label_status('pickup',$b['pickup_method']))?> | <?=h($b['delivery_status']??'Belum dijadwalkan')?> | <?=tgl($b['start_date'])?> – <?=tgl($b['end_date'])?></p><p>Alamat: <?=h($b['delivery_address']??'-')?></p>
<?php if($b['booking_status']==='confirmed'):?>
<form method="post"><?=csrf_field()?><input type="hidden" name="booking_id" value="<?=(int)$b['booking_id']?>"><label>Jadwal serah terima</label><input type="datetime-local" name="schedule" required><input name="notes" placeholder="Catatan operasional"><button class="btn" name="action" value="schedule">Simpan jadwal</button></form>
<?php if($b['pickup_method']==='doorstep_delivery' && $b['delivery_status']==='scheduled'):?><form method="post"><?=csrf_field()?><input type="hidden" name="booking_id" value="<?=(int)$b['booking_id']?>"><button class="btn" name="action" value="on_the_way">Mulai pengantaran</button></form><?php endif;?>
<?php if(in_array($b['delivery_status'],['scheduled','on_the_way'],true)):?><form method="post"><?=csrf_field()?><input type="hidden" name="booking_id" value="<?=(int)$b['booking_id']?>"><h3>Checklist Penyerahan</h3><?php foreach($details as $it):?><div class="form-row"><label><?=h($it['equipment_name'])?> (jumlah booking <?=(int)$it['quantity']?>)</label><input type="number" required min="0" max="<?=(int)$it['quantity']?>" value="<?=(int)$it['quantity']?>" name="quantity[<?=(int)$it['booking_detail_id']?>]"><select name="condition[<?=(int)$it['booking_detail_id']?>]"><option value="excellent">Sangat baik</option><option value="good" selected>Baik</option><option value="fair">Cukup</option></select></div><?php endforeach;?><div class="form-row"><label>Jenis kartu identitas jaminan (jangan unggah foto/nomor identitas)</label><input name="identity_type" maxlength="60" required placeholder="Contoh: KTP"></div><input name="notes" placeholder="Catatan serah terima"><button class="btn" name="action" value="handover">Konfirmasi serah terima</button></form><?php endif;?>
<?php else:?><p>Barang sudah diserahkan. Jaminan identitas: <?=$b['identity_received']?'diterima':'belum tercatat'?>.</p><?php endif;?></div>
<p><a href="index.php">&larr; Kembali ke Daftar Pengantaran dan Serah Terima</a></p>
<?php endif;?></div><?php include __DIR__.'/../../includes/panel_footer.php';?>
