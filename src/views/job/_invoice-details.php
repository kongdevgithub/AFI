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

    <div class="row clearfix">
        <div class="col-xs-6">
            <h2><?= $model->company->name ?></h2>
            <p class="address">
                <span class="fa fa-map-marker"></span>
                <?php if ($model->addresses) { ?>
                    <?= $model->addresses[0]->street ?>,
                    <?= $model->addresses[0]->city ?>
                    <?= $model->addresses[0]->state ?>
                    <?= $model->addresses[0]->postcode ?><br>
                <?php } ?>
                <?= $model->company->phone ? '<span class="fa fa-phone"></span> ' . $model->company->phone . '<br>' : '' ?>
                <br>
                <?= '<span class="fa fa-user-circle"></span> ' . $model->contact->label ?><br>
                <?= $model->contact->phone ? '<span class="fa fa-phone"></span> ' . $model->contact->phone . '<br>' : '' ?>
            </p>
        </div>
        <div class="col-xs-6">
            <table class="table table-condensed table-striped table-bordered">
                <tr>
                    <th class="text-right">Proforma Invoice#</th>
                    <td><?= $model->vid ?></td>
                </tr>
                <tr>
                    <th class="text-right">Date</th>
                    <td><?= Yii::$app->formatter->asDate($model->production_at ? $model->production_at : $model->created_at) ?></td>
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
    $greeting = $model->getInvoiceGreetingHtml();
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
            <th class="text-center" style="width:120px">Total</th>
        </tr>
        </thead>
        <tbody>
        <tr class="product bottom-border">
            <td>
                <?= $model->name ?>
            </td>
            <td class="text-right" style="width:80px;">
                <?php
                echo '$' . number_format($model->quote_retail_price, 2);
                ?>
            </td>
        </tr>
        <?php
        $totals = [];
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
        $totals['ExGST Total'] = $model->quote_total_price - $model->quote_tax_price;
        $totals['GST'] = $model->quote_tax_price;
        $totals['IncGST Total'] = $model->quote_total_price;
        ?>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        <?php
        foreach ($totals as $k => $v) {
            ?>
            <tr>
                <td class="text-right">
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

        ?>

        </tbody>
    </table>

    <div class="text-sm">
        <ul>
            <li>
                Banking Details: BSB 013-483, Acct No 1006-10009, Acct Name AFI Branding Solutions Pty Ltd
            </li>
        </ul>
    </div>

</div>