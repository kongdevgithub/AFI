<?php

namespace app\modules\goldoc\controllers;

use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use yii\web\Controller;

/**
 * Class DefaultController
 * @package app\modules\goldoc\controllers
 */
class DefaultController extends Controller
{
    use AccessBehaviorTrait;

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionStaff()
    {
        return $this->render('staff');
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionGlossary()
    {
        return $this->render('glossary');
    }

}
