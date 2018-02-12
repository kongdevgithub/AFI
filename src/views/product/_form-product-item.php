<?php

use app\components\quotes\components\BaseComponentQuote;
use app\components\quotes\items\BaseItemQuote;
use app\models\ItemType;
use app\models\ProductToComponent;
use app\models\ProductToOption;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\form\ProductForm $model
 * @var app\models\Item $item
 * @var kartik\form\ActiveForm $form
 * @var string $key
 * @var int $productToOption_k
 * @var int $productToComponent_k
 */

$select2Options = [
    'multiple' => false,
    'theme' => 'krajee',
    'placeholder' => '',
    'language' => 'en-US',
    'width' => '100%',
    'allowClear' => true,
    'minimumInputLength' => 1,
    'ajax' => [
        'url' => Url::to(['component/json-list']),
        'dataType' => 'json',
        'data' => new JsExpression('function(params) { return {q:params.term}; }')
    ],
];

?>

<div id="product-type-to-item-type-<?= $item->product_type_to_item_type_id ? $item->product_type_to_item_type_id : uniqid() ?>"
     class="col-md-4 product-item">
    <div class="box box-info box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $item->name ?></h3>
            <div class="box-tools pull-right">
                <?php if (!$item->splits) { ?>
                    <?= Html::a('<i class="fa fa-minus"></i> ' . Yii::t('app', 'Remove Item'), 'javascript:void(0);', [
                        'class' => 'product-remove-item-button btn btn-box-tool',
                    ]) ?>
                <?php } ?>
                <?php if (Yii::$app->user->can('custom-quoter')) { ?>
                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Add Option'), 'javascript:void(0);', [
                        'id' => 'product-new-productToOption-button-' . $key,
                        'data-key' => $key,
                        'class' => 'btn btn-box-tool',
                    ]) ?>
                <?php } ?>
                <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Add Component'), 'javascript:void(0);', [
                    'id' => 'product-new-productToComponent-button-' . $key,
                    'data-key' => $key,
                    'class' => 'btn btn-box-tool',
                ]) ?>
            </div>
        </div>
        <div class="box-body">
            <div class="table table-condensed">
                <div class="table-row">
                    <div class="table-cell" style="border: 0; padding: 0;">
                        <?php
                        echo $form->field($item, 'name')->textInput([
                            'id' => "Items_{$key}_name",
                            'name' => "Items[$key][name]",
                            'class' => 'item-name form-control',
                        ]);
                        echo Html::activeHiddenInput($item, 'product_type_to_item_type_id', [
                            'id' => "Items_{$key}_product_type_to_item_type_id",
                            'name' => "Items[$key][product_type_to_item_type_id]",
                        ]);
                        if ($item->product_type_to_item_type_id || !$item->isNewRecord) {
                            echo Html::activeHiddenInput($item, 'item_type_id', [
                                'id' => "Items_{$key}_item_type_id",
                                'name' => "Items[$key][item_type_id]",
                            ]);
                        } else {
                            echo $form->field($item, 'item_type_id')->dropDownList(ArrayHelper::map(ItemType::find()->all(), 'id', 'name'), [
                                'id' => "Items_{$key}_item_type_id",
                                'name' => "Items[$key][item_type_id]",
                                'prompt' => '',
                            ]);
                        }
                        if (Yii::$app->user->can('custom-quoter')) {
                            echo $form->field($item, 'quote_class')->dropDownList(BaseItemQuote::opts(), [
                                'id' => "Items_{$key}_quote_class",
                                'name' => "Items[$key][quote_class]",
                                'prompt' => '',
                            ]);
                        } else {
                            echo Html::activeHiddenInput($item, 'quote_class', [
                                'id' => "Items_{$key}_quote_class",
                                'name' => "Items[$key][quote_class]",
                            ]);
                        }
                        echo $form->field($item, 'quantity')->textInput([
                            'id' => "Items_{$key}_quantity",
                            'name' => "Items[$key][quantity]",
                        ])->hint(Yii::t('app', 'Item Quantity is multiplied by Product Quantity'));
                        ?>
                    </div>
                </div>
            </div>

            <?php
            // productToOption table
            $productToOption = new ProductToOption();
            $productToOption->loadDefaultValues();
            echo '<div id="product-productToOptions-' . $key . '" class="table table-condensed product-productToOptions">';
            // existing productToOption
            foreach ($model->productToOptions as $_key => $_productToOption) {
                $display = false;
                if ($_productToOption->item_id) {
                    if ($_productToOption->item_id == $item->id) {
                        $display = true;
                    }
                } else {
                    $productTypeToItemType = $_productToOption->productTypeToOption ? $_productToOption->productTypeToOption->productTypeToItemType : false;
                    if ($productTypeToItemType && $productTypeToItemType->id == $item->product_type_to_item_type_id) {
                        $display = true;
                    }
                }
                if ($display) {
                    if ($_productToOption->isNewRecord) {
                        $_key = strpos($_key, 'new') !== false ? $_key : 'new' . ($_key + $productToOption_k);
                    } else {
                        $_key = $_productToOption->id;
                    }
                    echo '<div class="table-row">';
                    echo $this->render('_form-product-to-option', [
                        'key' => $_key,
                        'itemKey' => $key,
                        'form' => $form,
                        'productToOption' => $_productToOption,
                        'allowOptionChange' => false,
                        'allowOptionRemove' => false,
                    ]);
                    echo '</div>';
                    \app\widgets\JavaScript::begin(['runOnAjax' => false]);
                    ?>
                    <script>
                        productToOption_k += 1;
                    </script>
                    <?php
                    \app\widgets\JavaScript::end();
                }
            }
            // new productToOption
            echo '<div class="table-row" id="product-new-productToOption-block-' . $key . '" style="display: none;">';
            echo $this->render('_form-product-to-option', [
                'key' => '__productToOption_id__',
                'itemKey' => $key,
                'form' => $form,
                'productToOption' => $productToOption,
                'allowOptionChange' => true,
                'allowOptionRemove' => true,
            ]);
            echo '</div>';
            echo '</div>';
            ?>
            <?php \app\widgets\JavaScript::begin(['runOnAjax' => false]) ?>
            <script>
                // add productToOption button
                //productToOption_k = <?php echo isset($_key) ? str_replace('new', '', $_key) : 0; ?>;
                $('#product-new-productToOption-button-<?= $key ?>').on('click', function () {
                    productToOption_k += 1;
                    $('#product-productToOptions-<?= $key ?>')
                        .append('<div class="table-row">' + $('#product-new-productToOption-block-<?= $key ?>').html().replace(/__productToOption_id__/g, 'new' + productToOption_k) + '</div>');
                });
            </script>
            <?php \app\widgets\JavaScript::end() ?>


            <?php
            // productToComponent table
            $productToComponent = new ProductToComponent();
            $productToComponent->loadDefaultValues();
            echo '<div id="product-productToComponents-' . $key . '" class="table table-condensed product-productToComponents">';
            // existing productToComponent
            foreach ($model->productToComponents as $_key => $_productToComponent) {
                $display = false;
                if ($_productToComponent->item_id) {
                    if ($_productToComponent->item_id == $item->id) {
                        $display = true;
                    }
                } else {
                    $productTypeToItemType = $_productToComponent->productTypeToComponent ? $_productToComponent->productTypeToComponent->productTypeToItemType : false;
                    if ($productTypeToItemType && $productTypeToItemType->id == $item->product_type_to_item_type_id) {
                        $display = true;
                    }
                }
                if ($display) {
                    if ($_productToComponent->isNewRecord) {
                        $_key = strpos($_key, 'new') !== false ? $_key : 'new' . ($_key + $productToComponent_k);
                    } else {
                        $_key = $_productToComponent->id;
                    }
                    echo '<div class="table-row">';
                    echo $this->render('_form-product-to-component', [
                        'key' => $_productToComponent->isNewRecord ? (strpos($_key, 'new') !== false ? $_key : 'new' . $_key) : $_productToComponent->id,
                        'itemKey' => $key,
                        'form' => $form,
                        'productToComponent' => $_productToComponent,
                        'allowComponentChange' => false,
                        'allowComponentRemove' => false,
                    ]);
                    echo '</div>';
                }
            }
            // new productToComponent
            echo '<div class="table-row" id="product-new-productToComponent-block-' . $key . '" style="display: none;">';
            echo $this->render('_form-product-to-component', [
                'key' => '__productToComponent_id__',
                'itemKey' => $key,
                'form' => $form,
                'productToComponent' => $productToComponent,
                'allowComponentChange' => true,
                'allowComponentRemove' => true,
            ]);
            echo '</div>';
            echo '</div>';
            ?>

            <?php \app\widgets\JavaScript::begin(['runOnAjax' => false]) ?>
            <script>
                // add productToComponent button
                //productToComponent_k = <?php echo isset($_key) ? str_replace('new', '', $_key) : 0; ?>;
                $('#product-new-productToComponent-button-<?= $key ?>').on('click', function () {
                    productToComponent_k += 1;
                    var $productToComponents = $('#product-productToComponents-<?= $key ?>');
                    $productToComponents.append('<div class="table-row">' + $('#product-new-productToComponent-block-<?= $key ?>').html().replace(/__productToComponent_id__/g, 'new' + productToComponent_k) + '</div>');
                    $productToComponents.find('select:visible').select2(<?= Json::encode($select2Options) ?>);
                    $productToComponents.find('#ProductToComponents_new' + productToComponent_k + '_quote_class').val(<?= Json::encode(BaseComponentQuote::className()) ?>);
                });
            </script>
            <?php \app\widgets\JavaScript::end() ?>

        </div>
    </div>
</div>