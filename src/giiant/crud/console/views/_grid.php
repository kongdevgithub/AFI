<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use cornernote\gii\helpers\TabPadding;

/**
 * @var yii\web\View $this
 * @var schmunk42\giiant\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

/** @var \yii\db\ActiveRecord $model */
$model = new $generator->modelClass;
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->getTableSchema()->columnNames;
}

$class = $generator->modelClass;
$pk = (new $class)->primaryKey()[0];

echo "<?php\n";
?>

use app\components\GridView;
use app\components\ReturnUrl;
use <?= $generator->modelClass ?>;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var <?= ltrim($generator->searchModelClass, '\\') ?> $searchModel
 */



$columns = [];
$columns[] = [
    'attribute' => '<?=$pk?>',
    'value' => function ($model) {
        /** @var <?= StringHelper::basename($generator->modelClass) ?> $model */
        return Yii::$app->user->can('app_<?= Inflector::camel2id(StringHelper::basename($generator->modelClass),'-', true) ?>_view', ['route' => true]) ? Html::a($model->id, ['view', <?= $urlParams ?>]) : $model-><?= $pk ?>;
    },
    'format' => 'raw',
];
<?php
$count = 0;
foreach ($safeAttributes as $attribute) {
    $format = $generator->columnFormat($attribute,$model);
    if ($format == false) continue;
    echo (++$count < 10) ? "\$columns[] = {$format};\n" : "        /* \$columns[] = " . trim($format) . "*/\n";
}
?>

$gridActions = [];
$gridActions[] = Html::button('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), [
    'class' => 'btn btn-default btn-xs modal-remote-form',
    'data-toggle' => 'modal',
    'data-target' => '#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass),'-', true) ?>-searchModal',
]);
if (Yii::$app->user->can('app_<?= Inflector::camel2id(StringHelper::basename($generator->modelClass),'-', true) ?>_create', ['route' => true])) {
    $gridActions[] = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
        'create',
        'ru' => ReturnUrl::getToken()
    ], ['class' => 'btn btn-default btn-xs']);
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
    //'multiActions' => $multiActions,
    'gridActions' => $gridActions,
    'panel' => [
        'heading' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>,
    ],
]);
echo $this->render('_search', ['model' => $searchModel]);

