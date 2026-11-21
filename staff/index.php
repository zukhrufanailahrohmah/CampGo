<?php
// =================================
// CAMPGO - DASHBOARD STAFF
// =================================

require_once __DIR__ . '/../includes/panel_functions.php';

requirePanelAccess(['staff', 'admin']);

$panelTitle = 'Dashboard Staff';
$activeMenu = 'dashboard';

include __DIR__ . '/../includes/panel_header.php';
?>

<p>Selamat datang di panel staff CampGo!</p>

<?php include __DIR__ . '/../includes/panel_footer.php'; ?>