<?php

use app\models\Option;
use yii\bootstrap\Alert;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\widgets\DetailView;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Product $model
 */

$this->title = $model->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->job->vid . ': ' . $model->job->name, 'url' => ['/job/view', 'id' => $model->job->id]];
$this->params['breadcrumbs'][] = 'product-' . $model->id . ': ' . $model->name;

if (!$model->checkUnitCount()) {
    echo Alert::widget([
        'body' => Yii::t('app', 'Unit count is incorrect for some items!'),
        'options' => ['class' => 'alert-danger'],
        'closeButton' => false,
    ]);
}

$job = $model->job;
?>
<div class="product-view">

    <?= $this->render('_menu', ['model' => $model]); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <?= implode(' | ', [
                            $model->getDescription(['showDetails' => false, 'showItems' => false]),
                            $job->name,
                        ]) ?>
                    </h3>
                    <div class="box-tools pull-right text-right">
                        <?php
                        $tags = [];
                        $tags[] = Html::tag('span', 'x' . $model->quantity, [
                            'class' => 'label label-info',
                        ]);
                        //$tags[] = Html::tag('span', 'x' . $model->quantity, [
                        //    'title' => Yii::t('app', 'Quantity'),
                        //    'class' => 'label label-info',
                        //    'data-toggle' => 'tooltip',
                        //]);
                        if ($model->productType) {
                            $tags[] = Html::tag('span', $model->productType->getBreadcrumbString(' > '), [
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
                            if ($model->job->installation_date) {
                                $dueDates[] = Yii::$app->formatter->asDate($model->job->installation_date) . ' ' . Yii::t('app', 'Installed');
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
                                    'label' => Yii::t('app', 'Product Log Dates'),
                                    'value' => implode('<br>', $logDates),
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
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><?= Yii::t('app', 'Description') ?></h3>
                                </div>
                                <div class="panel-body">
                                    <?php
                                    echo $model->getDescription(['showItems' => false]);
                                    echo '<hr style="margin:3px 0;">';
                                    echo $model->getDescription([
                                        'showName' => false,
                                        'showMaterials' => false,
                                        'showDetails' => false,
                                        'itemDescriptionOptions' => [
                                            'highlightOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
                                        ],
                                    ]);
                                    ?>
                                </div>
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
                    <div class="box-tools pull-right text-right">
                        <?= $model->getIcons() ?>
                    </div>
                </div>
                <div class="box-body">
                    <?= $model->getStatusButtons(true) ?>
                </div>
            </div>
            <?= $this->render('_items', ['model' => $model]) ?>
            <?= $this->render('/product/_notes', ['model' => $model]) ?>
            <?= $this->render('/product/_links', ['model' => $model]) ?>
            <?= $this->render('/product/_attachments', ['model' => $model]) ?>
        </div>
    </div>

</div>
