<?php

use app\components\quotes\items\BaseItemQuote;
use app\models\Item;
use app\models\ItemType;
use app\models\ProductType;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use \dmstr\bootstrap\Tabs;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\ProductTypeToItemType $model
 * @var yii\bootstrap\ActiveForm $form
 */

?>

<div class="product-type-to-item-type-form">

    <?php $form = ActiveForm::begin([
        'id' => 'ProductTypeToItemType',
        //'layout' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Item Type'); ?></h3>
        </div>
        <div class="box-body">

            <?= $form->field($model, 'product_type_id')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'item_type_id')->widget(Select2::className(), [
                'name' => 'class_name',
                'model' => $model,
                'attribute' => 'item_type_id',
                'data' => Item::optsItemType(),
                'options' => [
                    'placeholder' => '',
                    'multiple' => false,
                ]
            ]); ?>

            <?= $form->field($model, 'quote_class')->dropDownList(BaseItemQuote::opts(), [
                'prompt' => '',
            ]) ?>

            <?= $form->field($model, 'quantity')->textInput() ?>

        </div>
    </div>


    <?= Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?php echo Html::a('<span class="fa fa-times"></span> ' . Yii::t('app', 'Cancel'), ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>
    <?php if (!$model->isNewRecord) echo Html::a('<span class="fa fa-trash"></span> ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()], [
        'class' => 'btn btn-danger',
        'data-confirm' => Yii::t('app', 'Are you sure?'),
        'data-method' => 'post',
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
