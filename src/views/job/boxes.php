<?php

use app\components\freight\Freight;
use app\components\ReturnUrl;
use app\models\Item;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 * @var ActiveForm $form
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Freight');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="job-boxes">

    <?= $this->render('_menu', ['model' => $model]); ?>
    <?= $this->render('_account_term_warning', ['model' => $model]) ?>

    <div class="row">
        <div class="col-md-9">
            <?= $this->render('_details', ['model' => $model]); ?>

            <?php
            $boxes = Freight::getBoxes($model);
            $carriers = Freight::getCarrierFreight($model->shippingAddresses ? $model->shippingAddresses[0] : false, $boxes);
            $unboxed = Freight::getUnboxed($model, $boxes);

            // unboxed
            $items = [];
            foreach ($unboxed as $item_id => $quantity) {
                $item = Item::findOne($item_id);
                $item->quantity = $quantity;
                $items[] = $item;
            }
            $columns = [];
            $columns[] = [
                'label' => Yii::t('app', 'Item'),
                'value' => function ($model) {
                    /** @var Item $model */
                    return Html::a('item-' . $model->id, ['/item/view', 'id' => $model->id]);
                },
                'format' => 'raw',
            ];
            $columns[] = [
                'label' => Yii::t('app', 'Name'),
                'value' => function ($model) {
                    /** @var Item $model */
                    $size = $model->getSizeHtml();
                    return $model->product->name . ' | ' . $model->name . ($size ? ' | ' . $size : '');
                },
                'format' => 'raw',
            ];
            $columns[] = [
                'label' => Yii::t('app', 'Quantity'),
                'value' => function ($model) {
                    /** @var Item $model */
                    return $model->quantity;
                },
                'format' => 'raw',
            ];
            echo GridView::widget([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $items,
                    'pagination' => ['pageSize' => 0],
                    'sort' => false,
                ]),
                'layout' => '{items}',
                'columns' => $columns,
                'panel' => [
                    'heading' => Yii::t('app', 'Unboxed'),
                    'footer' => false,
                    'before' => false,
                    'after' => false,
                    'type' => GridView::TYPE_DEFAULT,
                ],
                'panelHeadingTemplate' => '<h3 class="panel-title">{heading}</h3>',
                'bordered' => true,
                'striped' => false,
                'condensed' => true,
                'responsive' => true,
                'hover' => false,
            ]);

            // carriers
            if ($carriers) {
                $columns = [];
                $columns[] = 'name';
                $columns[] = 'zone';
                $columns[] = 'weight';
                $columns[] = [
                    'attribute' => 'cost',
                    'value' => function ($model) {
                        return '$' . number_format($model['cost'], 2);
                    },
                ];
                $columns[] = [
                    'attribute' => 'price',
                    'value' => function ($model) {
                        return '$' . number_format($model['price'], 2);
                    },
                ];
                echo GridView::widget([
                    'dataProvider' => new ArrayDataProvider([
                        'allModels' => $carriers,
                        'pagination' => ['pageSize' => 0],
                        'sort' => false,
                    ]),
                    'layout' => '{items}',
                    'columns' => $columns,
                    'panel' => [
                        'heading' => Yii::t('app', 'Carriers'),
                        'footer' => false,
                        'before' => false,
                        'after' => false,
                        'type' => GridView::TYPE_DEFAULT,
                    ],
                    'panelHeadingTemplate' => '<h3 class="panel-title">{heading}</h3>',
                    'bordered' => true,
                    'striped' => false,
                    'condensed' => true,
                    'responsive' => true,
                    'hover' => false,
                ]);
            }

            // boxes
            if ($boxes) {
                $columns = [];
                $columns[] = 'type';
                $columns[] = 'width';
                $columns[] = 'length';
                $columns[] = 'height';
                $columns[] = 'dead_weight';
                $columns[] = 'cubic_weight';
                $columns[] = [
                    'attribute' => 'pieces',
                    'value' => function ($model) {
                        return GridView::widget([
                            'dataProvider' => new ArrayDataProvider([
                                'allModels' => $model['pieces'],
                                'pagination' => ['pageSize' => 100000],
                                'sort' => false,
                            ]),
                            'layout' => '{items}',
                            'columns' => [
                                'code',
                                'width',
                                'length',
                                'height',
                                'dead_weight',
                                'cubic_weight',
                            ],
                            'bordered' => true,
                            'striped' => false,
                            'condensed' => true,
                            'responsive' => true,
                            'hover' => false,
                        ]);
                    },
                    'format' => 'raw',
                ];
                $columns[] = [
                    'attribute' => 'products',
                    'value' => function ($model) {
                        $products = [];
                        foreach ($model['products'] as $k => $_products) {
                            $products[] = Html::tag('strong', $k) . '<br>' . implode('<br>', $_products);
                        }
                        return implode('<hr>', $products);
                    },
                    'format' => 'raw',
                ];
                $columns[] = [
                    'attribute' => 'items',
                    'value' => function ($model) {
                        $items = [];
                        foreach ($model['items'] as $k => $_items) {
                            $items[] = Html::tag('strong', $k) . '<br>' . implode('<br>', $_items);
                        }
                        return implode('<hr>', $items);
                    },
                    'format' => 'raw',
                ];
                $columns[] = [
                    'attribute' => 'quantity',
                    'value' => function ($model) {
                        $quantity = [];
                        foreach ($model['quantity'] as $k => $_quantity) {
                            $quantity[] = Html::tag('strong', $k) . ': ' . $_quantity;
                        }
                        return implode('<br>', $quantity);
                    },
                    'format' => 'raw',
                ];
                echo GridView::widget([
                    'dataProvider' => new ArrayDataProvider([
                        'allModels' => $boxes,
                        'pagination' => ['pageSize' => 100000],
                        'sort' => false,
                    ]),
                    'layout' => '{items}',
                    'columns' => $columns,
                    'panel' => [
                        'heading' => Yii::t('app', 'Boxes') . ' | count:' . count($boxes),
                        'footer' => false,
                        'before' => false,
                        'after' => false,
                        'type' => GridView::TYPE_DEFAULT,
                    ],
                    'panelHeadingTemplate' => '<h3 class="panel-title">{heading}</h3>',
                    'bordered' => true,
                    'striped' => false,
                    'condensed' => true,
                    'responsive' => true,
                    'hover' => false,
                ]);
            }

            ?>
        </div>
        <div class="col-md-3">
            <?= $this->render('/job/_status-box', ['model' => $model]) ?>
            <?= $this->render('/job/_quote-version-fork', ['model' => $model]) ?>
            <?= $this->render('/job/_job-copy', ['model' => $model]) ?>
            <?= $this->render('/job/_job-redo', ['model' => $model]) ?>
            <?= $this->render('/job/_notes', ['model' => $model]) ?>
            <?= $this->render('/attachment/_index', ['model' => $model, 'title' => Yii::t('app', 'Job Attachments')]) ?>
        </div>
    </div>

</div>

