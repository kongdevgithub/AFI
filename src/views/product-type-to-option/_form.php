<?php

use app\components\fields\BaseField;
use app\components\fields\ComponentField;
use app\components\quotes\components\BaseComponentQuote;
use app\models\Option;
use app\models\ProductType;
use app\models\ProductTypeToOption;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var ProductTypeToOption $model
 * @var ActiveForm $form
 */
?>

<div class="product-type-to-option-form">

    <?php $form = ActiveForm::begin([
        'id' => 'ProductTypeToOption',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Option'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            echo $form->field($model, 'product_type_to_item_type_id')->widget(Select2::className(), [
                'model' => $model,
                'attribute' => 'product_type_to_item_type_id',
                'data' => $model->productType ? ArrayHelper::map($model->productType->getProductTypeToItemTypes()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name') : [],
                'options' => [
                    'allowClear' => true,
                    'placeholder' => '',
                    'multiple' => false,
                ]
            ]);
            ?>

            <?php
            echo $form->field($model, 'option_id')->widget(Select2::className(), [
                'model' => $model,
                'attribute' => 'option_id',
                'data' => ArrayHelper::map(Option::find()->notDeleted()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
                'options' => [
                    'allowClear' => true,
                    'placeholder' => '',
                    'multiple' => false,
                ]
            ]);
            ?>

            <?= $form->field($model, 'required')->checkbox(); ?>

            <?= $form->field($model, 'describes_item')->checkbox(); ?>

            <?php
            if ($model->option) {
                /** @var BaseField $field */
                $field = new $model->option->field_class;
                if ($field instanceof ComponentField) {
                    echo $form->field($model, 'quote_class')->dropDownList(BaseComponentQuote::opts(), ['prompt' => Yii::t('app', 'Inherit')]);
                    echo $form->field($model, 'quantity_factor')->textarea()->hint(Yii::t('app', 'Leave empty to inherit from component.<br><br>Format:<br>quantity1 factor1<br>quantity2 factor2<br><br>Eg:<br>0 2<br>10 1.8<br>100 1.5'));
                }
                echo $field->fieldProductType($model, $form);
            }
            ?>

            <?= $form->field($model, 'config')->textarea() ?>

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
