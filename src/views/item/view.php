<?php

use app\components\Helper;
use app\models\Component;
use app\models\ItemType;
use app\models\Option;
use app\components\ReturnUrl;
use yii\bootstrap\Alert;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Item $model
 */

$this->title = $model->getTitle();

$product = $model->product;
$job = $product->job;

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $job->vid . ': ' . $job->name, 'url' => ['/job/view', 'id' => $job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $product->id . ': ' . $product->name, 'url' => ['/product/view', 'id' => $product->id]];
$this->params['breadcrumbs'][] = 'item-' . $model->id . ': ' . $model->name;

if (!$model->checkUnitCount()) {
    echo Alert::widget([
        'body' => Yii::t('app', 'Unit count is incorrect for some items!'),
        'options' => ['class' => 'alert-danger'],
        'closeButton' => false,
    ]);
}

?>
<div class="item-view">

    <?= $this->render('_menu', ['model' => $model]); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <?= implode(' | ', [
                            $model->name,
                            $product->getDescription(['showDetails' => false, 'showItems' => false]),
                            $job->name,
                        ]) ?>
                    </h3>
                    <div class="box-tools pull-right text-right">
                        <?php
                        $tags = [];
                        $tags[] = Html::tag('span', 'x' . ($model->quantity * $product->quantity), [
                            'title' => Yii::t('app', 'Quantity'),
                            'class' => 'label label-info',
                            'data-toggle' => 'tooltip',
                        ]);
                        $tags[] = Html::tag('span', $model->itemType->name, [
                            'title' => Yii::t('app', 'Item Type'),
                            'class' => 'label label-info',
                            'data-toggle' => 'tooltip',
                        ]);
                        if ($product->productType) {
                            $tags[] = Html::tag('span', $product->productType->getBreadcrumbString(' > '), [
                                'title' => Yii::t('app', 'Product Type'),
                                'class' => 'label label-info',
                                'data-toggle' => 'tooltip',
                            ]);
                        }
                        echo implode(' ', $tags);
                        ?>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            $attributes = [];

                            $attributes[] = [
                                'label' => Yii::t('app', 'Contact'),
                                'value' => implode(' | ', [
                                    Html::a($job->company->name, ['company/preview', 'id' => $job->company->id], ['class' => 'modal-remote']),
                                    Html::a($job->contact->getLabel(true), ['contact/preview', 'id' => $job->contact->id], ['class' => 'modal-remote']),
                                ]),
                                'format' => 'raw',
                            ];
                            $attributes[] = [
                                'label' => Yii::t('app', 'Purchase Order'),
                                'value' => $job->purchase_order,
                                'visible' => $job->purchase_order,
                            ];
                            //$attributes[] = [
                            //    'label' => Yii::t('app', 'Win Chance'),
                            //    'attribute' => 'quote_win_chance',
                            //    'value' => $job->quote_win_chance ? '<span class="label label-info">' . $job->optsQuoteWinChance()[$job->quote_win_chance] . '</span>' : '',
                            //    'format' => 'raw',
                            //];
                            $attributes[] = [
                                'label' => Yii::t('app', 'Staff'),
                                'value' => implode('<br>', [
                                    $job->staffLead ? $job->staffLead->getLink() . ' ' . Yii::t('app', 'BDM') : null,
                                    $job->staffRep ? $job->staffRep->getLink() . ' ' . Yii::t('app', 'AM') : null,
                                    $job->staffCsr ? $job->staffCsr->getLink() . ' ' . Yii::t('app', 'CSR') : null,
                                ]),
                                'format' => 'raw',
                            ];
                            $attributes[] = [
                                'attribute' => 'rollout_id',
                                'value' => $job->rollout ? $job->rollout->name : '',
                                'visible' => !empty($job->rollout_id),
                                'format' => 'raw',
                            ];

                            if ($model->supplier) {
                                $supplierInfo = [];
                                $supplierInfo[] = Html::a($model->supplier->name, ['company/preview', 'id' => $model->supplier->id], ['class' => 'modal-remote']);
                                $supplierInfo[] = $model->purchase_order;
                                $supplierInfo[] = Yii::$app->formatter->asDate($model->supply_date) . ' ' . Yii::t('app', 'Supply');
                                $attributes[] = [
                                    'label' => Yii::t('app', 'Supplier'),
                                    'value' => implode('<br>', $supplierInfo),
                                    'format' => 'raw',
                                ];
                            }

                            $dueDates = [];
                            $extra = '';
                            if (Yii::$app->user->can('app_job_due', ['route' => true])) {
                                $extra = ' ' . Html::a('<span class="fa fa-pencil"></span>', ['/job/due', 'id' => $job->id, 'ru' => ReturnUrl::getToken()], ['class' => 'modal-remote']);
                            }
                            //$dueDates[] = Yii::$app->formatter->asDate($job->production_date) . ' ' . Yii::t('app', 'Production');
                            if ($job->prebuild_days) {
                                $dueDates[] = Yii::$app->formatter->asDate($job->prebuild_date) . ' ' . Yii::t('app', 'Prebuild');
                            }
                            $dueDates[] = Yii::$app->formatter->asDate($job->despatch_date) . ' ' . Yii::t('app', 'Despatched');
                            $dueDates[] = Yii::$app->formatter->asDate($job->due_date) . ' ' . Yii::t('app', 'Delivered') . $extra;
                            if ($job->installation_date) {
                                $dueDates[] = Yii::$app->formatter->asDate($job->installation_date) . ' ' . Yii::t('app', 'Installed');
                            }
                            $attributes[] = [
                                'label' => Yii::t('app', 'Job Due Dates'),
                                'value' => implode('<br>', $dueDates),
                                'format' => 'raw',
                            ];

                            $logDates = [];
                            foreach (['production_at', 'despatch_at', 'packed_at', 'complete_at'] as $field) {
                                if (!empty($model->$field)) {
                                    $logDates[] = Yii::$app->formatter->asDatetime($model->$field) . ' ' . Inflector::humanize(str_replace(['_at'], '', $field), true);
                                }
                            }
                            if (!empty($logDates)) {
                                $attributes[] = [
                                    'label' => Yii::t('app', 'Item Log Dates'),
                                    'value' => implode('<br>', $logDates),
                                    'format' => 'raw',
                                ];
                            }

                            $productLogDates = [];
                            foreach (['production_at', 'despatch_at', 'packed_at', 'complete_at'] as $field) {
                                if (!empty($product->$field)) {
                                    $productLogDates[] = Yii::$app->formatter->asDatetime($product->$field) . ' ' . Inflector::humanize(str_replace(['_at'], '', $field), true);
                                }
                            }
                            if (!empty($productLogDates)) {
                                $attributes[] = [
                                    'label' => Yii::t('app', 'Product Log Dates'),
                                    'value' => implode('<br>', $productLogDates),
                                    'format' => 'raw',
                                ];
                            }

                            if ($model->artwork_notes) {
                                $attributes[] = [
                                    'label' => Yii::t('app', 'Artwork Notes'),
                                    'value' => $model->artwork_notes,
                                    'format' => 'ntext',
                                ];
                            }

                            if ($model->item_type_id == ItemType::ITEM_TYPE_PRINT) {
                                $substrate = '';
                                if ($productToOption = $model->getProductToOption(Option::OPTION_SUBSTRATE)) {
                                    if ($productToOption->valueDecoded) {
                                        if ($component = Component::findOne($productToOption->valueDecoded)) {
                                            $substrate = ' s:' . $component->code;
                                        }
                                    }
                                }
                                $size = $model->getSize();
                                $height = !empty($size['height']) ? ' h:' . $size['height'] : '';
                                $attributes[] = [
                                    'label' => Yii::t('app', 'Printable Name'),
                                    'value' => Html::img(Helper::getTextImage($model->getTitle() . ' | ' . $height . $substrate)),
                                    'format' => 'raw',
                                ];
                            }

                            echo DetailView::widget([
                                'model' => $model,
                                'attributes' => $attributes,
                                'options' => ['class' => 'table table-condensed detail-view'],
                            ]);
                            ?>
                        </div>
                        <div class="col-md-2">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><?= Yii::t('app', 'Artwork') ?></h3>
                                </div>
                                <div class="panel-body text-center">
                                    <?php
                                    if ($model->artwork) {
                                        $thumb = Html::img($model->artwork->getFileUrl('300x300'));
                                        if (Yii::$app->user->can('app_item_artwork', ['route' => true])) {
                                            echo Html::a($thumb, $model->getUrl('artwork', ['ru' => ReturnUrl::getToken()]), ['class' => 'modal-remote']);
                                        } else {
                                            echo Html::a($thumb, $model->artwork->getFileUrl('800x800'), ['data-fancybox' => 'gallery-' . $model->artwork->id]);
                                        }
                                    } else {
                                        $thumb = $model->product->productType ? Html::img($model->product->productType->getImageSrc()) : '<i class="fa fa-upload"></i>';
                                        if (Yii::$app->user->can('app_item_artwork', ['route' => true])) {
                                            echo Html::a($thumb, ['/item/artwork', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                                'class' => 'modal-remote',
                                                'title' => Yii::t('app', 'Artwork'),
                                                'data-toggle' => 'tooltip',
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
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><?= Yii::t('app', 'Description') ?></h3>
                                </div>
                                <div class="panel-body">
                                    <?php
                                    echo $product->getDescription(['showItems' => false]);
                                    echo '<hr style="margin:3px 0;">';
                                    echo $model->name;
                                    if ($model->checkShowSize()) {
                                        echo ' - ' . $model->getSizeHtml();
                                    }
                                    echo $model->getDescription([
                                        'highlightOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
                                        'forceOptions' => [
                                            ['option_id' => Option::OPTION_PRINTER, 'value' => Component::COMPONENT_BLANK],
                                        ],
                                    ])
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?= $this->render('_components', ['model' => $model]) ?>

        </div>
        <div class="col-md-4">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Status'); ?></h3>
                    <div class="box-tools pull-right text-right">
                        <?= $model->getIcons() ?>
                    </div>
                </div>
                <div class="box-body">
                    <?= $model->getStatusButtons(true) ?>
                </div>
            </div>

            <?= $this->render('_units', ['model' => $model]) ?>
            <?= $this->render('/item/_notes', ['model' => $model]) ?>
            <?= $this->render('/item/_attachments', ['model' => $model]) ?>

        </div>
    </div>

</div>
