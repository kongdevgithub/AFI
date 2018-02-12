<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Product $product
 * @var \app\models\Log $log
 * @var string $url
 */
use Html2Text\Html2Text;

$internalErrors = libxml_use_internal_errors(true);
echo Html2Text::convert($this->render('html', [
    'product' => $product,
    'log' => $log,
    'url' => $url,
]));
libxml_use_internal_errors($internalErrors);
