<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var schmunk42\giiant\generators\crud\Generator $generator
 */

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\helpers\Url;
use cornernote\returnurl\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 */

$this->title = <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?> . ': ' . <?= $generator->generateString('Create') ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = <?= $generator->generateString('Create') ?>;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass), '-', true) ?>-create">

    <?= "<?= " ?>$this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
