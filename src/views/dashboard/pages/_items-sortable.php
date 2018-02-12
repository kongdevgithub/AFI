<?php

use app\models\search\ItemSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\Sortable;
use yii\web\JsExpression;


/**
 * @var array $params
 * @var ActiveDataProvider $dataProvider
 * @var array $showColumns
 */
$itemSearch = new ItemSearch;
$dataProvider = $itemSearch->search(isset($params) ? $params : []);

if (isset($pageSize)) {
    $dataProvider->pagination->pageSize = $pageSize;
}
if (isset($orderBy)) {
    $dataProvider->query->orderBy($orderBy);
}
if (!isset($showColumns)) {
    $showColumns = ['name'];
}

$title = isset($title) ? $title : '';

if (!isset($headerCallback)) {
    $headerCallback = function ($dataProvider) {
        return '';
    };
}


$sortables = [];
foreach ($dataProvider->getModels() as $model) {
    $sortables[] = [
        'content' => $this->render('_item-view', [
            'model' => $model,
            'showColumns' => ArrayHelper::merge($showColumns, ['sortable']),
        ]),
        'options' => [
            'id' => 'Item_' . $model->id,
            'data-key' => $model->id,
        ],
    ];
}
?>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title pull-left">
            <?= $title ?>
        </h3>
        <div class="pull-right">
            <?= $headerCallback($dataProvider) ?>
        </div>
    </div>
    <div class="panel-body no-padding">
        <table class="no-margin kv-grid-table table table-striped kv-table-wrap">
            <?php
            if ($sortables) {
                echo Sortable::widget([
                    'items' => $sortables,
                    'options' => [
                        'tag' => 'tbody',
                    ],
                    'itemOptions' => [
                        'tag' => 'tr',
                    ],
                    'clientOptions' => [
                        'axis' => 'y',
                        'forcePlaceholderSize' => true,
                        'cursor' => 'move',
                        'handle' => '.sortable-handle',
                        'start' => new JsExpression('function(e, ui){
                            ui.placeholder.height(ui.item.height());
                        }'),
                        'update' => new JsExpression("function(event, ui) {
                            $.ajax({
                                type: 'POST',
                                url: '" . Url::to(['/sortable/sort']) . "',
                                data: $(event.target).sortable('serialize')
                            });
                        }"),
                    ],
                ]);
            } else {
                ?>
                <table class="no-margin kv-grid-table table table-striped kv-table-wrap">
                    <tbody>
                    <tr>
                        <td colspan="2">
                            <?= Html::tag('div', Yii::t('app', 'No results found.'), ['class' => 'empty']) ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <?php

            }
            ?>
        </table>
    </div>
</div>
