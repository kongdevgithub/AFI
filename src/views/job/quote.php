<?php

use yii\bootstrap\Alert;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;


if ($model->quote_generated != 1) {
    echo Alert::widget([
        'body' => Html::a('<i class="fa fa-refresh" title="' . Yii::t('app', 'Reload') . '"></i>', ['job/quote', 'id' => $model->id]) . '&nbsp;&nbsp;' . Yii::t('app', 'Quote is being generated') . '<small>[quote_generated=' . $model->quote_generated . '|gearman_quote=' . $model->gearman_quote . ']</small>',
        'options' => ['class' => 'alert-danger'],
        'closeButton' => false,
    ]);
} else {
    if (!$model->checkTotals()) {
        echo Alert::widget([
            'body' => Yii::t('app', 'Quote totals are incorrect please regenerate quote!'),
            'options' => ['class' => 'alert-danger'],
            'closeButton' => false,
        ]);
    }
}
if ($model->product_imports_pending) {
    echo Alert::widget([
        'body' => Html::a('<i class="fa fa-refresh" title="' . Yii::t('app', 'Reload') . '"></i>', ['job/quote', 'id' => $model->id]) . '&nbsp;&nbsp;' . Yii::t('app', 'Products are being imported') . '<small>[product_imports_pending=' . count($model->product_imports_pending) . '|gearman_product_import=' . $model->gearman_product_import . ']</small>',
        'options' => ['class' => 'alert-danger'],
        'closeButton' => false,
    ]);
}
?>
<div class="job-view">

    <?= $this->render('_menu', ['model' => $model]); ?>
    <?= $this->render('_account_term_warning', ['model' => $model]) ?>

    <div class="row">
        <div class="col-md-9">
            <?= $this->render('_details', ['model' => $model]); ?>
            <?= $this->render('_quote-products', ['model' => $model]) ?>
            <div class="row">
                <div class="col-md-3">
                    <?= $this->render('_quote-totals', ['model' => $model]) ?>
                </div>
                <?php if (Yii::$app->user->can('_view_cost_prices')) { ?>
                    <div class="col-md-3">
                        <?= $this->render('_finance-discounts', ['model' => $model]) ?>
                        <?php //echo $this->render('_finance-preserved-prices', ['model' => $model]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $this->render('_finance-product-margins', ['model' => $model]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $this->render('_finance-job-margins', ['model' => $model]) ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="col-md-3">
            <?php
            if ($model->status == 'job/draft') {
                $this->render('_status', ['model' => $model]); // no echo, just register assets
                if (Yii::$app->user->can('app_job_status', ['route' => true])) {
                    echo Html::a('<span class="fa fa-envelope"></span> ' . Yii::t('app', 'Send Quote'), [
                        'status',
                        'id' => $model->id,
                        'Job' => ['status' => 'job/quote', 'send_email' => 1],
                        'ru' => ReturnUrl::getToken(),
                    ], [
                        'class' => 'btn btn-success btn-lg modal-remote',
                        'style' => 'width:100%;margin-bottom:20px;',
                    ]);
                }
            }
            ?>
            <?= $this->render('/job/_status-box', ['model' => $model]) ?>
            <?= $this->render('/job/_quote-version-fork', ['model' => $model]) ?>
            <?= $this->render('/job/_job-copy', ['model' => $model]) ?>
            <?= $this->render('/job/_job-redo', ['model' => $model]) ?>
            <?= $this->render('/job/_notes', ['model' => $model]) ?>
            <?= $this->render('/attachment/_index', ['model' => $model, 'title' => Yii::t('app', 'Job Attachments')]) ?>
        </div>
    </div>

</div>

