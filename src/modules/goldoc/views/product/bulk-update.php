<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use cornernote\returnurl\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\form\BulkProductForm $model
 */
$this->title = Yii::t('goldoc', 'Product') . ': ' . Yii::t('goldoc', 'Bulk Update');
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Bulk Update');

$this->registerCss('.kv-file-zoom, .fileinput-cancel, .file-preview .fileinput-remove { display: none; }');

?>
<div class="product-bulk-update">

    <?php echo $this->render('_bulk-form', [
        'model' => $model,
    ]); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Products Selected'); ?></h3>
        </div>
        <div class="box-body">
            <?= Html::ul(ArrayHelper::map($model->getProducts(), 'id', 'title')) ?>
        </div>
    </div>

</div>
