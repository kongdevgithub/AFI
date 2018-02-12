<?php
/**
 * @var yii\web\View $this
 * @var app\models\Product $model
 */

use app\components\fields\BaseField;
use app\models\Component;
use app\models\Item;
use app\models\ItemType;
use app\models\MachineType;
use app\models\Option;
use app\components\ReturnUrl;
use cornernote\shortcuts\Y;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;

?>

<div class="kv-detail-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-9">

                <?= GridView::widget([
                    'dataProvider' => new ActiveDataProvider([
                        'query' => $model->getItems()->andWhere('quantity > 0'),
                        'pagination' => ['pageSize' => 100000, 'pageParam' => 'page-items'],
                        'sort' => false,
                    ]),
                    'layout' => '{items}',
                    'columns' => [
                        [
                            'class' => 'kartik\grid\ExpandRowColumn',
                            'value' => function ($model, $key, $index, $column) {
                                return GridView::ROW_COLLAPSED;
                            },
                            'detail' => function ($model, $key, $index, $column) {
                                /** @var $model Item */
                                return Yii::$app->controller->renderPartial('_production-expand-item', ['model' => $model]);
                            },
                            //'detailUrl' => ['job/index'],
                            'detailRowCssClass' => '',
                            'expandOneOnly' => false,
                        ],
                        [
                            'attribute' => 'id',
                            'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
                            'value' => function ($model, $key, $index, $widget) {
                                /** @var $model Item */
                                $items = [];
                                $items[] = $model->getLabel() . '<br>';
                                //if (Y::user()->can('app_item_update', ['route' => true])) {
                                //    $items[] = Html::a('<i class="fa fa-pencil"></i>', ['/item/update', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                //        'title' => Yii::t('app', 'Update'),
                                //        'data-toggle' => 'tooltip',
                                //        //'data-pjax' => 0,
                                //    ]);
                                //}
                                //if (Y::user()->can('app_item_copy', ['route' => true])) {
                                //    $items[] = Html::a('<i class="fa fa-copy"></i>', ['/item/copy', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                //        'title' => Yii::t('app', 'Copy'),
                                //        'data-toggle' => 'tooltip',
                                //        //'data-pjax' => 0,
                                //    ]);
                                //}
                                if (Y::user()->can('app_item_split', ['route' => true])) {
                                    if ($model->split_id) {
                                        $items[] = Html::a('<i class="fa icon-merge"></i>', ['/item/split', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                            'title' => Yii::t('app', 'Merge'),
                                            'data-toggle' => 'tooltip',
                                            'data-confirm' => Yii::t('app', 'Are you sure?'),
                                            'data-method' => 'post',
                                        ]);
                                        if (Y::user()->can('app_item_split-parent', ['route' => true])) {
                                            $items[] = Html::a('<i class="fa fa-dot-circle-o"></i>', ['/item/split-parent', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                                'title' => Yii::t('app', 'Make Parent of Split'),
                                                'data-toggle' => 'tooltip',
                                                'data-confirm' => Yii::t('app', 'Are you sure?'),
                                                'data-method' => 'post',
                                            ]);
                                        }
                                    } elseif ($model->quantity * $model->product->quantity > 1) {
                                        $items[] = Html::a('<i class="fa icon-split"></i>', ['/item/split', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                            'class' => 'modal-remote',
                                            'title' => Yii::t('app', 'Split'),
                                            'data-toggle' => 'tooltip',
                                        ]);
                                    }
                                }
                                if (Y::user()->can('app_item_fix-unit-count', ['route' => true])) {
                                    if (!$model->checkUnitCount()) {
                                        $items[] = Html::a('<i class="fa icon-check"></i>', ['/item/fix-unit-count', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                            'title' => Yii::t('app', 'Fix Unit Count'),
                                            'data-toggle' => 'tooltip',
                                        ]);
                                    }
                                }
                                //if (Y::user()->can('app_item_delete', ['route' => true])) {
                                //    $items[] = Html::a('<span class="fa fa-trash"></span>', ['/item/delete', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                //        'title' => Yii::t('app', 'Delete'),
                                //        'data-confirm' => Yii::t('app', 'Are you sure?'),
                                //        'data-method' => 'post',
                                //        'data-toggle' => 'tooltip',
                                //    ]);
                                //}

                                $size = [];
                                $area = $model->getArea();
                                if ($area) {
                                    $size[] = ceil($area) . 'm<sup>2</sup>';
                                }
                                $perimeter = $model->getPerimeter();
                                if ($perimeter) {
                                    $size[] = ceil($perimeter) . 'm';
                                }
                                $sizeString = '<hr style="margin:0 5px">' . Html::tag('span', $model->getSizeHtml(), ['class' => 'label label-default']) . ' ' . Html::tag('span', implode('&nbsp;|&nbsp;', $size), ['class' => 'label label-default']);
                                $checkbox = Html::checkbox('check') . ' ';
                                return implode(' &nbsp;', $items) . '<hr style="margin: 5px 0;">' . $checkbox . $model->getStatusButtons() . $sizeString;
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'description',
                            'value' => function ($model, $key, $index, $widget) {
                                /** @var $model Item */
                                $size = '';
                                if ($model->checkShowSize()) {
                                    $size = ' - ' . $model->getSizeHtml();
                                }
                                $description = $model->getDescription([
                                    'highlightOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
                                    'forceOptions' => [
                                        ['option_id' => Option::OPTION_PRINTER, 'value' => Component::COMPONENT_BLANK],
                                    ],
                                ]);
                                $change = '';
                                if (explode('/', $model->status)[1] == 'change') {
                                    $content = Yii::t('app', 'Change Request by') . ' ' . $model->change_requested_by . ':<br>' . Yii::$app->formatter->asNtext($model->change_request_details);
                                    $change = Html::tag('div', $content, [
                                            'class' => 'alert alert-danger',
                                        ]) . '<br>';
                                }
                                return $change . Html::encode($model->name) . $size . $description;
                            },
                            'format' => 'raw',
                        ],
                        //[
                        //    'header' => Yii::t('app', 'Size'),
                        //    'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:100px;'],
                        //    'attribute' => 'sizeHtml',
                        //    'value' => function ($model, $key, $index, $widget) {
                        //        /** @var $model Item */
                        //        $size = [];
                        //        $area = $model->getArea();
                        //        if ($area) {
                        //            $size[] = ceil($area) . 'm<sup>2</sup>';
                        //        }
                        //        $perimeter = $model->getPerimeter();
                        //        if ($perimeter) {
                        //            $size[] = ceil($perimeter) . 'm';
                        //        }
                        //        return $model->getSizeHtml() . '<br>' . Html::tag('small', implode('&nbsp;|&nbsp;', $size));
                        //    },
                        //    'format' => 'raw',
                        //],
                        [
                            'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:100px;'],
                            'label' => Yii::t('app', 'Icons'),
                            'value' => function ($model, $key, $index, $widget) {
                                /** @var $model Item */
                                return trim($model->getIcons() . ' ' . $model->getPrintTagIcon());
                            },
                            'format' => 'raw',
                        ],
                        [
                            'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
                            'label' => Yii::t('app', 'Qty'),
                            'attribute' => 'quantity',
                            'value' => function ($model, $key, $index, $widget) {
                                /** @var $model Item */
                                if ($model->product->job->status == 'job/draft') {
                                    return Html::a($model->quantity * $model->product->quantity, ['/item/quantity', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                        'class' => 'modal-remote label label-default',
                                        'title' => Yii::t('app', 'Update Item Quantity'),
                                        'data-toggle' => 'tooltip',
                                    ]);
                                }
                                return Html::tag('span', $model->quantity * $model->product->quantity, [
                                    'class' => 'label label-default',
                                ]);
                            },
                            'hAlign' => 'center',
                            'format' => 'raw',
                        ],
                        [
                            'header' => Yii::t('app', 'Artwork'),
                            'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
                            'value' => function ($model, $key, $index, $widget) {
                                /** @var $model Item */
                                if ($model->artwork) {
                                    $thumb = Html::img($model->artwork->getFileUrl('100x100'));
                                    if (Y::user()->can('app_item_artwork', ['route' => true])) {
                                        return Html::a($thumb, $model->getUrl('artwork', ['ru' => ReturnUrl::getToken()]), ['class' => 'modal-remote']);
                                    }
                                    return Html::a($thumb, $model->artwork->getFileUrl('800x800'), ['data-fancybox' => 'gallery-' . $model->artwork->id]);
                                }
                                if (Y::user()->can('app_item_artwork', ['route' => true])) {
                                    return Html::a('<i class="fa fa-upload"></i>', ['/item/artwork', 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                        'class' => 'modal-remote',
                                        'title' => Yii::t('app', 'Artwork'),
                                        'data-toggle' => 'tooltip',
                                    ]);
                                }
                                return '';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'header' => Yii::t('app', 'Machine'),
                            'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
                            'value' => function ($model, $key, $index, $widget) {
                                /** @var $model Item */
                                $link = '';
                                if ($model->item_type_id == ItemType::ITEM_TYPE_PRINT) {
                                    if (Y::user()->can('app_item_printer', ['route' => true])) {
                                        $link = Html::a('<i class="fa fa-print"></i>', ['/item/printer', 'machine_type_id' => MachineType::MACHINE_TYPE_PRINTER, 'id' => $model->id, 'ru' => ReturnUrl::getToken()], [
                                                'class' => 'modal-remote',
                                                'title' => Yii::t('app', 'Printer'),
                                                'data-toggle' => 'tooltip',
                                                //'data-pjax' => 0,
                                            ]) . '<br>';
                                    }
                                }
                                $machines = [];
                                foreach ($model->itemToMachines as $itemToMachine) {
                                    $machines[] = Html::tag('strong', $itemToMachine->machine->name) . '<br>' . Yii::$app->formatter->asNtext(trim($itemToMachine->details));
                                }
                                return $link . implode('<hr>', $machines);
                            },
                            'format' => 'raw',
                        ],
                    ],
                    'panel' => [
                        'heading' => false,
                        'footer' => false,
                        'before' => false,
                        'after' => false,
                        'type' => GridView::TYPE_DEFAULT,
                    ],
                    'panelHeadingTemplate' => '<h3 class="panel-title">{heading}</h3>',
                    //'pjax' => true,
                    'bordered' => true,
                    'striped' => false,
                    'condensed' => true,
                    'responsive' => true,
                    'hover' => false,
                ]) ?>

            </div>
            <div class="col-md-3">
                <?= $this->render('/note/_index', ['model' => $model, 'title' => Yii::t('app', 'Product Notes')]) ?>
                <?= $this->render('/link/_index', ['model' => $model, 'title' => Yii::t('app', 'Product Links')]) ?>
                <?= $this->render('/attachment/_index', ['model' => $model, 'title' => Yii::t('app', 'Product Attachments')]) ?>
            </div>
        </div>
    </div>
</div>