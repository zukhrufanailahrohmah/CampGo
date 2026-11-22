<?php
// Artikel juga tersedia pada panel Admin, dengan handler CRUD yang sama.
$campgoRequiredRole = 'admin';
$campgoModule = 'articles';
$campgoManagePath = 'admin/equipment/index.php';
$campgoPackagePath = 'admin/equipment/packages.php';
$campgoStockPath = 'admin/equipment/stock.php';
require __DIR__ . '/../../staff/manage.php';
