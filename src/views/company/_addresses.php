<?php

use app\models\Address;
use app\components\ReturnUrl;
use cornernote\shortcuts\Y;
use kartik\grid\GridView;
use yii\bootstrap\Alert;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Company $model
 */

$company = $model;

$createAddressLink = '';
if (Y::user()->can('app_company_shipping-address', ['route' => true])) {
    $createAddressLink = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Add Address'), [
        'company/shipping-address',
        'id' => $model->id,
        'ru' => ReturnUrl::getToken()
    ], [
        'class' => 'btn btn-primary btn-xs modal-remote',
    ]);
}

$importAddressLink = '';
if (Y::user()->can('app_company_shipping-address-import', ['route' => true])) {
    $importAddressLink = Html::a('<i class="fa fa-upload"></i> ' . Yii::t('app', 'Import Address CSV'), [
        'company/shipping-address-import',
        'id' => $model->id,
        'ru' => ReturnUrl::getToken()
    ], [
        'class' => 'btn btn-default btn-xs modal-remote',
    ]);
}


$columns = [];
$columns[] = [
    'attribute' => 'type',
    'value' => function ($model) use ($company) {
        /** @var Address $model */
        $items = [];
        if ($model->type == Address::TYPE_SHIPPING) {
            if (Y::user()->can('app_company_shipping-address', ['route' => true])) {
                $items[] = Html::a('<i class="fa fa-pencil"></i>', ['//company/shipping-address', 'id' => $company->id, 'address_id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                    'title' => Yii::t('app', 'Update'),
                    'class' => 'modal-remote',
                    'data-toggle' => 'tooltip',
                ]);
            }
            if (Y::user()->can('app_company_shipping-address-delete', ['route' => true])) {
                $items[] = Html::a('<i class="fa fa-trash"></i>', ['//company/shipping-address-delete', 'id' => $company->id, 'address_id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                    'title' => Yii::t('app', 'Delete'),
                    'data-confirm' => Yii::t('app', 'Are you sure?'),
                    'data-method' => 'post',
                    'data-toggle' => 'tooltip',
                    //'data-pjax' => 0,
                ]);
            }
        }
        $type = $model->type ? Address::optsType()[$model->type] : null;
        $links = implode(' ', $items);
        return $type . ' ' . $links;
    },
    'format' => 'raw',
];
$columns[] = [
    'attribute' => 'label',
    'value' => function ($model) {
        /** @var Address $model */
        return $model->getLabel('<br>');
    },
    'format' => 'raw',
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
    'panel' => [
        'heading' => '', //Yii::t('app', 'Addresses'),
        'footer' => false,
        'before' => false,
        'after' => false,
        'type' => GridView::TYPE_DEFAULT,
    ],
    'panelHeadingTemplate' => '<div class="pull-right">' . trim($importAddressLink . ' ' . $createAddressLink) . '</div><h3 class="panel-title">{heading}</h3><div class="clearfix"></div>',
    'showHeader' => false,
    'striped' => false,
    'bordered' => false,
]);