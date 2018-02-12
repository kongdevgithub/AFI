<?php

use kartik\file\FileInput;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use cornernote\returnurl\ReturnUrl;

/**
 *
 * @var yii\web\View $this
 * @var app\modules\goldoc\models\form\BulkProductArtworkForm $model
 */
$this->title = Yii::t('goldoc', 'Product') . ': ' . Yii::t('goldoc', 'Bulk Artwork');
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Bulk Artwork');

$this->registerCss('.kv-file-zoom, .fileinput-cancel, .file-preview .fileinput-remove { display: none; }');

?>
<div class="product-bulk-artwork">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('goldoc', 'Upload New Artwork File'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'Product',
                'formConfig' => ['labelSpan' => 0],
                'enableClientValidation' => false,
                'options' => ['enctype' => 'multipart/form-data'],
            ]);
            echo Html::hiddenInput('confirm', 1);
            echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
            echo $form->errorSummary($model);
            foreach ($model->ids as $id) {
                echo Html::hiddenInput('ids[]', $id);
            }

            //echo $form->field($artwork, 'upload')->fileInput(['multiple' => true, 'accept' => 'image/*']);
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

            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('goldoc', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Products Selected'); ?></h3>
        </div>
        <div class="box-body">
            <?= Html::ul(ArrayHelper::map($model->getProducts(), 'id', 'title')) ?>
        </div>
    </div>

</div>
