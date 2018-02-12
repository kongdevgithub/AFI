<?php

use app\models\Contact;
use cornernote\shortcuts\Y;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Company $model
 */

$company = $model;

$createContactLink = '';
if (Y::user()->can('app_contact_create', ['route' => true])) {
    $createContactLink = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'New Contact'), [
        'contact/create',
        'Contact' => ['default_company_id' => $model->id],
        'ru' => ReturnUrl::getToken()
    ], [
        'class' => 'btn btn-primary btn-xs modal-remote',
    ]);
}

$assignContactLink = '';
if (Y::user()->can('app_company_contact-assign', ['route' => true])) {
    $assignContactLink = Html::a('<i class="fa fa-link"></i> ' . Yii::t('app', 'Assign Contact'), [
        'company/contact-assign',
        'id' => $model->id,
        'ru' => ReturnUrl::getToken()
    ], [
        'class' => 'btn btn-default btn-xs modal-remote',
    ]);
}

$columns = [];
$columns[] = [
    'label' => Yii::t('app', 'Name'),
    'value' => function ($model) {
        /** @var Contact $model */
        return $model->getLink($model->label);
    },
    'format' => 'raw',
];
$columns[] = 'phone';
$columns[] = 'email:email';
$columns[] = [
    'label' => Yii::t('app', 'links'),
    'value' => function ($model) use ($company) {
        /** @var Contact $model */
        $links = [];
        if ($company->default_contact_id == $model->id) {
            $links[] = Html::tag('span', '', ['class' => 'fa fa-star']);
        } else {
            $links[] = Html::a(Html::tag('span', '', ['class' => 'fa fa-star-o']), ['company/contact-default', 'id' => $company->id, 'contact_id' => $model->id], [
                'data-confirm' => Yii::t('app', 'Are you sure?'),
                'data-method' => 'post',
            ]);
            $links[] = Html::a(Html::tag('span', '', ['class' => 'fa fa-unlink']), ['company/contact-unassign', 'id' => $company->id, 'contact_id' => $model->id], [
                'data-confirm' => Yii::t('app', 'Are you sure?'),
                'data-method' => 'post',
            ]);
        }
        return implode(' ', $links);
    },
    'format' => 'raw',
];

echo GridView::widget([
    'dataProvider' => new ActiveDataProvider(['query' => $model->getContacts(), 'pagination' => ['pageSize' => 1000, 'pageParam' => 'page-contacts']]),
    'columns' => $columns,
    'panel' => [
        'heading' => '', //Yii::t('app', 'Contacts'),
        'footer' => false,
        'before' => false,
        'after' => false,
        'type' => GridView::TYPE_DEFAULT,
    ],
    'panelHeadingTemplate' => '<div class="pull-right">' . trim($assignContactLink . ' ' . $createContactLink) . '</div><h3 class="panel-title">{heading}</h3><div class="clearfix"></div>',
    'showHeader' => false,
]);
