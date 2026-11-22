<?php
require_once __DIR__.'/inventory_service.php';
function campgoProcess(mysqli $conn,string $module,int $id,string $action,array $input): string {
 return campgoTransact($conn,function()use($conn,$module,$id,$action,$input){
 if($module==='cancellations'){
  $row=dbFetchOne($conn,'SELECT c.*,b.booking_status FROM cancellations c JOIN bookings b ON b.booking_id=c.booking_id WHERE c.cancellation_id=? FOR UPDATE','i',[$id]);
  if(!$row || $row['cancellation_status']!=='pending')campgoFail('Pengajuan sudah diproses atau tidak ada.');
  if(!in_array($row['booking_status'],['pending_payment','waiting_verification','confirmed'],true))campgoFail('Booking tidak dapat dibatalkan pada status ini.');
  if($action==='approve'){
   $refund=campgoAmount($input['refund_amount']??0);$paid=dbFetchOne($conn,"SELECT COALESCE(SUM(amount),0) paid FROM payments WHERE booking_id=? AND payment_status='paid'",'i',[$row['booking_id']]);
   if($refund>(float)$paid['paid'])campgoFail('Refund melebihi dana pembayaran tercatat.');
   dbExecute($conn,"UPDATE cancellations SET cancellation_status='approved',processed_by=?,processed_at=NOW(),refund_amount=?,refund_status=?,staff_notes=? WHERE cancellation_id=?",'idssi',[currentUserId(),$refund,$refund>0?'pending':'not_required',campgoText($input['notes']??'',1000,false),$id]);
   dbExecute($conn,"UPDATE bookings SET booking_status='cancelled' WHERE booking_id=?",'i',[$row['booking_id']]);
  }elseif($action==='reject')dbExecute($conn,"UPDATE cancellations SET cancellation_status='rejected',processed_by=?,processed_at=NOW(),staff_notes=? WHERE cancellation_id=?",'isi',[currentUserId(),campgoText($input['notes']??'',1000,true),$id]);
  elseif($action==='refund_completed')campgoFail('Selesaikan refund dari pengajuan yang sudah disetujui.');
  else campgoFail('Aksi tidak tersedia.');
 }elseif($module==='extensions'){
  $row=dbFetchOne($conn,'SELECT x.*,b.start_date,b.end_date,b.booking_status FROM extensions x JOIN bookings b ON b.booking_id=x.booking_id WHERE x.extension_id=? FOR UPDATE','i',[$id]);
  if(!$row || $row['extension_status']!=='pending')campgoFail('Pengajuan tidak tersedia.');
  if(!in_array($row['booking_status'],['confirmed','ongoing'],true))campgoFail('Hanya booking terkonfirmasi/berjalan dapat diperpanjang.');
  if($action==='approve'){
   $end=campgoDate($row['requested_end_date']);if($end<=$row['end_date'] || $row['old_end_date']!==$row['end_date'])campgoFail('Tanggal lama tidak sesuai atau tanggal tambahan tidak valid.');
   $days=(int)parseTanggal($row['end_date'])->diff(parseTanggal($end))->days;
   if(hitungHariSewa($row['start_date'],$end)>MAX_RENTAL_DAYS)campgoFail('Perpanjangan melewati batas durasi.');
   campgoCheckStock($conn,(int)$row['booking_id'],$row['end_date'], $end);
   // Nilai biaya tambahan ditentukan dari harga harian historis tiap detail dan paket.
   $sum=dbFetchOne($conn,'SELECT COALESCE(SUM(CASE WHEN booking_package_id IS NULL THEN unit_price*quantity ELSE 0 END),0) amount FROM booking_details WHERE booking_id=?','i',[$row['booking_id']]);
   $pack=dbFetchOne($conn,'SELECT COALESCE(SUM(unit_price*quantity),0) amount FROM booking_packages WHERE booking_id=?','i',[$row['booking_id']]);
   $extra=($sum['amount']+$pack['amount'])*$days;
   dbExecute($conn,"UPDATE extensions SET extension_status='approved',approved_end_date=?,additional_cost=?,processed_by=?,processed_at=NOW(),staff_notes=? WHERE extension_id=?",'sdisi',[$end,$extra,currentUserId(),campgoText($input['notes']??'',1000,false),$id]);
   // Alokasikan tanggal tambahan saat disetujui agar tidak ditabrak booking lain.
   // Pembayaran tambahan dicatat belum lunas, diverifikasi kemudian oleh Backend 2.
   dbExecute($conn,'UPDATE bookings SET end_date=?,total_amount=total_amount+? WHERE booking_id=?','sdi',[$end,$extra,$row['booking_id']]);
   $method=dbFetchOne($conn,"SELECT payment_method FROM payments WHERE booking_id=? AND payment_type='full_payment' ORDER BY payment_id DESC LIMIT 1",'i',[$row['booking_id']]);
   dbExecute($conn,"INSERT INTO payments (booking_id,extension_id,payment_method,payment_type,amount,payment_status) VALUES (?,?,?,'extension',?,'unpaid')",'iisd',[$row['booking_id'],$id,$method['payment_method']??'cash',$extra]);
  }elseif($action==='reject')dbExecute($conn,"UPDATE extensions SET extension_status='rejected',processed_by=?,processed_at=NOW(),staff_notes=? WHERE extension_id=?",'isi',[currentUserId(),campgoText($input['notes']??'',1000,true),$id]);
  else campgoFail('Aksi tidak tersedia.');
 }elseif($module==='reschedules'){
  $row=dbFetchOne($conn,'SELECT r.*,b.start_date,b.end_date,b.booking_status FROM reschedules r JOIN bookings b ON b.booking_id=r.booking_id WHERE r.reschedule_id=? FOR UPDATE','i',[$id]);
  if(!$row || $row['reschedule_status']!=='pending')campgoFail('Pengajuan tidak tersedia.');
  if(!in_array($row['booking_status'],['pending_payment','waiting_verification','confirmed'],true))campgoFail('Status booking tidak memungkinkan reschedule.');
  if($action==='approve'){
   $start=campgoDate($row['new_start_date']);$end=campgoDate($row['new_end_date']);validasiPeriode($start,$end);
   if(hitungHariSewa($start,$end)!==hitungHariSewa($row['start_date'],$row['end_date']))campgoFail('Reschedule tidak boleh mengubah durasi.');
   if($row['old_start_date']!==$row['start_date'] || $row['old_end_date']!==$row['end_date'])campgoFail('Jadwal booking sudah berubah.');
   campgoCheckStock($conn,(int)$row['booking_id'],$start,$end);
   dbExecute($conn,'UPDATE bookings SET start_date=?,end_date=? WHERE booking_id=?','ssi',[$start,$end,$row['booking_id']]);
   dbExecute($conn,"UPDATE reschedules SET reschedule_status='approved',processed_by=?,processed_at=NOW(),staff_notes=? WHERE reschedule_id=?",'isi',[currentUserId(),campgoText($input['notes']??'',1000,false),$id]);
  }elseif($action==='reject')dbExecute($conn,"UPDATE reschedules SET reschedule_status='rejected',processed_by=?,processed_at=NOW(),staff_notes=? WHERE reschedule_id=?",'isi',[currentUserId(),campgoText($input['notes']??'',1000,true),$id]);
  else campgoFail('Aksi tidak tersedia.');
 }else campgoFail('Modul tidak tersedia.');
 campgoLog($conn,$action,$module,$id);
 return 'Pengajuan berhasil diproses.';
 });
}
