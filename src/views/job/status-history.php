<?php

use app\models\Item;
use app\models\Product;
use app\models\Unit;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => 'job-' . $model->id . ': ' . $model->name, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Trail');

?>
<div class="job-log">

    <?= $this->render('_menu', ['model' => $model]); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Status History'); ?></h3>
        </div>
        <div class="box-body">

            <?= $this->render('/layouts/_audit_trails', [
                'query' => $model->getAuditTrails([
                    Product::className(),
                    Item::className(),
                    Unit::className(),
                ]),
                'columns' => ['user_id', 'entry_id', 'action', 'model', 'model_id', 'field', 'diff', 'created'],
                'params' => [
                    'AuditTrailSearch' => [
                        'field' => 'status',
                    ],
                ],
            ]) ?>

        </div>
    </div>

</div>

