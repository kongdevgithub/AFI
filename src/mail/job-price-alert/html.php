<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Job $job
 * @var string $url
 */

$this->params['headerImage'] = Yii::$app->params['s3BucketUrl'] . '/img/mail/afi/header.jpg';
?>

<p>A quote is within 30% of cost price! <?= $job->getTitle() ?>.</p>

<?php if ($url) { ?>
    <p style="padding: 15px; background-color: #ECF8FF;">
        To view the quote please
        <a href="<?= $url ?>" style="font-weight: bold; color: #2BA6CB;">Click here &raquo;</a>
    </p>
<?php } ?>

