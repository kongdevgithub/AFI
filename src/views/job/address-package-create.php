<?php

use app\components\PrintSpool;
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 * @var app\models\form\AddressPackageCreateForm $formModel
 */

$this->title = Yii::t('app', 'Create Packages from Addresses');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;

$ru = isset($ru) ? $ru : ReturnUrl::getRequestToken()

?>
<div class="job-address-package-create">

    <?php
    $form = ActiveForm::begin([
        'id' => 'AddressPackageCreate',
    ]);
    echo Html::hiddenInput('ru', $ru);
    echo $form->errorSummary($formModel);
    ?>
    <table class="table">
        <thead>
        <tr>
            <th></th>
            <th><?= Yii::t('app', 'Type') ?></th>
            <th><?= Yii::t('app', 'Name') ?></th>
            <th><?= Yii::t('app', 'Street') ?></th>
            <th><?= Yii::t('app', 'City') ?></th>
            <th><?= Yii::t('app', 'Postcode') ?></th>
            <th><?= Yii::t('app', 'State') ?></th>
            <th><?= Yii::t('app', 'Country') ?></th>
            <th><?= Yii::t('app', 'Contact') ?></th>
            <th><?= Yii::t('app', 'Phone') ?></th>
            <th><?= Yii::t('app', 'Instructions') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($model->addresses as $address) {
            ?>
            <tr>
                <td><?= Html::checkbox("ids[$address->id]", false, ['value' => $address->id]) ?></td>
                <td><?= $address->type ?></td>
                <td><?= $address->name ?></td>
                <td><?= $address->street ?></td>
                <td><?= $address->city ?></td>
                <td><?= $address->postcode ?></td>
                <td><?= $address->state ?></td>
                <td><?= $address->country ?></td>
                <td><?= $address->contact ?></td>
                <td><?= $address->phone ?></td>
                <td><?= $address->instructions ?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php

    echo $form->field($formModel, 'print_labels')->checkbox();
    echo $form->field($formModel, 'print_spool')->dropDownList(PrintSpool::optsSpool());

    echo Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Create'), [
        'id' => 'save-' . $formModel->formName(),
        'class' => 'btn btn-success'
    ]);
    ActiveForm::end();
    ?>
</div>

