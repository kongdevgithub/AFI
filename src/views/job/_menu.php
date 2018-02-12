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
if (Yii::$app->user->can('app_job_quote', ['route' => true])) {
    $quoteItems[] = [
        'label' => Yii::t('app', 'Quote'),
        'url' => ['quote', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'quote',
    ];
}
if (Yii::$app->user->can('app_job_delivery', ['route' => true])) {
    $quoteItems[] = [
        'label' => Yii::t('app', 'Delivery'),
        'url' => ['delivery', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'delivery',
    ];
}
if (Yii::$app->user->can('app_job_price', ['route' => true])) {
    $quoteItems[] = [
        'label' => Yii::t('app', 'Bulk Prices'),
        'url' => ['price', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'price',
    ];
}
if (Yii::$app->user->can('app_job_preserve-unit-prices', ['route' => true])) {
    $quoteItems[] = [
        'label' => Yii::t('app', 'Preserve All Unit Prices'),
        'url' => ['preserve-unit-prices', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'preserve-unit-prices',
        'linkOptions' => [
            'data-confirm' => Yii::t('app', 'Are you sure?'),
        ],
    ];
}
if (Yii::$app->user->can('app_job_preserve-unit-prices', ['route' => true])) {
    $quoteItems[] = [
        'label' => Yii::t('app', 'Un-Preserve All Unit Prices'),
        'url' => ['preserve-unit-prices', 'id' => $model->id, 'switch' => 'off', 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'preserve-unit-prices',
        'linkOptions' => [
            'data-confirm' => Yii::t('app', 'Are you sure?'),
        ],
    ];
}
if (Yii::$app->user->can('app_job_re-quote', ['route' => true])) {
    $quoteItems[] = [
        'label' => Yii::t('app', 'ReQuote'),
        'url' => ['re-quote', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
    ];
}
if (in_array($model->status, ['job/draft', 'job/quote']) && Yii::$app->user->can('csr')) {
    $quoteItems[] = [
        'label' => Yii::t('app', 'Quote Approval'),
        'url' => ['//approval/quote', 'id' => $model->id, 'key' => md5($model->id . '.' . getenv('APP_COOKIE_VALIDATION_KEY'))],
        'linkOptions' => [
            'target' => '_blank',
        ],
    ];
}
if ($model->status == 'job/draft') {
    if (Yii::$app->user->can('app_job_quote-pdf', ['route' => true])) {
        $quoteItems[] = [
            'label' => Yii::t('app', 'PDF Preview'),
            'url' => ['quote-pdf', 'id' => $model->id, 'time' => time()],
            'linkOptions' => [
                'target' => '_blank',
            ],
        ];
    }
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
        if (Yii::$app->user->can('app_job_quote-pdf-attach', ['route' => true])) {
            $quoteItems[] = [
                'label' => Yii::t('app', 'PDF - Attach New'),
                'url' => ['quote-pdf-attach', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
                'linkOptions' => [
                    'data-confirm' => Yii::t('app', 'Are you sure you want to attach a new quote PDF?'),
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

$productionItems = [];
if (Yii::$app->user->can('app_job_production', ['route' => true])) {
    $productionItems[] = [
        'label' => Yii::t('app', 'Production'),
        'url' => ['production', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
    ];
}

$productionItems[] = [
    'label' => Yii::t('app', 'Artwork PDF'),
    'url' => ['/approval/artwork-pdf', 'id' => $model->id, 'key' => md5($model->id . '.' . getenv('APP_COOKIE_VALIDATION_KEY'))],
    'linkOptions' => [
        'target' => '_blank',
    ],
];

$productionItems[] = [
    'label' => Yii::t('app', 'Artwork Approval'),
    'url' => ['/approval/artwork', 'id' => $model->id, 'key' => md5($model->id . '.' . getenv('APP_COOKIE_VALIDATION_KEY'))],
    'linkOptions' => [
        'target' => '_blank',
    ],
];

if (Yii::$app->user->can('app_job_artwork-email', ['route' => true])) {
    $productionItems[] = [
        'label' => Yii::t('app', 'Email Artwork Approval'),
        'url' => ['artwork-email', 'id' => $model->id],
        'linkOptions' => [
            'class' => 'modal-remote',
        ],
    ];
}

//if (Yii::$app->user->can('staff')) {
//    $productionItems[] = [
//        'label' => Yii::t('app', 'Artwork Approval'),
//        'url' => ['//approval/artwork', 'id' => $model->id, 'key' => md5($model->id . '.' . getenv('APP_COOKIE_VALIDATION_KEY'))],
//        'linkOptions' => [
//            'target' => '_blank',
//        ],
//    ];
//}
if ($productionItems) {
    $items[] = [
        'label' => '<i class="fa fa-legal"></i> ' . Yii::t('app', 'Production'),
        'url' => ['production', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'production',
        'items' => $productionItems,
        'encode' => false,
    ];
}

$despatchItems = [];
if (Yii::$app->user->can('app_job_despatch', ['route' => true])) {
    $despatchItems[] = [
        'label' => Yii::t('app', 'Despatch'),
        'url' => ['despatch', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'despatch',
    ];
}
if (Yii::$app->user->can('app_job_boxes', ['route' => true])) {
    $despatchItems[] = [
        'label' => Yii::t('app', 'Boxes'),
        'url' => ['boxes', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'boxes',
    ];
}
if (Yii::$app->user->can('app_job_bulk-package', ['route' => true])) {
    $despatchItems[] = [
        'label' => Yii::t('app', 'Bulk Package'),
        'url' => ['bulk-package', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'bulk-package',
    ];
}
if ($despatchItems) {
    $items[] = [
        'label' => '<i class="fa fa-truck"></i> ' . Yii::t('app', 'Despatch'),
        'url' => ['despatch', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => in_array(Yii::$app->controller->action->id, ['despatch', 'boxes']),
        'items' => $despatchItems,
        'encode' => false,
    ];
}

if (Yii::$app->user->can('app_job_finance', ['route' => true])) {
    $financeItems = [];
    $financeItems[] = [
        'label' => Yii::t('app', 'Finance'),
        'url' => ['finance', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'finance',
    ];
    $financeItems[] = [
        'label' => Yii::t('app', 'Invoice PDF'),
        'url' => ['invoice-pdf', 'id' => $model->id, 'time' => time()],
        'linkOptions' => [
            'target' => '_blank',
        ],
    ];
    $financeItems[] = [
        'label' => Yii::t('app', 'Email Invoice'),
        'url' => ['invoice-email', 'id' => $model->id],
        'linkOptions' => [
            'class' => 'modal-remote',
        ],
    ];
    if ($financeItems) {
        $items[] = [
            'label' => '<i class="fa fa-dollar"></i> ' . Yii::t('app', 'Finance'),
            'url' => ['finance', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
            'items' => $financeItems,
            'active' => Yii::$app->controller->action->id == 'finance',
            'encode' => false,
        ];
    }
}

$printItems = [];
if (Yii::$app->user->can('app_job_production-pdf', ['route' => true])) {
    $printItems[] = [
        'label' => Yii::t('app', 'Print'),
        'url' => ['print', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
        'linkOptions' => ['class' => 'modal-remote'],
    ];
    $printItems[] = [
        'label' => Yii::t('app', 'Printing PDF'),
        'url' => ['production-pdf', 'id' => $model->id, 'item_types' => ItemType::ITEM_TYPE_PRINT, 'time' => time()],
        'linkOptions' => ['target' => '_blank'],
    ];
    $printItems[] = [
        'label' => Yii::t('app', 'Fabrication PDF'),
        'url' => ['production-pdf', 'id' => $model->id, 'item_types' => ItemType::ITEM_TYPE_FABRICATION, 'time' => time()],
        'linkOptions' => ['target' => '_blank'],
    ];
}
if ($printItems) {
    $items[] = [
        'label' => '<i class="fa fa-print"></i> ' . Yii::t('app', 'Print'),
        'url' => ['production', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'print',
        'items' => $printItems,
        'encode' => false,
    ];
}

if (Yii::$app->user->can('app_job_update', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Update'),
        'url' => ['update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()],
        'encode' => false,
        'active' => Yii::$app->controller->action->id == 'update'
    ];
}
if (Yii::$app->user->can('app_job_sort', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-sort"></i> ' . Yii::t('app', 'Sort'),
        'url' => ['sort', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'sort',
        'encode' => false,
    ];
}


$copyItems = [];
if (in_array($model->status, ['job/draft', 'job/quote', 'job/cancelled', 'job/production', 'job/productionPending']) && Yii::$app->user->can('app_job_version', ['route' => true])) {
    $copyItems[] = [
        'label' => Yii::t('app', 'Version'),
        'url' => ['status', 'id' => $model->id, 'fork' => 1, 'ru' => ReturnUrl::urlToToken(Url::to(['/job/version', 'id' => $model->id]))],
        'linkOptions' => [
            'class' => 'modal-remote',
            //'data-confirm' => Yii::t('app', 'Are you sure you want to version fork?'),
        ],
        'active' => Yii::$app->controller->action->id == 'version',
    ];
}
if (Yii::$app->user->can('app_job_copy', ['route' => true])) {
    $copyItems[] = [
        'label' => Yii::t('app', 'Copy'),
        'url' => ['copy', 'id' => $model->id],
        'active' => Yii::$app->controller->action->id == 'copy',
    ];
}
if (in_array($model->status, ['job/production', 'job/despatch', 'job/complete', 'job/cancelled']) && Yii::$app->user->can('app_job_redo', ['route' => true])) {
    $copyItems[] = [
        'label' => Yii::t('app', 'Redo'),
        'url' => ['redo', 'id' => $model->id],
        'active' => Yii::$app->controller->action->id == 'redo',
    ];
}
if ($copyItems) {
    $items[] = [
        'label' => '<i class="fa fa-copy"></i> ' . Yii::t('app', 'Copy'),
        'url' => ['copy', 'id' => $model->id],
        'encode' => false,
        'items' => $copyItems,
    ];
}


$linkItems = [];
if ($model->hubSpotDeal) {
    $linkItems[] = [
        'label' => Yii::t('app', 'HubSpot Deal'),
        'url' => 'https://app.hubspot.com/sales/2659477/deal/' . $model->hubSpotDeal->hub_spot_id . '/',
        'linkOptions' => [
            'target' => '_blank',
        ],
    ];
}
if ($model->dearSale) {
    $linkItems[] = [
        'label' => Yii::t('app', 'Dear Sale'),
        'url' => 'https://inventory.dearsystems.com/Sale#' . $model->dearSale->dear_id,
        'linkOptions' => [
            'target' => '_blank',
        ],
    ];
}
$linkItems[] = [
    'label' => Yii::t('app', 'Dear Push'),
    'url' => ['dear-push', 'id' => $model->id, 'force' => 1, 'ru' => ReturnUrl::getToken()],
    'linkOptions' => [
        'data-confirm' => Yii::t('app', 'Are you sure?'),
    ],
];
if ($linkItems) {
    $items[] = [
        'label' => '<i class="fa fa-link"></i> ' . Yii::t('app', 'Links'),
        'url' => '#',
        'encode' => false,
        'items' => $linkItems,
    ];
}

$auditItems = [];
if (Yii::$app->user->can('app_job_log', ['route' => true])) {
    $auditItems[] = [
        'label' => Yii::t('app', 'Log'),
        'url' => ['log', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'log',
    ];
}
if (Yii::$app->user->can('app_job_trail', ['route' => true])) {
    $auditItems[] = [
        'label' => Yii::t('app', 'Trail'),
        'url' => ['trail', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'trail',
    ];
}
if (Yii::$app->user->can('app_job_status-history', ['route' => true])) {
    $auditItems[] = [
        'label' => Yii::t('app', 'Status History'),
        'url' => ['status-history', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()],
        'active' => Yii::$app->controller->action->id == 'status-history',
    ];
}
if ($auditItems) {
    $items[] = [
        'label' => '<i class="fa fa-database"></i> ' . Yii::t('app', 'Audit'),
        'url' => '#',
        'encode' => false,
        'items' => $auditItems,
    ];
}

if (Yii::$app->user->can('app_job_delete', ['route' => true])) {
    $items[] = [
        'label' => '<i class="fa fa-trash"></i> ' . Yii::t('app', 'Delete'),
        'url' => ['delete', 'id' => $model->id],
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
