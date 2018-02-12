<?php
use app\models\Job;

/**
 * @var Job $model
 * @var \yii\web\View $this
 */

$this->registerCss(file_get_contents(Yii::getAlias('@app/assets/web/css/pdf.css')));
$this->registerCss(file_get_contents(Yii::getAlias('@app/assets/web/css/pdf-' . $model->quote_template . '.css')));
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style type="text/css" media="all">
        h1 {
            margin-top: 0;
            margin-bottom: 30px;
            color: #fff !important;
            text-align: right;
            font-size: 1px;
        }

        <?= file_get_contents(Yii::getAlias('@app/assets/web/css/pdf.css')); ?>
        <?= file_get_contents(Yii::getAlias('@app/assets/web/css/pdf-'.$model->quote_template.'.css')); ?>
    </style>

</head>
<body>
<div style="margin: 0 50px;">

    <h1>Quote #<?= $model->vid ?> - <?= $model->name ?></h1>

    <?= $this->render('@app/views/job/_quote-details', ['model' => $model]) ?>

</div>
</body>
</html>