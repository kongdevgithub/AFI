<?php

use kartik\file\FileInput;
use yii\bootstrap\Tabs;
use yii\helpers\Json;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Company $model
 */

ob_start();
(new FileInput(['name' => 'fake']))->registerAssets();
ob_end_clean();

$this->title = $model->name;
//$this->params['heading'] = $model->name;
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->params['heading'];
?>
<div class="company-view">

    <?= $this->render('_menu', compact('model')); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Company'); ?></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            $attributes = [];
                            $attributes[] = 'name';
                            $attributes[] = 'phone';
                            $attributes[] = 'fax';
                            $attributes[] = 'website';
                            $attributes[] = [
                                'attribute' => 'staff_rep_id',
                                'value' => $model->staffRep ? $model->staffRep->label : null,
                            ];
                            $attributes[] = [
                                'attribute' => 'price_structure_id',
                                'value' => $model->priceStructure ? $model->priceStructure->name : null,
                            ];
                            $attributes[] = [
                                'attribute' => 'account_term_id',
                                'value' => $model->accountTerm ? $model->accountTerm->name : null,
                            ];
                            $attributes[] = 'purchase_order_required:boolean';
                            $attributes[] = [
                                'attribute' => 'job_type_id',
                                'value' => $model->jobType ? $model->jobType->name : null,
                            ];
                            $attributes[] = [
                                'attribute' => 'industry_id',
                                'value' => $model->industry ? $model->industry->name : null,
                            ];
                            $attributes[] = [
                                'attribute' => 'delivery_docket_header',
                                'value' => $model->delivery_docket_header,
                                'format' => 'raw',
                            ];
                            //if (Yii::$app->user->can('admin')) {
                            //    $attributes[] = [
                            //        'attribute' => 'rates',
                            //        'value' => $model->rates_encoded ? '<pre>' . Json::encode($model->getRates(), JSON_PRETTY_PRINT) . '</pre>' : '',
                            //        'format' => 'raw',
                            //    ];
                            //}
                            echo DetailView::widget([
                                'model' => $model,
                                'attributes' => $attributes,
                            ]);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <?= Tabs::widget([
                                    'encodeLabels' => false,
                                    'items' => [
                                        [
                                            'label' => Yii::t('app', 'Contacts'),
                                            'content' => $this->render('_contacts', ['model' => $model]),
                                            'active' => true,
                                        ],
                                        [
                                            'label' => Yii::t('app', 'Addresses'),
                                            'content' => $this->render('_addresses', ['model' => $model]),
                                            'active' => false,
                                        ],
                                    ]
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Status'); ?></h3>
                </div>
                <div class="box-body">
                    <?= $model->getStatusButton() ?>
                </div>
            </div>
            <?= $this->render('/note/_index', ['model' => $model]) ?>
        </div>
    </div>

</div>
