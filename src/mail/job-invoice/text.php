<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Job $job
 */
use Html2Text\Html2Text;

$internalErrors = libxml_use_internal_errors(true);
echo Html2Text::convert($this->render('html', [
    'job' => $job,
]));
libxml_use_internal_errors($internalErrors);
