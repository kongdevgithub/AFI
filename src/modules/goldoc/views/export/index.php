<?php
/**
 * @package default
 */

use app\models\Export;
use yii\helpers\Html;
use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\ExportSearch $searchModel
 */
$this->title = Yii::t('models', 'Exports');
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="export-index">

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('goldoc', 'Exports'); ?></h3>
            <div class="box-tools pull-right">
                <?php //echo Html::button('<i class="fa fa-search"></i> ' . Yii::t('goldoc', 'Search Exports'), ['class' => 'btn btn-box-tool', 'data-toggle' => 'modal', 'data-target' => '#export-searchModal']) ?>
            </div>
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <?php
                $columns = [];
                $columns[] = [
                    'attribute' => 'id',
                    'value' => function ($model) {
                        /** @var Export $model */
                        return Yii::$app->user->can('goldoc_export_view') ? Html::a($model->id, ['export/view', 'id' => $model->id]) : $model->id;
                    },
                    'format' => 'raw',
                ];
                $columns[] = 'user_id';
                //$columns[] = 'model_name';
                //$columns[] = 'model_params:ntext';
                $columns[] = 'status';
                $columns[] = 'total_rows';
                $columns[] = 'created_at:dateTime';
                $columns[] = 'updated_at:dateTime';
                echo GridView::widget([
                    'layout' => '{summary}{pager}{items}{pager}',
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => $columns,
                ]);
                ?>
            </div>
        </div>
    </div>

    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>

</div>
