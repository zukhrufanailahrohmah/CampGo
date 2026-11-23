<?php
require_once __DIR__.'/../includes/inventory_service.php';
requireRole($campgoRequiredRole ?? 'staff');
$module=(string)($campgoModule ?? ($_GET['module']??'equipment'));$configs=campgoCrudConfig();
if(!isset($configs[$module])){http_response_code(404);exit('Modul tidak ditemukan.');}
$config=$configs[$module];$pk=$config['pk'];$base=(($campgoRequiredRole ?? 'staff')==='admin' && $module==='articles' ? 'admin/articles/index.php' : ($campgoManagePath ?? 'staff/manage.php').'?module='.$module);
$editSeparator = str_contains($base, '?') ? '&' : '?';
// Harga dan inventaris boleh staff kelola, laporan/akun tetap khusus admin.
if($_SERVER['REQUEST_METHOD']==='POST'){
 campgoPost($base);
 campgoBack($base,function()use($conn,$module,$config,$pk){
  $action=$_POST['action']??'save';$id=isset($_POST['id']) && $_POST['id']!=='' ? (int)$_POST['id'] : 0;
  if($action==='delete'){
   $id=campgoId($id);dbExecute($conn,"DELETE FROM `$module` WHERE `$pk`=?",'i',[$id]);campgoLog($conn,'delete',$module,$id);return 'Data dihapus.';
  }
  $values=[];foreach($config['fields'] as $key=>$spec)$values[$key]=campgoCrudValue($key,$spec,$_POST[$key]??'');
  if($module==='articles'){$values['published_at']=$values['article_status']==='published'?date('Y-m-d H:i:s'):null;if(!$id)$values['author_id']=currentUserId();}
  if($module==='equipment' && !$id){
   $values['stock_total']=campgoQty($_POST['stock_total']??null,true);
   $values['stock_unavailable']=campgoQty($_POST['stock_unavailable']??null,true);
   if($values['stock_unavailable']>$values['stock_total'])campgoFail('Stok tidak tersedia tidak boleh melebihi stok total.');
  }
  if($module==='equipment' && $id){ // stok hanya boleh melalui histori
   $old=dbFetchOne($conn,'SELECT equipment_id FROM equipment WHERE equipment_id=?','i',[$id]);if(!$old)campgoFail('Barang tidak ditemukan.');
  }
  if(in_array($module,['equipment','packages','articles'],true)){
   $photo=campgoUpload($_FILES['photo']??null,$module==='packages'?'equipment':$module);
   if($photo)$values['image_path']=$photo;
  }
  $keys=array_keys($values);$params=array_values($values);
  $types=implode('',array_map(fn($v)=>is_int($v)?'i':(is_float($v)?'d':'s'),$params));
  if($id){$sets=implode(',',array_map(fn($k)=>"`$k`=?",$keys));dbExecute($conn,"UPDATE `$module` SET $sets WHERE `$pk`=?",$types.'i',[...$params,$id]);}
  elseif($module==='equipment'){
   // Pencatatan stok awal dilakukan atomik bersama pembuatan barang.
   return campgoTransact($conn,function()use($conn,$module,$keys,$params,$types,$values){
    $cols=implode(',',array_map(fn($k)=>"`$k`",$keys));$qm=implode(',',array_fill(0,count($keys),'?'));
    $newId=dbInsert($conn,"INSERT INTO `$module` ($cols) VALUES ($qm)",$types,$params);
    $total=(int)$values['stock_total'];$unavailable=(int)$values['stock_unavailable'];
    if($total>0 || $unavailable>0){
     dbInsert($conn,'INSERT INTO stock_movements (equipment_id,movement_type,stock_total_before,stock_total_after,stock_unavailable_before,stock_unavailable_after,movement_notes,changed_by) VALUES (?,?,?,?,?,?,?,?)',
      'iiiiiisi',[$newId,'stock_added',0,$total,0,$unavailable,'Stok awal saat menambahkan barang',currentUserId()]);
    }
    campgoLog($conn,'save',$module,$newId);
    return 'Data berhasil disimpan.';
   });
  }
  else{$cols=implode(',',array_map(fn($k)=>"`$k`",$keys));$qm=implode(',',array_fill(0,count($keys),'?'));$id=dbInsert($conn,"INSERT INTO `$module` ($cols) VALUES ($qm)",$types,$params);}
  campgoLog($conn,'save',$module,$id);return 'Data berhasil disimpan.';
 });
}
$edit=null;
if(isset($_GET['edit']))$edit=dbFetchOne($conn,"SELECT * FROM `$module` WHERE `$pk`=?",'i',[campgoId($_GET['edit'])]);
$rows=dbFetchAll($conn,"SELECT * FROM `$module` ORDER BY `$pk` DESC LIMIT 200");
// Ringkasan stok hanya untuk tabel Barang; semua modul lain tetap seperti semula.
$stockStart = (string)($_GET['stock_start'] ?? date('Y-m-d'));
$stockEnd = (string)($_GET['stock_end'] ?? $stockStart);
$stockSummary = [];
$stockError = '';
if ($module === 'equipment') {
 try {
  $stockSummary = campgoInventoryAvailability($conn, $rows, $stockStart, $stockEnd);
 } catch (DomainException $e) {
  $stockError = $e->getMessage();
 } catch (Throwable $e) {
  error_log('CampGo inventory availability: '.$e->getMessage());
  $stockError = 'Gagal mengambil ringkasan stok. Periksa koneksi database.';
 }
}
$categories=dbFetchAll($conn,'SELECT category_id,category_name FROM categories ORDER BY category_name');
$panelTitle='Kelola '.$config['name'];$activeMenu=in_array($module,['articles'],true)?'artikel':'inventaris';$extraCss=['app-pages.css'];
include __DIR__.'/../includes/panel_header.php';
?>
<div class="page-wrap"><h1><?=h($config['name'])?></h1><?=flash_render()?>
<?php if ($module !== 'articles'): ?>
<?php $inventoryActive = $module; include __DIR__ . '/../includes/inventory_navigation.php'; ?>
<?php endif; ?>
<?php
// Tampilan daftar terlebih dahulu; form hanya ketika diminta, tanpa mengubah proses POST.
$showCrudForm = $edit !== null || (($_GET['form'] ?? '') === 'add');
$addLabels = [
 'categories'=>'Tambah Kategori',
 'equipment'=>'Tambah Barang',
 'packages'=>'Tambah Paket',
 'contacts'=>'Tambah Kontak',
 'articles'=>'Tambah Artikel',
];
$backLabels = [
 'categories'=>'Kembali ke Daftar Kategori',
 'equipment'=>'Kembali ke Daftar Barang',
 'packages'=>'Kembali ke Daftar Paket',
 'contacts'=>'Kembali ke Daftar Kontak',
 'articles'=>'Kembali ke Daftar Artikel',
];
?>
<?php if (!$showCrudForm): ?>
<div style="margin: 14px 0 18px;">
<a class="btn" href="<?=h(url($base))?><?=h($editSeparator)?>form=add"><?=h($addLabels[$module] ?? 'Tambah Data')?></a>
</div>
<?php endif; ?>
<?php if ($showCrudForm): ?>
<div class="card"><h2><?= $edit?'Edit':'Tambah' ?> <?=h($config['name'])?></h2>
<form method="post" enctype="multipart/form-data"><?=csrf_field()?><input type="hidden" name="id" value="<?= (int)($edit[$pk]??0) ?>">
<?php foreach($config['fields'] as $key=>$spec): [$label,$type,$max,$required]=$spec;$value=$edit[$key]??''; ?>
<div class="form-row"><label for="f_<?=h($key)?>"><?=h($label)?></label>
<?php if($type==='textarea'):?><textarea id="f_<?=h($key)?>" name="<?=h($key)?>" rows="<?= $key==='content'?8:3 ?>" <?= $required?'required':'' ?>><?=h($value)?></textarea>
<?php elseif($type==='category'):?><select id="f_<?=h($key)?>" name="<?=h($key)?>" required><?php foreach($categories as $cat):?><option value="<?= (int)$cat['category_id']?>" <?= $value==$cat['category_id']?'selected':''?>><?=h($cat['category_name'])?></option><?php endforeach;?></select>
<?php elseif(str_starts_with($type,'enum:')):?><select id="f_<?=h($key)?>" name="<?=h($key)?>"><?php foreach(explode(',',substr($type,5)) as $choice):?><option value="<?=h($choice)?>" <?= (string)$value===$choice?'selected':''?>><?=h($choice)?></option><?php endforeach;?></select>
<?php else:?><input id="f_<?=h($key)?>" name="<?=h($key)?>" value="<?=h($value)?>" type="<?=in_array($type,['integer','amount'],true)?'number':'text'?>" <?= $type==='amount'?'step="0.01" min="0"':($type==='integer'?'step="1" min="0"':'maxlength="'.(int)$max.'"')?> <?= $required?'required':'' ?>><?php endif;?></div>
<?php endforeach;?>
<?php if($module==='equipment'): ?>
<?php if(!$edit): ?>
<div class="form-row"><label for="stock_total">Stok Total</label><input id="stock_total" type="number" name="stock_total" min="0" step="1" value="0" required></div>
<div class="form-row"><label for="stock_unavailable">Stok Tidak Tersedia</label><input id="stock_unavailable" type="number" name="stock_unavailable" min="0" step="1" value="0" required></div>
<?php endif; ?>
<?php endif; ?>
<?php if(in_array($module,['equipment','packages','articles'],true)):?><div class="form-row"><label>Foto (opsional JPG/PNG/WEBP, 2 MB)</label><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></div><?php endif;?>
<button class="btn" type="submit">Simpan</button> <button class="btn btn-light" type="reset">Reset</button></form></div>
<p style="margin: 16px 0;"><a href="<?=h(url($base))?>">← <?=h($backLabels[$module] ?? 'Kembali ke Daftar')?></a></p>
<?php else: ?>
<?php if ($module === 'equipment'): ?>
<div class="card">
  <form method="get" action="<?=h(url($campgoManagePath ?? 'staff/manage.php'))?>">
    <input type="hidden" name="module" value="equipment">
    <div class="form-row"><label for="stock_start">Tanggal Mulai</label>
      <input id="stock_start" name="stock_start" type="date" value="<?=h($stockStart)?>" required>
    </div>
    <div class="form-row"><label for="stock_end">Tanggal Selesai</label>
      <input id="stock_end" name="stock_end" type="date" value="<?=h($stockEnd)?>" required>
    </div>
    <button class="btn" type="submit">Cek Stok</button>
  </form>
  <p><small>Jumlah Sedang Disewa adalah jumlah maksimum unit yang terikat booking aktif dalam periode yang dipilih (termasuk barang dalam paket).</small></p>
  <?php if ($stockError !== ''): ?><p role="alert" style="color:#b91c1c"><?=h($stockError)?></p><?php endif; ?>
