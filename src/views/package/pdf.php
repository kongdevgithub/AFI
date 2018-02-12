<?php
use app\models\Package;
use yii\helpers\Html;

/**
 * @var Package $model
 * @var \yii\web\View $this
 */

?>
<html>
<head>
    <link rel="stylesheet" type="text/css"
          href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style type="text/css" media="all">
        h1 {
            margin: 10px 0;
            padding: 10px 0;
            border-bottom: 1px solid #999;
        }

        tr, td, th, tbody, thead, tfoot {
            page-break-inside: avoid !important;
        }

        table tbody tr td:before,
        table tbody tr td:after {
            content: "";
            display: block;
        }

        <?= file_get_contents(Yii::getAlias('@app/assets/web/css/pdf.css')); ?>
    </style>

</head>
<body>
<div style="margin: 0 50px;">

    <h1>Delivery Docket - Package #<?= $model->pickup ? $model->pickup->id : '00000' ?>-<?= $model->id ?></h1>

    <div class="pdf">

        <div class="row clearfix">
            <div class="col-xs-6">
                <h2><?= $model->address->name ?></h2>
                <p class="address">
                    <?= $model->address->street ?><br>
                    <?= $model->address->city ?>
                    <?= $model->address->state ?>
                    <?= $model->address->postcode ?><br>
                    <br>
                    <?= $model->address->contact ? 'ATTN: ' . trim($model->address->contact) . '<br>' : '' ?>
                    <?= $model->address->phone ? 'PH: ' . trim($model->address->phone) . '<br>' : '' ?>
                    <?= $model->address->instructions ? trim($model->address->instructions) . '<br>' : '' ?>
                </p>
            </div>
            <div class="col-xs-6">
                <table class="table table-condensed table-striped table-bordered">
                    <tr>
                        <th class="text-right">Pickup #</th>
                        <td><?= $model->pickup ? $model->pickup->id : '' ?></td>
                    </tr>
                    <tr>
                        <th class="text-right">Package #</th>
                        <td><?= $model->id ?></td>
                    </tr>
                    <tr>
                        <th class="text-right">Carton</th>
                        <td><?= $model->getCartonCountLabel() ?></td>
                    </tr>
                    <tr>
                        <th class="text-right">Date</th>
                        <td><?= Yii::$app->formatter->asDate($model->pickup && $model->pickup->collected_at ? $model->pickup->collected_at : $model->created_at) ?></td>
                    </tr>
                    <tr>
                        <th class="text-right">Carrier Ref</th>
                        <td><?= $model->pickup ? $model->pickup->carrier_ref : '' ?></td>
                    </tr>
                    <tr>
                        <th class="text-right">Account Manager</th>
                        <td>
                            <?php
                            $jobs = [];
                            foreach ($model->units as $unit) {
                                $job = $unit->item->product->job;
                                $jobs[$job->id] = $job;
                            }
                            $reps = [];
                            foreach ($jobs as $job) {
                                $reps[] = $job->staffRep->label;
                            }
                            implode('<br>', $reps);
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="table table-condensed table-striped table-bordered">
            <thead>
            <tr>
                <th>Job</th>
                <th>Product</th>
                <th>Item</th>
                <th class="text-right">Qty</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($model->units as $unit) {
                ?>
                <tr>
                    <td>
                        <?php
                        echo '[' . $unit->item->product->job->id . '] ';
                        echo $unit->item->product->job->name;
                        ?>
                    </td>
                    <td>
                        <?php
                        echo '[' . $unit->item->product->id . '] ';
                        echo $unit->item->product->name;
                        ?>
                    </td>
                    <td>
                        <?php
                        echo '[' . $unit->item->id . '] ';
                        echo $unit->item->name;
                        $size = $unit->item->getSizeHtml();
                        if ($size) {
                            echo ' - ' . $size;
                        }
                        ?>
                    </td>
                    <td class="text-right">
                        <?php
                        echo $unit->quantity;
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>

    </div>

</div>
</body>
</html>