<?php

use app\models\Component;
use app\models\Item;
use app\models\ItemType;
use app\models\Job;
use app\models\Option;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var Job $model
 * @var \yii\web\View $this
 */

$item_types = isset($item_types) ? $item_types : false;
$types = [];
if ($item_types) {
    foreach (explode(',', $item_types) as $type) {
        $types[] = trim($type);
    }
}


?>
<html>
<head>
    <link rel="stylesheet" type="text/css"
          href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style type="text/css" media="all">
        h1 {
            margin: 20px 0;
        }

        <?= file_get_contents(Yii::getAlias('@app/assets/web/css/pdf.css')); ?>
    </style>

</head>
<body>
<div style="margin: 0 50px;">
    <br>
    <div class="pull-right">
        <?php
        $tags = [];
        $tags[] = Html::tag('span', 'Job', ['class' => 'label label-default']);
        foreach ($types as $type) {
            $itemType = ItemType::findOne($type);
            $tags[] = Html::tag('span', $itemType->name, ['class' => 'label label-default']);
        }
        echo implode(' ', $tags);
        ?>
    </div>
    <h1><?= '#' . $model->vid ?></h1>
    <h2><?= implode(' | ', [$model->name, $model->company->name]) ?></h2>

    <div class="pdf">

        <div class="row">
            <div class="col-xs-6">
                <?php
                $attributes = [];
                $attributes[] = [
                    'label' => Yii::t('app', 'Contact'),
                    'value' => implode(' | ', [
                        $model->company->name,
                        $model->contact->getLabel(true),
                    ]),
                    'format' => 'raw',
                ];
                $attributes[] = [
                    'label' => Yii::t('app', 'Staff'),
                    'value' => implode(' | ', [
                        $model->staffRep->getLabel(),
                        $model->staffCsr->getLabel(),
                    ]),
                    'format' => 'raw',
                ];
                $dueDates = [];
                //$dueDates[] = Yii::$app->formatter->asDate($model->production_date) . ' ' . Yii::t('app', 'Production');
                if ($model->prebuild_days) {
                    $dueDates[] = Yii::$app->formatter->asDate($model->prebuild_date) . ' ' . Yii::t('app', 'Prebuild');
                }
                $dueDates[] = Yii::$app->formatter->asDate($model->despatch_date) . ' ' . Yii::t('app', 'Despatched');
                $dueDates[] = Yii::$app->formatter->asDate($model->due_date) . ' ' . Yii::t('app', 'Delivered');
                if ($model->installation_date) {
                    $dueDates[] = Yii::$app->formatter->asDate($model->installation_date) . ' ' . Yii::t('app', 'Installed');
                }
                $attributes[] = [
                    'label' => Yii::t('app', 'Due Dates'),
                    'value' => implode('<br>', $dueDates),
                    'format' => 'raw',
                ];
                $shippingAddresses = [];
                foreach ($model->shippingAddresses as $shippingAddress) {
                    $shippingAddresses[] = $shippingAddress->getLabel('<br>');
                }
                $attributes[] = [
                    'label' => Yii::t('app', 'Shipping'),
                    'value' => implode('<hr style="margin: 3px 0">', $shippingAddresses),
                    'format' => 'raw',
                ];
                echo DetailView::widget([
                    'model' => $model,
                    'attributes' => $attributes,
                    'options' => ['class' => 'table table-condensed detail-view'],
                ]);
                ?>
            </div>
            <div class="col-xs-6">
                <?php
                if ($model->notes) {
                    ?>
                    <table class="table table-condensed table-bordered">
                        <tr>
                            <th>Job Notes</th>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                foreach ($model->notes as $note) {
                                    echo '<div class="note">';
                                    echo '<div class="note-title">' . $note->title . '</div>';
                                    echo '<div class="note-body text-sm">' . Yii::$app->formatter->asNtext($note->body) . '</div>';
                                    echo '</div>';
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                    <?php
                }
                if ($model->company->notes) {
                    ?>
                    <table class="table table-condensed table-bordered">
                        <tr>
                            <th>Company Notes</th>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                foreach ($model->company->notes as $note) {
                                    echo '<div class="note">';
                                    echo '<div class="note-title">' . $note->title . '</div>';
                                    echo '<div class="note-body text-sm">' . Yii::$app->formatter->asNtext($note->body) . '</div>';
                                    echo '</div>';
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
        foreach ($model->products as $product) {
            /** @var Item[] $items */
            $items = [];
            foreach ($product->items as $item) {
                if (!$item->quantity) continue;
                if (!empty($types) && !in_array($item->item_type_id, $types)) continue;
                $items[] = $item;
            }
            if (!$items) {
                continue;
            }
            ?>
            <div class="product">
                <?php echo '<div class="no-break">'; // no-break on product and first item ?>
                <h3><?= 'p' . $product->id . ' | ' . $product->getDescription(['showDetails' => false, 'showItems' => false]) ?></h3>
                <?php
                if ($product->details) {
                    ?>
                    <table class="table table-condensed table-bordered">
                        <tr>
                            <th>Product Details</th>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                echo '<div class="note">';
                                echo '<div class="note-body text-sm">' . Yii::$app->formatter->asNtext($product->details) . '</div>';
                                echo '</div>';
                                ?>
                            </td>
                        </tr>
                    </table>
                    <?php
                }
                if ($product->notes) {
                    ?>
                    <table class="table table-condensed table-bordered">
                        <tr>
                            <th>Product Notes</th>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                foreach ($product->notes as $note) {
                                    echo '<div class="note">';
                                    echo '<div class="note-title">' . $note->title . '</div>';
                                    echo '<div class="note-body text-sm">' . Yii::$app->formatter->asNtext($note->body) . '</div>';
                                    echo '</div>';
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                    <?php
                }
                foreach ($items as $k => $item) {
                    ?>
                    <div class="row">
                        <div class="col-xs-2">
                            <hr style="margin-top: 0">
                            <?php
                            echo Html::tag('span', $item->itemType->name, ['class' => 'label label-default']) . '<br>';
                            if ($item->artwork) {
                                echo Html::img($item->artwork->getFileUrl('100x100'));
                                //} elseif ($item->product->productType) {
                                //    echo Html::img($item->product->productType->getImageSrc());
                            }
                            ?>
                        </div>
                        <div class="col-xs-4">
                            <hr style="margin-top: 0">
                            <?php
                            $quantity = Html::tag('span', 'x' . ($product->quantity * $item->quantity), ['class' => 'label label-default']);
                            echo Html::tag('h4', 'i' . $item->id . ' ' . $quantity . ' ' . Html::encode($item->name));
                            echo $item->getDescription([
                                'forceOptions' => [
                                    ['option_id' => Option::OPTION_PRINTER, 'value' => Component::COMPONENT_BLANK],
                                ],
                            ])
                            ?>
                        </div>
                        <div class="col-xs-6 text-xs">
                            <?php
                            $columns = [];
                            $columns[] = [
                                'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:100px;'],
                                'attribute' => 'code',
                                'format' => 'raw',
                            ];
                            $columns[] = [
                                'attribute' => 'name',
                                'format' => 'raw',
                            ];
                            $columns[] = [
                                'header' => Yii::t('app', 'Qty'),
                                'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
                                'attribute' => 'quantity',
                                'hAlign' => 'right',
                                'format' => 'raw',
                            ];
                            $columns[] = [
                                'header' => Yii::t('app', 'Per'),
                                'headerOptions' => ['nowrap' => 'nowrap', 'style' => 'width:50px;'],
                                'attribute' => 'unit_of_measure',
                                'hAlign' => 'center',
                                'format' => 'raw',
                            ];
                            echo GridView::widget([
                                'dataProvider' => new ArrayDataProvider([
                                    'allModels' => $item->getMaterials([
                                        'ignoreComponents' => [Component::COMPONENT_ADMIN, Component::COMPONENT_DG104, Component::COMPONENT_ORBWRAP],
                                    ]),
                                    'pagination' => ['pageSize' => 100000, 'pageParam' => 'page-items'],
                                    'sort' => false,
                                ]),
                                'layout' => '{items}',
                                'columns' => $columns,
                                'panel' => false,
                                'bordered' => true,
                                'striped' => false,
                                'condensed' => true,
                                'responsive' => true,
                                'hover' => false,
                            ]);
                            ?>
                        </div>
                    </div>
                    <?php
                    if ($k == 0) echo '</div>'; // no-break on product and first item
                }
                ?>
            </div>
            <?php
        }
        ?>
    </div>

</div>
</body>
</html>