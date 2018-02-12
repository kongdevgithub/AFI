<?php

use app\components\GridView;
use app\models\Package;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$params = Yii::$app->request->get('PackageSearch');
$searchModel = new \app\models\search\PackageSearch();
$dataProvider = $searchModel->search(['PackageSearch' => $params]);
$dataProvider->query->andWhere(['in', 'package.id', array_keys($model->getPackages())]);
//$dataProvider->query->joinWith('addressSingle');
//$dataProvider->pagination->defaultPageSize = 10;
$dataProvider->pagination->pageParam = 'page-packages';
$dataProvider->sort->sortParam = 'sort-packages';
//$dataProvider->sort->defaultOrder = ['id' => SORT_ASC];
$dataProvider->sort->attributes = [
    'id' => [
        'asc' => ['package.id' => SORT_ASC],
        'desc' => ['package.id' => SORT_DESC],
    ],
    'pickup_id' => [
        'asc' => ['package.pickup_id' => SORT_ASC],
        'desc' => ['package.pickup_id' => SORT_DESC],
    ],
    'address' => [
        'asc' => ['address.name' => SORT_ASC, 'address.street' => SORT_ASC],
        'desc' => ['address.name' => SORT_DESC, 'address.street' => SORT_DESC],
    ],
];

$columns = [
    [
        'class' => 'kartik\grid\CheckboxColumn',
    ],
    [
        'attribute' => 'id',
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Package */
            return implode(' &nbsp;', [
                $model->getLabel() . ($model->overflow_package_id ? '<br>' . Html::tag('span', 'overflow-' . $model->overflow_package_id, ['class' => 'label label-default']) : '') . '<br>',
                Html::a('<i class="fa fa-pencil"></i>', ['/package/update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()]),
                Html::a('<span class="fa fa-trash"></span>', ['/package/delete', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                    'data-confirm' => Yii::t('app', 'Are you sure?'),
                    'data-method' => 'post',
                ]),
            ]);
        },
        'format' => 'raw',
    ],
    [
        'attribute' => 'status',
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Package */
            return $model->getStatusButtons();
        },
        'hAlign' => 'center',
        'format' => 'raw',
    ],
    [
        'attribute' => 'pickup_id',
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Package */
            $output = [];
            if ($model->pickup) {
                $output[] = $model->pickup->getLink();
                if ($model->pickup->carrier) {
                    $output[] = $model->pickup->carrier->name;
                }
                if ($model->pickup->carrier_ref) {
                    $output[] = $model->pickup->getTrackingLink();
                }
            }
            return implode('<br>', $output);
        },
        'format' => 'raw',
    ],
    [
        'attribute' => 'address',
        'label' => Yii::t('app', 'Address'),
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Package */
            return $model->getAddressLabel();
        },
        'format' => 'raw',
    ],
    [
        'label' => Yii::t('app', 'Dimensions'),
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Package */
            return $model->getDimensionsLabel();
        },
        'format' => 'raw',
    ],
    [
        'label' => Yii::t('app', 'Items'),
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Package */
            return $this->render('/package/_item_quantity', ['model' => $model]);
        },
        'format' => 'raw',
    ],
];

$multiActions = [
    [
        'label' => Yii::t('app', 'Address'),
        'url' => ['package/address', 'ru' => ReturnUrl::getToken()],
    ],
    [
        'label' => Yii::t('app', 'Dimensions'),
        'url' => ['package/dimensions', 'ru' => ReturnUrl::getToken()],
    ],
    [
        'label' => Yii::t('app', 'Pickup'),
        'url' => ['package/pickup', 'ru' => ReturnUrl::getToken()],
    ],
    [
        'label' => Yii::t('app', 'Print'),
        'url' => ['package/print', 'ru' => ReturnUrl::getToken()],
    ],
];

echo GridView::widget([
    'id' => 'package-grid',
    'dataProvider' => $dataProvider,
    'multiActions' => $multiActions,
    'columns' => $columns,
    'panel' => [
        'heading' => Yii::t('app', 'Packages'),
    ],
]);