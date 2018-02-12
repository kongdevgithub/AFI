<?php

namespace app\components;

use GuzzleHttp\Client;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * DearApi
 */
class DearApi extends Component
{
    /**
     * @var string
     */
    public $accountId;

    /**
     * @var string
     */
    public $applicationKey;

    /**
     * @var string
     */
    public $baseUrl = 'https://inventory.dearsystems.com/dearapi';

    /**
     * @param $service
     * @param $method
     * @param array $options
     * @param int $limit
     * @return array
     */
    public function getAllPages($service = 'Products', $method = 'GET', $options = [], $limit = 100)
    {
        $out = [
            $service => [],
        ];
        $page = 1;
        $options['query']['limit'] = $limit;
        while (true) {
            $options['query']['page'] = $page;
            $page++;
            $data = $this->call($service, $method, $options);
            if (empty($data[$service])) {
                break;
            }
            foreach ($data[$service] as $row) {
                $out[$service][] = $row;
            }
        }

        return $out;
    }

    /**
     * @param string $service
     * @param string $method
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function call($service, $method = 'GET', $options = [])
    {
        $client = new Client();
        $options = ArrayHelper::merge([
            'headers' => [
                'api-auth-accountid' => $this->accountId,
                'api-auth-applicationkey' => $this->applicationKey,
            ],
        ], $options);
        $res = $client->request($method, $this->baseUrl . '/' . $service, $options);
        return Json::decode($res->getBody());
    }

}