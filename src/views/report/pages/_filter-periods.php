<?php
/**
 * @var View $this
 * @var string $period
 * @var array $url
 */

use app\widgets\Nav;
use yii\helpers\ArrayHelper;
use yii\web\View;

$default = 'month';
$periods = [
    'week' => Yii::t('app', 'Weekly'),
    'month' => Yii::t('app', 'Monthly'),
    'quarter' => Yii::t('app', 'Quarterly'),
    'year' => Yii::t('app', 'Yearly'),
];
$items = [];
foreach ($periods as $k => $v) {
    $items[] = [
        'label' => $v,
        'url' => $k == $default ? $url : ArrayHelper::merge($url, ['period' => $k]),
        'active' => $k == $period,
    ];
}
echo Nav::widget([
    'encodeLabels' => false,
    'options' => ['class' => 'nav-pills'],
    'items' => $items,
]);
