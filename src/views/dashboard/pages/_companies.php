<?php

use app\models\Company;
use app\models\search\CompanySearch;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;


/**
 * @var View $this
 * @var array $params
 * @var ActiveDataProvider $dataProvider
 * @var int $pageSize
 */

$title = isset($title) ? $title : false;
$orderBy = isset($orderBy) ? $orderBy : 'company.name ASC';
$pageSize = isset($pageSize) ? $pageSize : 1000;
$dataProvider = isset($dataProvider) ? $dataProvider : false;
$params = isset($params) ? $params : [];
$showColumns = isset($showColumns) ? $showColumns : ['name'];
$headerCallback = isset($headerCallback) ? $headerCallback : false;

$companySearch = new CompanySearch;
if (!$dataProvider) {
    $dataProvider = $companySearch->search($params);
}
if ($pageSize) {
    $dataProvider->pagination->pageSize = $pageSize;
}
if ($orderBy) {
    $dataProvider->query->orderBy($orderBy);
}

$cacheKey = '/dashboard/pages/_companies/' . md5(serialize([
        'showColumns' => $showColumns,
    ])) . '/';

$columns = [];
if (in_array('status', $showColumns)) {
    $columns[] = [
        'attribute' => 'status',
        'value' => function ($model) {
            /** @var Company $model */
            return $model->getStatusButton();
        },
        'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
        'format' => 'raw',
        'enableSorting' => false,
    ];
}
if (in_array('name', $showColumns)) {
    $columns[] = [
        'attribute' => 'id',
        'value' => function ($model) use ($cacheKey, $showColumns) {
            /** @var Company $model */
            return $model->getLink();
        },
        'format' => 'raw',
        'enableSorting' => false,
    ];
}


$grid_id = 'grid-' . uniqid();

$panelHeadingExtra = is_callable($headerCallback) ? call_user_func_array($headerCallback, [$dataProvider]) : '';

echo GridView::widget([
    'id' => $grid_id,
    'layout' => '{items}',
    'dataProvider' => $dataProvider,
    //'filterModel' => $companySearch,
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
        'after' => false,
        'before' => false,
        'type' => GridView::TYPE_DEFAULT,
    ],
    'panelHeadingTemplate' => ($panelHeadingExtra ? '<div class="pull-right">' . $panelHeadingExtra . '</div>' : '') . '<h3 class="panel-title">{heading}</h3><div class="clearfix"></div>',
]);
