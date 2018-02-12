<?php
/**
 * @var yii\web\View $this
 * @var app\models\Product $model
 */

use app\models\Item;
use kartik\grid\GridView;
use yii\bootstrap\ButtonDropdown;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

$columns = [];
$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var Item $model */
        $items = [];
        if (Yii::$app->user->can('app_item_update', ['route' => true])) {
            $items[] = ['label' => Yii::t('app', 'Update'), 'url' => ['//item/update', 'id' => $model->id]];
        }
        return ButtonDropdown::widget([
            'tagName' => 'a',
            'options' => [
                'href' => Url::to(['//item/view', 'id' => $model->id]),
                'class' => 'btn btn-default',
            ],
            'label' => $model->id,
            'split' => true,
            'dropdown' => [
                'items' => $items,
            ],
        ]);
    },
    'contentOptions' => ['style' => 'width:100px;'],
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'status',
    'value' => function ($model) {
        /** @var Item $model */
        return $model->getStatusButtons();
    },
    'contentOptions' => ['style' => 'width:100px;'],
    'format' => 'raw',
];
$columns[] = 'name';
$columns[] = [
    'attribute' => 'item_type_id',
    'value' => function ($model) {
        /** @var Item $model */
        return Html::tag('span', $model->itemType->name, ['class' => 'label label-default']);
    },
    'contentOptions' => ['style' => 'width:50px;'],
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'quantity',
    'value' => function ($model) {
        /** @var Item $model */
        return Html::tag('span', 'x' . ($model->quantity * $model->product->quantity), ['class' => 'label label-default']);
    },
    'contentOptions' => ['style' => 'width:50px;'],
    'format' => 'raw',
];

?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Items'); ?></h3>
    </div>
    <div class="box-body no-padding">
        <?php
        echo GridView::widget([
            'dataProvider' => new ActiveDataProvider([
                'query' => $model->getItems()->andWhere('quantity > 0'),
                'pagination' => ['pageSize' => 1000, 'pageParam' => 'page-items'],
            ]),
            'layout' => '{items}',
            'showHeader' => false,
            'columns' => $columns,
        ]);
        ?>
    </div>
</div>
