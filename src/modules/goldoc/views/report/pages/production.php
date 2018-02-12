<?php

/**
 * @var yii\web\View $this
 */

use app\modules\goldoc\components\MenuItem;
use app\modules\goldoc\models\Colour;
use app\modules\goldoc\models\Design;
use app\modules\goldoc\models\Item;
use app\modules\goldoc\models\Product;
use app\modules\goldoc\models\ProductCode;
use app\modules\goldoc\models\ProductPrice;
use app\modules\goldoc\models\Substrate;
use yii\db\Query;
use yii\helpers\Html;

$this->title = Yii::t('goldoc', 'Production Summary');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

?>

<div class="report-budget">

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('goldoc', 'Product Codes'); ?></h3>
        </div>
        <div class="box-body no-padding">
            <table class="table table-condensed table-bordered">
                <thead>
                <tr>
                    <th><?= Yii::t('goldoc', 'Code') ?></th>
                    <th><?= Yii::t('goldoc', 'Name') ?></th>
                    <th><?= Yii::t('goldoc', 'Qty') ?></th>
                </tr>
                </thead>
                <tbody>

                <?php
                // product codes
                $totals = (new Query())
                    ->select([
                        'item_id',
                        'design_id',
                        'colour_id',
                        'substrate_id',
                        'width',
                        'height',
                        'depth',
                        'SUM(quantity) as quantity_sum',
                    ])
                    ->from('product')
                    ->andWhere(['product.deleted_at' => null])
                    ->leftJoin('item', 'item_id=item.id')
                    ->leftJoin('colour', 'colour_id=colour.id')
                    ->leftJoin('design', 'design_id=design.id')
                    ->leftJoin('substrate', 'substrate_id=substrate.id')
                    ->orderBy([
                        'item.code' => SORT_ASC,
                        'colour.code' => SORT_ASC,
                        'design.code' => SORT_ASC,
                        'substrate.code' => SORT_ASC,
                    ])
                    ->groupBy(['item_id', 'colour_id', 'design_id', 'substrate_id', 'width', 'height', 'depth'])
                    ->all(Yii::$app->dbGoldoc);

                foreach ($totals as $total) {
                    $code = [];
                    $code[] = ($total['item_id'] && $item = Item::findOne($total['item_id'])) ? $item->code : 'X';
                    $code[] = ($total['colour_id'] && $colour = Colour::findOne($total['colour_id'])) ? $colour->code : 'X';
                    $code[] = ($total['design_id'] && $design = Design::findOne($total['design_id'])) ? $design->code : 'X';
                    $code[] = ($total['substrate_id'] && $substrate = Substrate::findOne($total['substrate_id'])) ? $substrate->code : 'X';
                    if ($total['width']) {
                        if ($total['height']) {
                            if ($total['depth']) {
                                $code[] = $total['width'] . 'x' . $total['height'] . 'x' . $total['depth'];
                            } else {
                                $code[] = $total['width'] . 'x' . $total['height'];
                            }
                        } else {
                            $code[] = $total['width'];
                        }
                    }

                    $name = [];
                    $name[] = ($total['item_id'] && $item = Item::findOne($total['item_id'])) ? ($item->name ? $item->name : '!!!') : 'X';
                    $name[] = ($total['colour_id'] && $colour = Colour::findOne($total['colour_id'])) ? ($colour->name ? $colour->name : '!!!') : 'X';
                    $name[] = ($total['design_id'] && $design = Design::findOne($total['design_id'])) ? ($design->name ? $design->name : '!!!') : 'X';
                    $name[] = ($total['substrate_id'] && $substrate = Substrate::findOne($total['substrate_id'])) ? ($substrate->name ? $substrate->name : '!!!') : 'X';
                    if ($total['width']) {
                        if ($total['height']) {
                            if ($total['depth']) {
                                $name[] = $total['width'] . 'W ' . $total['height'] . 'H ' . $total['depth'] . 'D';
                            } else {
                                $name[] = $total['width'] . 'W ' . $total['height'] . 'H';
                            }
                        } else {
                            $name[] = $total['width'] . 'W';
                        }
                    }
                    ?>
                    <tr>
                        <td><?= Html::a(implode('-', $code), ['product/index', 'ProductSearch' => [
                                'item_id' => $total['item_id'] ? $total['item_id'] : '-',
                                'colour_id' => $total['colour_id'] ? $total['colour_id'] : '-',
                                'design_id' => $total['design_id'] ? $total['design_id'] : '-',
                                'substrate_id' => $total['substrate_id'] ? $total['substrate_id'] : '-',
                                'width' => $total['width'] ? $total['width'] : '-',
                                'height' => $total['height'] ? $total['height'] : '-',
                                'depth' => $total['depth'] ? $total['depth'] : '-',
                            ]], [
                                //'title' => $productPrice->name . ' - ' . $productPrice->sizeName,
                                //'data-toggle' => 'tooltip',
                            ]) ?></td>
                        <td><?= implode(' - ', $name) ?></td>
                        <td class="text-right"><?= $total['quantity_sum'] ?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>