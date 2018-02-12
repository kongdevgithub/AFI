<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Contact $contact
 */
use Html2Text\Html2Text;

$internalErrors = libxml_use_internal_errors(true);
echo Html2Text::convert($this->render('html', [
    'contact' => $contact,
]));
libxml_use_internal_errors($internalErrors);
