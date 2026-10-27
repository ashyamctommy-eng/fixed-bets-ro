<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle ?? SITE_NAME) ?> | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <?php if (isset($extraCss)): foreach ((array)$extraCss as $css): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/<?= $css ?>">
    <?php endforeach; endif; ?>
    <link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/assets/favicon.svg">
</head>
<?php
$themeClass = getSiteSetting($pdo, 'dashboard_theme') ?? 'dark';
?>
<body class="theme-<?= e($themeClass) ?>">
<div class="app-wrapper">
