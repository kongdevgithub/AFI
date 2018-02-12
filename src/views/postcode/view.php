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
 * @var app\models\Postcode $model
 */

$this->title = Yii::t('app', 'Postcode') . ' ' . $model->id;
$this->params['heading'] = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Postcodes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->params['heading'];
?>
<div class="postcode-view">

    <?= $this->render('_menu', compact('model')); ?>
    <?php $this->beginBlock('app\models\Postcode'); ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'postcode',
            'city',
            'state',
            'country',
        ],
    ]); ?>

    <?php $this->endBlock(); ?>

    <?= Tabs::widget([
        'id' => 'relation-tabs',
        'encodeLabels' => false,
        'items' => [
            [
                'label' => '<span class="fa fa-asterisk"></span> Postcode',
                'content' => $this->blocks['app\models\Postcode'],
                'active' => true,
            ],
        ]
    ]);
    ?>

</div>
