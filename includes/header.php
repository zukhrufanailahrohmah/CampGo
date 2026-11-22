<?php
require_once __DIR__ . '/functions.php'; // BASE_URL + session

// Judul halaman
$pageTitle = $pageTitle ?? 'CampGo - Rental Alat Camping';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- CSS Utama -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <?php foreach (($extraCss ?? []) as $css): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/<?= h($css) ?>">
    <?php endforeach; ?>
</head>
<body>
