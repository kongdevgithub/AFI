<?php

namespace app\components;

use GuzzleHttp\Client;
use SevenShores\Hubspot\Factory;
use Yii;
use yii\base\Component;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * AuthyApi
 *
 * @property \Authy\AuthyApi $client
 */
class AuthyApi extends Component
{
    /**
     * @var string
     */
    public $apiKey;

    /**
     * @var int
     */
    public $defaultCountryCode = 1;

    /**
     * @var \Authy\AuthyApi
     */
    private $_client;

    /**
     * @return \Authy\AuthyApi
     */
    public function getClient()
    {
        if (!$this->_client) {
            $this->_client = new \Authy\AuthyApi($this->apiKey);
        }
        return $this->_client;
    }

    /**
     * @param $email
     * @param $cellphone
     * @param int $country_code
     * @return \Authy\AuthyUser
     */
    public function registerUser($email, $cellphone, $country_code = null)
    {
        if (!$country_code) {
            $country_code = $this->defaultCountryCode;
        }
        return $this->getClient()->registerUser($email, $cellphone, $country_code);
    }

    /**
     * @param $authy_id
     * @param $token
     * @param array $opts
     * @return \Authy\AuthyResponse
     */
    public function verifyToken($authy_id, $token, $opts = [])
    {
        return $this->getClient()->verifyToken($authy_id, $token, $opts);
    }

    /**
     * @param $authy_id
     * @param array $opts
     * @return \Authy\AuthyResponse
     */
    public function requestSms($authy_id, $opts = [])
    {
        return $this->getClient()->requestSms($authy_id, $opts);
    }

    /**
     * @param $authy_id
     * @param array $opts
     * @return \Authy\AuthyResponse
     */
    public function phoneCall($authy_id, $opts = [])
    {
        return $this->getClient()->phoneCall($authy_id, $opts);
    }

    /**
     * @param $authy_id
     * @return \Authy\AuthyResponse
     */
    public function deleteUser($authy_id)
    {
        return $this->getClient()->deleteUser($authy_id);
    }
}