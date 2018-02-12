<?php

use app\models\Component;
use app\models\Option;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */
?>

<div class="pdf">

    <?php if ($model->status == 'job/draft') { ?>
        <div class="preview"><p>Draft Preview<br><br>do not send<br>to customer</p></div>
    <?php } ?>

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
                    <th class="text-right">Quote#</th>
                    <td><?= $model->vid ?></td>
                </tr>
                <tr>
                    <th class="text-right">Date</th>
                    <td><?= Yii::$app->formatter->asDate($model->quote_at ? $model->quote_at : $model->created_at) ?></td>
                </tr>
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
    $greeting = $model->getQuoteGreetingHtml();
    if ($greeting) {
        ?>
        <div class="greeting">
            <?= $greeting ?>
        </div>
        <?php
    }
    ?>

    <table class="table table-condensed table-striped table-bordered" id="quote-table">
        <thead>
        <tr>
            <th>Description</th>
            <?php if (!$model->hideProductPrices()) { ?>
                <th class="text-center" style="width:120px">Price</th>
            <?php } ?>
            <th class="text-center" style="width:120px">Quantity</th>
            <?php if (!$model->hideProductPrices()) { ?>
                <th class="text-center" style="width:120px">Total</th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($model->products as $product) {
            if ($product->quote_quantity == 0) {
                continue;
            }
            $itemDescriptions = $product->quote_hide_item_description ? [] : $product->getItemDescriptions([
                'forceOptions' => [
                    ['option_id' => Option::OPTION_PRINTER, 'value' => Component::COMPONENT_BLANK],
                ],
            ]);
            $rowspan = count($itemDescriptions) + 1;
            ?>
            <tr class="product<?= empty($itemDescriptions) ? ' bottom-border' : '' ?>">
                <td>
                    <?= $product->getDescription(['showItems' => false]) ?>
                </td>
                <?php if (!$model->hideProductPrices()) { ?>
                    <td class="text-right" style="width:80px;" rowspan="<?= $rowspan ?>">
                        <?php
                        $values = [];
                        $values[] = '$' . number_format(($product->quote_factor_price - $product->quote_discount_price) * $model->quote_markup / $product->quote_quantity, 2);
                        if ($product->forkQuantityProducts) {
                            //$values[] = '';
                            foreach ($product->forkQuantityProducts as $_product) {
                                $values[] = '$' . number_format(($_product->quote_factor_price - $_product->quote_discount_price) * $model->quote_markup / $_product->quote_quantity, 2);
                            }
                        }
                        echo implode('<br>', $values);
                        ?>
                    </td>
                <?php } ?>
                <td class="text-right" style="width:80px;" rowspan="<?= $rowspan ?>">
                    <?php
                    $values = [];
                    $values[] = $product->quantity;
                    if ($product->forkQuantityProducts) {
                        //$values[] = '';
                        foreach ($product->forkQuantityProducts as $_product) {
                            $values[] = $_product->quantity;
                        }
                    }
                    echo implode('<br>', $values);
                    ?>
                </td>
                <?php if (!$model->hideProductPrices()) { ?>
                    <td class="text-right" style="width:80px;" rowspan="<?= $rowspan ?>">
                        <?php
                        $values = [];
                        $values[] = '$' . number_format(($product->quote_factor_price - $product->quote_discount_price) * $model->quote_markup, 2);
                        if ($product->forkQuantityProducts) {
                            //$values[] = '';
                            foreach ($product->forkQuantityProducts as $_product) {
                                $values[] = '$' . number_format(($_product->quote_factor_price - $_product->quote_discount_price) * $model->quote_markup, 2);
                            }
                        }
                        echo implode('<br>', $values);
                        ?>
                    </td>
                <?php } ?>
            </tr>
            <?php
            foreach ($itemDescriptions as $k => $itemDescription) {
                $last = $k + 2 == $rowspan;
                ?>
                <tr class="item<?= $last ? ' bottom-border' : '' ?>">
                    <td>
                        <?php
                        echo Html::ul([$itemDescription], ['encode' => false]);
                        ?>
                    </td>
                </tr>
                <?php
            }

        }
        if (!$model->hideTotals()) {
            $cols = 2;
            $totals = [];
            if (!$model->hideProductPrices()) {
                $cols = 4;
                $totals['Sub-total'] = $model->quote_retail_price;
                if ($model->quote_freight_price != 0 || $model->freight_method || $model->freight_notes) {
                    $freight = [];
                    if ($model->freight_method || $model->freight_notes)
                        $freight[] = '';
                    if ($model->freight_method)
                        $freight[] = $model->freight_method;
                    if ($model->freight_notes)
                        $freight[] = $model->freight_notes;
                    $totals['Freight' . implode(' - ', $freight)] = $model->quote_freight_price;
                }
                if ($model->quote_surcharge_price != 0) {
                    $totals['Surcharge'] = $model->quote_surcharge_price;
                }
                if ($model->quote_discount_price != 0) {
                    $totals['Discount'] = $model->quote_discount_price;
                }
            }
            $totals['ExGST Total'] = $model->quote_total_price - $model->quote_tax_price;
            $totals['GST'] = $model->quote_tax_price;
            $totals['IncGST Total'] = $model->quote_total_price;
            ?>
            <tr>
                <td colspan="<?= $cols ?>">&nbsp;</td>
            </tr>
            <?php
            foreach ($totals as $k => $v) {
                ?>
                <tr>
                    <td colspan="<?= $cols - 1 ?>" class="text-right">
                        <?php if (in_array($k, ['ExGST Total', 'IncGST Total'])) { ?>
                            <b><?= $k ?></b>
                        <?php } else { ?>
                            <?= $k ?>
                        <?php } ?>
                    </td>
                    <td class="text-right">
                        <?php if (in_array($k, ['ExGST Total', 'IncGST Total'])) { ?>
                            <b>$<?= number_format($v, 2) ?></b>
                        <?php } else { ?>
                            $<?= number_format($v, 2) ?>
                        <?php } ?>
                    </td>
                </tr>
                <?php
            }
        }
        ?>

        </tbody>
    </table>

    <?php
    if ($model->quote_footer_text) {
        ?>
        <div class="footer-text">
            <?= Yii::$app->formatter->asNtext($model->quote_footer_text) ?>
        </div>
        <?php
    }
    ?>

    <div class="text-sm">
        <ul>
            <li>
                The typical lead time for despatch of fabricated items is up to five days, but this can vary for bespoke jobs.  We can provide job-specific estimates on request.
            </li>
            <li>
                All prices are in Australian Dollars (AUD) and are excluding Goods and Services Tax (GST) unless specified.
            </li>
            <li>
                Price levels used to generate this quote apply only to these specifications and total value.
            </li>
            <li>
                Price subject to change if the job composition is revised.
            </li>
            <li>
                Unless otherwise stated, this price is based on quote approval and any applicable artwork being provided a minimum of 48 hours before 4pm on the requested despatch date for simplified products, or more as may be advised for custom-designed products.
            </li>
            <li>
                Express turnaround can also be provided on request and is subject to a 20% premium.
            </li>
            <li>
                Please note this quote is sold as prepaid unless 30 day terms have been established, prices are valid for 90 days.
            </li>
            <li>
                Price excludes freight unless stated - any freight prices are estimates only.
            </li>
            <li>
                To see our standard terms and conditions, visit <?= Html::a('www.afibranding.com.au/pdfs/terms-and-conditions.pdf', 'http://www.afibranding.com.au/pdfs/terms-and-conditions.pdf', ['target' => '_blank']) ?>.
            </li>
        </ul>
    </div>

</div>