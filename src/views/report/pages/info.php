<?php

use app\components\MenuItem;
use app\widgets\Nav;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Info');
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();
?>
<div class="report-info">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $this->title; ?></h3>
        </div>
        <div class="box-body">
            <?= Nav::widget([
                'options' => ['class' => 'list-unstyled'],
                'encodeLabels' => false,
                'items' => [
                    [
                        'label' => Yii::t('app', 'Quote Classes'),
                        'url' => ['//report/quote-class'],
                    ],
                    [
                        'label' => Yii::t('app', 'Workflows'),
                        'url' => ['//report/workflow'],
                    ],
                    [
                        'label' => Yii::t('app', 'Product Types'),
                        'url' => ['//report/product-type'],
                    ],
                    [
                        'label' => Yii::t('app', 'Permissions'),
                        'url' => ['//report/permission'],
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>