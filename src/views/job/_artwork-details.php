<?php

use app\models\Component;
use app\models\Option;
use app\components\ReturnUrl;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

$this->registerCss(file_get_contents(Yii::getAlias('@app/assets/web/css/pdf.css')));
$this->registerCss(file_get_contents(Yii::getAlias('@app/assets/web/css/pdf-' . $model->quote_template . '.css')));

$key = isset($key) ? $key : '';
$allowApproval = isset($allowApproval) ? $allowApproval : false;
?>

<div class="pdf">

    <div class="row clearfix">
        <div class="col-xs-7">
            <h2><?= $model->company->name ?></h2>
            <p class="address">
                <?php if ($model->billingAddress) { ?>
                    <span class="fa fa-map-marker pull-left"></span>
                    <?= str_replace("\n", ', ', $model->billingAddress->street) . ', ' ?>
                    <?= $model->billingAddress->city . ', ' ?>
                    <?= $model->billingAddress->state ?>
                    <?= $model->billingAddress->postcode ?>
                <?php } ?>
                <?= $model->company->phone ? '<span class="fa fa-phone" style="margin-left: 0.5em"></span> ' . $model->company->phone : '' ?>
                <br>
                <span class="fa fa-user-circle" style="width: 1.2em"></span>
                <?= $model->contact->label ?>
                <?= $model->contact->phone ? '<span class="fa fa-phone" style="margin-left: 0.5em"></span> ' . $model->contact->phone . '<br>' : '' ?>
                <br>
                <?php
                if (count($model->shippingAddresses) <= 10) {
                    foreach ($model->shippingAddresses as $shippingAddress) {
                        ?>
                        <span class="fa fa-truck" style="width: 1.2em"></span>
                        <?= $shippingAddress->name . ', ' ?>
                        <?= $shippingAddress->street . ', ' ?>
                        <?= $shippingAddress->city . ', ' ?>
                        <?= $shippingAddress->state ?>
                        <?= $shippingAddress->postcode ?>
                        <span class="fa fa-phone" style="margin-left: 0.5em"></span>
                        <?= $shippingAddress->phone ?>
                        <br>
                        <?php
                    }
                } else {
                    ?>
                    <span class="fa fa-truck" style="width: 1.2em"></span>
                    <?= Yii::t('app', 'Multiple shipping addresses') ?>
                    <br>
                    <?php
                }
                ?>
            </p>
        </div>
        <div class="col-xs-5">
            <table class="table table-condensed table-striped table-bordered">
                <tr>
                    <th class="text-right">Job#</th>
                    <td><?= $model->id ?></td>
                </tr>
                <?php /* ?>
                <tr>
                    <th class="text-right">Due Date</th>
                    <td><?= $model->due_date ? Yii::$app->formatter->asDate($model->due_date) : '' ?></td>
                </tr>
                <?php */ ?>
                <tr>
                    <th class="text-right">Customer Account</th>
                    <td><?= $model->company_id ?></td>
                </tr>
                <tr>
                    <th class="text-right">Account Manager</th>
                    <td><?= $model->staffRep->label ?></td>
                </tr>
            </table>
        </div>
    </div>

    <?php
    $greeting = $model->getArtworkGreetingHtml();
    if ($greeting) {
        ?>
        <div class="greeting">
            <?= $greeting ?>
        </div>
        <?php
    }
    ?>

    <table class="table table-condensed table-striped table-bordered" id="artwork-table">
        <thead>
        <tr>
            <th>Description</th>
            <th class="text-center" style="width:120px">Quantity</th>
            <th class="text-center" style="width:120px">Artwork</th>
            <th class="text-center" style="width:120px">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($model->products as $product) {
            if (!$product->quantity) {
                continue;
            }
            $items = [];
            foreach ($product->items as $item) {
                if ($item->quantity) {
                    $items[] = $item;
                }
            }
            if (!$items) {
                continue;
            }
            $rowspan = count($items) + 1;
            ?>
            <tr class="product<?= empty($items) ? ' bottom-border' : '' ?>">
                <td>
                    <?= $product->getDescription(['showItems' => false]) ?>
                </td>
                <td class="text-right" style="width:80px;">
                </td>
                <td class="text-right" style="width:80px;">
                </td>
                <td class="text-right" style="width:80px;">
                </td>
            </tr>
            <?php
            foreach ($items as $k => $item) {
                $last = $k + 2 == $rowspan;
                $quantity = $item->quantity == 1 ? '' : ' x' . ($item->product->quantity * $item->quantity);
                $status = explode('/', $item->status)[1];
                ?>
                <tr class="item<?= $last ? ' bottom-border' : '' ?>">
                    <td>
                        <?php
                        $description = Html::encode($item->name) . $quantity . $item->getDescription([
                                'ignoreOptions' => [Option::OPTION_ARTWORK],
                                'forceOptions' => [
                                    ['option_id' => Option::OPTION_PRINTER, 'value' => Component::COMPONENT_BLANK],
                                ],
                            ]);
                        echo Html::ul([$description], ['encode' => false]);
                        ?>
                    </td>
                    <td class="text-right">
                        <?= $item->product->quantity * $item->quantity ?>
                    </td>
                    <td class="text-center">
                        <?php
                        if ($item->artwork) {
                            $thumb = $item->artwork->getFileUrl('100x100');
                            $image = $item->artwork->getFileUrl('800x800');
                            echo Html::a(Html::img($thumb), $image, [
                                'title' => Yii::t('app', 'Click to Enlarge'),
                                'data-fancybox' => 'gallery-' . $item->artwork->id,
                                'data-toggle' => 'tooltip',
                            ]);
                            echo '<br>';
                            echo Html::a(Yii::t('app', 'download'), ['/approval/artwork-download', 'id' => $item->product->job_id, 'item_id' => $item->id, 'key' => $key]);
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <?php
                        if ($status == 'artwork') {
                            echo Html::tag('span', Yii::t('app', 'AWAITING ARTWORK'), [
                                'class' => 'label label-default',
                                'title' => Yii::t('app', 'You will be notified for approval once the artwork is ready.'),
                                'data-toggle' => 'tooltip',
                            ]);
                        } elseif ($status == 'change') {
                            echo Html::tag('span', Yii::t('app', 'CHANGE REQUESTED'), [
                                'class' => 'label label-default',
                                'title' => Yii::t('app', 'You will be notified for approval once the change request is completed.'),
                                'data-toggle' => 'tooltip',
                            ]);
                        } elseif ($status == 'approval') {
                            if ($allowApproval) {
                                $url = ['artwork-approval-item', 'id' => $model->id, 'item_id' => $item->id, 'key' => $key, 'ru' => ReturnUrl::getToken()];
                                echo Html::a('<i class="fa fa-times"></i> ' . Yii::t('app', 'Change Request'), $url, [
                                    'class' => 'btn btn-danger btn-xs modal-remote',
                                ]);
                            }
                        } elseif (in_array($status, ['draft', 'awaitingInfo', 'design'])) {
                            echo Html::tag('span', Yii::t('app', 'PRE PRODUCTION'), [
                                'class' => 'label label-warning',
                                'title' => Yii::t('app', 'You will be notified when the item is ready for artwork approval.'),
                                'data-toggle' => 'tooltip',
                            ]);
                        } else {
                            echo Html::tag('span', Yii::t('app', 'PRODUCTION'), [
                                'class' => 'label label-primary',
                                'title' => Yii::t('app', 'You will be notified when the item is despatched.'),
                                'data-toggle' => 'tooltip',
                            ]);
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }

        }
        ?>
        </tbody>
    </table>

    <?php /* ?>
    <div class="terms">
        <ul>
            <li>
                ...
            </li>
        </ul>
    </div>
    <?php */ ?>

</div>