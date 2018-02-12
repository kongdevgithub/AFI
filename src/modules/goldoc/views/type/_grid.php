<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/71f573f8a24512e6aac3b417da853ba3
 *
 * @package default
 */


use app\components\GridView;
use app\components\ReturnUrl;
use app\modules\goldoc\models\Type;
use yii\helpers\Html;

/**
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\modules\goldoc\models\search\TypeSearch $searchModel
 */
$columns = [];
$columns[] = [
	'attribute' => 'id',
	'value' => function ($model) {
		/** @var Type $model */
		return Yii::$app->user->can('app_type_view', ['route' => true]) ? Html::a($model->id, ['view', 'id' => $model->id]) : $model->id;
	},
	'format' => 'raw',
];
$columns[] =         'code';
$columns[] =         'name';

$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
		'class' => 'btn btn-default btn-xs modal-remote-form',
		'data-toggle' => 'modal',
		'data-target' => '#type-searchModal',
	]);
if (Yii::$app->user->can('app_type_create', ['route' => true])) {
	$gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
			'create',
			'ru' => ReturnUrl::getToken()
		], ['class' => 'btn btn-default btn-xs']);
}

echo GridView::widget([
		'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'columns' => $columns,
		//'multiActions' => $multiActions,
		'gridActions' => $gridActions,
		'panel' => [
			'heading' => Yii::t('goldoc', 'Types'),
		],
	]);
echo $this->render('_search', ['model' => $searchModel]);
