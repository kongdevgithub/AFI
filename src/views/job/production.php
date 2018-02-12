<?php

use app\components\NfcTools;
use yii\bootstrap\Alert;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Url;

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
}

if (!$model->checkUnitCount()) {
    echo Alert::widget([
        'body' => Yii::t('app', 'Unit count is incorrect for some items!'),
        'options' => ['class' => 'alert-danger'],
        'closeButton' => false,
    ]);
}

?>
<div class="job-view">

    <?= $this->render('_menu', ['model' => $model]) ?>
    <?= $this->render('_account_term_warning', ['model' => $model]) ?>

    <div class="row">
        <div class="col-md-9">
            <?= $this->render('_details', ['model' => $model]); ?>
            <?= $this->render('_production-products', ['model' => $model]) ?>
        </div>
        <div class="col-md-3">
            <?php
            $artworkApprovalReady = false;
            if ($model->status == 'job/production') {
                foreach ($model->products as $product) {
                    foreach ($product->items as $item) {
                        if (explode('/', $item->status)[1] == 'artwork' && $item->artwork) {
                            $artworkApprovalReady = true;
                            break(2);
                        }
                    }
                }
            }
            if ($artworkApprovalReady) {
                echo Html::a('<span class="fa fa-envelope"></span> ' . Yii::t('app', 'Send Artwork Approval'), [
                    'artwork-email',
                    'id' => $model->id,
                    'Job' => ['status' => 'job/quote', 'send_email' => 1],
                    'ru' => ReturnUrl::getToken(),
                ], [
                    'class' => 'btn btn-success btn-lg modal-remote',
                    'style' => 'width:100%;margin-bottom:20px;',
                ]);
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
