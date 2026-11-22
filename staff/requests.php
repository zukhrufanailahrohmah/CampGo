<?php
require_once __DIR__.'/../includes/rental_operations_service.php';requireRole('staff');
$module=(string)($_GET['module']??'cancellations');
$config=[
 'cancellations'=>['name'=>'Pembatalan & Refund','pk'=>'cancellation_id','status'=>'cancellation_status','date'=>'created_at'],
 'extensions'=>['name'=>'Perpanjangan Sewa','pk'=>'extension_id','status'=>'extension_status','date'=>'created_at'],
 'reschedules'=>['name'=>'Perubahan Jadwal','pk'=>'reschedule_id','status'=>'reschedule_status','date'=>'created_at']
];
if(!isset($config[$module])){http_response_code(404);exit('Modul tidak dikenal.');}
$c=$config[$module];$back='staff/requests.php?module='.$module;
if($_SERVER['REQUEST_METHOD']==='POST') {campgoPost($back);campgoBack($back,function()use($conn,$module){
 $id=campgoId($_POST['id']??0);$action=$_POST['action']??'';
 if($module==='cancellations' && $action==='refund_completed'){
  if(($_SESSION['role']??'')!=='admin')campgoFail('Hanya admin dapat mencatat refund selesai.');
  return campgoTransact($conn,function()use($conn,$id){
   $r=dbFetchOne($conn,'SELECT * FROM cancellations WHERE cancellation_id=? FOR UPDATE','i',[$id]);
   if(!$r || $r['cancellation_status']!=='approved' || $r['refund_status']!=='pending')campgoFail('Refund tidak dalam status pending.');
   dbExecute($conn,"UPDATE cancellations SET refund_status='completed',refunded_at=NOW(),refund_notes=? WHERE cancellation_id=?",'si',[campgoText($_POST['notes']??'',1000,true),$id]);
   campgoLog($conn,'refund_completed','cancellations',$id);return 'Refund administratif tercatat selesai (tidak mentransfer dana).';
  });
 }
 return campgoProcess($conn,$module,$id,$action,$_POST);
});}
$rows=dbFetchAll($conn,"SELECT q.*,b.booking_code,b.booking_status,b.customer_name,b.start_date,b.end_date FROM `$module` q JOIN bookings b ON b.booking_id=q.booking_id ORDER BY q.created_at DESC LIMIT 200");
$panelTitle=$c['name'];$activeMenu=$module==='cancellations'?'pembatalan':($module==='extensions'?'perpanjangan':'reschedule');$extraCss=['app-pages.css'];include __DIR__.'/../includes/panel_header.php';?>
<div class="page-wrap"><h1><?=h($c['name'])?></h1><?=flash_render()?>
<div class="table-scroll"><table class="tbl"><tr><th>Booking</th><th>Customer</th><th>Detail pengajuan</th><th>Status</th><th>Aksi staff</th></tr>
<?php foreach($rows as $r):?><tr><td><a href="<?=h(url('staff/transactions/detail.php?code='.urlencode($r['booking_code'])))?>"><?=h($r['booking_code'])?></a><br><?=h($r['booking_status'])?></td><td><?=h($r['customer_name'])?></td>
<td><?php if($module==='cancellations'):?>Alasan: <?=h($r['reason'])?><br>Refund: <?=rupiah($r['refund_amount'])?> (<?=h($r['refund_status'])?>)
<?php elseif($module==='extensions'):?><?=tgl($r['old_end_date'])?> → <?=tgl($r['requested_end_date'])?><br>Biaya tambahan: <?=rupiah($r['additional_cost'])?>
<?php else:?><?=tgl($r['old_start_date'])?>–<?=tgl($r['old_end_date'])?> → <?=tgl($r['new_start_date'])?>–<?=tgl($r['new_end_date'])?><?php endif;?><br><span class="muted"><?=h($r['staff_notes']??'')?></span></td>
<td><?=h($r[$c['status']])?></td><td><?php if($r[$c['status']]==='pending'):?><form method="post"><?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$r[$c['pk']]?>"><label>Catatan</label><textarea name="notes"></textarea><?php if($module==='cancellations'):?><label>Refund (Rp; kebijakan ditentukan kelompok)</label><input type="number" step="0.01" min="0" value="0" name="refund_amount"><?php endif;?><button class="btn" name="action" value="approve">Setujui</button> <button class="btn btn-danger" name="action" value="reject">Tolak</button></form><?php elseif($module==='cancellations' && $r['cancellation_status']==='approved' && $r['refund_status']==='pending' && ($_SESSION['role']??'')==='admin'):?><form method="post"><?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$r[$c['pk']]?>"><textarea name="notes" required placeholder="Catatan refund administratif"></textarea><button class="btn" name="action" value="refund_completed">Tandai refund selesai</button></form><?php else:?>Sudah diproses<?php endif;?></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="5">Belum ada pengajuan.</td></tr><?php endif;?></table></div></div><?php include __DIR__.'/../includes/panel_footer.php';?>
