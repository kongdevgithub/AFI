<?php

namespace app\components;

use GuzzleHttp\Client;
use SevenShores\Hubspot\Factory;
use Yii;
use yii\base\Component;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * HubSpotApi
 *
 * @property Factory $client
 */
class HubSpotApi extends Component
{
    /**
     * @var string
     */
    public $apiKey = '';

    /**
     * @var string
     */
    public $clientId = '';

    /**
     * @var string
     */
    public $clientSecret = '';

    /**
     * @var array
     */
    public $redirect = [];

    /**
     * @var array
     */
    public $scopes = ['contacts', 'content', 'reports', 'social', 'automation', 'timeline', 'forms'];

    /**
     * @var Factory
     */
    private $_client;

    /**
     * @param null $token
     * @return Factory
     */
    public function getClient($token = null)
    {
        if (!$this->_client) {
            $this->_client = $token ? Factory::createWithToken($token) : Factory::create($this->apiKey);
        }
        return $this->_client;
    }

    /**
     * @param array|object $data
     * @param bool $getValue
     * @return array
     */
    public static function cleanResponseData($data, $getValue = false)
    {
        $_data = [];
        foreach ((array)$data as $k => $v) {
            if (is_scalar($v)) {
                $_data[$k] = $v;
            } elseif ($getValue && isset($v->value)) {
                $_data[$k] = $v->value;
            } else {
                $_data[$k] = static::cleanResponseData($v, $k == 'properties');
            }
        }
        return $_data;
    }

    /**
     * @param array $data
     * @param string $propertiesName
     * @return array
     */
    public static function cleanRequestData($data, $propertiesName = 'name')
    {
        $_data = [];
        foreach ($data as $k => $v) {
            $_data[] = [
                $propertiesName => $k,
                'value' => $v,
            ];
        }
        return $_data;
    }


    /**
     * @return string
     */
    public function getOauthUrl()
    {
        return 'https://app.hubspot.com/oauth/authorize?' . str_replace('+', '%20', http_build_query([
            'client_id' => $this->clientId,
            'scope' => implode(' ', $this->scopes),
            'redirect_uri' => Url::to($this->redirect, 'https'),
        ]));
    }

    /**
     * @param string $token
     * @param string $action authenticate|refresh
     * @return array|bool
     */
    public function oauth($token, $action = 'authenticate')
    {
        $client = new Client();
        $time = time();
        $request = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => Url::to($this->redirect, 'https'),
        ];
        if ($action == 'authenticate') {
            $request['grant_type'] = 'authorization_code';
            $request['code'] = $token;
        }
        if ($action == 'refresh') {
            $request['grant_type'] = 'refresh_token';
            $request['refresh_token'] = $token;
        }
        $response = $client->post('https://api.hubapi.com/oauth/v1/token', ['form_params' => $request]);
        $body = (string)$response->getBody();
        $token = Json::decode($body);
        if (isset($token['refresh_token'])) {
            $token['time'] = $time;
            Yii::$app->user->identity->setEavAttribute('hub_spot_token', Json::encode($token));
            return $token;
        }
        return false;
    }

//    /**
//     * @return array|bool
//     */
//    public function refresh()
//    {
//        $user = User::findOne(Y::user()->id);
//        if ($user->hub_spot_token) {
//            $token = Json::decode($user->hub_spot_token);
//            if (isset($token['refresh_token'])) {
//                return $this->oauth($token['refresh_token'], 'refresh');
//            }
//        }
//        return false;
//    }

    /**
     * @return bool|mixed
     */
    public function getToken()
    {
        if (Yii::$app->user->identity->hub_spot_token) {
            $token = Json::decode(Yii::$app->user->identity->hub_spot_token);
            if (time() - $token['time'] >= $token['expires_in']) {
                $token = $this->oauth($token['refresh_token'], 'refresh');
                if (!$token) {
                    Yii::$app->user->identity->setEavAttribute('hub_spot_token', null);
                    return false;
                }
            }
            return $token['access_token'];
        }
        return false;
    }
}