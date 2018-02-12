<?php

namespace app\controllers;

use app\components\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * HelpController
 */
class HelpController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $user = Yii::$app->user;
        $controller = $this;
        $page = Yii::$app->request->get('help');
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) use ($user, $controller, $page) {
                            $route = str_replace('/', '_', 'app_' . $controller->id . '_' . ($page ? $page : 'index'));
                            return $user->can($route, ['route' => true]);
                        },
                    ],
                ],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\web\ViewAction',
                'viewParam' => 'help',
            ],
        ];
    }

}
