<?php
/**
 * @var Item $model
 * @var \yii\web\View $this
 */

use app\components\Helper;
use app\components\ReturnUrl;
use app\models\Component;
use app\models\Item;
use app\models\ItemType;
use app\models\Option;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\widgets\DetailView;


$product = $model->product;
$job = $product->job;
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style type="text/css" media="all">
        h1 {
            margin: 0 0 20px 0;
        }

        <?= file_get_contents(Yii::getAlias('@app/assets/web/css/pdf.css')); ?>
    </style>

</head>
<body>
<div style="margin: 0 50px;">
    <br>
    <div class="pull-right">
        <?php
        echo implode(' ', [
            Html::tag('span', 'Item', ['class' => 'label label-default']),
            Html::tag('span', $model->itemType->name, ['class' => 'label label-default']),
            Html::tag('span', 'x' . ($model->quantity * $product->quantity), ['class' => 'label label-default']),
        ]);
        ?>
    </div>
    <h1><?= '#' . $job->vid . '.i' . $model->id ?></h1>
    <h2><?= implode(' | ', [$model->name, $product->name, $job->name]) ?></h2>

    <div class="pdf">


        <div class="row">
            <div class="col-xs-6">
                <?php
                $attributes = [];

                $attributes[] = [
                    'label' => Yii::t('app', 'Contact'),
                    'value' => implode(' | ', [
                        $job->company->name,
                        $job->contact->getLabel(true),
                    ]),
                    'format' => 'raw',
                ];

                $attributes[] = [
                    'label' => Yii::t('app', 'Staff'),
                    'value' => implode(' | ', [
                        $job->staffRep->getLabel(),
                        $job->staffCsr->getLabel(),
                    ]),
                    'format' => 'raw',
                ];

                $dueDates = [];
                //$dueDates[] = Yii::$app->formatter->asDate($job->production_date) . ' ' . Yii::t('app', 'Production');
                if ($job->prebuild_days) {
                    $dueDates[] = Yii::$app->formatter->asDate($job->prebuild_date) . ' ' . Yii::t('app', 'Prebuild');
                }
                $dueDates[] = Yii::$app->formatter->asDate($job->despatch_date) . ' ' . Yii::t('app', 'Despatched');
                $dueDates[] = Yii::$app->formatter->asDate($job->due_date) . ' ' . Yii::t('app', 'Delivered');
                if ($job->installation_date) {
                    $dueDates[] = Yii::$app->formatter->asDate($job->installation_date) . ' ' . Yii::t('app', 'Installed');
                }
                $attributes[] = [
                    'label' => Yii::t('app', 'Job Due Dates'),
                    'value' => implode('<br>', $dueDates),
                    'format' => 'raw',
                ];
                $shippingAddresses = [];
                foreach ($job->shippingAddresses as $shippingAddress) {
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
            <div class="col-xs-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <?php
                            echo Yii::t('app', 'Artwork');
                            ?>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <?php
                        if ($model->artwork) {
                            echo Html::img($model->artwork->getFileUrl('300x300'));
                        } elseif ($model->product->productType) {
                            echo Html::img($model->product->productType->getImageSrc());
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?= Yii::t('app', 'Description') ?></h3>
                    </div>
                    <div class="panel-body">
                        <?php
                        echo $product->getDescription(['showItems' => false]);
                        echo '<hr style="margin:3px 0;">';
                        echo $model->name;
                        if ($model->checkShowSize()) {
                            echo ' - ' . $model->getSizeHtml();
                        }
                        echo $model->getDescription([
                            'forceOptions' => [
                                ['option_id' => Option::OPTION_PRINTER, 'value' => Component::COMPONENT_BLANK],
                            ],
                        ])
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-8 text-xs">
                <?= $this->render('_components', ['model' => $model, 'hideCostPrices' => true]) ?>
            </div>
            <div class="col-xs-4">
                <?php
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
                                    echo '<div class="text-sm text-small">' . Yii::$app->formatter->asNtext($note->body) . '</div>';
                                    echo '</div>';
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                    <?php
                }
                if ($job->notes) {
                    ?>
                    <table class="table table-condensed table-bordered">
                        <tr>
                            <th>Job Notes</th>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                foreach ($job->notes as $note) {
                                    echo '<div class="note">';
                                    echo '<div class="note-title">' . $note->title . '</div>';
                                    echo '<div class="text-sm text-small">' . Yii::$app->formatter->asNtext($note->body) . '</div>';
                                    echo '</div>';
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                    <?php
                }
                if ($job->company->notes) {
                    ?>
                    <table class="table table-condensed table-bordered">
                        <tr>
                            <th>Company Notes</th>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                foreach ($job->company->notes as $note) {
                                    echo '<div class="note">';
                                    echo '<div class="note-title">' . $note->title . '</div>';
                                    echo '<div class="text-sm text-small">' . Yii::$app->formatter->asNtext($note->body) . '</div>';
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


    </div>

</div>
</body>
</html>