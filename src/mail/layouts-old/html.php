<?php
/**
 * @var $this \yii\web\View view component instance
 * @var $message \yii\mail\MessageInterface the message being composed
 * @var $content string main view render result
 */

use yii\helpers\Html;

?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>"/>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
        }

        img {
            max-width: 100%;
        }

        body {
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100% !important;
            height: 100%;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
            line-height: 1.1;
            margin-bottom: 15px;
            color: #000;
        }

        h1 small, h2 small, h3 small, h4 small, h5 small, h6 small {
            font-size: 60%;
            color: #6f6f6f;
            line-height: 0;
            text-transform: none;
        }

        h1 {
            font-weight: 200;
            font-size: 44px;
        }

        h2 {
            font-weight: 200;
            font-size: 37px;
        }

        h3 {
            font-weight: 500;
            font-size: 27px;
        }

        h4 {
            font-weight: 500;
            font-size: 23px;
        }

        h5 {
            font-weight: 900;
            font-size: 17px;
        }

        h6 {
            font-weight: 900;
            font-size: 14px;
            text-transform: uppercase;
            color: #444;
        }

        p, ul {
            margin-bottom: 10px;
            font-weight: normal;
            font-size: 14px;
            line-height: 1.6;
        }

        ul li {
            margin-left: 5px;
            list-style-position: inside;
        }

    </style>
    <?php $this->head() ?>
</head>
<body bgcolor="#FFFFFF">
<?php $this->beginBody() ?>
<table bgcolor="#EEEEEE" style="width: 100%; border-spacing: 0; border-collapse: collapse;">
    <tr>
        <td></td>
        <td bgcolor="#FFFFFF" style="display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;">
            <?= isset($this->params['headerImage']) ? Html::img($this->params['headerImage']) . '<br>' : '' ?>
            <div style="padding: 15px 15px 5px 15px; max-width: 600px; margin: 0 auto; display: block;">
                <?= $content ?>
            </div>
        </td>
        <td></td>
    </tr>
    <tr>
        <td colspan="3"><p>&nbsp;</p></td>
    </tr>
</table>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>


