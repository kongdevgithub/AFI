<?php

namespace app\assets;

use yii\web\AssetBundle;

class JuiFixAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/web';
    public $js = [
        'js/jquery-ui-fix.js',
    ];
    public $depends = [
        'yii\jui\JuiAsset',
    ];
}
