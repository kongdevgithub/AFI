<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var schmunk42\giiant\generators\crud\Generator $generator
 */

/** @var \yii\db\ActiveRecord $model */
$model = new $generator->modelClass;
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->getTableSchema()->columnNames;
}

echo "<?php\n";
?>

use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 * @var kartik\form\ActiveForm $form
 */

?>

<div class="<?= \yii\helpers\Inflector::camel2id(StringHelper::basename($generator->modelClass), '-', true) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'id' => '<?= $model->formName() ?>',
        //'type' => ActiveForm::TYPE_HORIZONTAL,
    ]); ?>

    <?= "<?=" ?> Html::hiddenInput('ru', ReturnUrl::getRequestToken()); ?>

    <?= "<?=" ?> $form->errorSummary($model); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= '<?= ' ?><?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?> ?></h3>
        </div>
        <div class="box-body">
            <?php foreach ($safeAttributes as $attribute) {
                $prepend = $generator->prependActiveField($attribute, $model);
                $field = $generator->activeField($attribute, $model);
                $append = $generator->appendActiveField($attribute, $model);

                if ($prepend) {
                    echo "\n    <?php " . $prepend . " ?>";
                }
                if ($field) {
                    echo "\n    <?= " . $field . " ?>";
                }
                if ($append) {
                    echo "\n    <?php " . $append . " ?>";
                }
                echo "\n";
            } ?>
        </div>
    </div>

    <?= "<?= " ?>Html::submitButton('<span class="fa fa-check"></span> ' . ($model->isNewRecord ? <?= $generator->generateString('Create') ?> : <?= $generator->generateString('Save') ?>), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]); ?>
    <?= "<?php //echo " ?>if($model->isNewRecord) echo Html::a('<span class="fa fa-times"></span> ' . <?= $generator->generateString('Cancel') ?>, ReturnUrl::getUrl(['index']), ['class' => 'btn btn-default']) ?>

    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
