<?php
/**
 * @var View $this
 * @var string $title
 * @var string $period
 * @var string $from
 * @var string $to
 * @var array $url
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;


if ($period == 'week') {
    // week
    $paginateDateFormat = 'd-m-Y';
    $paginateInterval = '1 ' . $period;
} elseif ($period == 'month') {
    // month
    $paginateDateFormat = 'F Y';
    $paginateInterval = '1 ' . $period;
} elseif ($period == 'quarter') {
    // quarter
    $paginateDateFormat = 'F Y';
    $paginateInterval = '3 months';
} else {
    // year
    $paginateDateFormat = 'Y';
    $paginateInterval = '1 ' . $period;
}

?>
<div class="row">
    <div class="col-sm-3 col-md-3">
        <?php
        $text = date($paginateDateFormat, strtotime($from . ' -' . $paginateInterval));
        $_url = ArrayHelper::merge($url, ['date' => date('Y-m-d', strtotime($from . ' -' . $paginateInterval))]);
        echo Html::a('<i class="fa fa-arrow-left"></i> ' . $text, $_url, ['class' => 'btn btn-default']);
        ?>
    </div>
    <div class="col-sm-6 col-md-6 text-center">
        <h2><?= $title . ' - ' . ucwords($period) . ' - ' . date($paginateDateFormat, strtotime($from)) ?></h2>
        <p><?= date('d-m-Y', strtotime($from)) ?> - <?= date('d-m-Y', strtotime($to)) ?></p>
    </div>
    <div class="col-sm-3 col-md-3 text-right">
        <?php
        $text = date($paginateDateFormat, strtotime($from . ' +' . $paginateInterval));
        $_url = ArrayHelper::merge($url, ['date' => date('Y-m-d', strtotime($from . ' +' . $paginateInterval))]);
        echo Html::a($text . ' <i class="fa fa-arrow-right"></i>', $_url, ['class' => 'btn btn-default']);
        ?>
    </div>
</div>

