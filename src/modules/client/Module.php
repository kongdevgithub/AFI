<?php

namespace app\modules\client;

use app\traits\AccessBehaviorTrait;

class Module extends \yii\base\Module
{
    use AccessBehaviorTrait;

    public $controllerNamespace = 'app\modules\client\controllers';

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
