<?php
use app\models\Pickup;

/**
 * @var Pickup $model
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

        h2 {
            margin: 10px 0;
            padding: 10px 0;
            border-top: 1px solid #999;
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

    <h1>Delivery Docket #<?= $model->id ?></h1>

    <div class="pdf">

        <div class="row clearfix">
            <div class="col-xs-6">
            </div>
            <div class="col-xs-6">
                <table class="table table-condensed table-striped table-bordered">
                    <tr>
                        <th width="40%" class="text-right">Pickup #</th>
                        <td width="60%"><?= $model->id ?></td>
                    </tr>
                    <tr>
                        <th class="text-right">Date</th>
                        <td><?= Yii::$app->formatter->asDate($model->collected_at ? $model->collected_at : $model->created_at) ?></td>
                    </tr>
                    <tr>
                        <th class="text-right">Carrier Ref</th>
                        <td><?= $model->carrier_ref ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <?php foreach ($model->packages as $package) { ?>
            <h2>Package #<?= $package->id ?></h2>

            <div class="row clearfix">
                <div class="col-xs-6">
                    <h3><?= $package->address->name ?></h3>
                    <p class="address">
                        <?= $package->address->street ?><br>
                        <?= $package->address->city ?><br>
                        <?= $package->address->state ?>
                        <?= $package->address->postcode ?><br>
                        <br>
                        <?= $package->address->contact ?><br>
                        <?= $package->address->phone ? $package->address->phone . '<br>' : '' ?>
                        <?= $package->address->instructions ? $package->address->instructions . '<br>' : '' ?>
                    </p>
                </div>
                <div class="col-xs-6">
                    <table class="table table-condensed table-striped table-bordered">
                        <tr>
                            <th width="40%" class="text-right">Package #</th>
                            <td width="60%"><?= $package->id ?></td>
                        </tr>
                        <tr>
                            <th width="40%" class="text-right">Cartons</th>
                            <td width="60%"><?= $package->getCartonCountLabel() ?></td>
                        </tr>
                        <tr>
                            <th class="text-right">Account Manager</th>
                            <td>
                                <?php
                                $jobs = [];
                                foreach ($package->units as $unit) {
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
                <?php foreach ($package->units as $unit) {
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
        <?php } ?>

    </div>

</div>
</body>
</html>