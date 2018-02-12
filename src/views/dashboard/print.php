<?php

/**
 * @var yii\web\View $this
 * @var string $heading
 * @var string $view
 * @var array $params
 */

use app\components\MenuItem;
use app\components\PdfManager;
use app\models\Item;
use app\models\ItemType;
use app\models\Machine;
use app\models\search\ItemSearch;
use app\models\search\UnitSearch;
use yii\helpers\Html;

//$this->title = Yii::t('app', 'Print');
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style type="text/css" media="all">
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

    <?php
    if ($heading) {
        echo Html::tag('h1', $heading);
    }
    ?>
    <div class="pdf">
        <?php
        echo $this->render($view, $params);
        ?>
    </div>

</div>
</body>
</html>