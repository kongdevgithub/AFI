<?php
/**
 * @var \yii\web\View $this
 * @var int $hub_spot_id
 * @var \app\models\Company $company
 * @var \app\models\Address $address
 * @var array $data
 */
use app\components\Helper;

$this->params['headerImage'] = Yii::$app->params['s3BucketUrl'] . '/img/mail/afi/header.jpg';
?>

<p>A company address could not be imported into console - hub_spot_id <?= $hub_spot_id ?>.</p>

<p>Errors:</p>
<pre><?= Helper::getErrorString($address); ?></pre>

<p>Data:</p>
<pre><?= \yii\helpers\VarDumper::export($data); ?></pre>


