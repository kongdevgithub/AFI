<?php

use app\models\AccountTerm;
use app\models\Address;
use app\models\Company;
use app\models\JobType;
use app\models\PriceStructure;
use app\models\Rollout;
use app\models\Contact;
use app\components\ReturnUrl;
use dektrium\user\models\User;
use kartik\file\FileInput;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\DetailView;
use zhuravljov\widgets\DatePicker;

/**
 * @var yii\web\View $this
 * @var app\models\Item $model
 * @var app\models\Attachment $artwork
 * @var ActiveForm $form
 */

$this->title = $model->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->product->job->vid . ': ' . $model->product->job->name, 'url' => ['/job/view', 'id' => $model->product->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->product->id . ': ' . $model->product->name, 'url' => ['/product/view', 'id' => $model->product->id]];
$this->params['breadcrumbs'][] = ['label' => 'item-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Artwork');

$this->registerCss('.file-preview .fileinput-remove { display: none; }');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="item-artwork">

    <?php if ($model->artwork) { ?>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Current Artwork File'); ?></h3>
                <div class="box-tools pull-right">
                    <?php
                    $links = [];
                    if (Yii::$app->user->can('app_item_artwork-delete', ['route' => true])) {
                        $links[] = Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Delete Artwork'), ['/item/artwork-delete', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()], [
                            'data-confirm' => Yii::t('app', 'Are you sure?'),
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
            <h3 class="box-title"><?= Yii::t('app', 'Upload New Artwork File'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'Item',
                'formConfig' => ['labelSpan' => 0],
                'enableClientValidation' => false,
                'options' => ['enctype' => 'multipart/form-data'],
            ]);
            echo Html::hiddenInput('ru', $ru);
            echo $form->errorSummary($artwork);

            //echo $form->field($artwork, 'upload')->fileInput(['multiple' => true, 'accept' => 'image/*']);
            echo $form->field($artwork, 'upload')->widget(FileInput::className(), [
                'options' => ['accept' => 'image/*'],
                'pluginOptions' => [
                    'showPreview' => true,
                    'showCaption' => false,
                    'showRemove' => false,
                    'showUpload' => false,
                ],
            ]);

            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>
        </div>
    </div>

</div>
