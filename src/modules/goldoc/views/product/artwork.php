<?php

use app\components\ReturnUrl;
use kartik\file\FileInput;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Attachment $artwork
 * @var ActiveForm $form
 */

$this->title = Yii::t('goldoc', 'Product') . ': ' . $model->id;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('goldoc', 'Update');

$this->registerCss('.file-preview .fileinput-remove { display: none; }');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="product-artwork">

    <?php if ($model->artwork) { ?>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('goldoc', 'Current Artwork File'); ?></h3>
                <div class="box-tools pull-right">
                    <?php
                    $links = [];
                    if (Yii::$app->user->can('goldoc_product_artwork-delete', ['route' => true])) {
                        $links[] = Html::a('<i class="fa fa-trash"></i> ' . Yii::t('goldoc', 'Delete Artwork'), ['product/artwork-delete', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()], [
                            'data-confirm' => Yii::t('goldoc', 'Are you sure?'),
                            'data-method' => 'post',
                            'class' => 'btn btn-box-tool',
                        ]);
                    }
                    echo implode(' ', $links);
                    ?>
                </div>
            </div>
            <div class="box-body text-center">
                <?php
                echo Html::img($model->artwork->getFileUrl('800x800'));

                $filename = explode('-', $model->artwork->filename . '.' . $model->artwork->extension);
                array_shift($filename);
                array_shift($filename);
                $filename = implode('-', $filename);
                echo DetailView::widget([
                    'model' => $model->artwork,
                    'attributes' => [
                        [
                            'attribute' => 'filename',
                            'value' => $filename,
                        ],
                        [
                            'attribute' => 'created_at',
                            'format' => 'dateTime',
                        ],
                    ],
                    'options' => ['class' => 'table table-condensed detail-view'],
                ]);
                ?>
            </div>
        </div>
    <?php } ?>

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
            echo Html::hiddenInput('ru', $ru);
            echo $form->errorSummary($artwork);

            //echo $form->field($artwork, 'upload')->fileInput(['multiple' => true, 'accept' => 'image/*']);
            echo $form->field($artwork, 'upload')->widget(FileInput::className(), [
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

</div>
