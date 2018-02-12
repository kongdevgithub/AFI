<?php

use app\models\Company;
use cornernote\shortcuts\Y;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Contact $model
 */

$contact = $model;

$createCompanyLink = '';
if (Y::user()->can('app_company_create', ['route' => true])) {
    $createCompanyLink = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'New Company'), [
        'company/create',
        'Company' => ['default_contact_id' => $model->id],
        'ru' => ReturnUrl::getToken()
    ], [
        'class' => 'btn btn-primary btn-xs modal-remote',
    ]);
}

$assignCompanyLink = '';
if (Y::user()->can('app_contact_company-assign', ['route' => true])) {
    $assignCompanyLink = Html::a('<i class="fa fa-link"></i> ' . Yii::t('app', 'Assign Company'), [
        'contact/company-assign',
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
        /** @var Company $model */
        return $model->getLink($model->name);
    },
    'format' => 'raw',
];
$columns[] = 'phone';
$columns[] = [
    'label' => Yii::t('app', 'links'),
    'value' => function ($model) use ($contact) {
        /** @var Company $model */
        $links = [];
        if ($contact->default_company_id == $model->id) {
            $links[] = Html::tag('span', '', ['class' => 'fa fa-star']);
        } else {
            $links[] = Html::a(Html::tag('span', '', ['class' => 'fa fa-star-o']), ['contact/company-default', 'id' => $contact->id, 'company_id' => $model->id], [
                'data-confirm' => Yii::t('app', 'Are you sure?'),
                'data-method' => 'post',
            ]);
            $links[] = Html::a(Html::tag('span', '', ['class' => 'fa fa-unlink']), ['contact/company-unassign', 'id' => $contact->id, 'company_id' => $model->id], [
                'data-confirm' => Yii::t('app', 'Are you sure?'),
                'data-method' => 'post',
            ]);
        }
        return implode(' ', $links);
    },
    'format' => 'raw',
];

echo GridView::widget([
    'dataProvider' => new ActiveDataProvider(['query' => $model->getCompanies(), 'pagination' => ['pageSize' => 1000, 'pageParam' => 'page-companies']]),
    'columns' => $columns,
    'panel' => [
        'heading' => '', //Yii::t('app', 'Companies'),
        'footer' => false,
        'before' => false,
        'after' => false,
        'type' => GridView::TYPE_DEFAULT,
    ],
    'panelHeadingTemplate' => '<div class="pull-right">' . trim($assignCompanyLink . ' ' . $createCompanyLink) . '</div><h3 class="panel-title">{heading}</h3><div class="clearfix"></div>',
    'showHeader' => false,
]);
