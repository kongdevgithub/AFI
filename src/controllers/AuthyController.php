<?php

namespace app\controllers;

use app\components\Controller;
use app\components\ReturnUrl;
use app\models\form\AuthySetupForm;
use app\models\form\AuthyVerifyForm;
use app\traits\AccessBehaviorTrait;
use Yii;
use yii\filters\AccessControl;

class AuthyController extends Controller
{
    //use AccessBehaviorTrait;

    public $layout = 'narrow';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'setup', 'verify-setup', 'verify', 'sms', 'phone', 'disable'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionSetup()
    {
        $model = new AuthySetupForm();
        $post = Yii::$app->request->post();
        if ($post) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Please check your phone for an SMS to proceed.'));
                return $this->redirect(['verify-setup', 'ru' => ReturnUrl::getRequestToken()]);
            }
        } else {
            if (Yii::$app->user->identity->authy) {
                $model->phone = Yii::$app->user->identity->authy['phone'];
                $model->country_code = Yii::$app->user->identity->authy['country_code'];
            } else {
                $model->phone = Yii::$app->user->identity->profile->phone;
                $model->country_code = Yii::$app->authyApi->defaultCountryCode;
            }
        }
        return $this->render('setup', [
            'model' => $model,
        ]);
    }

    public function actionVerifySetup()
    {
        $model = new AuthyVerifyForm();
        $post = Yii::$app->request->post();
        if ($post) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $user = Yii::$app->user->identity;
                $authy = $user->authy;
                $authy['enabled'] = true;
                $user->authy = $authy;
                $user->save(false);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Two factor authentication is now enabled!'));
                return $this->redirect(ReturnUrl::getUrl(['index']));
            }
        }
        $model->token = '';
        return $this->render('verify-setup', [
            'model' => $model,
        ]);
    }

    public function actionVerify()
    {
        $model = new AuthyVerifyForm();
        $post = Yii::$app->request->post();
        if ($post) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Two factor authentication is now enabled!'));
                return $this->redirect(ReturnUrl::getUrl(['site/index']));
            }
        }
        return $this->render('verify', [
            'model' => $model,
        ]);
    }

    public function actionSms()
    {
        Yii::$app->authyApi->requestSms(Yii::$app->user->identity->authy['id']);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Please check your phone for an SMS to proceed.'));
        return $this->redirect(ReturnUrl::getUrl(['verify']));
    }

    public function actionPhone()
    {
        Yii::$app->authyApi->phoneCall(Yii::$app->user->identity->authy['id']);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Please check your phone for a call to proceed.'));
        return $this->redirect(ReturnUrl::getUrl(['verify']));
    }

    public function actionDisable()
    {
        $user = Yii::$app->user->identity;
        $user->authy = null;
        $user->save(false);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Two factor authentication is now disabled!'));
        return $this->redirect(ReturnUrl::getUrl(['index']));
    }
}
