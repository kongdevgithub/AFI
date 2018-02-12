<?php

use app\components\quotes\items\BaseItemQuote;
use app\models\ItemType;
use app\models\ProductToComponent;
use app\models\ProductToOption;
use app\models\ProductTypeToItemType;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\form\ProductForm $model
 * @var app\models\Item $item
 * @var kartik\form\ActiveForm $form
 * @var string $key
 */

?>

<div class="col-md-4 product-item">
    <div class="box box-info box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $item->name ?></h3>
        </div>
        <div class="box-body">
            <div class="table table-condensed">
                <div class="table-row">
                    <div class="table-cell" style="border: 0; padding: 0;">
                        <?php
                        $items = [];
                        foreach (ProductTypeToItemType::find()->notDeleted()->all() as $productTypeToItemType) {
                            if ($productTypeToItemType->productType->deleted_at) {
                                continue;
                            }
                            $k = $productTypeToItemType->productType->id . '.' . $productTypeToItemType->id;
                            $v = strip_tags($productTypeToItemType->productType->getBreadcrumbHtml(' > ')) . ' > ' . $productTypeToItemType->name;
                            $items[$k] = $v;
                        }
                        asort($items);
                        echo $form->field($item, 'product_type_to_item_type_id')->dropDownList($items, [
                            'id' => "Items_{$key}_name",
                            'name' => "Items[$key][name]",
                            'prompt' => '',
                            'class' => 'item-product_type_to_item_type_id form-control',
                            'onchange' => new JsExpression("
                                var that = $(this);
                                var id = that.val().split('.');
                                var url = '" . Url::to([
                                    'product/create',
                                    'clone' => 1,
                                    'productToOption_k' => '-productToOption_k-',
                                    'productToComponent_k' => '-productToComponent_k-',
                                    'Product' => [
                                        'job_id' => $model->product->job_id,
                                        'product_type_id' => '-product_type_id-',
                                    ],
                                ]) . "';
                                $.ajax({
                                    url: url
                                        .replace('-product_type_id-', id[0])
                                        .replace('-productToOption_k-', productToOption_k)
                                        .replace('-productToComponent_k-', productToComponent_k),
                                    success: function (data) {
                                        var dom = $(document.createElement('html'));
                                        dom[0].innerHTML = data;
                                        var html = dom.find('#product-type-to-item-type-' + id[1]).html();
                                            html += $('<div>').append(dom.find('script').clone()).html();
                                        //var html = $('<div>').html(data).find('#product-type-to-item-type-' + id[1]).html();
                                        that.closest('.product-item').html(html.replace(/__cloneItem_id__/g, '" . $key . "'));
                                        $('.kv-hint-special').activeFieldHint();
                                        // add productToOption button
                                        productToOption_k += 1000;
                                        $('#product-new-productToOption-button-" . $key . "').on('click', function () {
                                            productToOption_k += 1;
                                            var _item_k = $(this).attr('data-key');
                                            $('#product-productToOptions-' + _item_k)
                                                .append('<div class=\"table-row\">' + $('#product-new-productToOption-block-' + _item_k).html().replace(/__productToOption_id__/g, 'new' + productToOption_k) + '</div>');
                                        });
                                        // add productToComponent button
                                        productToComponent_k += 1000;
                                        $('#product-new-productToComponent-button-" . $key . "').on('click', function () {
                                            productToComponent_k += 1;
                                            var _item_k = $(this).attr('data-key');
                                            $('#product-productToComponents-' + _item_k)
                                                .append('<div class=\"table-row\">' + $('#product-new-productToComponent-block-' + _item_k).html().replace(/__productToComponent_id__/g, 'new' + productToComponent_k) + '</div>');
                                        });

                                    }
                                });
                            "),
                        ])->label(Yii::t('app', 'Clone From'));
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>