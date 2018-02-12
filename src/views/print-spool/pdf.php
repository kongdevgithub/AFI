<?php
use app\models\Package;

/**
 * @var Package $model
 * @var \yii\web\View $this
 */

?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

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

    <h1>TEST PDF</h1>

    <div class="pdf">


    </div>

</div>
</body>
</html>