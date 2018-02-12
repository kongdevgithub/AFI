<?php
/**
 * @var View $this
 * @var string $contactCount
 * @var string $contactCountPercent
 * @var string $jobCount
 * @var string $jobCountPercent
 * @var string $jobValue
 * @var string $jobValuePercent
 * @var string $color
 * @var string $icon
 * @var array $url
 */

use app\widgets\Nav;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;


?>

<div class="small-box bg-<?= $color ?>">
    <div class="inner">
        <h3>
            <?= number_format($contactCountPercent, 0) ?>%
            <sup style="font-size: 20px">
                <?= $contactCount . ' surveys' ?>
                <?= Html::a('<span class="fa fa-eye"></span>', $url, ['class' => 'btn btn-default btn-xs modal-remote']) ?>
            </sup>
        </h3>
        <h4>
            <?= number_format($jobCountPercent, 0) ?>%
            <sup><?= $jobCount ?> jobs</sup>
        </h4>
        <h4>
            <?= number_format($jobValuePercent, 0) ?>%
            <sup>$<?= number_format($jobValue, 0) ?> job totals</sup>
        </h4>
        <p><?= $label ?></p>
    </div>
    <div class="icon">
        <i class="<?= $icon ?>"></i>
    </div>
</div>

