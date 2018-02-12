<?php
/**
 * @var $this \yii\web\View
 */

use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

if (!empty($this->params['breadcrumbs'])) {
    foreach ($this->params['breadcrumbs'] as $k => $v) {
        if (!is_array($v)) {
            $this->params['breadcrumbs'][$k] = ['label' => $v, 'url' => false];
        }
    }
    $links = $this->params['breadcrumbs'];
    foreach ($links as &$link) {
        $link['class'] = 'btn btn-default';
        if (empty($link['url'])) {
            $link['url'] = Url::current();
        }
    }
    echo Breadcrumbs::widget([
        'options' => [
            'class' => 'btn-group btn-breadcrumb',
        ],
        'homeLink' => [
            'label' => '<span class="fa fa-home"></span>',
            'url' => !empty($this->params['breadcrumb-home']) ? $this->params['breadcrumb-home'] : Yii::$app->homeUrl,
            'class' => 'btn btn-default',
            'encode' => false,
        ],
        'tag' => 'div',
        'itemTemplate' => '{link}',
        'activeItemTemplate' => '{link}',
        'links' => $links,
    ]);
}