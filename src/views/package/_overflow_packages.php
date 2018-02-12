<?php
/**
 * @var yii\web\View $this
 * @var app\models\Package $model
 */

use app\models\Package;
use app\models\search\PackageSearch;
use raoul2000\workflow\helpers\WorkflowHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ButtonDropdown;
use yii\grid\GridView;
use app\components\ReturnUrl;


$searchModel = new PackageSearch;
$params = Yii::$app->request->get();
$params['PackageSearch']['overflow_package_id'] = $model->id;
$dataProvider = $searchModel->search($params);

$columns = [];
$columns[] = [
    'attribute' => 'id',
    'value' => function ($model) {
        /** @var Package $model */
        $items = [];
        if (Yii::$app->user->can('app_package_update', ['route' => true])) {
            $items[] = ['label' => Yii::t('app', 'Update'), 'url' => ['//package/update', 'id' => $model->id]];
        }
        return ButtonDropdown::widget([
            'tagName' => 'a',
            'options' => [
                'href' => Url::to(['//package/view', 'id' => $model->id]),
                'class' => 'btn btn-default',
            ],
            'label' => $model->id,
            'split' => true,
            'dropdown' => [
                'items' => $items,
            ],
        ]);
    },
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'status',
    'value' => function ($model) {
        /** @var Package $model */
        return $model->getStatusButtons();
    },
    'filter' => WorkflowHelper::getAllStatusListData($searchModel->getWorkflow()->getId(), $searchModel->getWorkflowSource()),
    'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'pickup_id',
    'value' => function ($model) {
        /** @var Package $model */
        $output = [];
        if ($model->pickup) {
            $output[] = $model->pickup->getLink();
            if ($model->pickup->carrier) {
                $output[] = $model->pickup->carrier->getLink();
            }
            if ($model->pickup->carrier_ref) {
                $output[] = $model->pickup->getTrackingLink();
            }
        }
        return implode('<br>', $output);
    },
    'format' => 'raw',
    'enableSorting' => false,
];
$columns[] = [
    'attribute' => 'address',
    'label' => Yii::t('app', 'Address'),
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Package */
        return $model->getAddressLabel();
    },
    'format' => 'raw',
];
$columns[] = [
    'label' => Yii::t('app', 'Dimensions'),
    'value' => function ($model, $key, $index, $widget) {
        /** @var $model Package */
        return $model->getDimensionsLabel();
    },
    'format' => 'raw',
];

echo GridView::widget([
    'layout' => '{summary}{pager}{items}{pager}',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
]);


