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
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\Product $model
 * @var kartik\form\ActiveForm $form
 */
?>

<div class="product-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Product',
        //'type' => ActiveForm::TYPE_HORIZONTAL,
    ]); ?>

    <?php echo Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <div class="col-md-6">

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('goldoc', 'Venue') ?></h3>
                </div>
                <div class="box-body">

                    <div class="row">
                        <div class="col-md-4">
                            <?php
                            if (in_array('goldoc_manager_id', $model->scenarios()[$model->scenario])) {
                                echo $form->field($model, 'goldoc_manager_id')->widget(Select2::className(), [
                                    'model' => $model,
                                    'attribute' => 'goldoc_manager_id',
                                    'data' => ArrayHelper::map(User::find()
                                        ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('goldoc-goldoc')])
                                        ->orWhere(['id' => $model->goldoc_manager_id])
                                        ->orderBy('username')->all(), 'id', 'label'),
                                ]);
                            } else {
                                echo $form->field($model, 'goldoc_manager_id')->dropDownList(ArrayHelper::map(User::find()->andWhere(['id' => $model->goldoc_manager_id])->all(), 'id', 'label'), [
                                    'readonly' => 'readonly',
                                ]);
                            }
                            ?>
                        </div>
                        <div class="col-md-4">
                            <?php
                            if (in_array('goldoc_manager_id', $model->scenarios()[$model->scenario])) {
                                echo $form->field($model, 'active_manager_id')->widget(Select2::className(), [
                                    'model' => $model,
                                    'attribute' => 'active_manager_id',
                                    'data' => ArrayHelper::map(User::find()
                                        ->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('goldoc-active')])
                                        ->orWhere(['id' => $model->active_manager_id])
                                        ->orderBy('username')->all(), 'id', 'label'),
                                ]);
                            } else {
                                echo $form->field($model, 'active_manager_id')->dropDownList(ArrayHelper::map(User::find()->andWhere(['id' => $model->active_manager_id])->all(), 'id', 'label'), [
                                    'readonly' => 'readonly',
                                ]);
                            }
                            ?>
                        </div>
                        <div class="col-md-4">
                            <?php
                            if (in_array('sponsor_id', $model->scenarios()[$model->scenario])) {
                                echo $form->field($model, 'sponsor_id')->widget(Select2::className(), [
                                    'model' => $model,
                                    'attribute' => 'sponsor_id',
                                    'data' => ArrayHelper::map(Sponsor::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                                ]);
                            } else {
                                echo $form->field($model, 'sponsor_id')->dropDownList(ArrayHelper::map(Sponsor::find()->andWhere(['id' => $model->sponsor_id])->all(), 'id', 'label'), [
                                    'readonly' => 'readonly',
                                ]);
                            }
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <?php
                            if (in_array('venue_id', $model->scenarios()[$model->scenario])) {
                                echo $form->field($model, 'venue_id')->widget(Select2::className(), [
                                    'model' => $model,
                                    'attribute' => 'venue_id',
                                    'data' => ArrayHelper::map(Venue::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                                ]);
                            } else {
                                echo $form->field($model, 'venue_id')->dropDownList(ArrayHelper::map(Venue::find()->andWhere(['id' => $model->venue_id])->all(), 'id', 'label'), [
                                    'readonly' => 'readonly',
                                ]);
                            }
                            ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo $form->field($model, 'loc')->textInput([
                                'maxlength' => true,
                                'readonly' => !in_array('loc', $model->scenarios()[$model->scenario]),
                            ]) ?>
                        </div>
                    </div>

                </div>
            </div>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo Yii::t('goldoc', 'Product') ?></h3>
                </div>
                <div class="box-body">
                    <?php echo $form->field($model, 'type_id')->dropDownList(ArrayHelper::map(Type::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'), [
                        'prompt' => '',
                        'readonly' => !in_array('type_id', $model->scenarios()[$model->scenario]),
                    ]); ?>
                    <div class="row">
                        <div class="col-md-3">
                            <?php echo $form->field($model, 'item_id')->dropDownList(ArrayHelper::map(Item::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'), [
                                'prompt' => '',
                                'readonly' => !in_array('item_id', $model->scenarios()[$model->scenario]),
                            ]); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo $form->field($model, 'colour_id')->dropDownList(ArrayHelper::map(Colour::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'), [
                                'prompt' => '',
                                'readonly' => !in_array('colour_id', $model->scenarios()[$model->scenario]),
                            ]); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo $form->field($model, 'design_id')->dropDownList(ArrayHelper::map(Design::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'), [
                                'prompt' => '',
                                'readonly' => !in_array('design_id', $model->scenarios()[$model->scenario]),
                            ]); ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo $form->field($model, 'substrate_id')->dropDownList(ArrayHelper::map(Substrate::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'), [
                                'prompt' => '',
                                'readonly' => !in_array('substrate_id', $model->scenarios()[$model->scenario]),
                            ]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <?php echo $form->field($model, 'width')->textInput([
                                'type' => 'number',
                                'readonly' => !in_array('width', $model->scenarios()[$model->scenario]),
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo $form->field($model, 'height')->textInput([
                                'type' => 'number',
                                'readonly' => !in_array('height', $model->scenarios()[$model->scenario]),
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo $form->field($model, 'depth')->textInput([
                                'type' => 'number',
                                'readonly' => !in_array('depth', $model->scenarios()[$model->scenario]),
                            ]) ?>
                        </div>
                        <div class="col-sm-3">
                            <?php echo $form->field($model, 'quantity')->textInput([
                                'type' => 'number',
                                'readonly' => !in_array('quantity', $model->scenarios()[$model->scenario]),
                            ]) ?>
                        </div>
                    </div>
                    <?php echo $form->field($model, 'details')->textarea([
                        'readonly' => !in_array('details', $model->scenarios()[$model->scenario]),
                    ]) ?>
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
                            <?php
                            if (in_array('supplier_id', $model->scenarios()[$model->scenario])) {
                                echo $form->field($model, 'supplier_id')->widget(Select2::className(), [
                                    'model' => $model,
                                    'attribute' => 'supplier_id',
                                    'data' => ArrayHelper::map(Supplier::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                                ]);
                            } else {
                                echo $form->field($model, 'supplier_id')->dropDownList(ArrayHelper::map(Supplier::find()->andWhere(['id' => $model->supplier_id])->all(), 'id', 'label'), [
                                    'readonly' => 'readonly',
                                ]);
                            }
                            ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo $form->field($model, 'supplier_reference')->textInput([
                                'maxlength' => true,
                                'readonly' => !in_array('supplier_reference', $model->scenarios()[$model->scenario]),
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?php
                            if (in_array('installer_id', $model->scenarios()[$model->scenario])) {
                                echo $form->field($model, 'installer_id')->widget(Select2::className(), [
                                    'model' => $model,
                                    'attribute' => 'installer_id',
                                    'data' => ArrayHelper::map(Installer::find()->orderBy(['code' => SORT_ASC])->all(), 'id', 'label'),
                                ]);
                            } else {
                                echo $form->field($model, 'installer_id')->dropDownList(ArrayHelper::map(Installer::find()->andWhere(['id' => $model->installer_id])->all(), 'id', 'label'), [
                                    'readonly' => 'readonly',
                                ]);
                            }
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <?php echo $form->field($model, 'artwork_code')->textInput([
                                'maxlength' => true,
                                'readonly' => !in_array('artwork_code', $model->scenarios()[$model->scenario]),
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo $form->field($model, 'fixing_method')->textInput([
                                'maxlength' => true,
                                'readonly' => !in_array('fixing_method', $model->scenarios()[$model->scenario]),
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo $form->field($model, 'drawing_reference')->textInput([
                                'maxlength' => true,
                                'readonly' => !in_array('drawing_reference', $model->scenarios()[$model->scenario]),
                            ]) ?>
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
                                <?php echo $form->field($model, 'product_unit_price')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'readonly' => !in_array('product_unit_price', $model->scenarios()[$model->scenario]),
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo $form->field($model, 'supplier_priced')->checkbox([
                                    'readonly' => !in_array('supplier_priced', $model->scenarios()[$model->scenario]),
                                ]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <?php echo $form->field($model, 'installer_standard_hours')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'readonly' => !in_array('installer_standard_hours', $model->scenarios()[$model->scenario]),
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo $form->field($model, 'installer_specialist_hours')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'readonly' => !in_array('installer_specialist_hours', $model->scenarios()[$model->scenario]),
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo $form->field($model, 'bump_out_hours')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'readonly' => !in_array('bump_out_hours', $model->scenarios()[$model->scenario]),
                                ]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo $form->field($model, 'scissor_lift_hours')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'readonly' => !in_array('scissor_lift_hours', $model->scenarios()[$model->scenario]),
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo $form->field($model, 'rt_scissor_lift_hours')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'readonly' => !in_array('rt_scissor_lift_hours', $model->scenarios()[$model->scenario]),
                                ]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo $form->field($model, 'small_boom_hours')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'readonly' => !in_array('small_boom_hours', $model->scenarios()[$model->scenario]),
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo $form->field($model, 'large_boom_hours')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'readonly' => !in_array('large_boom_hours', $model->scenarios()[$model->scenario]),
                                ]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo $form->field($model, 'flt_hours')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'readonly' => !in_array('flt_hours', $model->scenarios()[$model->scenario]),
                                ]) ?>
                            </div>
                        </div>

                    </div>
                </div>
            <?php } ?>

        </div>
    </div>


    <?php echo Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('goldoc', 'Create') : Yii::t('goldoc', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?php //echo if($model->isNewRecord) echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('goldoc', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>
