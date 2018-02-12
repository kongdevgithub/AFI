<?php

use app\models\Address;
use app\components\ReturnUrl;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$job = $model;

$createAddressLink = '';
$createAddressLink = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Add Address'), [
    'job/shipping-address',
    'id' => $model->id,
    'ru' => ReturnUrl::getToken()
], [
    'class' => 'btn btn-primary btn-xs modal-remote',
]);

$columns = [];
if (Yii::$app->controller->action->id == 'despatch') {
    $columns[] = [
        'class' => '\kartik\grid\CheckboxColumn'
    ];
}
$columns[] = [
    'attribute' => 'type',
    'value' => function ($model) use ($job) {
        /** @var Address $model */
        $items = [];
        if ($model->type == Address::TYPE_BILLING) {
            $items[] = Html::a('<i class="fa fa-pencil"></i>', ['//client/job/billing-address', 'id' => $job->id, 'ru' => ReturnUrl::getToken()], [
                'title' => Yii::t('app', 'Update'),
                'class' => 'modal-remote',
                'data-toggle' => 'tooltip',
            ]);
        }
        if ($model->type == Address::TYPE_SHIPPING) {
            $items[] = Html::a('<i class="fa fa-pencil"></i>', ['//client/job/shipping-address', 'id' => $job->id, 'address_id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'title' => Yii::t('app', 'Update'),
                'class' => 'modal-remote',
                'data-toggle' => 'tooltip',
            ]);
            $items[] = Html::a('<i class="fa fa-trash"></i>', ['//client/job/shipping-address-delete', 'id' => $job->id, 'address_id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                'title' => Yii::t('app', 'Delete'),
                'data-confirm' => Yii::t('app', 'Are you sure?'),
                'data-method' => 'post',
                'data-toggle' => 'tooltip',
                //'data-pjax' => 0,
            ]);
        }
        $type = $model->type ? Address::optsType()[$model->type] : null;
        $links = implode(' ', $items);
        return $type . ' ' . $links;
    },
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'label',
    'value' => function ($model) use ($job) {
        /** @var Address $model */
        if ($model->type == Address::TYPE_BILLING) {
            $pieces = [
                $model->name,
                trim($model->street),
                trim($model->city . ' ' . $model->postcode . ' ' . $model->state),
            ];
            if ($model->country != 'Australia') {
                $pieces[] = $model->country;
            }
            if ($model->contact) {
                $pieces[] = 'ATTN: ' . trim($model->contact);
            } else {
                $pieces[] = 'ATTN: ' . trim($job->contact->label);
            }
            if ($model->phone) {
                $pieces[] = 'PH: ' . trim($model->phone);
            } else {
                $pieces[] = 'PH: ' . trim($job->contact->phone);
            }
            if ($model->instructions) {
                $pieces[] = trim($model->instructions);
            }
            return implode('<br>', $pieces);
        }
        return $model->getLabel('<br>');
    },
    'format' => 'raw',
];

$panel = [
    'heading' => Yii::t('app', 'Addresses'),
    'footer' => false,
    'before' => false,
    'after' => false,
    'type' => GridView::TYPE_DEFAULT,
];

echo GridView::widget([
    'id' => 'address-grid',
    'dataProvider' => new ActiveDataProvider([
        'query' => $model->getAddresses()->orderBy(['type' => SORT_ASC]),
        'pagination' => ['pageSize' => 1000],
        'sort' => false,
    ]),
    'layout' => '{items}',
    'columns' => $columns,
    'panel' => $panel,
    'panelHeadingTemplate' => '<div class="pull-right">' . trim($createAddressLink) . '</div><h3 class="panel-title">{heading}</h3><div class="clearfix"></div>',
    'showHeader' => false,
    'striped' => false,
    'bordered' => false,
]);

