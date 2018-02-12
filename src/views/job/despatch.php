<?php

use app\models\Component;
use app\models\Item;
use app\models\ItemType;
use app\models\Option;
use yii\bootstrap\Alert;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 * @var app\models\form\PackageItemForm $packageItemForm
 */

$this->title = $model->getTitle();
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;

if (!$model->checkUnitCount()) {
    echo Alert::widget([
        'body' => Yii::t('app', 'Unit count is incorrect for some items!'),
        'options' => ['class' => 'alert-danger'],
        'closeButton' => false,
    ]);
}

?>
<div class="job-despatch">

    <?= $this->render('_menu', ['model' => $model]); ?>
    <?= $this->render('_account_term_warning', ['model' => $model]) ?>

    <div class="row">
        <div class="col-md-9">
            <?= $this->render('_details', ['model' => $model]); ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= Yii::t('app', 'Package Items'); ?></h3>
                        </div>
                        <div class="panel-body">
                            <?= $this->render('_package-item-form', ['model' => $packageItemForm]); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= Yii::t('app', 'Items'); ?></h3>
                        </div>
                        <div class="panel-body">

                            <?php
                            $cache = $model->getCache('despatch.production');
                            if ($cache === false) {
                                $items = [];
                                $excludeStatusList = ['despatch', 'packed', 'complete', 'qualityFail'];
                                $total = 0;
                                foreach ($model->products as $product) {
                                    foreach ($product->items as $item) {
                                        if ($item->quantity == 0) continue;
                                        if ($item->itemType->virtual) continue;
                                        foreach ($item->units as $unit) {
                                            if (in_array(explode('/', $unit->status)[1], $excludeStatusList)) continue;
                                            $items[$unit->item_id] = isset($items[$unit->item_id]) ? $items[$unit->item_id] + $unit->quantity : $unit->quantity;
                                            $total += $unit->quantity;
                                        }
                                    }
                                }
                                ob_start();
                                if ($items) {
                                    ?>
                                    <h4><?= $total ?> Item<?= $total > 1 ? 's' : '' ?> in Production
                                        <small>not yet ready to pack</small>
                                    </h4>
                                    <table class="table table-condensed table-striped">
                                        <?php
                                        foreach ($items as $item_id => $quantity) {
                                            $item = Item::findOne($item_id);
                                            $size = $item->getSizeHtml();
                                            ?>
                                            <tr>
                                                <td nowrap="nowrap">
                                                    <?php echo $item->getStatusButtons(); ?>
                                                </td>
                                                <td>
                                                    <?php echo Html::a('product-' . $item->product->id, ['/product/view', 'id' => $item->product->id]); ?>
                                                </td>
                                                <td>
                                                    <?php echo Html::a('item-' . $item->id, ['/item/view', 'id' => $item->id]); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    echo $item->product->name . ' | ' . $item->name . ($size ? ' | ' . $size : '') . $item->getDescription([
                                                            'highlightOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
                                                            'allowOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
                                                            'allowComponents' => [0],
                                                        ]);
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </table>
                                    <?php
                                }
                                $cache = ob_get_clean();
                                $model->setCache('despatch.production', $cache);
                            }
                            echo $cache;
                            ?>

                            <?php
                            $cache = $model->getCache('despatch.despatch');
                            if ($cache === false) {
                                $items = [];
                                $includeStatusList = ['despatch'];
                                $total = 0;
                                foreach ($model->products as $product) {
                                    foreach ($product->items as $item) {
                                        if ($item->quantity == 0) continue;
                                        if ($item->itemType->virtual) continue;
                                        foreach ($item->units as $unit) {
                                            if (!in_array(explode('/', $unit->status)[1], $includeStatusList)) continue;
                                            $items[$unit->item_id] = isset($items[$unit->item_id]) ? $items[$unit->item_id] + $unit->quantity : $unit->quantity;
                                            $total += $unit->quantity;
                                        }
                                    }
                                }
                                ob_start();
                                if ($items) {
                                    ?>
                                    <h4><?= $total ?> Item<?= $total > 1 ? 's' : '' ?> to Package
                                        <small>no package assigned</small>
                                    </h4>
                                    <table class="table table-condensed table-striped">
                                        <?php
                                        foreach ($items as $item_id => $quantity) {
                                            $item = Item::findOne($item_id);
                                            $size = $item->getSizeHtml();
                                            ?>
                                            <tr>
                                                <td nowrap="nowrap">
                                                    <?php echo $item->getStatusButtons(false, $includeStatusList); ?>
                                                </td>
                                                <td>
                                                    <?php echo Html::a('product-' . $item->product->id, ['/product/view', 'id' => $item->product->id]); ?>
                                                </td>
                                                <td>
                                                    <?php echo Html::a('item-' . $item->id, ['/item/view', 'id' => $item->id]); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    echo $item->product->name . ' | ' . $item->name . ($size ? ' | ' . $size : '') . $item->getDescription([
                                                            'highlightOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
                                                            'allowOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
                                                            'allowComponents' => [0],
                                                        ]);
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </table>
                                    <?php
                                }
                                $cache = ob_get_clean();
                                $model->setCache('despatch.despatch', $cache);
                            }
                            echo $cache;
                            ?>

                        </div>
                    </div>
                </div>
            </div>

            <?= $this->render('/job/_despatch-packages', ['model' => $model]) ?>
            <?= $this->render('/job/_despatch-pickups', ['model' => $model]) ?>

        </div>
        <div class="col-md-3">
            <?= $this->render('/job/_status-box', ['model' => $model]) ?>
            <?= $this->render('/job/_quote-version-fork', ['model' => $model]) ?>
            <?= $this->render('/job/_job-copy', ['model' => $model]) ?>
            <?= $this->render('/job/_job-redo', ['model' => $model]) ?>
            <?= $this->render('/job/_notes', ['model' => $model]) ?>
            <?= $this->render('/attachment/_index', ['model' => $model, 'title' => Yii::t('app', 'Job Attachments')]) ?>
        </div>
    </div>

</div>
