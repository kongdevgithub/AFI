<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var array $searchParams
 */

$this->title = Yii::t('app', 'Export Items');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Items'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>
<div class="item-export">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Search Params'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'ItemExport',
                'enableClientValidation' => false,
            ]);
            $items = [];
            echo Html::hiddenInput('ru', $ru);
            foreach ($searchParams as $k => $v) {
                if (!empty($v)) {
                    $items[] = $k . ' = ' . $v;
                }
                echo Html::hiddenInput("ItemSearch[$k]", $v);
            }
            echo Html::ul($items);

            echo Html::submitButton('<span class="fa fa-download"></span> ' . Yii::t('app', 'Export'), [
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>
        </div>
    </div>


</div>
