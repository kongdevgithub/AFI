<?php
use app\models\Address;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\form\JobForm $model
 * @var yii\widgets\ActiveForm $form
 * @var string $key
 */

?>

<td>
    <?= $form->field($address, 'type')->dropDownList(Address::optsType(), [
        'id' => "Addresses_{$key}_type",
        'name' => "Addresses[$key][type]",
        'prompt' => '',
        'class' => 'form-control address-type',
    ])->label(false) ?>
</td>
<td>
    <?= $form->field($address, 'name')->textInput([
        'id' => "Addresses_{$key}_name",
        'name' => "Addresses[$key][name]",
        'class' => 'form-control address-name',
    ])->label(false) ?>
</td>
<td>
    <?= $form->field($address, 'street')->textarea([
        'id' => "Addresses_{$key}_street",
        'name' => "Addresses[$key][street]",
        'class' => 'form-control address-street',
    ])->label(false) ?>
</td>
<td>
    <?= $form->field($address, 'postcode')->textInput([
        'id' => "Addresses_{$key}_postcode",
        'name' => "Addresses[$key][postcode]",
        'class' => 'form-control address-postcode',
    ])->label(false) ?>
</td>
<td>
    <?= $form->field($address, 'city')->textInput([
        'id' => "Addresses_{$key}_city",
        'name' => "Addresses[$key][city]",
        'class' => 'form-control address-city',
    ])->label(false) ?>
</td>
<td>
    <?= $form->field($address, 'state')->textInput([
        'id' => "Addresses_{$key}_state",
        'name' => "Addresses[$key][state]",
        'class' => 'form-control address-state',
    ])->label(false) ?>
</td>
<td>
    <?= $form->field($address, 'country')->textInput([
        'id' => "Addresses_{$key}_country",
        'name' => "Addresses[$key][country]",
        'class' => 'form-control address-country',
    ])->label(false) ?>
</td>
<td style="width:30px;">
    <?= Html::a('<i class="fa fa-times"></i>', 'javascript:void(0);', [
        'class' => 'job-remove-address-button btn btn-default btn-xs',
    ]) ?>
</td>