<?php
/**
 * @var $this yii\web\View
 * @var $name string
 * @var $message string
 * @var $exception Exception
 */
use yii\helpers\Html;

$this->title = $name;
//$this->params['heading'] = false;
?>
<div class="site-error">

    <div class="well">
        <?= nl2br(Html::encode($message)) ?>
    </div>

</div>
