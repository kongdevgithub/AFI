<?php

use app\components\fields\BaseField;
use app\components\fields\ComponentField;
use app\components\quotes\components\BaseComponentQuote;
use app\models\ProductType;
use app\models\ProductTypeToOption;
use yii\helpers\Html;
use yii\web\View;
use app\components\ReturnUrl;

/**
 * @var View $this
 * @var ProductType $productType
 * @var ProductTypeToOption $productTypeToOption
 */

/** @var BaseField $field */
$field = new $productTypeToOption->option->field_class;
?>

<div class="pull-right">
    <?= Html::a('<i class="fa fa-pencil"></i>', ['/product-type-to-option/update', 'id' => $productTypeToOption->id, 'ru' => ReturnUrl::getToken()]) ?>
    <?= Html::a('<i class="fa fa-trash"></i>', ['/product-type-to-option/delete', 'id' => $productTypeToOption->id, 'ru' => ReturnUrl::getToken()], ['data-confirm' => Yii::t('app', 'Are you sure?'),
        'data-method' => 'post',]) ?>
    <?= Html::a('<i class="fa fa-arrows sortable-handle sortable-handle-option"></i>') ?>
</div>

<?= $field->valuesProductType($productTypeToOption) ?>

<?php
if ($field instanceof ComponentField) {
    /** @var BaseComponentQuote $componentQuote */
    $quoteClass = $productTypeToOption->quote_class ? $productTypeToOption->quote_class : false;
    if ($quoteClass) {
        $componentQuote = $quoteClass ? new $quoteClass : false;
        ?><span class="label label-info"><?= $componentQuote->getQuoteLabel(null, null) ?></span><?php
    }
}
?>

