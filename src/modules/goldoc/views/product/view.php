<?php

use app\components\ReturnUrl;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Product $model
 */
$this->title = Yii::t('goldoc', 'Product') . ': ' . $model->id;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
?>
<div class="product-view">

    <?php echo $this->render('_menu', ['model' => $model]); ?>

    <div class="row">
        <div class="col-md-4">

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('goldoc', 'Venue') ?></h3>
                </div>
                <div class="box-body">

                    <?php echo DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'format' => 'raw',
                                'attribute' => 'goldoc_manager_id',
                                'value' => $model->goldocManager ? $model->goldocManager->label : '',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'active_manager_id',
                                'value' => $model->activeManager ? $model->activeManager->label : '',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'sponsor_id',
                                'value' => $model->sponsor ? $model->sponsor->label : '',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'venue_id',
                                'value' => $model->venue ? $model->venue->label : '',
                            ],
                            'loc',
                        ],
                    ]); ?>

                </div>
            </div>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('goldoc', 'Product') ?></h3>
                </div>
                <div class="box-body">

                    <?php echo DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'format' => 'raw',
                                'attribute' => 'type_id',
                                'value' => $model->type ? $model->type->label : '',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'item_id',
                                'value' => $model->item ? $model->item->label : '',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'colour_id',
                                'value' => $model->colour ? $model->colour->label : '',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'design_id',
                                'value' => $model->design ? $model->design->label : '',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'substrate_id',
                                'value' => $model->substrate ? $model->substrate->label : '',
                            ],
                            [
                                'label' => Yii::t('goldoc', 'Size'),
                                'attribute' => 'sizeName',
                            ],
                            'quantity',
                            'details:ntext',
                        ],
                    ]); ?>

                </div>
            </div>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('goldoc', 'Artwork') ?></h3>
                </div>
                <div class="box-body text-center">

                    <?php
                    if ($model->artwork) {
                        $thumb = Html::img($model->artwork->getFileUrl('300x300'));
                        if (Yii::$app->user->can('goldoc_product_artwork', ['route' => true])) {
                            echo Html::a($thumb, $model->getUrl('artwork', ['ru' => ReturnUrl::getToken()]), ['class' => 'modal-remote']);
                        } else {
                            echo Html::a($thumb, $model->artwork->getFileUrl('800x800'), ['data-fancybox' => 'gallery-' . $model->artwork->id]);
                        }
                    } else {
                        $thumb = '<i class="fa fa-upload" style="font-size:300px;line-height:300px;"></i>';
                        if (Yii::$app->user->can('goldoc_product_artwork', ['route' => true])) {
                            echo Html::a($thumb, ['product/artwork', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                'class' => 'modal-remote',
                                //'title' => Yii::t('goldoc', 'Artwork'),
                                //'data-toggle' => 'tooltip',
                            ]);
                        } else {
                            echo $thumb;
                        }
                    }
                    ?>

                </div>
            </div>

        </div>
        <div class="col-md-4">

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('goldoc', 'Supplier') ?></h3>
                </div>
                <div class="box-body">
                    <?php
                    $supplierRef = '';
                    if ($model->supplier_reference) {
                        if ($model->supplier_id == 1) {
                            $supplierRef = Html::a('product-' . $model->supplier_reference, ['/product/view', 'id' => $model->supplier_reference], ['target' => '_blank']);
                        } elseif ($model->supplier_id == 2) {
                            $supplierRef = 'goldoc-adg-export-' . date('Y-m-d-H-i-s', strtotime($model->supplier_reference)) . '.csv';
                        } else {
                            $supplierRef = $model->supplier_reference;
                        }
                    }
                    echo DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'format' => 'raw',
                                'attribute' => 'supplier_id',
                                'value' => $model->supplier ? $model->supplier->label : '',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'supplier_reference',
                                'value' => $supplierRef,
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'installer_id',
                                'value' => $model->installer ? $model->installer->label : '',
                            ],
                            'artwork_code',
                            'fixing_method',
                            'drawing_reference',
                        ],
                    ]); ?>
                </div>
            </div>

            <?php if (Yii::$app->user->can('_goldoc_view_prices')) { ?>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo Yii::t('goldoc', 'Pricing') ?></h3>
                    </div>
                    <div class="box-body">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'supplier_priced:boolean',
                                'product_unit_price',
                                'installer_standard_hours',
                                'installer_specialist_hours',
                                'bump_out_hours',
                                'scissor_lift_hours',
                                'rt_scissor_lift_hours',
                                'small_boom_hours',
                                'large_boom_hours',
                                'flt_hours',
                                'product_price',
                                'labour_price',
                                'machine_price',
                                'total_price',
                            ],
                        ]) ?>
                    </div>
                </div>
            <?php } ?>

        </div>
        <div class="col-md-4">

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('goldoc', 'Status'); ?></h3>
                    <div class="box-tools pull-right text-right">
                        <?php // $model->getIcons() ?>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $model->getStatusButton() ?>
                        </div>
                        <div class="col-md-6">
                            <?= $model->getAfiStatusButtons(true) ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo $this->render('//note/_index', [
                'title' => Yii::t('goldoc', 'GOLDOC Notes'),
                'model' => $model,
                'modelName' => $model->className() . '-Goldoc',
                'relationName' => 'goldocNotes',
            ]) ?>
            <?php
            if (Yii::$app->user->can('goldoc-active')) {
                echo $this->render('//note/_index', [
                    'title' => Yii::t('goldoc', 'Active Private Notes'),
                    'model' => $model,
                    'modelName' => $model->className() . '-ActivePrivate',
                    'relationName' => 'activePrivateNotes',
                ]);
            }
            ?>
            <?php echo $this->render('//note/_index', [
                'title' => Yii::t('goldoc', 'Production Notes'),
                'model' => $model,
                'modelName' => $model->className() . '-Production',
                'relationName' => 'productionNotes',
            ]) ?>
            <?php echo $this->render('//note/_index', [
                'title' => Yii::t('goldoc', 'Installation Notes'),
                'model' => $model,
                'modelName' => $model->className() . '-Installation',
                'relationName' => 'installationNotes',
            ]) ?>
            <?php echo $this->render('//attachment/_index', ['model' => $model]) ?>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('goldoc', 'Status History'); ?></h3>
                </div>
                <div class="box-body">
                    <?= $this->render('/layouts/_audit_trails', [
                        'query' => $model->getAuditTrails(),
                        'columns' => ['user_id', 'new_value', 'created'],
                        'filter' => false,
                        'params' => [
                            'AuditTrailSearch' => [
                                'field' => 'status',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>

        </div>
    </div>

</div>
