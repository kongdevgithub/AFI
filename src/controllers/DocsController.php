<?php

namespace app\controllers;

use app\components\Controller;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;

/**
 * Docs controller.
 */
class DocsController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        return $this->redirect(['view', 'file' => 'README.md']);
    }

    /**
     * @param null $file
     * @return string
     */
    public function actionView($file = null)
    {
        return $this->render('view', [
            'file' => $file,
        ]);
    }
}
