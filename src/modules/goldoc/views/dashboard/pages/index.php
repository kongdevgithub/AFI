<?php

use app\modules\goldoc\components\MenuItem;
use app\widgets\Nav;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('goldoc', 'Dashboards');
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getDashboardsItems();

?>
<div class="dashboard-index">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $this->title; ?></h3>
        </div>
        <div class="box-body">
            <?php
            echo Nav::widget([
                'options' => ['class' => 'list-unstyled'],
                'encodeLabels' => false,
                'items' => MenuItem::getDashboardsItems(),
            ]);
            ?>
        </div>
    </div>
</div>