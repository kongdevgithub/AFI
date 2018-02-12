<?php

use app\modules\goldoc\models\Colour;
use app\modules\goldoc\models\Design;
use app\modules\goldoc\models\Item;
use app\modules\goldoc\models\search\ColourSearch;
use app\modules\goldoc\models\search\DesignSearch;
use app\modules\goldoc\models\search\ItemSearch;
use app\modules\goldoc\models\search\SponsorSearch;
use app\modules\goldoc\models\search\SubstrateSearch;
use app\modules\goldoc\models\search\SupplierSearch;
use app\modules\goldoc\models\Sponsor;
use app\modules\goldoc\models\Substrate;
use app\modules\goldoc\models\Supplier;
use cornernote\workflow\manager\models\Workflow;
use yii\grid\GridView;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('goldoc', 'Glossary');

//$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('goldoc', 'Items'); ?></h3>
            </div>
            <div class="box-body no-padding">
                <?php
                $dataProvider = (new ItemSearch)->search(Yii::$app->request->get());
                $dataProvider->pagination->defaultPageSize = 0;
                echo GridView::widget([
                    'layout' => '{items}',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'code',
                            'value' => function ($model) {
                                /** @var Item $model */
                                return Yii::$app->user->can('goldoc_item_view', ['route' => true]) ? Html::a($model->code, ['item/view', 'id' => $model->id]) : $model->code;
                            },
                            'format' => 'raw',
                        ],
                        'name',
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('goldoc', 'Colours'); ?></h3>
            </div>
            <div class="box-body no-padding">
                <?php
                $dataProvider = (new ColourSearch)->search(Yii::$app->request->get());
                $dataProvider->pagination->defaultPageSize = 0;
                echo GridView::widget([
                    'layout' => '{items}',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'code',
                            'value' => function ($model) {
                                /** @var Colour $model */
                                return Yii::$app->user->can('goldoc_colour_view', ['route' => true]) ? Html::a($model->code, ['colour/view', 'id' => $model->id]) : $model->code;
                            },
                            'format' => 'raw',
                        ],
                        'name',
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('goldoc', 'Designs'); ?></h3>
            </div>
            <div class="box-body no-padding">
                <?php
                $dataProvider = (new DesignSearch)->search(Yii::$app->request->get());
                $dataProvider->pagination->defaultPageSize = 0;
                echo GridView::widget([
                    'layout' => '{items}',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'code',
                            'value' => function ($model) {
                                /** @var Design $model */
                                return Yii::$app->user->can('goldoc_design_view', ['route' => true]) ? Html::a($model->code, ['design/view', 'id' => $model->id]) : $model->code;
                            },
                            'format' => 'raw',
                        ],
                        'name',
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('goldoc', 'Substrates'); ?></h3>
            </div>
            <div class="box-body no-padding">
                <?php
                $dataProvider = (new SubstrateSearch)->search(Yii::$app->request->get());
                $dataProvider->pagination->defaultPageSize = 0;
                echo GridView::widget([
                    'layout' => '{items}',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'code',
                            'value' => function ($model) {
                                /** @var Substrate $model */
                                return Yii::$app->user->can('goldoc_substrate_view', ['route' => true]) ? Html::a($model->code, ['substrate/view', 'id' => $model->id]) : $model->code;
                            },
                            'format' => 'raw',
                        ],
                        'name',
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('goldoc', 'Sponsors'); ?></h3>
            </div>
            <div class="box-body no-padding">
                <?php
                $dataProvider = (new SponsorSearch)->search(Yii::$app->request->get());
                $dataProvider->pagination->defaultPageSize = 0;
                echo GridView::widget([
                    'layout' => '{items}',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'code',
                            'value' => function ($model) {
                                /** @var Sponsor $model */
                                return Yii::$app->user->can('goldoc_sponsor_view', ['route' => true]) ? Html::a($model->code, ['sponsor/view', 'id' => $model->id]) : $model->code;
                            },
                            'format' => 'raw',
                        ],
                        'name',
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('goldoc', 'Suppliers'); ?></h3>
            </div>
            <div class="box-body no-padding">
                <?php
                $dataProvider = (new SupplierSearch)->search(Yii::$app->request->get());
                $dataProvider->pagination->defaultPageSize = 0;
                echo GridView::widget([
                    'layout' => '{items}',
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'code',
                            'value' => function ($model) {
                                /** @var Supplier $model */
                                return Yii::$app->user->can('goldoc_supplier_view', ['route' => true]) ? Html::a($model->code, ['supplier/view', 'id' => $model->id]) : $model->code;
                            },
                            'format' => 'raw',
                        ],
                        'name',
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <?= $this->render('//report/pages/_workflow', ['workflow' => Workflow::findOne('goldoc-product')]) ?>
    </div>

</div>
