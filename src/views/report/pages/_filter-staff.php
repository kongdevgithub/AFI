<?php
/**
 * @var View $this
 * @var User $staff
 * @var string|array $role
 * @var array $url
 */

use app\models\User;
use app\widgets\Nav;
use yii\helpers\ArrayHelper;
use yii\web\View;

$staffUrlParam = isset($staffUrlParam) ? $staffUrlParam : 'staff_id';

$items = [];
$items[] = [
    'label' => '-ALL-',
    'url' => ArrayHelper::merge($url, [$staffUrlParam => 'all']),
    'active' => !$staff,
];
if (!is_array($role)) {
    $role = [$role];
}
$users = [];
foreach ($role as $_role) {
    foreach (User::find()->byRole($_role)->all() as $user) {
        $users[$user->id] = $user;
    }
}
foreach ($users as $user) {
    $items[] = [
        'label' => $user->label,
        'url' => ArrayHelper::merge($url, [$staffUrlParam => $user->id]),
        'active' => $staff && $user->id == $staff->id,
    ];
}

echo Nav::widget([
    'encodeLabels' => false,
    'options' => ['class' => 'nav-pills'],
    'items' => $items,
]);