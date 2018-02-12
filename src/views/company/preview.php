<?php

use app\components\Helper;
use app\models\Address;
use app\models\Job;
use app\models\search\JobSearch;
use app\widgets\Nav;
use kartik\form\ActiveForm;
use raoul2000\workflow\helpers\WorkflowHelper;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Company $model
 */

$this->title = $model->name;
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-preview">

    <div class="row">
        <div class="col-md-8">

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'name',
                    'phone',
                    'fax',
                    'website',
                    [
                        'attribute' => 'staff_rep_id',
                        'value' => $model->staffRep ? $model->staffRep->label : null,
                    ],
                    [
                        'attribute' => 'price_structure_id',
                        'value' => $model->priceStructure ? $model->priceStructure->name : null,
                    ],
                    [
                        'attribute' => 'account_term_id',
                        'value' => $model->accountTerm ? $model->accountTerm->name : null,
                    ],
                    [
                        'attribute' => 'job_type_id',
                        'value' => $model->jobType ? $model->jobType->name : null,
                    ],
                    [
                        'attribute' => 'industry_id',
                        'value' => $model->industry ? $model->industry->name : null,
                    ],
                    [
                        'attribute' => 'default_contact_id',
                        'value' => $model->defaultContact ? $model->defaultContact->label : null,
                    ],
                    //[
                    //    'attribute' => 'rates',
                    //    'value' => $model->rates_encoded ? '<pre>' . Json::encode($model->getRates(), JSON_PRETTY_PRINT) . '</pre>' : '',
                    //    'format' => 'raw',
                    //],
                ],
            ]); ?>

        </div>
        <div class="col-md-4">
            <?php
            $items = [];
            $items[] = [
                'label' => Yii::t('app', 'View Company'),
                'url' => ['//company/view', 'id' => $model->id],
            ];
            $items[] = [
                'label' => Yii::t('app', 'View Jobs'),
                'url' => ['//job/index', 'JobSearch' => ['company_id' => $model->id]],
            ];
            echo Nav::widget([
                'options' => ['class' => 'list-unstyled'],
                'encodeLabels' => false,
                'items' => $items,
            ]) ?>
        </div>
    </div>


</div>
