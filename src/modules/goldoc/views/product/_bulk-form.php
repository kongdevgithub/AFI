<?php

use app\models\User;
use app\modules\goldoc\models\Colour;
use app\modules\goldoc\models\Design;
use app\modules\goldoc\models\Installer;
use app\modules\goldoc\models\Item;
use app\modules\goldoc\models\Sponsor;
use app\modules\goldoc\models\Substrate;
use app\modules\goldoc\models\Supplier;
use app\modules\goldoc\models\Type;
use app\modules\goldoc\models\Venue;
use kartik\file\FileInput;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\form\BulkProductForm $model
 * @var kartik\form\ActiveForm $form
 */
?>

<div class="product-bulk-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Product',
        //'type' => ActiveForm::TYPE_HORIZONTAL,
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <?php
    foreach ($model->ids as $id) {
        echo Html::hiddenInput('ids[]', $id);
    }
    echo Html::hiddenInput('confirm', 1);
    echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
    echo $form->errorSummary($model);
    ?>

    <div class="row">
        <div class="col-md-6">

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('goldoc', 'Venue') ?></h3>
                </div>
                <div class="box-body">

                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model->product, 'goldoc_manager_id')->widget(Select2::className(), [
                                'model' => $model->product,
                                'attribute' => 'goldoc_manager_id',
                                'data' => ArrayHelper::map(User::find()
                                    ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('goldoc-goldoc')])
                                    ->orWhere(['id' => $model->product->goldoc_manager_id])
                                    ->orderBy('username')->all(), 'id', 'label'),
                            ]); ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model->product, 'active_manager_id')->widget(Select2::className(), [
                                'model' => $model->product,
                                'attribute' => 'active_manager_id',
                                'data' => ArrayHelper::map(User::find()
                                    ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('goldoc-active')])
                                    ->orWhere(['id' => $model->product->active_manager_id])
                                    ->orderBy('username')->all(), 'id', 'label'),
                            ]); ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model->product, 'sponsor_id')->widget(Select2::className(), [
                                'model' => $model->product,
                                'attribute' => 'sponsor_id',
                                'data' => ArrayHelper::map(Sponsor::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                            ]); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model->product, 'venue_id')->widget(Select2::className(), [
                                'model' => $model->product,
                                'attribute' => 'venue_id',
                                'data' => ArrayHelper::map(Venue::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                            ]); ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo $form->field($model->product, 'loc')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                </div>
            </div>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('goldoc', 'Product') ?></h3>
                </div>
                <div class="box-body">
                    <?php echo $form->field($model->product, 'type_id')->dropDownList(ArrayHelper::map(Type::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'), ['prompt' => '']); ?>
                    <div class="row">
                        <div class="col-md-3">
                            <?php echo $form->field($model->product, 'item_id')->dropDownList(ArrayHelper::map(Item::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'), ['prompt' => '']); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo $form->field($model->product, 'colour_id')->dropDownList(ArrayHelper::map(Colour::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'), ['prompt' => '']); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo $form->field($model->product, 'design_id')->dropDownList(ArrayHelper::map(Design::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'), ['prompt' => '']); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo $form->field($model->product, 'substrate_id')->dropDownList(ArrayHelper::map(Substrate::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'), ['prompt' => '']); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <?php echo $form->field($model->product, 'width')->textInput(['type' => 'number']) ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo $form->field($model->product, 'height')->textInput(['type' => 'number']) ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo $form->field($model->product, 'depth')->textInput(['type' => 'number']) ?>
                        </div>
                        <div class="col-sm-3">
                            <?php echo $form->field($model->product, 'quantity')->textInput(['type' => 'number']) ?>
                        </div>
                    </div>
                    <?php echo $form->field($model->product, 'details')->textarea() ?>
                </div>
            </div>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('goldoc', 'Artwork') ?></h3>
                </div>
                <div class="box-body">
                    <?php
                    echo $form->field($model->artwork, 'upload')->widget(FileInput::className(), [
                        'options' => [
                            //'accept' => 'image/*',
                        ],
                        'pluginOptions' => [
                            'showPreview' => true,
                            'showCaption' => false,
                            'showRemove' => false,
                            'showUpload' => false,
                        ],
                    ]);
                    ?>
                </div>
            </div>

        </div>
        <div class="col-md-6">

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('goldoc', 'Supplier') ?></h3>
                </div>
                <div class="box-body">

                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model->product, 'supplier_id')->widget(Select2::className(), [
                                'model' => $model->product,
                                'attribute' => 'supplier_id',
                                'data' => ArrayHelper::map(Supplier::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                            ]); ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo $form->field($model->product, 'supplier_reference')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model->product, 'installer_id')->widget(Select2::className(), [
                                'model' => $model->product,
                                'attribute' => 'installer_id',
                                'data' => ArrayHelper::map(Installer::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                            ]); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <?php echo $form->field($model->product, 'artwork_code')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo $form->field($model->product, 'fixing_method')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo $form->field($model->product, 'drawing_reference')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                </div>
            </div>

            <?php if (Yii::$app->user->can('_goldoc_update_prices')) { ?>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo Yii::t('goldoc', 'Pricing') ?></h3>
                    </div>
                    <div class="box-body">

                        <div class="row">
                            <div class="col-md-4">
                                <?php echo $form->field($model->product, 'product_unit_price')->textInput(['type' => 'number', 'step' => '0.01', 'maxlength' => true]) ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo $form->field($model->product, 'supplier_priced')->checkbox() ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <?php echo $form->field($model->product, 'installer_standard_hours')->textInput(['type' => 'number', 'step' => '0.01', 'maxlength' => true]) ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo $form->field($model->product, 'installer_specialist_hours')->textInput(['type' => 'number', 'step' => '0.01', 'maxlength' => true]) ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo $form->field($model->product, 'bump_out_hours')->textInput(['type' => 'number', 'step' => '0.01', 'maxlength' => true]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo $form->field($model->product, 'scissor_lift_hours')->textInput(['type' => 'number', 'step' => '0.01', 'maxlength' => true]) ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo $form->field($model->product, 'rt_scissor_lift_hours')->textInput(['type' => 'number', 'step' => '0.01', 'maxlength' => true]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo $form->field($model->product, 'small_boom_hours')->textInput(['type' => 'number', 'step' => '0.01', 'maxlength' => true]) ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo $form->field($model->product, 'large_boom_hours')->textInput(['type' => 'number', 'step' => '0.01', 'maxlength' => true]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo $form->field($model->product, 'flt_hours')->textInput(['type' => 'number', 'step' => '0.01', 'maxlength' => true]) ?>
                            </div>
                        </div>

                    </div>
                </div>
            <?php } ?>

        </div>
    </div>


    <?php echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('goldoc', 'Save'), [
        'id' => 'save-' . $model->product->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
