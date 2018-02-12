<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var schmunk42\giiant\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();

/** @var \yii\db\ActiveRecord $model */
$model = new $generator->modelClass;
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->getTableSchema()->columnNames;
}

echo "<?php\n";
?>

use app\components\fields\BaseField;
use app\models\ProductType;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\widgets\DetailView;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 */

$this->title = <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?> . ': ' . $model-><?= $generator->getNameAttribute() ?>;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $model-><?= $generator->getNameAttribute() ?>;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass), '-', true) ?>-view">

    <?= '<?= ' ?>$this->render('_menu', ['model' => $model]);<?= ' ?>' ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= "<?= " ?><?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?> ?></h3>
        </div>
        <div class="box-body">
            <?= "<?= " ?>DetailView::widget([
            'model' => $model,
            'attributes' => [
            <?php
            foreach ($safeAttributes as $attribute) {
                $format = $generator->attributeFormat($attribute);
                if ($format === false) {
                    continue;
                } else {
                    echo '            ' . trim($format) . ",\n";
                }
            }
            ?>
            ],
            ]); ?>
        </div>
    </div>

</div>
