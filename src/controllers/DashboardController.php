<?php

namespace app\controllers;

use app\components\Controller;
use app\components\PdfManager;
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
    use TwoFactorTrait;

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
                'viewParam' => 'dashboard',
            ],
        ];
    }

    /**
     * @param $view
     * @param bool $heading
     * @param bool $html
     * @return string
     * @throws Exception
     */
    public function actionPrint($view, $heading = false, $html = false)
    {
        $params = Yii::$app->request->get('params');

        if (!$html) {
            $pdf = PdfManager::getDashboard($view, $heading, $params);
            $filename = 'test.pdf';
            if (!$pdf->send($filename, true)) {
                //debug($pdf->getCommand()->getOutput()); die;
                throw new Exception('Could not create PDF: ' . $pdf->getError());
            }
            return '';
        }

        $this->layout = false;
        return $this->render('print', [
            'heading' => $heading,
            'view' => 'pages/' . $view,
            'params' => $params,
        ]);

    }

}
