<?php

namespace app\controllers;

use app\components\Controller;
use app\components\ReturnUrl;
use app\components\TwoFactor;
use app\models\form\TwoFactorForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Cookie;

class TwoFactorController extends Controller
{
    public $layout = 'narrow';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'setup', 'check', 'disable'],
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
        $model = new TwoFactorForm();
        $post = Yii::$app->request->post();
        $user = Yii::$app->user->identity;

        if (!empty($user->two_factor['enabled'])) {
            return $this->redirect(['index', 'ru' => ReturnUrl::getRequestToken()]);
        }
        if (empty($user->two_factor)) {
            $user->two_factor = [
                'secret' => TwoFactor::generateRandomClue(),
            ];
            $user->save(false);
        }

        if ($post) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->user->identity->setEavAttribute('two_factor', [
                    'secret' => $user->two_factor['secret'],
                    'enabled' => 1,
                ]);
                Yii::$app->response->cookies->add(new Cookie([
                    'name' => 'two-factor-' . Yii::$app->user->id,
                    'value' => md5($user->two_factor['secret']),
                    'expire' => strtotime('+30days'),
                ]));
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Two factor authentication is now enabled!'));
                //return $this->redirect(['index', 'ru' => ReturnUrl::getRequestToken()]);
                return $this->redirect(ReturnUrl::getUrl(['//site/index']));
            }
        }
        $model->code = '';
        return $this->render('setup', [
            'model' => $model,
        ]);
    }


    public function actionCheck()
    {
        $model = new TwoFactorForm();
        $post = Yii::$app->request->post();
        $user = Yii::$app->user->identity;

        if (empty($user->two_factor['enabled'])) {
            return $this->redirect(ReturnUrl::getUrl(['site/index']));
        }
        if ($post) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->response->cookies->add(new Cookie([
                    'name' => 'two-factor-' . Yii::$app->user->id,
                    'value' => md5($user->two_factor['secret']),
                    'expire' => strtotime('+30days'),
                ]));
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Two factor authentication verified!'));
                return $this->redirect(ReturnUrl::getUrl(['//site/index']));
            }
        }
        $model->code = '';
        return $this->render('check', [
            'model' => $model,
        ]);
    }

    public function actionDisable()
    {
        //Yii::$app->user->identity->setEavAttribute('two_factor', null);
        //Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Two factor authentication is now disabled!'));

        Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Two factor authentication cannot be disabled!'));
        return $this->redirect(ReturnUrl::getUrl(['index']));
    }
}
