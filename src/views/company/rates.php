<?php

use app\components\GridView;
use app\models\CompanyRate;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Company $model
 */

$this->title = $model->name;
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="company-rates">

    <?= $this->render('_menu', ['model' => $model]); ?>

    <?php

    $columns = [];
    $columns[] = [
        'attribute' => 'id',
        'value' => function ($model) {
            /** @var CompanyRate $model */
            if (Yii::$app->user->can('app_company-rate_view', ['route' => true])) {
                return Html::a($model->id, ['//company-rate/view', 'id' => $model->id,]);
            }
            return $model->id;
        },
        'format' => 'raw',
        //'enableSorting' => false,
    ];
    $columns[] = [
        'attribute' => 'product_type_id',
        'value' => function ($model) {
            /** @var CompanyRate $model */
            if ($model->productType) {
                if (Yii::$app->user->can('app_product-type-view', ['route' => true])) {
                    return Html::a($model->productType->name, ['//product-type/view', 'id' => $model->productType->id,]);
                }
                return $model->productType->name;
            }
            return '';
        },
        'format' => 'raw',
        //'enableSorting' => false,
    ];
    $columns[] = [
        'attribute' => 'item_type_id',
        'value' => function ($model) {
            /** @var CompanyRate $model */
            if ($model->itemType) {
                if (Yii::$app->user->can('app_item-type-view', ['route' => true])) {
                    return Html::a($model->itemType->name, ['//item-type/view', 'id' => $model->itemType->id,]);
                }
                return $model->itemType->name;
            }
            return '';
        },
        'format' => 'raw',
        //'enableSorting' => false,
    ];
    $columns[] = [
        'attribute' => 'option_id',
        'value' => function ($model) {
            /** @var CompanyRate $model */
            if ($model->option) {
                if (Yii::$app->user->can('app_option-view', ['route' => true])) {
                    return Html::a($model->option->name, ['//option/view', 'id' => $model->option->id,]);
                }
                return $model->option->name;
            }
            return '';
        },
        'format' => 'raw',
        //'enableSorting' => false,
    ];
    $columns[] = [
        'attribute' => 'component_id',
        'value' => function ($model) {
            /** @var CompanyRate $model */
            if ($model->component) {
                if (Yii::$app->user->can('app_component-view', ['route' => true])) {
                    return Html::a($model->component->code . ' ' . $model->component->name, ['//component/view', 'id' => $model->component->id,]);
                }
                return $model->component->code . ' ' . $model->component->name;
            }
            return '';
        },
        'format' => 'raw',
    ];
    $columns[] = 'size';
    $columns[] = [
        'label' => 'Options',
        'value' => function ($model) {
            /** @var CompanyRate $model */
            return $model->getCompanyRateOptionsHtml();
        },
        'format' => 'raw',
    ];
    $columns[] = [
        'attribute' => 'price',
        'hAlign' => 'right',
    ];

    $gridActions = [];
    if (Yii::$app->user->can('app_company-rate_create', ['route' => true])) {
        $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
            '//company-rate/create',
            'CompanyRate' => ['company_id' => $model->id],
            'ru' => ReturnUrl::getToken()
        ], ['class' => 'btn btn-default btn-xs']);
    }
    if (Yii::$app->user->can('app_company_rate-import', ['route' => true])) {
        $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Import'), [
            '//company/rate-import',
            'id' => $model->id,
            'ru' => ReturnUrl::getToken()
        ], ['class' => 'btn btn-default btn-xs']);
    }

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => $columns,
        //'multiActions' => $multiActions,
        'gridActions' => $gridActions,
        'panel' => [
            'heading' => Yii::t('app', 'Company Rates'),
        ],
    ]);

    ?>

</div>
