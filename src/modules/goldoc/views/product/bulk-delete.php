<?php

use app\modules\goldoc\models\Product;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use cornernote\returnurl\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var array $ids
 */
$this->title = Yii::t('goldoc', 'Product') . ': ' . Yii::t('goldoc', 'Bulk Delete');
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Bulk Delete');
?>
<div class="product-bulk-delete">

    <?php $form = ActiveForm::begin([
        'id' => 'Product',
        //'type' => ActiveForm::TYPE_HORIZONTAL,
    ]); ?>

    <?php
    foreach ($ids as $id) {
        echo Html::hiddenInput('ids[]', $id);
    }
    echo Html::hiddenInput('confirm', 1);
    echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());

    $items = [];
    foreach ($ids as $id) {
        $product = Product::findOne($id);
        $items[] = Html::a($product->id, ['product/view', 'id' => $product->id]) . ': ' . $product->getCode() . '-' . $product->getSizeCode();
    }
    echo Html::ul($items, ['encode' => false]);
    ?>

    <?php echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('goldoc', 'Confirm Delete'), [
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
