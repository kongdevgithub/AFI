<?php

use app\models\Feedback;
use app\models\search\FeedbackSearch;
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
$orderBy = isset($orderBy) ? $orderBy : 'feedback.submitted_at ASC';
$pageSize = isset($pageSize) ? $pageSize : 1000;
$dataProvider = isset($dataProvider) ? $dataProvider : false;
$params = isset($params) ? $params : [];
$showColumns = isset($showColumns) ? $showColumns : ['contact', 'score', 'comments', 'submitted_at', 'dismiss'];
$headerCallback = isset($headerCallback) ? $headerCallback : false;

$feedbackSearch = new FeedbackSearch;
if (!$dataProvider) {
    $dataProvider = $feedbackSearch->search($params);
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
if (in_array('contact', $showColumns)) {
    $columns[] = [
        'attribute' => 'contact.label',
    ];
}
if (in_array('score', $showColumns)) {
    $columns[] = 'score';
}
if (in_array('comments', $showColumns)) {
    $columns[] = 'comments:ntext';
}
if (in_array('submitted_at', $showColumns)) {
    $columns[] = [
        'attribute' => 'submitted_at',
        'format' => 'dateTime',
        'contentOptions' => ['nowrap' => 'nowrap']
    ];
}
if (in_array('dismiss', $showColumns)) {
    $columns[] = [
        'value' => function ($model) use ($cacheKey, $showColumns) {
            /** @var Feedback $model */
            return $model->getLink(Yii::t('app', 'Dismiss'), 'dismiss', [], [
                'class' => 'modal-remote',
            ]);
        },
        'format' => 'raw',
    ];
}


$grid_id = 'grid-' . uniqid();

$panelHeadingExtra = is_callable($headerCallback) ? call_user_func_array($headerCallback, [$dataProvider]) : '';

echo GridView::widget([
    'id' => $grid_id,
    'layout' => '{items}',
    'dataProvider' => $dataProvider,
    //'filterModel' => $feedbackSearch,
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
