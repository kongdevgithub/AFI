<?php
/**
 * @var yii\web\View $this
 * @var app\models\Feedback $model
 * @var string $complete
 * @var string $key
 */

use app\components\ReturnUrl;
use kartik\date\DatePickerAsset;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

DatePickerAsset::register($this);

$this->title = Yii::t('app', 'Thank you so much!');
$this->params['heading'] = '';

?>

<div class="feedback-thank-you">

    <?php if ($complete) { ?>

        <div class="jumbotron">
            <h1><?= Yii::t('app', 'Thanks again!') ?></h1>
            <h2><?= Yii::t('app', 'We appreciate you taking the time to help us serve you better.') ?></h2>
        </div>

    <?php } else { ?>

        <div class="jumbotron">
            <h1><?= $this->title ?></h1>
            <h2><?= Yii::t('app', 'Your feedback means the world to us.') ?></h2>
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'Feedback',
            'enableClientValidation' => false,
        ]); ?>
        <?= Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>
        <?= $form->errorSummary($model); ?>

        <div class="row">
            <div class="col-md-6">
                <div class="small-box bg-blue">
                    <div class="inner">
                        <p><?= Yii::t('app', 'You gave us a score of') ?>:</p>
                        <h3>
                            <span id="feedback-score-container"><?= $model->score ?></span><sup style="font-size: 20px">/10</sup>
                        </h3>
                        <p><?= Html::a(Yii::t('app', 'change score'), 'javascript:void(0);', [
                                'id' => 'feedback-change-score',
                                'class' => 'btn btn-primary btn-xs',
                            ]) ?></p>
                        <?= $form->field($model, 'score')->radioList([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10], ['inline' => true])->label(false) ?>
                    </div>
                    <div class="icon">
                        <i class="fa fa-star-o"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-body">
                        <?= $form->field($model, 'comments')->textarea()->label(Yii::t('app', 'What is the most important reason for your score?')) ?>
                        <?= Html::submitButton(Yii::t('app', 'Save Additional Feedback'), [
                            'id' => 'save-' . $model->formName(),
                            'class' => 'btn btn-success'
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    <?php } ?>

</div>

<?php \app\widgets\JavaScript::begin() ?>
<script>
    $('#feedback-score').hide();
    $('#feedback-change-score').click(function () {
        $(this).parent().hide();
        $('#feedback-score').show();
    });
    $('input[name=Feedback\\[score\\]]').change(function () {
        var score = $(this).val();
        $('#feedback-score').hide();
        $('#feedback-change-score').parent().show();
        $('#feedback-score-container').html(score);
        $.get('<?=Url::to(['feedback/ajax-score', 'id' => $model->id, 'score' => '--score--', 'key' => $key])?>'.replace('--score--', score));
    });
</script>
<?php \app\widgets\JavaScript::end() ?>

