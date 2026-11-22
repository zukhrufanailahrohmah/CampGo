<?php
require_once __DIR__.'/../includes/inventory_service.php';requireRole($campgoRequiredRole ?? 'staff');$back=$campgoCurrentPath ?? 'staff/stock.php';
if($_SERVER['REQUEST_METHOD']==='POST'){campgoPost($back);campgoBack($back,function()use($conn){
 $id=campgoId($_POST['equipment_id']??0);$total=campgoQty($_POST['stock_total']??null,true);$unavailable=campgoQty($_POST['stock_unavailable']??null,true);if($unavailable>$total)campgoFail('Stok tidak tersedia tidak boleh melebihi stok total.');
 $notes=campgoText($_POST['notes']??'',1000,true);
 return campgoTransact($conn,function()use($conn,$id,$total,$unavailable,$notes){
  $old=dbFetchOne($conn,'SELECT * FROM equipment WHERE equipment_id=? FOR UPDATE','i',[$id]);if(!$old)campgoFail('Barang tidak ditemukan.');
  $ot=(int)$old['stock_total'];$ou=(int)$old['stock_unavailable'];
  if($ot===$total && $ou===$unavailable)campgoFail('Stok tidak berubah.');
  // Jangan kurangi kapasitas di bawah puncak unit yang sudah dijanjikan untuk hari manapun.
  $reserved=dbFetchAll($conn,"SELECT b.start_date,b.end_date,SUM(bd.quantity) qty FROM booking_details bd JOIN bookings b ON b.booking_id=bd.booking_id WHERE bd.equipment_id=? AND b.booking_status IN ('pending_payment','waiting_verification','confirmed','ongoing') AND b.end_date>=CURRENT_DATE() GROUP BY b.booking_id,b.start_date,b.end_date",'i',[$id]);
  $points=[];
  foreach($reserved as $r){$a=max($r['start_date'],date('Y-m-d'));$points[$a]=($points[$a]??0)+(int)$r['qty'];$after=date('Y-m-d',strtotime($r['end_date'].' +1 day'));$points[$after]=($points[$after]??0)-(int)$r['qty'];}
  ksort($points);$running=0;$peak=0;foreach($points as $delta){$running+=$delta;$peak=max($peak,$running);}
  if($total-$unavailable<$peak)campgoFail('Stok efektif lebih kecil dari jumlah unit yang telah dipesan pada tanggal mendatang.');
  $type=$total>$ot?'stock_added':($total<$ot?'stock_removed':($unavailable>$ou?'marked_unavailable':'restored_available'));
  dbExecute($conn,'UPDATE equipment SET stock_total=?,stock_unavailable=? WHERE equipment_id=?','iii',[$total,$unavailable,$id]);
  dbInsert($conn,'INSERT INTO stock_movements (equipment_id,movement_type,stock_total_before,stock_total_after,stock_unavailable_before,stock_unavailable_after,movement_notes,changed_by) VALUES (?,?,?,?,?,?,?,?)','iiiiiisi',[$id,$type,$ot,$total,$ou,$unavailable,$notes,currentUserId()]);
  campgoLog($conn,'adjust_stock','equipment',$id,$notes);return 'Stok dan histori diperbarui.';
 });
});}
$items=dbFetchAll($conn,'SELECT * FROM equipment ORDER BY equipment_name');$movements=dbFetchAll($conn,'SELECT m.*,e.equipment_name,u.full_name FROM stock_movements m JOIN equipment e ON e.equipment_id=m.equipment_id LEFT JOIN users u ON u.user_id=m.changed_by ORDER BY m.created_at DESC,m.movement_id DESC LIMIT 100');
$panelTitle='Penyesuaian Stok';$activeMenu='inventaris';$extraCss=['app-pages.css'];include __DIR__.'/../includes/panel_header.php';?>
<div class="page-wrap"><h1>Penyesuaian Stok</h1><?=flash_render()?>
<?php $inventoryActive = 'stock'; include __DIR__ . '/../includes/inventory_navigation.php'; ?><p>Stok total tidak berkurang saat booking. Setiap perubahan manual disimpan dalam riwayat.</p>
<div class="card"><form method="post"><?=csrf_field()?><div class="form-row"><label>Barang</label><select name="equipment_id" id="eq" required><?php foreach($items as $i):?><option data-total="<?=(int)$i['stock_total']?>" data-unavailable="<?=(int)$i['stock_unavailable']?>" value="<?=(int)$i['equipment_id']?>"><?=h($i['equipment_name'])?> (total <?=(int)$i['stock_total']?>)</option><?php endforeach;?></select></div><div class="form-row"><label>Stok total baru</label><input type="number" id="total" name="stock_total" min="0" required></div><div class="form-row"><label>Rusak/Maintenance Baru</label><input type="number" id="unavailable" name="stock_unavailable" min="0" required></div><div class="form-row"><label>Alasan perubahan</label><textarea name="notes" required></textarea></div><button class="btn">Simpan penyesuaian</button></form></div>
<div class="table-scroll"><table class="tbl"><tr><th>Waktu</th><th>Barang</th><th>Jenis</th><th>Total</th><th>Rusak/Maintenance</th><th>Petugas</th><th>Alasan</th></tr><?php foreach($movements as $m):?><tr><td><?=h($m['created_at'])?></td><td><?=h($m['equipment_name'])?></td><td><?=h($m['movement_type'])?></td><td><?=(int)$m['stock_total_before']?> → <?=(int)$m['stock_total_after']?></td><td><?=(int)$m['stock_unavailable_before']?> → <?=(int)$m['stock_unavailable_after']?></td><td><?=h($m['full_name'])?></td><td><?=h($m['movement_notes'])?></td></tr><?php endforeach;?></table></div></div>
<script>const eq=document.getElementById('eq');function syncStock(){const o=eq?.selectedOptions[0];if(o){document.getElementById('total').value=o.dataset.total;document.getElementById('unavailable').value=o.dataset.unavailable;}}eq?.addEventListener('change',syncStock);syncStock();</script>
<?php include __DIR__.'/../includes/panel_footer.php';?>
