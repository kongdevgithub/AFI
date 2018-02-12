<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Job $job
 * @var \app\models\Log $log
 * @var string $url
 */
use Html2Text\Html2Text;

$internalErrors = libxml_use_internal_errors(true);
echo Html2Text::convert($this->render('html', [
    'job' => $job,
    'log' => $log,
    'url' => $url,
]));
libxml_use_internal_errors($internalErrors);
