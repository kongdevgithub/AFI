<?php

use app\models\Component;
use app\models\Option;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 *
 * @var yii\web\View $this
 * @var app\models\form\CompanyRateForm $model
 * @var app\models\CompanyRateOption $companyRateOption
 * @var kartik\form\ActiveForm $form
 * @var string $key
 */
?>
<td>
    <?= $form->field($companyRateOption, 'option_id')->dropDownList(ArrayHelper::map(Option::find()->all(), 'id', 'name'), [
        'id' => "CompanyRateOptions_{$key}_option_id",
        'name' => "CompanyRateOptions[$key][option_id]",
        'class' => 'form-control' . ($key == '__id__' ? '' : ' addSelect2'),
        'prompt' => '',
    ])->label(false) ?>
</td>
<td>
    <?= $form->field($companyRateOption, 'component_id')->dropDownList(ArrayHelper::map(Component::find()->all(), 'id', 'name'), [
        'id' => "CompanyRateOptions_{$key}_component_id",
        'name' => "CompanyRateOptions[$key][component_id]",
        'class' => 'form-control' . ($key == '__id__' ? '' : ' addSelect2'),
        'prompt' => '',
    ])->label(false) ?>
</td>
<td>
    <?= Html::a('Remove', 'javascript:void(0);', [
        'class' => 'company-rate-remove-company-rate-option-button btn btn-default btn-xs',
    ]) ?>
</td>