</div>
<div class="table-scroll"><table class="tbl">
<thead><tr><th>ID</th><th>Nama Barang</th><th>Stok Total</th><th>Rusak/Maintenance</th><th>Sedang Disewa</th><th>Sisa Tersedia</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach($rows as $r):
 $stock = $stockSummary[(int)$r['equipment_id']] ?? null;
?><tr>
 <td><?= (int)$r['equipment_id'] ?></td>
 <td><?=h($r['equipment_name'])?></td>
 <td><?= (int)$r['stock_total'] ?></td>
 <td><?= (int)$r['stock_unavailable'] ?></td>
 <td><?= $stock === null ? '—' : (int)$stock['sedang_disewa'] ?></td>
 <td><?= $stock === null ? '—' : (int)$stock['sisa_tersedia'] ?></td>
 <td><a href="<?=h(url($base))?><?=h($editSeparator)?>edit=<?=(int)$r[$pk]?>">Edit</a> <form method="post" style="display:inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')"><?=csrf_field()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$r[$pk]?>"><button type="submit">Hapus</button></form></td>
</tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="7">Belum ada data.</td></tr><?php endif;?>
</tbody></table></div>
<?php else: ?>
<div class="table-scroll"><table class="tbl"><tr><th>ID</th><th>Nama / judul</th><th>Keterangan</th><th>Aksi</th></tr>
<?php foreach($rows as $r):?><tr><td><?= (int)$r[$pk] ?></td><td><?=h($r['equipment_name']??$r['category_name']??$r['package_name']??$r['label']??$r['title']??'')?></td><td><?=h($r['item_status']??$r['package_status']??$r['article_status']??$r['contact_type']??$r['description']??'')?></td><td><a href="<?=h(url($base))?><?=h($editSeparator)?>edit=<?=(int)$r[$pk]?>">Edit</a> <form method="post" style="display:inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')"><?=csrf_field()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$r[$pk]?>"><button type="submit">Hapus</button></form></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="4">Belum ada data.</td></tr><?php endif;?></table></div>
<?php endif; ?>
<?php endif; // Tutup tampilan daftar; form tambah/edit berdiri sendiri. ?>
</div>
<?php include __DIR__.'/../includes/panel_footer.php'; ?>
