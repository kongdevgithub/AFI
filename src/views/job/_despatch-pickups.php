<?php

use app\components\GridView;
use app\models\Pickup;
use app\models\search\PickupSearch;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Inflector;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$params = Yii::$app->request->get('PickupSearch');
$searchModel = new PickupSearch();
$dataProvider = $searchModel->search(['PickupSearch' => $params]);
$dataProvider->query->andWhere(['in', 'pickup.id', array_keys($model->getPickups())]);
$dataProvider->pagination->defaultPageSize = 1000;
$dataProvider->pagination->pageParam = 'page-pickups';
$dataProvider->sort->sortParam = 'sort-pickups';

$columns = [
    [
        'class' => 'kartik\grid\CheckboxColumn',
    ],
    [
        'attribute' => 'id',
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Pickup */
            return implode(' &nbsp;', [
                Html::a('pickup-' . $model->id, ['/pickup/view', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], ['class' => 'label label-default']) . '<br>',
                Html::a('<i class="fa fa-pencil"></i>', ['/pickup/update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()]),
                Html::a('<span class="fa fa-trash"></span>', ['/pickup/delete', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
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
            /** @var $model Pickup */
            return $model->getStatusButtons();
        },
        'hAlign' => 'center',
        'format' => 'raw',
    ],
    [
        'attribute' => 'carrier_id',
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Pickup */
            if ($model->carrier) {
                return $model->carrier->name . '<br>' . $model->getTrackingLink();
            }
            return '';
        },
        'format' => 'raw',
    ],
    [
        'label' => Yii::t('app', 'Dates'),
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Pickup */
            $logDates = [];
            foreach (['collected_at', 'emailed_at'] as $field) {
                if (!empty($model->$field)) {
                    $logDates[] = Yii::$app->formatter->asDatetime($model->$field) . ' ' . Inflector::humanize(str_replace(['_at'], '', $field), true);
                }
            }
            return implode('<br>', $logDates);
        },
        'format' => 'raw',
    ],
    [
        'label' => Yii::t('app', 'Packages'),
        'value' => function ($model, $key, $index, $widget) {
            /** @var $model Pickup */
            return $this->render('/pickup/_packages', ['model' => $model, 'showUnits' => false]);
        },
        'format' => 'raw',
    ],
];

$multiActions = [
    [
        'label' => Yii::t('app', 'My Freight'),
        'url' => ['pickup/my-freight', 'ru' => ReturnUrl::getToken()],
    ],
    [
        'label' => Yii::t('app', 'Cope Freight'),
        'url' => ['pickup/cope-freight', 'ru' => ReturnUrl::getToken()],
    ],
];

echo GridView::widget([
    'id' => 'pickup-grid',
    'dataProvider' => $dataProvider,
    'multiActions' => $multiActions,
    'columns' => $columns,
    'panel' => [
        'heading' => Yii::t('app', 'Pickups'),
    ],
]);