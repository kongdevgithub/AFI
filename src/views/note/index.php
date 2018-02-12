<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ButtonDropdown;
use yii\grid\GridView;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\NoteSearch $searchModel
 */

$this->title = Yii::t('app', 'Notes');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="note-index">

    <div class="clearfix">

        <p class="pull-left">
            <?= Html::a('<span class="fa fa-plus"></span> ' . Yii::t('app', 'Create') . ' ' . Yii::t('app', 'Note'), ['create', 'ru' => ReturnUrl::getToken()], ['class' => 'btn btn-success']) ?>
            <?php //echo Html::button('<span class="fa fa-search"></span> ' . Yii::t('app', 'Search') . ' ' . Yii::t('app', 'Notes'), ['class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#note-searchModal']) ?>
        </p>

        <div class="pull-right">

            <?php 
            /*
            echo ButtonDropdown::widget([
                'id' => 'giiant-relations',
                'encodeLabel' => false,
                'label' => '<span class="fa fa-paperclip"></span> ' . Yii::t('app', 'Relations'),
                'dropdown' => [
                    'options' => [
                        'class' => 'dropdown-menu-right'
                    ],
                    'encodeLabels' => false,
                    'items' => [],
                ],
            ]);
            */
            ?>

        </div>

    </div>

    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="table-responsive">
        <?= GridView::widget([
            'layout' => '{summary}{pager}{items}{pager}',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                [
                    'class' => 'yii\grid\ActionColumn',
                    'urlCreator' => function ($action, $model, $key, $index) {
                        /** @var app\models\Note $model */
                        // using the column name as key, not mapping to 'id' like the standard generator
                        $params = is_array($key) ? $key : [$model->primaryKey()[0] => (string)$key];
                        $params[0] = Yii::$app->controller->id ? Yii::$app->controller->id . '/' . $action : $action;
                        $params['ru'] = ReturnUrl::getToken();
                        return Url::toRoute($params);
                    },
                    'contentOptions' => ['nowrap' => 'nowrap']
                ],
                'id',
                'model_name',
                'model_id',
                'important',
                'title',
                'body:ntext',
            ],
        ]); ?>
    </div>

</div>