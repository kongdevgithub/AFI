<?php

use app\components\ReturnUrl;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\jui\Sortable;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var \yii\db\ActiveRecord $model
 */

$title = isset($title) ? $title : Yii::t('app', 'Notes');
$modelName = isset($modelName) ? $modelName : $model->className();
$modelId = isset($modelId) ? $modelId : $model->getPrimaryKey();
$ru = isset($ru) ? $ru : ReturnUrl::getToken();
$relationName = isset($relationName) ? $relationName : 'notes';
$showActions = isset($showActions) ? $showActions : true;
?>

<div class="box">
    <div class="box-header box-solid">
        <h3 class="box-title"><?= $title ?></h3>
        <?php if ($showActions) { ?>
            <div class="box-tools pull-right">
                <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create'), [
                    '//note/create',
                    'Note' => ['model_name' => $modelName, 'model_id' => $modelId],
                    'ru' => $ru,
                ], ['class' => 'btn btn-box-tool modal-remote']) ?>
            </div>
        <?php } ?>
    </div>
    <div class="box-body">
        <?php
        $sortables = [];
        foreach ($model->$relationName as $note) {
            $sortables[] = [
                'content' => $this->render('//note/_view', ['model' => $note, 'showActions' => $showActions]),
                'options' => [
                    'id' => 'Note_' . $note->id,
                    'class' => 'list-group-item',
                    'style' => 'border:0;padding:0;',
                ],
            ];
        }
        echo Sortable::widget([
            'items' => $sortables,
            'options' => [
                'class' => 'list-group',
                'style' => 'margin-bottom: 0;',
            ],
            'clientOptions' => [
                'axis' => 'y',
                'forcePlaceholderSize' => true,
                'cursor' => 'move',
                'handle' => '.sortable-handle',
                'helper' => new JsExpression('function(event, ui) {
                    var $clone =  $(ui).clone();
                    $clone.css("position", "absolute");
                    return $clone.get(0);
                }'),
                'start' => new JsExpression('function(e, ui){
                    ui.placeholder.height(ui.item.height()-50);
                }'),
                'update' => new JsExpression("function(event, ui) {
                    $.ajax({
                        type: 'POST',
                        url: '" . Url::to(['//note/sort']) . "',
                        data: $(event.target).sortable('serialize')
                    });
                }"),
            ],
        ]);
        ?>
    </div>
</div>