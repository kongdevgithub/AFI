<?php

use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\bootstrap\ActiveForm;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Feedback $model
 */

$this->title = Yii::t('app', 'Dismiss') . ' ' . Yii::t('app', 'Feedback');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Feedbacks'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Dismiss');
?>
<div class="feedback-dismiss">

    <?php $form = ActiveForm::begin([
        'id' => 'Feedback',
        'layout' => 'horizontal',
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= $form->errorSummary($model); ?>


    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Feedback'); ?></h3>
        </div>
        <div class="box-body">

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'contact.link:raw',
                    'score',
                    'comments',
                    'submitted_at:dateTime',
                ],
            ]); ?>

            <?= $form->field($model, 'staff_comments')->textarea() ?>
        </div>
    </div>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>


</div>
