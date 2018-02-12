<?php

namespace app\traits;

use app\components\ReturnUrl;
use Yii;
use yii\web\Cookie;

/**
 * Trait to be attached to a `yii\base\Module` or `yii\web\Controller`
 *
 * Enables accessFilter for "route-access"
 */
trait TwoFactorTrait
{

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (Yii::$app->twoFactor->active) {
            if ($tfa = $this->beforeActionTwoFactor($action)) {
                return $tfa;
            }
        }
        return true;
    }

    public function beforeActionTwoFactor($action)
    {
        $this->renewTwoFactorCookie();
        if ($this->checkTwoFactorAuth($action)) {
            return $this->redirect(['//two-factor/check', 'ru' => ReturnUrl::getToken()]);
        }
        // force tfa setup
        if (empty(Yii::$app->user->identity->two_factor['enabled'])) {
            return $this->redirect(['//two-factor/setup', 'ru' => ReturnUrl::getToken()]);
        }
        return false;
    }

    private function renewTwoFactorCookie()
    {
        $user = Yii::$app->user;
        if ($user->isGuest) {
            return;
        }
        if ($cookie = Yii::$app->request->cookies->get('two-factor-' . $user->id)) {
            if (!empty($user->identity->two_factor['enabled']) && $cookie->value == md5($user->identity->two_factor['secret'])) {
                // renew cookie
                Yii::$app->response->cookies->add(new Cookie([
                    'name' => 'two-factor-' . $user->id,
                    'value' => md5($user->identity->two_factor['secret']),
                    'expire' => strtotime('+30days'),
                ]));
            } else {
                // remove cookie
                Yii::$app->response->cookies->remove(new Cookie([
                    'name' => 'two-factor-' . $user->id,
                ]));
            }
        }
    }

    private function checkTwoFactorAuth($action)
    {
        $user = Yii::$app->user;
        if ($user->isGuest) {
            return false;
        }
        if (empty($user->identity->two_factor['enabled'])) {
            return false;
        }
        if ($action->controller->id == 'two-factor' && $action->id == 'check') {
            return false;
        }
        if ($cookie = Yii::$app->request->cookies->get('two-factor-' . $user->id)) {
            if (!empty($user->identity->two_factor['enabled']) && $cookie->value == md5($user->identity->two_factor['secret'])) {
                return false;
            }
        }
        return true;
    }

}