<?php

use app\components\MenuItem;
use app\widgets\Nav;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Check');
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();
?>
<div class="report-index">
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
                        'label' => Yii::t('app', 'Company Check'),
                        'url' => ['//report/company-check'],
                    ],
                    [
                        'label' => Yii::t('app', 'Domain Check'),
                        'url' => ['//report/company-domain-check'],
                    ],
                    [
                        'label' => Yii::t('app', 'Contact Check'),
                        'url' => ['//report/contact-check'],
                    ],
                    [
                        'label' => Yii::t('app', 'Rep Check'),
                        'url' => ['//report/rep-check'],
                    ],
                    [
                        'label' => Yii::t('app', 'HubSpot Check'),
                        'url' => ['//report/hub-spot-check'],
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>