<?php

namespace app\modules\goldoc\controllers;

use app\components\Controller;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * Report controller.
 */
class ReportController extends Controller
{

    use AccessBehaviorTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $user = Yii::$app->user;
        $controller = $this;
        $page = Yii::$app->request->get('report');
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
                'viewParam' => 'report',
            ],
        ];
    }

}
