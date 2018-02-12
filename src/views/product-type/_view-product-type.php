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
 */

echo Html::a('<i class="fa fa-arrows sortable-handle sortable-handle-child pull-right"></i>');
echo Html::a($productType->name . '<br>' . Html::img($productType->getImageSrc(), ['width' => '150', 'height' => '150']), [
    '/product-type/view',
    'id' => $productType->id,
    'ru' => ReturnUrl::getToken(),
]);

