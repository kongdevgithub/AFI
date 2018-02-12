<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use dmstr\bootstrap\Tabs;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Pickup $model
 */

$this->title = Yii::t('app', 'Pickup') . ' ' . $model->id;
//$this->params['heading'] = $model->id;
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pickups'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->params['heading'];
?>
<div class="pickup-view">

    <?= $this->render('_menu', compact('model')); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Pickup'); ?></h3>
                    <div class="box-tools pull-right">
                        <?= Html::a('<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Update'), ['update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], ['class' => 'btn btn-box-tool']) ?>
                        <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-box-tool',
                            'data-confirm' => Yii::t('app', 'Are you sure?'),
                            'data-method' => 'post',
                        ]); ?>
                    </div>
                </div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'carrier_id',
                                'value' => $model->carrier ? Html::a($model->carrier->name, ['/carrier/view', 'id' => $model->carrier->id,]) : '',
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'carrier_ref',
                                'value' => $model->getTrackingLink(),
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'pod_date',
                                'format' => 'dateTime',
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Status'); ?></h3>
                    <div class="box-tools pull-right text-right">
                        <?php //echo $model->getIcons(); ?>
                    </div>
                </div>
                <div class="box-body">
                    <?= $model->getStatusButtons(true) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Packages'); ?></h3>
            <div class="box-tools pull-right text-right">
                <?= Html::a('<span class="fa fa-plus"></span> ' . Yii::t('app', 'Assign Package'), ['package', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                    'class' => 'btn btn-box-tool modal-remote',
                ]) ?>
            </div>
        </div>
        <div class="box-body">
            <?= $this->render('/pickup/_packages', ['model' => $model]) ?>
        </div>
    </div>

</div>
