<?php

use app\components\Helper;
use app\components\ReturnUrl;
use app\modules\goldoc\models\Product;
use app\modules\goldoc\models\search\ProductSearch;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;


/**
 * @var View $this
 * @var array $params
 * @var ActiveDataProvider $dataProvider
 * @var int $pageSize
 */

$title = isset($title) ? $title : false;
//$orderBy = isset($orderBy) ? $orderBy : 'product.id ASC, product.id ASC';
$pageSize = isset($pageSize) ? $pageSize : 1000;
$dataProvider = isset($dataProvider) ? $dataProvider : false;
$params = isset($params) ? $params : [];
$showColumns = isset($showColumns) ? $showColumns : ['status', 'details', 'id', 'code', 'goldoc_manager_id', 'venue_id', 'loc'];
$headerCallback = isset($headerCallback) ? $headerCallback : false;

$productSearch = new ProductSearch;
if (!$dataProvider) {
    $dataProvider = $productSearch->search($params);
}
if ($pageSize) {
    $dataProvider->pagination->pageSize = $pageSize;
}
//if ($orderBy) {
//    $dataProvider->query->orderBy($orderBy);
//}

$columns = [];
if (in_array('status', $showColumns)) {
    $columns[] = [
        'attribute' => 'status',
        'value' => function ($model) {
            /** @var Product $model */
            $afiStatus = $model->getAfiStatusButtons(true);
            if ($afiStatus) {
                $afiStatus = '<hr style="margin:2px 0">' . $afiStatus;
            }
            return $model->getStatusButton() . $afiStatus;
        },
        'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
        'format' => 'raw',
        'enableSorting' => false,
    ];
}
if (in_array('details', $showColumns)) {
    $columns[] = [
        'attribute' => 'details',
        'value' => function ($model) {
            /** @var Product $model */
            return $model->details ? Html::tag('span', '', [
                'class' => 'fa fa-info-circle',
                'title' => $model->details,
                'data-toggle' => 'tooltip',
            ]) : '';
        },
        'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
        'format' => 'raw',
        'enableSorting' => false,
    ];
}
if (in_array('id', $showColumns)) {
    $columns[] = [
        'label' => Yii::t('goldoc', 'ID'),
        'value' => function ($model) {
            /** @var Product $model */
            return Html::a($model->id, ['product/view', 'id' => $model->id]);
        },
        'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
        'format' => 'raw',
        'enableSorting' => false,
    ];
}

if (in_array('name', $showColumns)) {
    $columns[] = [
        'label' => Yii::t('goldoc', 'Name'),
        'value' => function ($model) {
            /** @var Product $model */
            return $model->name;
        },
        'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
        'format' => 'raw',
        'enableSorting' => false,
    ];
}

if (in_array('code', $showColumns)) {
    $columns[] = [
        'value' => function ($model) {
            /** @var Product $model */
            return $model->code . '-' . $model->sizeCode;
        },
        'contentOptions' => ['class' => 'text-left', 'nowrap' => 'nowrap'],
        'format' => 'raw',
        'enableSorting' => false,
    ];
}
if (in_array('goldoc_manager_id', $showColumns)) {
    $columns[] = [
        'value' => function ($model) {
            /** @var Product $model */
            return $model->goldocManager ? $model->goldocManager->initials : '';
        },
        'format' => 'raw',
        'enableSorting' => false,
    ];
}
if (in_array('venue_id', $showColumns)) {
    $columns[] = [
        'value' => function ($model) {
            /** @var Product $model */
            return $model->venue ? $model->venue->code : '';
        },
        'format' => 'raw',
        'enableSorting' => false,
    ];
}
if (in_array('loc', $showColumns)) {
    $columns[] = [
        'value' => function ($model) {
            /** @var Product $model */
            return $model->loc;
        },
        'format' => 'raw',
        'enableSorting' => false,
    ];
}


$grid_id = 'grid-' . uniqid();

$panelHeadingExtra = is_callable($headerCallback) ? call_user_func_array($headerCallback, [$dataProvider]) : '';

$panelAfter = false;
$actionButtons = false;
if ($panelAfter) {
    $checkAll = Html::label(Html::checkbox('check_all', false, ['class' => 'select-on-check-all']) . ' ' . Yii::t('goldoc', 'check all'));
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
}

echo GridView::widget([
    'id' => $grid_id,
    'layout' => '{items}',
    'dataProvider' => $dataProvider,
    //'filterModel' => $productSearch,
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
