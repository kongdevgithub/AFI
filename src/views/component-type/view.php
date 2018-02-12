<?php

use app\components\fields\BaseField;
use app\components\quotes\components\BaseComponentQuote;
use app\models\Component;
use app\models\Option;
use app\models\search\ComponentSearch;
use yii\bootstrap\ButtonDropdown;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use dmstr\bootstrap\Tabs;
use app\components\ReturnUrl;

/**
 * @var yii\web\View $this
 * @var app\models\ComponentType $model
 */

$this->title = Yii::t('app', 'Component Type') . ' ' . $model->name;
$this->params['heading'] = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Component Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->params['heading'];
?>
<div class="component-type-view">

    <?php //echo $this->render('_menu', compact('model')); ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Component Type'); ?></h3>
            <div class="box-tools pull-right">
                <?= Html::a('<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Update'), ['update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], ['class' => 'btn btn-box-tool']) ?>
                <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-box-tool',
                    'data-confirm' => Yii::t('app', 'Are you sure?'),
                    'data-method' => 'post',
                ]); ?>
            </div>
        </div>
        <div class="box-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    //'id',
                    'name',
                    //[
                    //    'attribute' => 'quote_class',
                    //    'value' => BaseComponentQuote::opts()[$model->quote_class],
                    //],
                ],
            ]) ?>
        </div>
    </div>

    <?php
    $options = [];
    foreach (Option::find()->notDeleted()->all() as $option) {
        $fieldConfig = $option->getFieldConfigDecoded();
        if (isset($fieldConfig['condition']['component_type_id']) && $fieldConfig['condition']['component_type_id'] == $model->id) {
            $options[$option->id] = $option;
        }
    }
    if ($options) {
        ?>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Options'); ?></h3>
            </div>
            <div class="box-body">

                <div class="table-responsive">
                    <?= GridView::widget([
                        'layout' => '{summary}{pager}{items}{pager}',
                        'dataProvider' => new ArrayDataProvider([
                            'allModels' => $options,
                            'pagination' => ['pageSize' => 100000, 'pageParam' => 'page-options'],
                        ]),
                        'columns' => [
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view} {update}',
                                'urlCreator' => function ($action, $model, $key, $index) {
                                    /** @var Option $model */
                                    // using the column name as key, not mapping to 'id' like the standard generator
                                    $params = [$model->primaryKey()[0] => (string)$key];
                                    $params[0] = '/option/' . $action;
                                    $params['ru'] = ReturnUrl::getToken();
                                    return Url::toRoute($params);
                                },
                                'headerOptions' => ['style' => 'width:30px'],
                                'contentOptions' => ['nowrap' => 'nowrap'],
                            ],
                            'name',
                            [
                                'attribute' => 'field_class',
                                'filter' => BaseField::opts(),
                                'value' => function ($model) {
                                    return $model->field_class ? BaseField::opts()[$model->field_class] : null;
                                },
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Components'); ?></h3>
        </div>
        <div class="box-body">

            <div class="table-responsive">
                <?= GridView::widget([
                    'layout' => '{summary}{pager}{items}{pager}',
                    'dataProvider' => new ActiveDataProvider([
                        'query' => $model->getComponents(),
                        'pagination' => ['pageSize' => 100000, 'pageParam' => 'page-components'],
                    ]),
                    'columns' => [
                        [
                            'attribute' => 'id',
                            'value' => function ($model) {
                                /** @var Component $model */
                                $items = [];
                                if (Yii::$app->user->can('app_component_update', ['route' => true])) {
                                    $items[] = ['label' => Yii::t('app', 'Update'), 'url' => ['//component/update', 'id' => $model->id]];
                                }
                                return ButtonDropdown::widget([
                                    'tagName' => 'a',
                                    'options' => [
                                        'href' => Url::to(['//component/view', 'id' => $model->id]),
                                        'class' => 'btn btn-default',
                                    ],
                                    'label' => $model->id,
                                    'split' => true,
                                    'dropdown' => [
                                        'items' => $items,
                                    ],
                                ]);
                            },
                            'format' => 'raw',
                            'enableSorting' => false,
                        ],
                        'code',
                        'name',
                        [
                            'attribute' => 'quote_class',
                            'value' => function ($model) {
                                /** @var Component $model */
                                /** @var BaseComponentQuote $quote */
                                $quote = new $model->quote_class;
                                return '<span class="label label-default">' . $quote->getQuoteLabel() . '</span>';
                            },
                            'filter' => BaseComponentQuote::opts(),
                            'format' => 'raw',
                        ],
                        'make_ready_cost',
                        'unit_cost',
                        'minimum_cost',
                        'quantity_factor:ntext',
                        'unit_weight',
                    ],
                ]) ?>
            </div>
        </div>
    </div>

</div>
