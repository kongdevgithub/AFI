<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Job $job
 * @var string $message
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->params['headerImage'] = Yii::$app->params['s3BucketUrl'] . '/img/mail/afi/header.jpg';
?>

<p>A job could not be exported into dear - job_id <?= Html::a($job->vid, Url::to(['job/quote', 'id' => $job->id], 'https')) ?>.</p>

<p><strong>Error Message:</strong><br><?= $message; ?></p>





