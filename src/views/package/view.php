<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use dmstr\bootstrap\Tabs;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Package $model
 */

$this->title = Yii::t('app', 'Package') . ' ' . $model->id;

//$this->params['heading'] = $model->id;
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Packages'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->params['heading'];
?>
<div class="package-view">

    <?= $this->render('_menu', compact('model')); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Package'); ?></h3>
                </div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'pickup_id',
                                'value' => $model->pickup ? $model->pickup->getLink() : null,
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'cartons',
                                'visible' => $model->cartons > 1,
                            ],
                            [
                                'attribute' => 'cartonCountLabel',
                            ],
                            [
                                'label' => Yii::t('app', 'Size'),
                                'value' => $model->getDimensionsLabel(),
                                'format' => 'raw',
                            ],
                            [
                                'label' => Yii::t('app', 'Address'),
                                'value' => $model->address ? $model->address->getLabel('<br>') : null,
                                'format' => 'raw',
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

    <div class="row">
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Units'); ?></h3>
                </div>
                <div class="box-body">
                    <?= $this->render('/package/_item_quantity', ['model' => $model->overflow_package_id ? $model->overflowPackage : $model]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <?php if (!$model->overflow_package_id) { ?>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= Yii::t('app', 'Overflow Packages'); ?></h3>
                        <div class="box-tools pull-right">
                            <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create Overflow Package'), [
                                'overflow',
                                'id' => $model->id,
                                'ru' => ReturnUrl::getToken()
                            ], ['class' => 'btn btn-box-tool modal-remote']) ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <div class="table-responsive">
                                <?= $this->render('/package/_overflow_packages', ['model' => $model]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= Yii::t('app', 'Overflow from Package'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <div class="table-responsive">
                                <?= $model->overflowPackage->getLink() ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

</div>
