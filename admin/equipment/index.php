<?php
// Halaman inventaris Admin: gunakan logika CRUD bersama tanpa redirect ke URL Staff.
$campgoRequiredRole = 'admin';
$campgoManagePath = 'admin/equipment/index.php';
$campgoPackagePath = 'admin/equipment/packages.php';
$campgoStockPath = 'admin/equipment/stock.php';
require __DIR__ . '/../../staff/manage.php';
