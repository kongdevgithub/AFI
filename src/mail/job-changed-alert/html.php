<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Job $job
 * @var \app\models\Log $log
 * @var string $url
 */

use yii\helpers\Html;

$this->params['headerImage'] = Yii::$app->params['s3BucketUrl'] . '/img/mail/afi/header.jpg';
?>

<p>A job has been changed! <?= $job->getTitle() ?>.</p>

<?= Html::ul($log->getAuditTrails(), ['encode' => false]) ?>

<?php if ($url) { ?>
    <p style="padding: 15px; background-color: #ECF8FF;">
        To view the job please
        <a href="<?= $url ?>" style="font-weight: bold; color: #2BA6CB;">Click here &raquo;</a>
    </p>
<?php } ?>

