<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var array $searchParams
 */

$this->title = Yii::t('goldoc', 'Export Products');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Products'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken();
?>
<div class="product-export">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('goldoc', 'Search Params'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'ProductExport',
                'enableClientValidation' => false,
            ]);
            $items = [];
            echo Html::hiddenInput('ru', $ru);
            foreach ($searchParams as $k => $v) {
                if (!empty($v)) {
                    $items[] = $k . ' = ' . \yii\helpers\VarDumper::export($v);
                }
                if (is_array($v)) {
                    foreach ($v as $kk => $vv) {
                        echo Html::hiddenInput("ProductSearch[$k][$kk]", $vv);
                    }
                } else {
                    echo Html::hiddenInput("ProductSearch[$k]", $v);
                }
            }
            echo Html::ul($items);

            echo Html::submitButton('<span class="fa fa-download"></span> ' . Yii::t('goldoc', 'Export'), [
                'class' => 'btn btn-success'
            ]);
            ActiveForm::end();
            ?>
        </div>
    </div>


</div>
