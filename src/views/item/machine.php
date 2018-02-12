<?php

use app\models\Machine;
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Item $model
 * @var app\models\ItemToMachine $itemToMachine
 * @var int $machine_type_id
 * @var ActiveForm $form
 */

$this->title = $model->getTitle();

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['/job/index']];
$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->product->job->vid . ': ' . $model->product->job->name, 'url' => ['/job/view', 'id' => $model->product->job->id]];
$this->params['breadcrumbs'][] = ['label' => 'product-' . $model->product->id . ': ' . $model->product->name, 'url' => ['/product/view', 'id' => $model->product->id]];
$this->params['breadcrumbs'][] = ['label' => 'item-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Machine');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>

<div class="item-machine">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Machine'); ?></h3>
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
            echo $form->errorSummary($itemToMachine);

            $machines = ArrayHelper::map(Machine::find()->notDeleted()->andWhere(['machine_type_id' => $machine_type_id])->all(), 'id', 'name');
            echo $form->field($itemToMachine, 'machine_id')->dropDownList($machines, ['prompt' => '']);
            echo $form->field($itemToMachine, 'details')->textarea();

            echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
                'id' => 'save-' . $model->formName(),
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>
        </div>
    </div>

</div>

