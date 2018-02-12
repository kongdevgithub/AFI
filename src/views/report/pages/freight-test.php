<?php

use app\components\freight\Freight;
use app\components\MenuItem;
use app\models\Item;
use app\models\Job;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Freight Test');
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();
?>
<div class="report-freight-test">


    <?php

    $freightJobs = [];
    $unboxedJobs = [];

    $jobs = Job::find()
        ->andWhere(['status' => 'job/complete'])
        //->andWhere(['is not', 'invoice_paid', null])
        ->andWhere(['>', 'quote_freight_price', 0])
        ->limit(500)
        ->orderBy(['complete_at' => SORT_DESC])
        ->all();

    foreach ($jobs as $job) {

        $boxes = $job->getCache('Freight.getBoxes');
        if ($boxes === false) {
            $boxes = Freight::getBoxes($job);
            $job->setCache('Freight.getBoxes', $boxes);
        }
        $carriers = $job->getCache('Freight.getCarrierFreight');
        if ($carriers === false) {
            $carriers = $job->shippingAddresses && count($job->shippingAddresses) == 1 ? Freight::getCarrierFreight($job->shippingAddresses[0], $boxes) : false;
            $job->setCache('Freight.getCarrierFreight', $carriers);
        }
        $unboxed = $job->getCache('Freight.getUnboxed');
        if ($unboxed === false) {
            $unboxed = Freight::getUnboxed($job, $boxes);
            $job->setCache('Freight.getUnboxed', $unboxed);
        }
        if ($unboxed) {
            $unboxedJobs[] = [
                'job_id' => $job->id,
                'actual' => $job->quote_freight_price,
                'carrier' => $job->freight_method,
                'boxes' => $boxes,
                'carriers' => $carriers,
                'unboxed' => $unboxed,
                'weight' => Freight::getWeight($boxes),
                'postcode' => $job->shippingAddresses ? $job->shippingAddresses[0]->postcode : '',
            ];
            continue;
        }
        $freightJobs[] = [
            'job_id' => $job->id,
            'actual' => $job->quote_freight_price,
            'carrier' => $job->freight_method,
            'boxes' => $boxes,
            'carriers' => $carriers,
            'unboxed' => $unboxed,
            'weight' => Freight::getWeight($boxes),
            'postcode' => $job->shippingAddresses ? $job->shippingAddresses[0]->postcode : '',
        ];
    }

    $columns = [];
    $columns[] = [
        'attribute' => 'job_id',
        'value' => function ($model) {
            return Html::a($model['job_id'], ['/job/boxes', 'id' => $model['job_id']]);
        },
        'format' => 'raw',
    ];
    $columns[] = [
        'attribute' => 'actual',
        'value' => function ($model) {
            return number_format($model['actual'], 2);
        },
    ];
    $columns[] = [
        'label' => Yii::t('app', 'Estimated Price'),
        'attribute' => 'carriers',
        'value' => function ($model) {
            $cope = isset($model['carriers']['cope-road']['price']) ? $model['carriers']['cope-road']['price'] : false;
            $swift = isset($model['carriers']['swift']['price']) ? $model['carriers']['swift']['price'] : false;
            if ($model['carrier'] == 'cope-road') {
                return number_format($cope, 2);
            }
            if ($model['carrier'] == 'swift') {
                return number_format($swift, 2);
            }
            if ($cope && $swift && !$model['carriers']['swift']['quote']) {
                return number_format(min($cope, $swift), 2);
            }
            if ($cope) {
                return number_format($cope, 2);
            }
            if ($swift && !$model['carriers']['swift']['quote']) {
                return number_format($swift, 2);
            }
            return '-';
        },
        'format' => 'raw',
    ];
    $columns[] = [
        'label' => Yii::t('app', 'Estimated Cost'),
        'attribute' => 'carriers',
        'value' => function ($model) {
            $cope = isset($model['carriers']['cope-road']['cost']) ? $model['carriers']['cope-road']['cost'] : false;
            $swift = isset($model['carriers']['swift']['cost']) ? $model['carriers']['swift']['cost'] : false;
            if ($model['carrier'] == 'cope-road') {
                return number_format($cope, 2);
            }
            if ($model['carrier'] == 'swift') {
                return number_format($swift, 2);
            }
            if ($cope && $swift && !$model['carriers']['swift']['quote']) {
                return number_format(min($cope, $swift), 2);
            }
            if ($cope) {
                return number_format($cope, 2);
            }
            if ($swift && !$model['carriers']['swift']['quote']) {
                return number_format($swift, 2);
            }
            return '';
        },
        'format' => 'raw',
    ];
    $columns[] = [
        'attribute' => 'weight',
    ];
    $columns[] = [
        'attribute' => 'postcode',
    ];
    $columns[] = [
        'attribute' => 'carrier',
    ];
    echo GridView::widget([
        'dataProvider' => new ArrayDataProvider([
            'allModels' => $freightJobs,
            'pagination' => ['pageSize' => 0],
            'sort' => false,
        ]),
        'layout' => '{items}',
        'columns' => $columns,
        'panel' => [
            'heading' => Yii::t('app', 'Freight Price vs Estimate') . ': ' . count($freightJobs),
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


    $columns = [];
    $columns[] = [
        'attribute' => 'job_id',
        'value' => function ($model) {
            return Html::a($model['job_id'], ['/job/boxes', 'id' => $model['job_id']]);
        },
        'format' => 'raw',
    ];
    $columns[] = [
        'attribute' => 'unboxed',
        'value' => function ($model) {
            if (!$model['unboxed']) {
                return '';
            }
            $items = [];
            foreach ($model['unboxed'] as $item_id => $quantity) {
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
            return GridView::widget([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $items,
                    'pagination' => ['pageSize' => 10000],
                    'sort' => false,
                ]),
                'layout' => '{items}',
                'columns' => $columns,
                'bordered' => true,
                'striped' => false,
                'condensed' => true,
                'responsive' => true,
                'hover' => false,
            ]);
        },
        'format' => 'raw',
    ];

    echo GridView::widget([
        'dataProvider' => new ArrayDataProvider([
            'allModels' => $unboxedJobs,
            'pagination' => ['pageSize' => 10000],
            'sort' => false,
        ]),
        'layout' => '{items}',
        'columns' => $columns,
        'panel' => [
            'heading' => Yii::t('app', 'Unboxed Jobs') . ': ' . count($unboxedJobs),
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

    ?>

</div>