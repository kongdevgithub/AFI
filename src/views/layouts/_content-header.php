<?php
/**
 * @var $this \yii\web\View
 */

use yii\bootstrap\Nav;

if (!empty($this->params['heading'])) {
    if ($this->params['heading']) {
        echo '<h1>' . $this->params['heading'] . '</h1>';
    }
} else if ($this->title && !isset($this->params['heading'])) {
    echo '<h1>' . $this->title . '</h1>';
}
if (!empty($this->params['subheading'])) {
    echo '<h2>' . $this->params['subheading'] . '</h2>';
}

if (!empty($this->params['nav'])) {
    echo Nav::widget([
        'items' => $this->params['nav'],
        'options' => ['class' => 'nav-tabs'],
        'activateParents' => true,
        'encodeLabels' => false,
    ]);
}