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

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $model->name; ?></h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <?php
                $attributes = [];
                $attributes[] = [
                    'attribute' => 'company_id',
                    'value' => implode(' | ', [
                        $model->company->name,
                        $model->company->phone,
                    ]),
                    'format' => 'raw',
                ];
                $attributes[] = [
                    'attribute' => 'contact_id',
                    'value' => implode(' | ', [
                        $model->contact->getLabel(true),
                        $model->contact->phone,
                        $model->contact->email,
                    ]),
                    'format' => 'raw',
                ];
                $attributes[] = [
                    'attribute' => 'staff_rep_id',
                    'value' => $model->staffRep ? $model->staffRep->getLabel(true) : null,
                    'format' => 'raw',
                ];
                $attributes[] = [
                    'attribute' => 'staff_csr_id',
                    'value' => $model->staffCsr ? $model->staffCsr->getLabel(true) : null,
                    'format' => 'raw',
                ];
                if ($model->staffDesigner) {
                    $attributes[] = [
                        'attribute' => 'staff_designer_id',
                        'value' => $model->staffDesigner ? $model->staffDesigner->getLabel(true) : null,
                        'format' => 'raw',
                    ];
                }
                $attributes[] = [
                    'attribute' => 'quote_at',
                    'value' => Yii::$app->formatter->asDate($model->quote_at),
                    'visible' => !empty($model->quote_at),
                    'format' => 'raw',
                ];
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
