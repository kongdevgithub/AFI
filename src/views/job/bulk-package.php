<?php

use app\components\PrintSpool;
use app\components\ReturnUrl;
use app\widgets\JavaScript;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\ItemBulkPackageForm $model
 */

$this->title = $model->job->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="job-despatch">

    <?= $this->render('_menu', ['model' => $model->job]); ?>

    <?php

    $form = ActiveForm::begin([
        'id' => 'Job',
        'encodeErrorSummary' => false,
        'fieldConfig' => [
            'errorOptions' => [
                'encode' => false,
                'class' => 'help-block',
            ],
        ],
    ]);
    echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
    echo $form->errorSummary($model);

    echo $form->field($model, 'ids')->textarea();
    echo $form->field($model, 'package_id')->textInput();
    echo $form->field($model, 'print')->checkboxList($model->optsPrint(), ['inline' => true]);
    echo $form->field($model, 'print_spool')->dropDownList(PrintSpool::optsSpool(), ['prompt' => '']);

    echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]);
    ActiveForm::end();

    JavaScript::begin();
    ?>
    <script>
        $('#packageitemform-ids').focus();
    </script>
    <?php
    JavaScript::end();
    ?>

</div>
