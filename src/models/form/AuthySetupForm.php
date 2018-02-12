<?php

namespace app\models\form;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Class AuthySetupForm
 * @package app\models\form
 *
 */
class AuthySetupForm extends Model
{
    /**
     * @var
     */
    public $phone;
    /**
     * @var
     */
    public $country_code;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['phone', 'country_code'], 'required'],
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

        $authyApi = Yii::$app->authyApi;
        $user = Yii::$app->user->identity;

        // register authy user
        $registerUser = $authyApi->registerUser($user->email, $this->phone, $this->country_code);
        if (!$registerUser->ok()) {
            $errors = [];
            foreach ($registerUser->errors() as $k => $v) {
                $errors[] = $k . ' = ' . $v;
            }
            $this->addError('phone', 'cannot register cellphone: ' . implode(', ', $errors));
            return false;
        }

        // send sms
        $requestSms = $authyApi->requestSms($registerUser->id());
        if (!$requestSms->ok()) {
            $errors = [];
            foreach ($requestSms->errors() as $k => $v) {
                $errors[] = $k . ' = ' . $v;
            }
            $this->addError('phone', 'cannot send sms: ' . implode(', ', $errors));
            return false;
        }

        // save authy details
        $user->authy = [
            'id' => $registerUser->id(),
            'phone' => $this->phone,
            'country_code' => $this->country_code,
        ];
        $user->save(false);

        return true;
    }
}