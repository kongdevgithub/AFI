<?php

/**
 * @var yii\web\View $this
 */

use app\modules\goldoc\components\MenuItem;
use app\modules\goldoc\models\Product;
use app\modules\goldoc\models\Venue;
use yii\bootstrap\Nav;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = Yii::t('goldoc', 'Budget Overview');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

$status = Yii::$app->request->get('status');
?>

<div class="report-budget">

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Filters') ?></h3>
        </div>
        <div class="box-body">
            <?php
            // staff
            echo '<h4>Status</h4>';
            $items = [];
            $items[] = [
                'label' => '-ALL-',
                'url' => ArrayHelper::merge(['/goldoc/report/index', 'report' => 'budget-overview'], ['status' => null]),
                'active' => !$status,
            ];
            $items[] = [
                'label' => Yii::t('app', 'Unapproved'),
                'url' => ArrayHelper::merge(['/goldoc/report/index', 'report' => 'budget-overview'], ['status' => 'unapproved']),
                'active' => $status == 'unapproved',
            ];
            $items[] = [
                'label' => Yii::t('app', 'Approved'),
                'url' => ArrayHelper::merge(['/goldoc/report/index', 'report' => 'budget-overview'], ['status' => 'approved']),
                'active' => $status == 'approved',
            ];

            echo Nav::widget([
                'encodeLabels' => false,
                'options' => ['class' => 'nav-pills'],
                'items' => $items,
            ]);
            ?>
        </div>
    </div>

    <?= $this->render('_budget-overview', ['status' => $status]) ?>

</div>