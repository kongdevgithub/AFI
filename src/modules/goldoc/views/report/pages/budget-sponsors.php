<?php

/**
 * @var yii\web\View $this
 */

use app\modules\goldoc\components\MenuItem;
use app\modules\goldoc\models\Product;
use app\modules\goldoc\models\Venue;
use yii\db\Query;
use yii\helpers\Html;

$this->title = Yii::t('goldoc', 'Budget Sponsors');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('goldoc', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

?>

<div class="report-budget">

    <?= $this->render('_budget-sponsors') ?>

</div>