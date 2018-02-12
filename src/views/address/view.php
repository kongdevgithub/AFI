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
 * @var app\models\Address $model
 */

$this->title = Yii::t('app', 'Address') . ' ' . $model->name;
$this->params['heading'] = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Addresses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->params['heading'];
?>
<div class="address-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'model_name',
            'model_id',
            'type',
            'name',
            'street',
            'postcode',
            'city',
            'state',
            'country',
        ],
    ]); ?>

</div>
