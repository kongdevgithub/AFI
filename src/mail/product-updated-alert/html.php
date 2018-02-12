<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Product $product
 * @var \app\models\Log $log
 * @var string $url
 */

use yii\helpers\Html;

$this->params['headerImage'] = Yii::$app->params['s3BucketUrl'] . '/img/mail/afi/header.jpg';
?>

<p>A product has been changed! <?= $product->getTitle() ?>.</p>

<?= Html::ul($log->getAuditTrails(), ['encode' => false]) ?>

<?php if ($url) { ?>
    <p style="padding: 15px; background-color: #ECF8FF;">
        To view the product please
        <a href="<?= $url ?>" style="font-weight: bold; color: #2BA6CB;">Click here &raquo;</a>
    </p>
<?php } ?>

