<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/a0a12d1bd32eaeeb8b2cff56d511aa22
 *
 * @package default
 */


use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/**
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\NotificationSearch $searchModel
 */
$this->title = Yii::t('models', 'Notifications');
$this->params['breadcrumbs'][] = $this->title;


/**
 * create action column template depending acces rights
 */
$actionColumnTemplates = [];

if (\Yii::$app->user->can('app_notification_view', ['route' => true])) {
	$actionColumnTemplates[] = '{view}';
}

if (\Yii::$app->user->can('app_notification_update', ['route' => true])) {
	$actionColumnTemplates[] = '{update}';
}

if (\Yii::$app->user->can('app_notification_delete', ['route' => true])) {
	$actionColumnTemplates[] = '{delete}';
}
if (isset($actionColumnTemplates)) {
	$actionColumnTemplate = implode(' ', $actionColumnTemplates);
	$actionColumnTemplateString = $actionColumnTemplate;
} else {
	Yii::$app->view->params['pageButtons'] = Html::a('<span class="glyphicon glyphicon-plus"></span> ' . Yii::t('cruds', 'New'), ['create'], ['class' => 'btn btn-success']);
	$actionColumnTemplateString = "{view} {update} {delete}";
}
$actionColumnTemplateString = '<div class="action-buttons">'.$actionColumnTemplateString.'</div>';
?>
<div class="giiant-crud notification-index">

    <?php
//             echo $this->render('_search', ['model' =>$searchModel]);
?>


    <?php \yii\widgets\Pjax::begin(['id'=>'pjax-main', 'enableReplaceState'=> false, 'linkSelector'=>'#pjax-main ul.pagination a, th a', 'clientOptions' => ['pjax:success'=>'function(){alert("yo")}']]) ?>

    <h1>
        <?php echo Yii::t('models', 'Notifications') ?>
        <small>
            List
        </small>
    </h1>
    <div class="clearfix crud-navigation">
<?php
if (\Yii::$app->user->can('app_notification_create', ['route' => true])) {
?>
        <div class="pull-left">
            <?php echo Html::a('<span class="glyphicon glyphicon-plus"></span> ' . Yii::t('cruds', 'New'), ['create'], ['class' => 'btn btn-success']) ?>
        </div>
<?php
}
?>
        <div class="pull-right">


            <?php echo
\yii\bootstrap\ButtonDropdown::widget(
	[
		'id' => 'giiant-relations',
		'encodeLabel' => false,
		'label' => '<span class="glyphicon glyphicon-paperclip"></span> ' . Yii::t('cruds', 'Relations'),
		'dropdown' => [
			'options' => [
				'class' => 'dropdown-menu-right'
			],
			'encodeLabels' => false,
			'items' => [

			]
		],
		'options' => [
			'class' => 'btn-default'
		]
	]
);
?>
        </div>
    </div>

    <hr />

    <div class="table-responsive">
        <?php echo GridView::widget([
		'dataProvider' => $dataProvider,
		'pager' => [
			'class' => yii\widgets\LinkPager::className(),
			'firstPageLabel' => Yii::t('cruds', 'First'),
			'lastPageLabel' => Yii::t('cruds', 'Last'),
		],
		'filterModel' => $searchModel,
		'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
		'headerRowOptions' => ['class'=>'x'],
		'columns' => [
			[
				'class' => 'yii\grid\ActionColumn',
				'template' => $actionColumnTemplateString,
				'buttons' => [
					'view' => function ($url, $model, $key) {
						$options = [
							'title' => Yii::t('yii', 'View'),
							'aria-label' => Yii::t('yii', 'View'),
							'data-pjax' => '0',
						];
						return Html::a('<span class="glyphicon glyphicon-file"></span>', $url, $options);
					}


				],
				'urlCreator' => function($action, $model, $key, $index) {
					// using the column name as key, not mapping to 'id' like the standard generator
					$params = is_array($key) ? $key : [$model->primaryKey()[0] => (string) $key];
					$params[0] = \Yii::$app->controller->id ? \Yii::$app->controller->id . '/' . $action : $action;
					return Url::toRoute($params);
				},
				'contentOptions' => ['nowrap'=>'nowrap']
			],
			'id',
			'model_name',
			'model_id',
			'title',
			'body:ntext',
			'type',
			'deleted_at',
			/*'created_by',*/
			/*'created_at',*/
			/*'updated_by',*/
			/*'updated_at',*/
		],
	]); ?>
    </div>

</div>


<?php \yii\widgets\Pjax::end() ?>
