<?php

use app\components\bulk_product\BulkProduct;
use kartik\file\FileInput;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use app\components\ReturnUrl;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\ProductBulkCreateForm $model
 */


$this->title = $model->job->getTitle();

$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->job->vid . ': ' . $model->job->name, 'url' => ['/job/view', 'id' => $model->job->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Create Product'), 'url' => ['/product/create', 'Product' => ArrayHelper::merge($_GET['Product'], ['product_type_id' => null]), 'ru' => ReturnUrl::getRequestToken()]];
foreach ($model->productType->getBreadcrumb() as $breadcrumb) {
    $this->params['breadcrumbs'][] = [
        'label' => $breadcrumb->name,
        'url' => ['/product/create', 'Product' => ArrayHelper::merge($_GET['Product'], ['product_type_id' => $breadcrumb->id]), 'ru' => ReturnUrl::getRequestToken()],
    ];
}
$this->params['breadcrumbs'][] = Yii::t('app', 'Bulk Create');
?>
<div class="product-bulk-create">

    <?php
    /** @var BulkProduct $class */
    $class = BulkProduct::className() . '_' . $model->productType->id;
    if (class_exists($class)) {
        ?>
        <div class="row">
            <div class="col-md-6">

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= Yii::t('app', 'CSV Guide') ?></h3>
                        <div class="box-tools pull-right text-right">
                            <?php
                            echo Html::a('<span class="fa fa-download"></span> ' . Yii::t('app', 'download sample'), ['bulk-create', 'Product' => $_GET['Product'], 'download' => 1], [
                                'class' => 'btn btn-box-tool',
                            ]);
                            ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <h4><?= Yii::t('app', 'Allowed Fields') ?></h4>
                        <p><?= Yii::t('app', 'The following fields can be used, in any order:') ?></p>
                        <?php
                        $items = [];
                        foreach ($class::getDoc() as $k => $v) {
                            $items[] = Html::tag('code', $k) . ' - ' . $v;
                        }
                        echo Html::ul($items, ['encode' => false]);
                        //echo Html::tag('pre', YdCsv::arrayToCsv($class::getSample()));
                        ?>
                        <h4><?= Yii::t('app', 'Importing Delivery Addresses') ?></h4>
                        <p><?= Yii::t('app', 'You may add additional fields for despatch quantities by prefixing them with <code>D:</code>.  If the address name exists in the job it will be used. Otherwise the address name from the company will be used.  If neither exist then a new Address will be created.') ?></p>
                        <?php
                        $items = [];
                        if ($model->job->shippingAddresses) {
                            foreach ($model->job->shippingAddresses as $shippingAddress) {
                                $items[] = Html::tag('code', 'D: ' . $shippingAddress->name) . ' - ' . Yii::t('app', 'Quantity to deliver to this address');
                            }
                        } else {
                            $items[] = Html::tag('code', 'D: ' . Yii::t('app', 'The Shipping Address Name')) . ' - ' . Yii::t('app', 'Quantity to deliver to this address');
                        }
                        echo Html::ul($items, ['encode' => false]);
                        ?>
                    </div>
                </div>

                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= Yii::t('app', 'Sample Data') ?></h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php
                        echo Html::tag('pre', \app\components\YdCsv::arrayToCsv($class::getSample($model->job)));
                        ?>
                    </div>
                </div>


                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= Yii::t('app', 'Raw Form') ?></h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php
                        echo Html::tag('pre', \yii\helpers\VarDumper::export($class::getProductFormTemplate()));
                        ?>
                    </div>
                </div>

            </div>
            <div class="col-md-6">

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= Yii::t('app', 'Upload CSV') ?></h3>
                    </div>
                    <div class="box-body">

                        <?php $form = ActiveForm::begin([
                            'id' => 'Address',
                            'type' => 'horizontal',
                            'enableClientValidation' => false,
                            'options' => ['enctype' => 'multipart/form-data'],
                        ]); ?>

                        <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

                        <?= $form->errorSummary($model); ?>

                        <?= $form->field($model, 'upload')->widget(FileInput::className(), [
                            'options' => ['accept' => '*.csv'],
                            'pluginOptions' => [
                                'showPreview' => true,
                                'showCaption' => false,
                                'showRemove' => true,
                                'showUpload' => false,
                            ],
                        ])->label(false); ?>

                        <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                            'id' => 'save-' . $model->formName(),
                            'class' => 'btn btn-success',
                        ]); ?>

                        <?php ActiveForm::end(); ?>


                    </div>
                </div>

            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="container-fluid">
            <div class="jumbotron">
                <h2><?= Yii::t('app', 'Bulk Create Not Available') ?></h2>
                <p><?= Yii::t('app', 'This product does not have a Bulk Create at this point in time.') ?></p>
            </div>
        </div>
        <?php
    }
    ?>

</div>
