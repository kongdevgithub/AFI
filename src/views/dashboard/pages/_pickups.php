<?php

use app\components\ReturnUrl;
use app\models\Pickup;
use app\models\search\PickupSearch;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use app\models\Job;
use yii\helpers\Json;
use yii\web\View;


/**
 * @var View $this
 * @var array $params
 * @var ActiveDataProvider $dataProvider
 * @var int $pageSize
 */

$title = isset($title) ? $title : false;
$orderBy = isset($orderBy) ? $orderBy : 'pickup.id ASC';
$pageSize = isset($pageSize) ? $pageSize : 1000;
$dataProvider = isset($dataProvider) ? $dataProvider : false;
$params = isset($params) ? $params : [];
$showColumns = isset($showColumns) ? $showColumns : ['name', 'name.name'];
$headerCallback = isset($headerCallback) ? $headerCallback : false;

$pickupSearch = new PickupSearch;
if (!$dataProvider) {
    $dataProvider = $pickupSearch->search($params);
}
if ($pageSize) {
    $dataProvider->pagination->pageSize = $pageSize;
}
if ($orderBy) {
    $dataProvider->query->orderBy($orderBy);
}

$cacheKey = '/dashboard/pages/_pickups/' . md5(serialize([
        'showColumns' => $showColumns,
    ])) . '/';

$columns = [];
if (in_array('status', $showColumns)) {
    $columns[] = [
        'attribute' => 'status',
        'value' => function ($model) use ($showColumns) {
            /** @var Pickup $model */
            $checkbox = '';
            if (in_array('status.checkbox', $showColumns)) {
                $checkbox = Html::checkbox('check') . ' ';
            }
            return $checkbox . $model->getStatusButtons();
        },
        'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
        'format' => 'raw',
        'enableSorting' => false,
    ];
}
if (in_array('id', $showColumns)) {
    $columns[] = [
        'attribute' => 'id',
        'value' => function ($model) use ($cacheKey, $showColumns) {
            /** @var Pickup $model */
            return Html::a('pickup-' . $model->id, ['/pickup/view', 'id' => $model->id]);
        },
        'format' => 'raw',
        'enableSorting' => false,
    ];
}
if (in_array('jobs', $showColumns)) {
    $columns[] = [
        'label' => Yii::t('app', 'Jobs'),
        'value' => function ($model) use ($cacheKey, $showColumns) {
            /** @var Pickup $model */
            $jobs = [];
            foreach ($model->getJobs() as $job) {
                $jobs[] = $job->getLink($job->getTitle());
            }
            return implode('<br>', $jobs);
        },
        'format' => 'raw',
        'enableSorting' => false,
    ];
}

$grid_id = 'grid-' . uniqid();

$panelHeadingExtra = is_callable($headerCallback) ? call_user_func_array($headerCallback, [$dataProvider]) : '';

$actionButtons = implode(' ', [
    Html::a(Yii::t('app', 'Progress Pickups'), ['/pickup/progress', 'status' => 'pickup/complete', 'ru' => ReturnUrl::getToken()], [
        'type' => 'button',
        'title' => Yii::t('app', 'Progress'),
        'class' => 'btn btn-primary btn-xs modal-remote-form',
        'data-grid' => $grid_id,
    ]),
]);
$checkAll = Html::label(Html::checkbox('check_all', false, ['class' => 'select-on-check-all']) . ' ' . Yii::t('app', 'check all'));
$checkAll = Html::tag('div', $checkAll, ['class' => 'checkbox']);
$panelAfter = Html::tag('div', $checkAll, ['class' => 'pull-left']) . Html::tag('div', $actionButtons, ['class' => 'pull-right']);
$panelAfter = Html::tag('div', $panelAfter, ['class' => 'clearfix']);
if ($dataProvider->totalCount) {
    $this->registerJs("jQuery('#$grid_id').yiiGridView('setSelectionColumn', " . Json::encode([
            'name' => 'check',
            'multiple' => true,
            'checkAll' => 'check_all',
        ]) . ");");
}

echo GridView::widget([
    'id' => $grid_id,
    'layout' => '{items}',
    'dataProvider' => $dataProvider,
    //'filterModel' => $pickupSearch,
    //'showPageSummary' => true,
    'columns' => $columns,
    'tableOptions' => [
        'class' => 'no-margin',
    ],
    'striped' => false,
    'condensed' => true,
    'bordered' => false,
    'showHeader' => false,
    'panel' => [
        'heading' => $title,
        'footer' => false,
        'after' => $panelAfter,
        'before' => false,
        'type' => GridView::TYPE_DEFAULT,
    ],
    'panelHeadingTemplate' => ($panelHeadingExtra ? '<div class="pull-right">' . $panelHeadingExtra . '</div>' : '') . '<h3 class="panel-title">{heading}</h3><div class="clearfix"></div>',
]);
