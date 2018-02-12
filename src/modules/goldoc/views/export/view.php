<?php

use app\components\Helper;
use yii\bootstrap\Alert;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use dmstr\bootstrap\Tabs;

/**
 * @var yii\web\View $this
 * @var app\models\Export $model
 */
$copyParams = $model->attributes;

$this->title = Yii::t('models', 'Export');
//$this->params['breadcrumbs'][] = ['label' => Yii::t('models', 'Exports'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => (string)$model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('cruds', 'View');

if ($model->status != 'complete') {
    echo Alert::widget([
        'body' => Html::a('<i class="fa fa-refresh" title="' . Yii::t('goldoc', 'Reload') . '"></i>', ['export/view', 'id' => $model->id]) . '&nbsp;&nbsp;' . Yii::t('goldoc', 'Export is being generated') . '<small>[status=' . $model->status . '|gearman_process=' . $model->gearman_process . ']</small>',
        'options' => ['class' => 'alert-danger'],
        'closeButton' => false,
    ]);
}

?>
<div class="export-view">

    <?php
    $columns = [];
    $columns[] = 'id';
    $columns[] = 'user_id';
    $columns[] = 'model_name';
    $columns[] = 'model_params';
    $columns[] = 'status';
    if ($model->status == 'processing') {
        $columns[] = [
            'attribute' => 'total_rows',
            'value' => Helper::countCsvRows(Yii::$app->runtimePath . '/export/' . $model->id . '.csv') . ' / ' . $model->total_rows,
        ];
    }
    if ($model->status == 'complete') {
        $columns[] = [
            'attribute' => 'total_rows',
            'value' => $model->total_rows,
        ];
        $columns[] = [
            'attribute' => 'remote_filename',
            'value' => $model->remote_filename ? (Html::a('download', Yii::$app->params['s3BucketUrl'] . '/' . $model->remote_filename)) : '',
            'format' => 'raw',
        ];
    }
    $columns[] = 'created_at:dateTime';
    $columns[] = 'updated_at:dateTime';
    echo DetailView::widget([
        'model' => $model,
        'attributes' => $columns,
    ]);
    ?>

</div>
