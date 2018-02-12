<?php

namespace app\traits;

use Yii;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * Trait to be attached to a `yii\base\Module` or `yii\web\Controller`
 *
 * Enables accessFilter for "route-access"
 */
trait AccessBehaviorTrait
{
    public function behaviors()
    {
        if ($this instanceof Module) {
            $controller = Yii::$app->controller;
        } else {
            $controller = $this;
        }
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'matchCallback' => function ($rule, $action) use ($controller) {
                                $module = $controller->module->id;
                                if ($module == Yii::$app->id) {
                                    $module = 'app';
                                }
                                $permission = str_replace('/', '_', $module . '_' . $controller->id . '_' . $action->id);
                                return Yii::$app->user->can($permission, ['route' => true]);
                            },
                        ]
                    ]
                ]
            ]
        );
    }
}