<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var array $searchParams
 */

$this->title = Yii::t('app', 'Export Components');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Components'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>
<div class="component-export">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Search Params'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'ComponentExport',
                'enableClientValidation' => false,
            ]);
            $components = [];
            echo Html::hiddenInput('ru', $ru);
            foreach ($searchParams as $k => $v) {
                if (!empty($v)) {
                    $components[] = $k . ' = ' . $v;
                }
                echo Html::hiddenInput("ComponentSearch[$k]", $v);
            }
            echo Html::ul($components);

            echo Html::submitButton('<span class="fa fa-download"></span> ' . Yii::t('app', 'Export'), [
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>
        </div>
    </div>


</div>
