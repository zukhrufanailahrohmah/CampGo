<?php
// =================================
// CAMPGO - DASHBOARD STAFF
// Frontend: Nisa (Frontend 2)
// =================================

require_once __DIR__ . '/../includes/panel_functions.php';

requireRole('staff'); // admin otomatis boleh

$panelTitle = 'Dashboard Staff';
$activeMenu = 'dashboard';

include __DIR__ . '/../includes/panel_header.php';
?>

<p>Selamat datang di panel staff CampGo!</p>

<?php include __DIR__ . '/../includes/panel_footer.php'; ?>