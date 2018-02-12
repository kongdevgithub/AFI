<?php
/**
 * @var $this \yii\web\View
 */

$name = Yii::$app->name;
$baseUrl = Yii::$app->params['s3BucketUrl'] . '/img/favicon';
$version = 'PYEz09d3N0';
?>
<link rel="apple-touch-icon" sizes="180x180" href="<?= $baseUrl . '/apple-touch-icon.png?v=' . $version ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= $baseUrl . '/favicon-32x32.png?v=' . $version ?>">
<link rel="icon" type="image/png" sizes="194x194" href="<?= $baseUrl . '/favicon-194x194.png?v=' . $version ?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?= $baseUrl . '/android-chrome-192x192.png?v=' . $version ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?= $baseUrl . '/favicon-16x16.png?v=' . $version ?>">
<link rel="manifest" href="<?= $baseUrl . '/manifest.json?v=' . $version ?>">
<link rel="mask-icon" href="<?= $baseUrl . '/safari-pinned-tab.svg?v=' . $version ?>" color="#2d89ef">
<link rel="shortcut icon" href="<?= $baseUrl . '/favicon.ico?v=' . $version ?>">
<meta name="apple-mobile-web-app-title" content="<?= $name ?>">
<meta name="application-name" content="<?= $name ?>">
<meta name="msapplication-config" content="<?= $baseUrl . '/browserconfig.xml?v=' . $version ?>">
<meta name="theme-color" content="#ffffff">