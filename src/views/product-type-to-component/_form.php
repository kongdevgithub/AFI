<?php

use app\components\quotes\components\BaseComponentQuote;
use app\models\Component;
use app\models\ProductTypeToItemType;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\ProductTypeToComponent $model
 * @var ActiveForm $form
 */
?>

<div class="product-type-to-component-form">

    <?php $form = ActiveForm::begin([
        'id' => 'ProductTypeToComponent',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Component'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            echo $form->field($model, 'product_type_to_item_type_id')->widget(Select2::className(), [
                'model' => $model,
                'attribute' => 'product_type_to_item_type_id',
                'data' => $model->productType ? ArrayHelper::map($model->productType->productTypeToItemTypes, 'id', 'name') : [],
                'options' => [
                    'allowClear' => true,
                    'placeholder' => '',
                    'multiple' => false,
                ]
            ]);
            ?>

            <?= $form->field($model, 'component_id')->widget(Select2::className(), [
                'name' => 'class_name',
                'model' => $model,
                'attribute' => 'component_id',
                'data' => ArrayHelper::map(Component::find()->all(), 'id', 'label'),
                'options' => [
                    'allowClear' => true,
                    'placeholder' => '',
                    'multiple' => false,
                ]
            ]); ?>

            <?= $form->field($model, 'describes_item')->checkbox(); ?>

            <?= $form->field($model, 'quote_class')->dropDownList(BaseComponentQuote::opts(), ['prompt' => Yii::t('app', 'Inherit')]) ?>

            <?= $form->field($model, 'quantity')->textInput() ?>

            <?= $form->field($model, 'quantity_factor')->textarea()->hint(Yii::t('app', 'Leave empty to inherit from component.<br><br>Format:<br>quantity1 factor1<br>quantity2 factor2<br><br>Eg:<br>0 2<br>10 1.8<br>100 1.5')) ?>
        </div>
    </div>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?php echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>
    <?php if (!$model->isNewRecord) echo Html::a('<span class="fa fa-trash"></span> ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data-confirm' => Yii::t('app', 'Are you sure?'),
        'data-method' => 'post',
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
