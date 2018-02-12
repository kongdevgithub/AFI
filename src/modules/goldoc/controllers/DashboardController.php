<?php

namespace app\modules\goldoc\controllers;

use app\components\Controller;
use app\components\PdfManager;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * This is the class for controller "app\controllers\DashboardController".
 */
class DashboardController extends Controller
{
    use AccessBehaviorTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $user = Yii::$app->user;
        $controller = $this;
        $page = Yii::$app->request->get('dashboard');
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) use ($user, $controller, $page) {
                            $route = str_replace('/', '_', 'goldoc_' . $controller->id . '_' . ($page ? $page : 'index'));
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
                'viewParam' => 'dashboard',
            ],
        ];
    }

}
