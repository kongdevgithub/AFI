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
 * @var app\models\Product $product
 */

?>
<div class="box box-default box-solid no-margin">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $product->name; ?></h3>
        <div class="box-tools pull-right">
            <?= Html::a('<i class="fa fa-arrows sortable-handle"></i>', null, [
                'class' => 'btn btn-box-tool',
            ]) ?>
        </div>
    </div>
    <div class="box-body">
        <?php
        $sortables = [];
        foreach ($product->items as $item) {
            if (!$item->quantity) continue;
            $sortables[] = [
                'content' => $this->render('_sort-item', ['item' => $item]),
                'options' => [
                    'id' => 'Item_' . $item->id,
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
                        url: '" . Url::to(['/item/sort']) . "',
                        data: $(event.target).sortable('serialize')
                    });
                }"),
            ],
        ]);
        ?>
    </div>
</div>