<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/web';
    public $js = [
        'js/app.js',
        //'js/letter-avatar.js',
    ];
    public $css = [
        'css/app.css',
        'css/breadcrumb.css',
        'css/background.css',
        'css/diff.css',
    ];
    public $depends = [
        'bedezign\yii2\audit\web\JSLoggingAsset',
        'app\assets\AdminLteAsset',
        'yii2mod\alert\AlertAsset',
        'newerton\fancybox\FancyBoxAsset',
    ];
}
