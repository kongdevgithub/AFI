<?php

use app\models\Product;
use app\components\ReturnUrl;
use cornernote\shortcuts\Y;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\Sortable;
use yii\web\JsExpression;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */


$sortables = [];
foreach ($model->products as $product) {
    $sortables[] = [
        'content' => $this->render('_sort-product', ['product' => $product]),
        'options' => [
            'id' => 'Product_' . $product->id,
            'class' => 'list-group-item',
            'style' => 'border:0;padding:0;',
        ],
    ];
}
echo Sortable::widget([
    'items' => $sortables,
    'options' => [
        'class' => 'list-group',
        'style' => 'margin-bottom: 0;',
    ],
    'clientOptions' => [
        'axis' => 'y',
        'forcePlaceholderSize' => true,
        'cursor' => 'move',
        'handle' => '.sortable-handle',
        'helper' => new JsExpression('function(event, ui) {
            var $clone =  $(ui).clone();
            $clone.css("position", "absolute");
            return $clone.get(0);
        }'),
        'start' => new JsExpression('function(e, ui){
            ui.placeholder.height(ui.item.height()-22);
        }'),
        'update' => new JsExpression("function(event, ui) {
            $.ajax({
                type: 'POST',
                url: '" . Url::to(['/product/sort']) . "',
                data: $(event.target).sortable('serialize')
            });
        }"),
    ],
]);

?>
<style>
    ul.list-group li.list-group-item {
        margin-bottom: 10px;
    }

    ul.list-group li.list-group-item:not([style*="display: none"]):last-child {
        margin-bottom: 0 !important;
    }
</style>
