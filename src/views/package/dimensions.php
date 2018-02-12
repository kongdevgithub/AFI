<?php

/**
 * @var yii\web\View $this
 * @var array $ids
 * @var app\models\form\PackageDimensionsForm $model
 */
use app\models\PackageType;
use app\components\ReturnUrl;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Assign Dimensions to Packages');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Packages'), 'url' => ['package/index']];
//$this->params['breadcrumbs'][] = ['label' => 'package-' . $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Status');
?>

<div class="package-dimensions">

    <?php
    $form = ActiveForm::begin([
        'id' => 'PackageDimensions',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        //'formConfig' => ['labelSpan' => 0],
        'action' => ['dimensions', 'confirm' => true],
        'enableClientValidation' => false,
        'encodeErrorSummary' => false,
    ]);
    echo $form->errorSummary($model);
    foreach ($model->ids as $id) {
        echo Html::hiddenInput('ids[]', $id);
    }
    echo Html::hiddenInput('ru', ReturnUrl::getRequestToken());
    ?>

    <?php
    $items = ArrayHelper::map(PackageType::find()->all(), 'id', 'label');
    echo $form->field($model, 'package_type_id')->dropDownList($items, [
        'prompt' => '',
    ])->label(Yii::t('app', 'Lookup'));
    echo $form->field($model, 'type')->textInput();
    echo $form->field($model, 'length')->textInput();
    echo $form->field($model, 'width')->textInput();
    echo $form->field($model, 'height')->textInput();
    echo $form->field($model, 'dead_weight')->textInput();
    ?>

    <?= Html::submitButton('<span class="fa fa-check"></span> ' . Yii::t('app', 'Save'), [
        //'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>

    <?php ActiveForm::end(); ?>

    <?php \app\widgets\JavaScript::begin() ?>
    <script>
        $('#packagedimensionsform-package_type_id').change(function () {
            populatePackageDimensions($(this).val());
        });
        function populatePackageDimensions(package_type_id) {
            var url = '<?= Url::to(['package-type/json-list', 'PackageTypeSearch' => ['id' => '-package_type_id-']]) ?>';
            $.ajax({
                url: url.replace('-package_type_id-', package_type_id),
                success: function (data) {
                    data.forEach(function (packageType) {
                        $('#packagedimensionsform-type').val(packageType.type);
                        $('#packagedimensionsform-width').val(packageType.width);
                        $('#packagedimensionsform-length').val(packageType.length);
                        $('#packagedimensionsform-height').val(packageType.height);
                        $('#packagedimensionsform-dead_weight').val(packageType.dead_weight);
                    });
                }
            });
        }
    </script>
    <?php \app\widgets\JavaScript::end() ?>
</div>

