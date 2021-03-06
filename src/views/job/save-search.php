<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;
use app\components\ReturnUrl;
use yii\helpers\Json;
use yii\helpers\VarDumper;

/**
 * @var yii\web\View $this
 * @var \app\models\Search $search
 */

$this->title = Yii::t('app', 'Save Search');

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>
<div class="job-save-search">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Search Details'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'SaveSearch',
                'enableClientValidation' => false,
            ]);
            $items = [];
            echo Html::hiddenInput('ru', $ru);

            echo $form->field($search, 'name')->textInput(['maxSize' => true]);

            echo Html::tag('h3', Yii::t('app', 'Search Params'));
            foreach (Json::decode($search->model_params) as $k => $v) {
                if (!empty($v)) {
                    $items[] = $k . ' = ' . VarDumper::export($v);
                }
                if (is_array($v)) {
                    foreach ($v as $kk => $vv) {
                        echo Html::hiddenInput("JobSearch[$k][$kk]", $vv);
                    }
                } else {
                    echo Html::hiddenInput("JobSearch[$k]", $v);
                }
            }
            echo Html::ul($items);

            echo Html::submitButton('<span class="fa fa-download"></span> ' . Yii::t('app', 'Save'), [
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>
        </div>
    </div>


</div>
