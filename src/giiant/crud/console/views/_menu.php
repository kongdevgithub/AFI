<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var schmunk42\giiant\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use cornernote\returnurl\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 */

$items = [];

$items[] = [
    'label' => Yii::t('app', 'View'),
    'url' => ['view', <?= $urlParams ?>, 'ru' => ReturnUrl::getRequestToken()],
    'active' => Yii::$app->controller->action->id == 'view',
];
if (Yii::$app->user->can('app_<?= Inflector::camel2id(StringHelper::basename($generator->modelClass),'-', true) ?>_update', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Update'),
        'url' => ['update', <?= $urlParams ?>, 'ru' => ReturnUrl::getToken()],
        'encode' => false,
        'active' => Yii::$app->controller->action->id == 'update'
    ];
}
if (Yii::$app->user->can('app_<?= Inflector::camel2id(StringHelper::basename($generator->modelClass),'-', true) ?>_copy', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-copy"></i> ' . Yii::t('app', 'Copy'),
        'url' => ['copy', <?= $urlParams ?>, 'ru' => ReturnUrl::getToken()],
        'encode' => false,
        'active' => Yii::$app->controller->action->id == 'copy'
    ];
}
if (Yii::$app->user->can('app_<?= Inflector::camel2id(StringHelper::basename($generator->modelClass),'-', true) ?>_delete', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-trash"></i> ' . Yii::t('app', 'Delete'),
        'url' => ['delete', <?= $urlParams ?>],
        'linkOptions' => [
            'data-confirm' => Yii::t('app', 'Are you sure?'),
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
