<?php
require_once __DIR__.'/../../includes/inventory_service.php';requireRole('admin');
$rows=dbFetchAll($conn,'SELECT l.*,u.full_name,u.role FROM activity_logs l LEFT JOIN users u ON u.user_id=l.user_id ORDER BY l.created_at DESC,l.log_id DESC LIMIT 200');
$panelTitle='Log Aktivitas';$activeMenu='log';$extraCss=['app-pages.css'];include __DIR__.'/../../includes/panel_header.php';?>
<div class="page-wrap"><h1>Log Aktivitas Staff/Admin</h1><div class="table-scroll"><table class="tbl"><tr><th>Waktu</th><th>Akun</th><th>Aksi</th><th>Objek</th><th>Keterangan</th></tr><?php foreach($rows as $r):?><tr><td><?=h($r['created_at'])?></td><td><?=h($r['full_name']??'Akun dihapus')?> (<?=h($r['role']??'-')?>)</td><td><?=h($r['action'])?></td><td><?=h($r['target_table'])?> #<?=h($r['target_id'])?></td><td><?=h($r['description'])?></td></tr><?php endforeach;?></table></div></div><?php include __DIR__.'/../../includes/panel_footer.php';?>
