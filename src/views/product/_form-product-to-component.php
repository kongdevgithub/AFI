<?php

use app\components\fields\BaseField;
use app\components\quotes\components\BaseComponentQuote;
use app\models\Component;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\ProductForm $model
 * @var app\models\ProductToComponent $productToComponent
 * @var kartik\form\ActiveForm $form
 * @var string $key
 * @var string $itemKey
 * @var boolean $allowComponentChange
 * @var boolean $allowComponentRemove
 */
?>

<div class="table-cell">
    <?php
    $fields = [];
    if ($allowComponentChange || !$productToComponent->product_type_to_component_id) {
        $data = ArrayHelper::map(Component::find()->andWhere(['id' => $productToComponent->component_id])->orderBy(['name' => SORT_ASC])->all(), 'id', 'label');
        $fields[] = $form->field($productToComponent, 'component_id')->dropDownList($data, [
            'id' => "ProductToComponents_{$key}_component_id",
            'name' => "ProductToComponents[$key][component_id]",
            'class' => 'form-control ProductToComponent_component_id',
            'data-key' => $key,
            'data-item-key' => $itemKey,
            'prompt' => '',
        ]);
        $fields[] = $form->field($productToComponent, 'quantity')->textInput([
            'id' => "ProductToComponents_{$key}_quantity",
            'name' => "ProductToComponents[$key][quantity]",
        ]);
    } else {
        $fields[] = Html::activeHiddenInput($productToComponent, 'component_id', [
            'id' => "ProductToComponents_{$key}_component_id",
            'name' => "ProductToComponents[$key][component_id]",
        ]);
        $fields[] = Html::activeHiddenInput($productToComponent, 'quantity', [
            'id' => "ProductToComponents_{$key}_quantity",
            'name' => "ProductToComponents[$key][quantity]",
        ]);
    }
    $fields[] = Html::activeHiddenInput($productToComponent, 'product_type_to_component_id', [
        'id' => "ProductToComponents_{$key}_product_type_to_component_id",
        'name' => "ProductToComponents[$key][product_type_to_component_id]",
    ]);
    $fields[] = Html::activeHiddenInput($productToComponent, 'item_id', [
        'id' => "ProductToComponents_{$key}_item_id",
        'name' => "ProductToComponents[$key][item_id]",
        'value' => $itemKey,
    ]);
    $fields[] = Html::activeHiddenInput($productToComponent, 'quote_class', [
        'id' => "ProductToComponents_{$key}_quote_class",
        'name' => "ProductToComponents[$key][quote_class]",
    ]);
    $fields[] = Html::activeHiddenInput($productToComponent, 'quote_quantity_factor', [
        'id' => "ProductToComponents_{$key}_quote_quantity_factor",
        'name' => "ProductToComponents[$key][quote_quantity_factor]",
    ]);
    ?>
    <?php
    if ($allowComponentRemove || !$productToComponent->product_type_to_component_id) {
        ?>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Component'); ?></h3>
                <div class="box-tools pull-right">
                    <?= Html::a('<i class="fa fa-minus"></i>', 'javascript:void(0);', [
                        'title' => Yii::t('app', 'Remove'),
                        'class' => 'product-remove-productToComponent-button btn btn-box-tool',
                    ]) ?>
                </div>
            </div>
            <div class="box-body">
                <?php
                echo implode('', $fields);
                ?>
            </div>
        </div>
        <?php
    } else {
        echo implode('', $fields);
    }
    \app\widgets\JavaScript::begin(['runOnAjax' => false]);
    ?>
    <script>
        productToComponent_k += 1;
    </script>
    <?php
    \app\widgets\JavaScript::end();
    ?>
</div>
