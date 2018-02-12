<?php

namespace app\models;

use app\components\EmailManager;
use app\components\Helper;
use app\components\quotes\components\BaseComponentQuote;
use app\models\query\DearSaleQuery;
use GuzzleHttp\Exception\ClientException;
use Yii;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * This is the model class for table "dear".
 */
class DearSale extends Dear
{

    /**
     *
     */
    const MODEL_NAME = 'app\models\Job';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->model_name = self::MODEL_NAME;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->model_name = self::MODEL_NAME;
        return parent::beforeSave($insert);
    }

    /**
     * @return DearSaleQuery
     */
    public static function find()
    {
        return new DearSaleQuery(get_called_class(), ['model_name' => self::MODEL_NAME]);
    }

    /**
     * @var array
     */
    public static $pushErrors = [];

    /**
     * @param $model_id
     * @param bool $force
     * @return bool
     * @throws Exception
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public static function dearPush($model_id, $force = false)
    {
        if (YII_ENV != 'prod') return false;

        static::$pushErrors = [];
        $dearApi = Yii::$app->dearApi;

        $job = Job::findOne($model_id);
        if (!$job) {
            return false;
        }

        // log push
        Log::log('dear sale push', $job);

        // delete dear sale
        if ($job->deleted_at) {
            $dearSale = self::find()->andWhere(['model_id' => $job->id])->one();
            if ($dearSale) {
                $dearApi->call('Sale', 'DELETE', [
                    'json' => [
                        'ID' => $dearSale->dear_id,
                    ],
                ]);
                $dearSale->delete();
            }
            return true;
        }

        if (!$job->dear_mode) $job->dear_mode = 'AUTOPICKPACKSHIP'; // legacy
        $mode = $job->status == 'job/complete' ? 'AUTOPICKPACKSHIP' : 'AUTOPICK';

        $data = static::getDearPushData($job, $mode);

        // first check if the materials match
        $hash = md5(Json::encode($data['Lines']));
        if (!$force && $job->dear_mode == $mode && $job->dear_materials_hash == $hash) {
            static::$pushErrors[] = 'materials and mode already match';
            return false;
        }
        if (empty($data['Lines'])) {
            return true;
        }

        $dearSale = self::find()->andWhere(['model_id' => $job->id])->one();
        if ($dearSale) {
            $dearApi->call('Sale', 'DELETE', [
                'json' => [
                    'ID' => $dearSale->dear_id,
                ],
            ]);
            $dearSale->delete();
        }

        $dearSale = new DearSale();
        $dearSale->model_id = $job->id;

        if ($dearSale->isNewRecord) {
            $method = 'POST';
        } else {
            $method = 'PUT';
            $data['ID'] = $dearSale->dear_id;
        }
        try {
            $response = $dearApi->call('Sale', $method, ['json' => $data]);
        } catch (ClientException $e) {
            $response = Json::decode($e->getResponse()->getBody()->getContents());
            $message = isset($response[0]['Exception']) ? $response[0]['Exception'] : false;
            if ($message) {
                static::$pushErrors[] = $message;
                EmailManager::sendDearSalePushError($job, $message);
                return false;
            } else {
                throw $e;
            }
        }
        if ($dearSale->isNewRecord) {
            $dearSale->dear_id = $response['ID'];
        }

        // save
        if (!$dearSale->save()) {
            throw new Exception('cannot save DearSale [model_id:' . $dearSale->model_id . '] ' . Helper::getErrorString($dearSale));
        }

        // save the materials list
        $job->dear_materials_hash = $hash;
        $job->dear_mode = $mode;
        if (!$job->save(false)) {
            throw new Exception('cannot save Job [model_id:' . $job->id . '] ' . Helper::getErrorString($job));
        }

        return true;
    }

    /**
     * @param Job $job
     * @param string $mode
     * @return array
     */
    public static function getDearPushData($job, $mode)
    {
        $data = [
            'CustomerID' => 'a29a5dfb-b209-4ad5-861c-3030dc6e4b7a',
            'AutoPickPackShipMode' => $mode,
            'OrderStatus' => 'Authorised',
            'CustomerReference' => 'job-' . $job->vid,
            'ShipBy' => $job->despatch_date,
            'Location' => 'AFI Branding',
            'Lines' => [],
        ];
        foreach ($job->products as $product) {
            foreach ($product->items as $item) {
                if ($item->quantity < 1) continue;
                foreach ($item->getMaterials() as $material) {
                    $data['Lines'][] = [
                        'SKU' => $material['code'],
                        'Quantity' => $material['quantity'],
                        'Price' => 0,
                        'Tax' => 0,
                        'Total' => 0,
                        'TaxRule' => 'GST on Income',
                        'Comment' => 'product-' . $product->id . ' item-' . $item->id,
                    ];
                }
            }
        }
        return $data;
    }

}
