<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var schmunk42\giiant\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;
use cornernote\returnurl\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 */

$this->title = <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?> . ': ' . $model-><?= $generator->getNameAttribute() ?>;
$this->params['heading'] = $this->title;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model-><?= $generator->getNameAttribute() ?>, 'url' => ['view', <?= $urlParams ?>]];
$this->params['breadcrumbs'][] = <?= $generator->generateString('Copy') ?>;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass),'-', true) ?>-copy">

    <?= '<?= ' ?>$this->render('_menu', compact('model'));<?= ' ?>' ?>

    <?= "<?php " ?>echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
