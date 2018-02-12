<?php

use kartik\form\ActiveForm;
use yii\bootstrap\Collapse;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\JobForm $model
 * @var app\models\Job $modelCopy
 * @var ActiveForm $form
 */

?>

<div class="row">
    <div class="col-md-4">
        <?php
        echo $form->field($model, 'copy_notes')->checkbox(['label' => Yii::t('app', 'Copy Job Notes')]);
        echo $form->field($model, 'copy_attachments')->checkbox(['label' => Yii::t('app', 'Copy Job Attachments')]);
        ?>
    </div>
    <div class="col-md-8">
        <?php
        foreach ($modelCopy->products as $product) {
            echo Html::tag('h4', 'product-' . $product->id . ': ' . $product->name);
            echo $form->field($model, "productsMeta[$product->id][copy_notes]")->checkbox(['label' => Yii::t('app', 'Copy Product Notes')]);
            echo $form->field($model, "productsMeta[$product->id][copy_attachments]")->checkbox(['label' => Yii::t('app', 'Copy Product Attachments')]);
        }
        ?>
    </div>
</div>

