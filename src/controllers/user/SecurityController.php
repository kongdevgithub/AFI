<?php

namespace app\controllers\user;

use dektrium\user\controllers\SecurityController as BaseSecurityController;
use dektrium\user\models\LoginForm;
use Yii;
use yii\web\Cookie;
use yii\web\Response;

/**
 * Class SecurityController
 * @package app\controllers\user
 */
class SecurityController extends BaseSecurityController
{
    public $layout = '@app/views/layouts/narrow';

    /**
     * Displays the login page.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            $this->goHome();
        }

        /** @var LoginForm $model */
        $model = \Yii::createObject(LoginForm::className());
        $event = $this->getFormEvent($model);

        $this->performAjaxValidation($model);

        $this->trigger(self::EVENT_BEFORE_LOGIN, $event);

        if ($model->load(\Yii::$app->getRequest()->post())) {
            if ($model->login()) {
                $this->trigger(self::EVENT_AFTER_LOGIN, $event);
                return $this->goBack();
            }
        } else {
            $model->rememberMe = true;
        }

        return $this->render('login', [
            'model' => $model,
            'module' => $this->module,
        ]);
    }

    /**
     * Logs the user out and then redirects to the homepage.
     *
     * @return Response
     */
    public function actionLogout()
    {
        $event = $this->getUserEvent(\Yii::$app->user->identity);

        $this->trigger(self::EVENT_BEFORE_LOGOUT, $event);

        // remove two-factor cookie
        if (Yii::$app->request->cookies->get('two-factor-' . Yii::$app->user->id)) {
            Yii::$app->response->cookies->remove(new Cookie([
                'name' => 'two-factor-' . Yii::$app->user->id,
            ]));
        }

        Yii::$app->user->logout();

        $this->trigger(self::EVENT_AFTER_LOGOUT, $event);

        return $this->goHome();
    }
}
