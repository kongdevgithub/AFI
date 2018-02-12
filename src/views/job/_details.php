<?php

use app\components\Helper;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Inflector;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */
?>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $model->name; ?></h3>
        <div class="box-tools pull-right text-right">
            <?php
            $items = [
                Html::tag('span', $model->priceStructure->name, [
                    'title' => Yii::t('app', 'Price Structure'),
                    'class' => 'label label-info',
                    'data-toggle' => 'tooltip',
                ]),
                Html::tag('span', $model->accountTerm->name, [
                    'title' => Yii::t('app', 'Account Term'),
                    'class' => 'label label-info',
                    'data-toggle' => 'tooltip',
                ]),
                Html::tag('span', $model->jobType->name, [
                    'title' => Yii::t('app', 'Job Type'),
                    'class' => 'label label-info',
                    'data-toggle' => 'tooltip',
                ]),
            ];
            if ($model->company->industry) {
                $items[] = Html::tag('span', $model->company->industry->name, [
                    'title' => Yii::t('app', 'Company Industry'),
                    'class' => 'label label-info',
                    'data-toggle' => 'tooltip',
                ]);
            }
            echo implode(' ', $items);
            ?>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <?php
                $attributes = [];
                $attributes[] = [
                    'attribute' => 'company_id',
                    'value' => implode(' | ', [
                        Html::a($model->company->name, ['company/preview', 'id' => $model->company->id], ['class' => 'modal-remote']),
                        Html::a($model->company->phone, 'tel:' . $model->company->phone),
                    ]),
                    'format' => 'raw',
                ];
                $contact = [];
                $contact[] = Html::a($model->contact->getLabel(true), ['contact/preview', 'id' => $model->contact->id], ['class' => 'modal-remote']);
                if ($model->contact->phone) {
                    $contact[] = Html::a($model->contact->phone, 'tel:' . $model->contact->phone);
                }
                $contact[] = Html::a($model->contact->email, 'mailto:' . $model->contact->email);
                $attributes[] = [
                    'attribute' => 'contact_id',
                    'value' => implode(' | ', $contact),
                    'format' => 'raw',
                ];
                $attributes[] = [
                    'attribute' => 'purchase_order',
                    'visible' => $model->purchase_order,
                ];
                //$attributes[] = [
                //    'label' => Yii::t('app', 'Win Chance'),
                //    'attribute' => 'quote_win_chance',
                //    'value' => $model->quote_win_chance ? '<span class="label label-info">' . $model->optsQuoteWinChance()[$model->quote_win_chance] . '</span>' : '',
                //    'format' => 'raw',
                //];
                $attributes[] = [
                    'label' => Yii::t('app', 'Staff'),
                    'value' => implode('<br>', [
                        $model->staffLead ? $model->staffLead->getLink() . ' ' . Yii::t('app', 'BDM') : null,
                        $model->staffRep ? $model->staffRep->getLink() . ' ' . Yii::t('app', 'AM') : null,
                        $model->staffCsr ? $model->staffCsr->getLink() . ' ' . Yii::t('app', 'CSR') : null,
                        $model->staffDesigner ? $model->staffDesigner->getLink() . ' ' . Yii::t('app', 'DSN') : null,
                    ]),
                    'format' => 'raw',
                ];
                $attributes[] = [
                    'attribute' => 'rollout_id',
                    'value' => $model->rollout ? $model->rollout->name : '',
                    'visible' => !empty($model->rollout_id),
                    'format' => 'raw',
                ];

                $dueDates = [];
                $extra = '';
                if (Yii::$app->user->can('app_job_due', ['route' => true])) {
                    $extra = ' ' . Html::a('<span class="fa fa-pencil"></span>', ['/job/due', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], ['class' => 'modal-remote']);
                }
                //$dueDates[] = Yii::$app->formatter->asDate($model->production_date) . ' ' . Yii::t('app', 'Production');
                if ($model->prebuild_days) {
                    $dueDates[] = Yii::$app->formatter->asDate($model->prebuild_date) . ' ' . Yii::t('app', 'Prebuild');
                }
                $dueDates[] = Yii::$app->formatter->asDate($model->despatch_date) . ' ' . Yii::t('app', 'Despatched');
                $dueDates[] = Yii::$app->formatter->asDate($model->due_date) . ' ' . Yii::t('app', 'Delivered');
                if ($model->installation_date) {
                    $dueDates[] = Yii::$app->formatter->asDate($model->installation_date) . ' ' . Yii::t('app', 'Installed');
                }
                $attributes[] = [
                    'label' => Yii::t('app', 'Due Dates'),
                    'value' => implode('<br>', $dueDates) . $extra,
                    'format' => 'raw',
                ];

                $logDates = [];
                foreach (['quote_at', 'quote_lost_at', 'production_pending_at', 'production_at', 'despatch_at', 'packed_at', 'complete_at', 'installed_at'] as $field) {
                    if (!empty($model->$field)) {
                        $logDates[] = Yii::$app->formatter->asDatetime($model->$field) . ' ' . Inflector::humanize(str_replace(['_at'], '', $field), true);
                    }
                }
                $attributes[] = [
                    'label' => Yii::t('app', 'Log Dates'),
                    'value' => implode('<br>', $logDates),
                    'format' => 'raw',
                ];

                $attributes[] = [
                    'attribute' => 'quote_approved_by',
                    'visible' => !in_array($model->status, ['job/draft', 'job/quote', 'job/quoteLost']) && $model->quote_approved_by,
                ];
                //if (Yii::$app->user->can('admin')) {
                //    $attributes[] = [
                //        'attribute' => 'quote_class',
                //        'value' => '<span class="label label-info">' . $model->quote_label . '</span>',
                //        'format' => 'raw',
                //    ];
                //}
                echo DetailView::widget([
                    'model' => $model,
                    'attributes' => $attributes,
                    'options' => ['class' => 'table table-condensed detail-view'],
                ]);
                ?>
            </div>
            <div class="col-md-6">
                <?= $this->render('_addresses', ['model' => $model]); ?>
            </div>
        </div>
    </div>
</div>
