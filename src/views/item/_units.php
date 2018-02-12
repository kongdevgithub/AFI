<?php
/**
 * @var yii\web\View $this
 * @var app\models\Item $model
 */

use app\components\ReturnUrl;
use app\models\Unit;
use cornernote\shortcuts\Y;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

$columns = [];
$columns[] = [
    'attribute' => 'package_id',
    'value' => function ($model) {
        /** @var Unit $model */
        return $model->package ? Html::a('package-' . $model->package->id, ['package/view', 'id' => $model->package->id]) : '';
    },
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'quantity',
    'format' => 'raw',

];
$columns[] = [
    'attribute' => 'status',
    'value' => function ($model) {
        /** @var Unit $model */
        return $model->getStatusButton();
    },
    'contentOptions' => ['class' => 'text-center'],
    'format' => 'raw',

];
?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Units'); ?></h3>
        <div class="box-tools pull-right text-right">
            <?php
            $items = [];
            if (Y::user()->can('app_item_fix-unit-count', ['route' => true])) {
                if (!$model->checkUnitCount()) {
                    $items[] = Html::a('<i class="icon-check"></i>', ['/item/fix-unit-count', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                        'title' => Yii::t('app', 'Fix Unit Count'),
                        'data-toggle' => 'tooltip',
                    ]);
                }
            }
            if ($items) {
                echo implode(' ', $items);
            }
            ?>
        </div>
    </div>
    <div class="box-body no-padding">
        <?php
        echo GridView::widget([
            'dataProvider' => new ActiveDataProvider([
                'query' => $model->getUnits(),
                'pagination' => ['pageSize' => 100000, 'pageParam' => 'page-units'],
                'sort' => false,
            ]),
            'tableOptions' => ['class' => 'no-margin', 'style' => 'border:0;'],
            'layout' => '{items}',
            'columns' => $columns,
        ]);
        ?>
    </div>
</div>
