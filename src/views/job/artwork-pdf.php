<?php
use app\models\Job;
use app\models\Option;
use yii\helpers\Html;

/**
 * @var Job $model
 * @var \yii\web\View $this
 */

//$this->registerCss(file_get_contents(Yii::getAlias('@app/assets/web/css/pdf.css')));
//$this->registerCss(file_get_contents(Yii::getAlias('@app/assets/web/css/pdf-afi.css')));
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style type="text/css" media="all">
        <?= file_get_contents(Yii::getAlias('@app/assets/web/css/pdf.css')); ?>
        .image {
            max-width: 950px;
            max-height: 1350px;
        }
    </style>

</head>
<body>
<div style="margin: 0 50px;">

    <div class="pdf">

        <div class="panel no-break">
            <div class="panel-heading">
                <h1 class="panel-title"><?= Yii::t('app', 'Artwork Approval') . ' #' . $model->vid; ?></h1>
            </div>
            <div class="panel-body">
                <?php
                // table of contents
                $artworks = [];
                foreach ($model->products as $product) {
                    foreach ($product->items as $item) {
                        if ($item->artwork) {
                            $artworks[] = implode(' | ', [
                                '#' . $item->product->job->vid . '.i' . $item->id . ': ' . $item->name,
                                $item->product->name,
                                $item->getSizeHtml(),
                                'qty:' . $item->quantity * $product->quantity,
                            ]);
                        }
                    }
                }
                echo Html::ul($artworks);
                ?>
            </div>
        </div>

        <?php
        // artwork
        foreach ($model->products as $product) {
            foreach ($product->items as $item) {
                if ($item->artwork) {
                    ?>
                    <div class="panel no-break">
                        <div class="panel-heading">
                            <h2 class="panel-title"><?= implode(' | ', [
                                    '#' . $item->product->job->vid . '.i' . $item->id . ': ' . $item->name,
                                    $item->getSizeHtml(),
                                    'qty:' . $item->quantity * $product->quantity,
                                ]) . $item->getDescription([
                                    'ignoreOptions' => [Option::OPTION_ARTWORK, Option::OPTION_LABEL],
                                    'listOptions' => ['class' => 'list-unstyled'],
                                ]); ?></h2>
                        </div>
                        <div class="panel-body">
                            <?php
                            echo Html::img($item->artwork->getFileUrl(), ['class' => 'image rotate']);
                            ?>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        ?>

    </div>

</div>
</body>
</html>