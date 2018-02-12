<?php

use app\components\ReturnUrl;
use kartik\file\FileInput;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\Sortable;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var \yii\db\ActiveRecord $model
 */

$title = isset($title) ? $title : Yii::t('app', 'Attachments');
$showActions = isset($showActions) ? $showActions : true;
?>

<div class="box">
    <div class="box-header box-solid">
        <h3 class="box-title"><?= $title ?></h3>
    </div>
    <div class="box-body">
        <?php
        $sortables = [];
        foreach ($model->attachments as $attachment) {
            $sortables[] = [
                'content' => $this->render('//attachment/_view', ['model' => $attachment, 'showActions' => $showActions]),
                'options' => [
                    'id' => 'Attachment_' . $attachment->id,
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
                        url: '" . Url::to(['/attachment/sort']) . "',
                        data: $(event.target).sortable('serialize')
                    });
                }"),
            ],
        ]);

        if ($showActions) {
            echo FileInput::widget([
                'name' => 'Attachment[upload]',
                'options' => [
                    'multiple' => true,
                ],
                'pluginOptions' => [
                    'uploadUrl' => Url::to(['//attachment/upload']),
                    'uploadExtraData' => [
                        'model_name' => $model->className(),
                        'model_id' => $model->getPrimaryKey(),
                    ],
                    'showCaption' => false,
                    //'showPreview' => false,
                    'showRemove' => false,
                    //'showUpload' => false,
                    'showCancel' => false,
                    'showClose' => false,
                    'showBrowse' => false,
                    'browseOnZoneClick' => true,
                    'autoReplace' => true,
                    'layoutTemplates' => ['actions' => ''],
                ],
                'pluginEvents' => [
                    'filebatchuploadcomplete' => new JsExpression('function(event, files, extra) {
                    location.reload(); 
                }'),
                ],
            ]);
        }
        ?>
    </div>
</div>