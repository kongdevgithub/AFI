<?php

use app\components\MenuItem;
use app\widgets\Nav;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Help');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="help-index">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $this->title; ?></h3>
        </div>
        <div class="box-body">
            <?= Nav::widget([
                'options' => ['class' => 'list-unstyled'],
                'encodeLabels' => false,
                'items' => MenuItem::getHelpItems(),
            ]) ?>
        </div>
    </div>
</div>
