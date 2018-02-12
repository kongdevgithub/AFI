<?php

namespace app\models\form;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Class AuthyVerifyForm
 * @package app\models\form
 *
 */
class AuthyVerifyForm extends Model
{
    /**
     * @var
     */
    public $token;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['token'], 'required'],
        ];
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $verification = Yii::$app->authyApi->verifyToken(Yii::$app->user->identity->authy['id'], $this->token);
        if (!$verification->ok()) {
            $this->addError('token', Yii::t('app', 'Invalid verification token.'));
            return false;
        }
        return true;
    }
}