<?php

use app\models\Address;
use app\models\Package;
use app\models\Product;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="job-view">

    <?= $this->render('_menu', ['model' => $model]); ?>
    <?= $this->render('_account_term_warning', ['model' => $model]) ?>

    <div class="row">
        <div class="col-md-9">
            <?= $this->render('_details', ['model' => $model]); ?>

            <?php echo $this->render('_finance-products', [
                'model' => $model,
            ]); ?>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Invoice Details'); ?></h3>
                </div>
                <div class="box-body">
                    <?php if (Yii::$app->user->can('finance')) { ?>
                        <?php echo $this->render('_form-finance', [
                            'model' => $model,
                        ]); ?>
                    <?php } else { ?>
                        <?php echo $this->render('_finance-details', [
                            'model' => $model,
                        ]); ?>
                    <?php } ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <?= $this->render('_quote-totals', ['model' => $model]) ?>
                </div>
                <?php if (Yii::$app->user->can('manager')) { ?>
                    <div class="col-md-3">
                        <?= $this->render('_finance-discounts', ['model' => $model]) ?>
                        <?= $this->render('_finance-preserved-prices', ['model' => $model]) ?>
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
            <?= $this->render('/job/_status-box', ['model' => $model]) ?>
            <?= $this->render('/job/_quote-version-fork', ['model' => $model]) ?>
            <?= $this->render('/job/_job-copy', ['model' => $model]) ?>
            <?= $this->render('/job/_job-redo', ['model' => $model]) ?>
            <?= $this->render('/job/_notes', ['model' => $model]) ?>
            <?= $this->render('/attachment/_index', ['model' => $model, 'title' => Yii::t('app', 'Job Attachments')]) ?>
        </div>
    </div>

</div>
