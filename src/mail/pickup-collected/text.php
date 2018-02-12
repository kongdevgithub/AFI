<?php
/**
 * @var \yii\web\View $this
 * @var array $pickups
 */
use Html2Text\Html2Text;

$internalErrors = libxml_use_internal_errors(true);
echo Html2Text::convert($this->render('html', [
    'pickups' => $pickups,
]));
libxml_use_internal_errors($internalErrors);
