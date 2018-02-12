<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/2137b70b4fa481c12623574a84f199bc
 *
 * @package default
 */


use cornernote\returnurl\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Sponsor $model
 */
$items = [];

$items[] = [
	'label' => Yii::t('goldoc', 'View'),
	'url' => ['view', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
	'active' => Yii::$app->controller->action->id == 'view',
];
if (Yii::$app->user->can('goldoc_sponsor_update', ['route' => true])) {
	$items[] = [
		'label' => '<i class="fa fa-pencil"></i> ' . Yii::t('goldoc', 'Update'),
		'url' => ['update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
		'encode' => false,
		'active' => Yii::$app->controller->action->id == 'update'
	];
}
if (Yii::$app->user->can('goldoc_sponsor_copy', ['route' => true])) {
	$items[] = [
		'label' => '<i class="fa fa-copy"></i> ' . Yii::t('goldoc', 'Copy'),
		'url' => ['copy', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
		'encode' => false,
		'active' => Yii::$app->controller->action->id == 'copy'
	];
}
if (Yii::$app->user->can('goldoc_sponsor_delete', ['route' => true])) {
	$items[] = [
		'label' => '<i class="fa fa-trash"></i> ' . Yii::t('goldoc', 'Delete'),
		'url' => ['delete', 'id' => $model->id],
		'linkOptions' => [
			'data-confirm' => Yii::t('goldoc', 'Are you sure?'),
			'data-method' => 'post',
		],
		'encode' => false,
	];
}

$this->params['nav'] = $items;

//echo Nav::widget([
//    'items' => $items,
//    'options' => ['class' => 'nav-tabs'],
//    'activateParents' => true,
//]);
