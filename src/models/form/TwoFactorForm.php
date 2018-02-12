<?php

namespace app\models\form;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Class TwoFactorForm
 * @package app\models\form
 *
 */
class TwoFactorForm extends Model
{
    /**
     * @var
     */
    public $code;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['code'], 'required'],
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

        if (!Yii::$app->twoFactor->check($this->code)) {
            $this->addError('code', Yii::t('app', 'Invalid code'));
            return false;
        }

        return true;
    }
}