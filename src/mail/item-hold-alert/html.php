<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Item $item
 * @var string $url
 */

$this->params['headerImage'] = Yii::$app->params['s3BucketUrl'] . '/img/mail/afi/header.jpg';
?>

<p>An item has been put on hold! <?= $item->getTitle() ?>.</p>

<?php if ($url) { ?>
    <p style="padding: 15px; background-color: #ECF8FF;">
        To view the item please
        <a href="<?= $url ?>" style="font-weight: bold; color: #2BA6CB;">Click here &raquo;</a>
    </p>
<?php } ?>

