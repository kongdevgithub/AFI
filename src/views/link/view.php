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
 * @var app\models\Link $model
 */

$this->title = Yii::t('app', 'Link') . ' ' . $model->id;
$this->params['heading'] = $model->id;
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Links'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->params['heading'];
?>
<div class="link-view">

    <?php //echo $this->render('_menu', compact('model')); ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'model_name',
            'model_id',
            'title',
            'url:link',
            'body:ntext',
        ],
    ]); ?>

</div>
