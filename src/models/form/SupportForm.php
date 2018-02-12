<?php
namespace app\models\form;

use app\models\Address;
use app\models\Company;
use app\models\HubSpotCompany;
use app\models\User;
use shakura\yii2\gearman\JobWorkload;
use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * SupportForm
 * @package app\models\form
 */
class SupportForm extends Model
{
    /**
     * @var
     */
    public $subject;
    
    /**
     * @var
     */
    public $body;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['subject', 'body'], 'required'],
        ];
    }

    /**
     * @return bool
     */
    public function sendSupportEmail()
    {
        $subject = $this->subject;
        $body = 'project: afi-console' . "\n\n" . $this->body;
        /** @var User $user */
        $user = Yii::$app->user->identity;
        if ($this->validate()) {
            Yii::$app->mailer->compose()
                ->setTo('service@mrphp.com.au')
                ->setFrom([$user->email => $user->label])
                ->setSubject($subject)
                ->setTextBody($body)
                ->send();
            return true;
        }
        return false;
    }
}