<?php

use app\models\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \kartik\form\ActiveForm $form
 * @var app\models\form\ProductBulkComponentsForm $model
 * @var app\models\Product $product
 * @var app\models\Item $item
 * @var app\models\ProductToOption $productToOption
 * @var string $key
 */


echo Html::hiddenInput("Items[$key][sort_order]", 0, [
    'id' => "Items_{$key}_sort_order",
]);
?>
<td>
    <?php
    $data = ArrayHelper::map(Component::find()->andWhere(['id' => $productToOption->valueDecoded])->all(), 'id', 'label');
    echo $form->field($productToOption, 'valueDecoded')->dropDownList($data, [
        'id' => "ProductToOptions_{$key}_valueDecoded_component",
        'name' => "ProductToOptions[$key][valueDecoded][component]",
    ])->label(false);
    ?>
</td>
<td>
    <?= $form->field($product, 'quantity')->textInput([
        'id' => "Products_{$key}_quantity",
        'name' => "Products[$key][quantity]",
    ])->label(false) ?>
</td>
<td>
    <?= $form->field($productToOption, 'quantity')->textInput([
        'id' => "ProductToOptions_{$key}_quantity",
        'name' => "ProductToOptions[$key][quantity]",
    ])->label(false) ?>
</td>
<td>
    <?= $form->field($product, 'details')->textarea([
        'id' => "Products_{$key}_details",
        'name' => "Products[$key][details]",
    ])->label(false) ?>
</td>
<td>
    <?= Html::a('Remove', 'javascript:void(0);', [
        'class' => 'bulk-components-remove-product-button btn btn-default btn-xs',
    ]) ?>
</td>
