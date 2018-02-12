<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Component $component
 * @var string $message
 */
use Html2Text\Html2Text;

$internalErrors = libxml_use_internal_errors(true);
echo Html2Text::convert($this->render('html', [
    'component' => $component,
    'message' => $message,
]));
libxml_use_internal_errors($internalErrors);
