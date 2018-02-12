<?php

use app\models\ItemType;
use app\components\ReturnUrl;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */


$items = [];


$quoteItems = [];
$quoteItems[] = [
    'label' => Yii::t('app', 'View'),
    'url' => ['view', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
    'active' => Yii::$app->controller->action->id == 'view',
];
if (in_array($model->status, ['job/draft', 'job/quote'])) {
    $quoteItems[] = [
        'label' => Yii::t('app', 'Quote Approval'),
        'url' => ['//approval/quote', 'id' => $model->id, 'key' => md5($model->id . '.' . getenv('APP_COOKIE_VALIDATION_KEY'))],
        'linkOptions' => [
            'target' => '_blank',
        ],
        'encode' => false,
    ];
}
if ($model->status == 'job/draft') {
    $quoteItems[] = [
        'label' => Yii::t('app', 'PDF Preview'),
        'url' => ['quote-pdf', 'id' => $model->id, 'time' => time()],
        'linkOptions' => [
            'target' => '_blank',
        ],
        'encode' => false,
    ];
} else {
    if ($model->quotePdfs) {
        foreach ($model->quotePdfs as $quotePdf) {
            $quoteItems[] = [
                'label' => Yii::t('app', 'PDF') . ' - ' . Yii::$app->formatter->asDatetime($quotePdf->created_at),
                'url' => $quotePdf->getFileUrl(),
                'linkOptions' => [
                    'target' => '_blank',
                ],
            ];
        }
    }
}
if ($quoteItems) {
    $items[] = [
        'label' => '<i class="fa fa-comment-o"></i> ' . Yii::t('app', 'Quote'),
        'url' => ['quote', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'items' => $quoteItems,
        'active' => Yii::$app->controller->action->id == 'quote',
        'encode' => false,
    ];
}

$items[] = [
    'label' => '<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Update'),
    'url' => ['update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
    'encode' => false,
    'active' => Yii::$app->controller->action->id == 'update'
];
$items[] = [
    'label' => '<i class="fa fa-sort"></i> ' . Yii::t('app', 'Sort'),
    'url' => ['sort', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
    'active' => Yii::$app->controller->action->id == 'sort',
    'encode' => false,
];


//$copyItems = [];
//if (in_array($model->status, ['job/draft', 'job/quote', 'job/cancelled', 'job/production', 'job/productionPending'])) {
//    $copyItems[] = [
//        'label' => Yii::t('app', 'Version'),
//        'url' => ['status', 'id' => $model->id, 'fork' => 1, 'ru' => ReturnUrl::urlToToken(Url::to(['/job/version', 'id' => $model->id]))],
//        'encode' => false,
//        'linkOptions' => [
//            'class' => 'modal-remote',
//            //'data-confirm' => Yii::t('app', 'Are you sure you want to version fork?'),
//        ],
//        'active' => Yii::$app->controller->action->id == 'version',
//    ];
//}
//$copyItems[] = [
//    'label' => Yii::t('app', 'Copy'),
//    'url' => ['copy', 'id' => $model->id],
//    'encode' => false,
//    'active' => Yii::$app->controller->action->id == 'copy',
//];
//if ($copyItems) {
//    $items[] = [
//        'label' => '<i class="fa fa-copy"></i> ' . Yii::t('app', 'Copy'),
//        'url' => ['copy', 'id' => $model->id],
//        'encode' => false,
//        'items' => $copyItems,
//    ];
//}

$this->params['nav'] = $items;

//echo Nav::widget([
//    'items' => $items,
//    'options' => ['class' => 'nav-tabs'],
//    'activateParents' => true,
//]);
