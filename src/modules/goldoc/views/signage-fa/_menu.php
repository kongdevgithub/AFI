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
 * @var app\modules\goldoc\models\SignageFa $model
 */
$items = [];

$items[] = [
    'label' => Yii::t('app', 'View'),
    'url' => ['view', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
    'active' => Yii::$app->controller->action->id == 'view',
];
//if (Yii::$app->user->can('app_signage-fa_update', ['route' => true])) {
//	$items[] = [
//		'label' => '<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Update'),
//		'url' => ['update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
//		'encode' => false,
//		'active' => Yii::$app->controller->action->id == 'update'
//	];
//}
//if (Yii::$app->user->can('app_signage-fa_copy', ['route' => true])) {
//	$items[] = [
//		'label' => '<i class="fa fa-copy"></i> ' . Yii::t('app', 'Copy'),
//		'url' => ['copy', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
//		'encode' => false,
//		'active' => Yii::$app->controller->action->id == 'copy'
//	];
//}
//if (Yii::$app->user->can('app_signage-fa_delete', ['route' => true])) {
//	$items[] = [
//		'label' => '<i class="fa fa-trash"></i> ' . Yii::t('app', 'Delete'),
//		'url' => ['delete', 'id' => $model->id],
//		'linkOptions' => [
//			'data-confirm' => Yii::t('app', 'Are you sure?'),
//			'data-method' => 'post',
//		],
//		'encode' => false,
//	];
//}

$this->params['nav'] = $items;

//echo Nav::widget([
//    'items' => $items,
//    'options' => ['class' => 'nav-tabs'],
//    'activateParents' => true,
//]);
