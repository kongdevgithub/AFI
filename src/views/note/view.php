<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use dmstr\bootstrap\Tabs;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\Note $model
 */

$this->title = Yii::t('app', 'Note') . ' ' . $model->id;
$this->params['heading'] = $model->id;
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Notes'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->params['heading'];
?>
<div class="note-view">

    <?php //echo $this->render('_menu', compact('model')); ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'model_name',
            'model_id',
            'important',
            'title',
            'body:ntext',
        ],
    ]); ?>

</div>
