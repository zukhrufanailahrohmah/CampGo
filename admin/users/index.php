<?php
// Tampilan akun Admin. Perubahan role, password, dan status tetap diproses oleh Backend 2.
require_once __DIR__.'/../../includes/auth.php';
requireRole('admin');
$rows=dbFetchAll($conn,'SELECT user_id,full_name,email,phone,role,account_status,created_at FROM users ORDER BY created_at DESC LIMIT 200');
$panelTitle='Manajemen Akun';$activeMenu='akun';$extraCss=['app-pages.css'];
include __DIR__.'/../../includes/panel_header.php';
?>
<div class="page-wrap"><h1>Daftar Akun CampGo</h1><p>Halaman ini menampilkan akun Customer, Staff, dan Admin. Penambahan, penonaktifan, dan perubahan role akan dihubungkan dengan modul akun Backend 2 agar autentikasi tidak dikerjakan ganda.</p>
<div class="table-scroll"><table class="tbl"><tr><th>ID</th><th>Nama</th><th>Email</th><th>Telepon</th><th>Role</th><th>Status</th><th>Dibuat</th></tr>
<?php foreach($rows as $r):?><tr><td><?=(int)$r['user_id']?></td><td><?=h($r['full_name'])?></td><td><?=h($r['email'])?></td><td><?=h($r['phone']??'-')?></td><td><?=h($r['role'])?></td><td><?=h($r['account_status'])?></td><td><?=h($r['created_at'])?></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="7">Belum ada akun.</td></tr><?php endif;?></table></div></div>
<?php include __DIR__.'/../../includes/panel_footer.php'; ?>
