<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Job $job
 * @var string $url
 */

$this->params['headerImage'] = Yii::$app->params['s3BucketUrl'] . '/img/mail/afi/header.jpg';
?>

<p>A job has been suspended! <?= $job->getTitle() ?>.</p>

<ul>
    <li>Account Terms: <?= $job->accountTerm->name ?></li>
    <li>Invoice Sent: <?= $job->invoice_sent ? 'yes' : 'no' ?></li>
</ul>

<?php if ($url) { ?>
    <p style="padding: 15px; background-color: #ECF8FF;">
        To view the job please
        <a href="<?= $url ?>" style="font-weight: bold; color: #2BA6CB;">Click here &raquo;</a>
    </p>
<?php } ?>

