<?php

use app\models\Company;
use app\models\ItemType;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var app\models\Item $model
 * @var ActiveForm $form
 */

?>

<div class="item-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Item',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Item Details'); ?></h3>
        </div>
        <div class="box-body">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?php
            if (Yii::$app->user->can('admin')) {
                echo $form->field($model, 'item_type_id')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'item_type_id',
                    'data' => ItemType::getDropdownOpts(),
                    'options' => [
                        'placeholder' => Yii::t('app', 'Type to autocomplete'),
                        'multiple' => false,
                    ]
                ]);
            }
            ?>

            <?= $form->field($model, 'quantity')->textInput(['maxlength' => true]) ?>

            <?php if (in_array($model->item_type_id, [ItemType::ITEM_TYPE_PRINT, ItemType::ITEM_TYPE_EM_PRINT])) { ?>
                <?= $form->field($model, 'artwork_notes')->textarea() ?>
            <?php } ?>

            <?php if (in_array($model->item_type_id, [ItemType::ITEM_TYPE_EM_PRINT, ItemType::ITEM_TYPE_EM_HARDWARE])) { ?>
                <?= $form->field($model, 'supplier_id')->widget(Select2::className(), [
                    'model' => $model,
                    'attribute' => 'supplier_id',
                    'data' => ArrayHelper::map(Company::find()->andWhere(['id' => $model->supplier_id])->all(), 'id', 'name'),
                    'options' => [
                        'multiple' => false,
                        'theme' => 'krajee',
                        'placeholder' => '',
                        'language' => 'en-US',
                        'width' => '100%',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 2,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                        ],
                        'ajax' => [
                            'url' => Url::to(['company/json-list']),
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                    ],
                ]) ?>
                <?= $form->field($model, 'purchase_order')->textInput(); ?>
                <?= $form->field($model, 'supply_date')->widget(DatePicker::className(), [
                    'layout' => '{picker}{input}',
                    'options' => ['class' => 'form-control'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd',
                        //'orientation' => 'top left',
                    ],
                ]) ?>
            <?php } ?>

        </div>
    </div>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?php if ($model->isNewRecord) echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>
