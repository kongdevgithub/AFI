<?php

use app\components\quotes\components\BaseComponentQuote;
use app\models\ProductType;
use app\models\ProductTypeToComponent;
use app\components\ReturnUrl;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var View $this
 * @var ProductType $productType
 * @var ProductTypeToComponent $productTypeToComponent
 */

/** @var BaseComponentQuote $componentQuote */
$quoteClass = $productTypeToComponent->getQuoteClass();
$componentQuote = $quoteClass ? new $quoteClass : false;
?>
<div class="pull-right">
    <?= Html::a('<i class="fa fa-pencil"></i>', ['/product-type-to-component/update', 'id' => $productTypeToComponent->id, 'ru' => ReturnUrl::getToken()]) ?>
    <?= Html::a('<i class="fa fa-trash"></i>', ['/product-type-to-component/delete', 'id' => $productTypeToComponent->id, 'ru' => ReturnUrl::getToken()], [
        'data-confirm' => Yii::t('app', 'Are you sure?'),
        'data-method' => 'post',
    ]) ?>
    <?= Html::a('<i class="fa fa-arrows sortable-handle sortable-handle-option"></i>') ?>
</div>

<strong><?= ($productTypeToComponent->describes_item ? '*' : '') . $productTypeToComponent->component->name ?></strong>

<span class="label label-default"><?= $productTypeToComponent->component->code ?></span>

<span class="label label-default">x<?= $productTypeToComponent->quantity + 0 ?></span>

<?php if ($componentQuote) { ?>
    <span class="label label-info"><?= $componentQuote->getQuoteLabel($productTypeToComponent->component, null) ?></span>
<?php } ?>
