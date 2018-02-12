<?php

namespace app\models;

use app\components\EmailManager;
use app\components\Helper;
use app\components\quotes\components\BaseComponentQuote;
use app\models\query\DearProductQuery;
use GuzzleHttp\Exception\ClientException;
use Yii;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * This is the model class for table "dear".
 */
class DearProduct extends Dear
{

    /**
     *
     */
    const MODEL_NAME = 'app\models\Component';

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
     * @return DearProductQuery
     */
    public static function find()
    {
        return new DearProductQuery(get_called_class(), ['model_name' => self::MODEL_NAME]);
    }

    /**
     * @var array
     */
    public static $pushErrors = [];

    /**
     * @param $model_id
     * @param bool $quick
     * @return bool
     * @throws Exception
     */
    public static function dearPush($model_id, $quick = false)
    {
        if (YII_ENV != 'prod') return false;

        static::$pushErrors = [];
        $dearApi = Yii::$app->dearApi;

        $dearProduct = self::find()->andWhere(['model_id' => $model_id])->one();
        if ($quick && $dearProduct) return true;
        if (!$dearProduct) {
            $dearProduct = new DearProduct();
            $dearProduct->model_id = $model_id;
        }

        $component = Component::findOne($dearProduct->model_id);
        if (!$component){
            return false;
        }

        // log push
        Log::log('dear product push', $component);

        $factor = false;
        if (trim($component->quantity_factor)) {
            list($_, $factor) = explode(' ', explode("\n", trim($component->quantity_factor))[0]);
        }
        if (!$factor) {
            $factor = 2;
        }

        $data = [
            'SKU' => $component->code,
            'Name' => $component->name,
            'Category' => $component->componentType->name,
            'Brand' => $component->brand,
            'Type' => $component->track_stock ? 'Stock' : 'Service',
            'CostingMethod' => 'FIFO',
            'DefaultLocation' => 'AFI Branding',
            'UOM' => $component->unit_of_measure,
            'PriceTier1' => $component->unit_cost * $factor * 1.18, // Full Margin
            'PriceTier2' => $component->unit_cost * $factor, // Wholesale
            'PriceTier3' => $component->unit_cost, // Cost
            'Status' => $component->deleted_at ? 'Deprecated' : 'Active',
            'Weight' => $component->unit_dead_weight,
            //'COGSAccount' => '',
            //'RevenueAccount' => '',
        ];
        if ($dearProduct->isNewRecord) {
            $method = 'POST';
        } else {
            $method = 'PUT';
            $data['ID'] = $dearProduct->dear_id;
        }

        try {
            $response = $dearApi->call('Products', $method, ['json' => $data]);
        } catch (ClientException $e) {
            $response = Json::decode($e->getResponse()->getBody()->getContents());
            $message = isset($response[0]['Exception']) ? $response[0]['Exception'] : false;
            if ($message) {
                static::$pushErrors[] = $message;
                EmailManager::sendDearProductPushError($component, $message);
                return false;
            } else {
                throw $e;
            }
        }

        if ($dearProduct->isNewRecord) {
            $dearProduct->dear_id = $response['Products'][0]['ID'];
        }

        // save
        if (!$dearProduct->save()) {
            throw new Exception('cannot save DearProduct [model_id:' . $dearProduct->model_id . '] ' . Helper::getErrorString($dearProduct));
        }

        return true;
    }

    /**
     * @param $dear_id
     * @param null $data
     * @return bool
     * @throws Exception
     */
    public static function dearPull($dear_id, $data = null)
    {
        if (YII_ENV != 'prod') return false;

        // begin transaction
        $transaction = Yii::$app->dbData->beginTransaction();

        // get dear product
        $dearProduct = DearProduct::findOne(['dear_id' => $dear_id]);
        if (!$dearProduct) {
            // create dear product
            $dearProduct = new DearProduct();
            $dearProduct->dear_id = $dear_id;
            $dearProduct->model_id = 0;
            if (!$dearProduct->save()) {
                $transaction->rollBack();
                throw new Exception('Cannot save DearProduct: ' . Helper::getErrorString($dearProduct));
            }
        }

        // get component type
        $componentType = ComponentType::find()->notDeleted()->andWhere(['name' => $data['Category']])->one();
        if (!$componentType) {
            // create component type
            $componentType = new ComponentType();
            $componentType->name = $data['Category'];
            if (!$componentType->save()) {
                $transaction->rollBack();
                throw new Exception('Cannot save ComponentType: ' . Helper::getErrorString($componentType));
            }
        }
        // get component
        $component = false;
        if ($dearProduct->model_id) {
            $component = Component::findOne($dearProduct->model_id);
        }
        if (!$component) {
            // create component
            $component = new Component();
            $component->loadDefaultValues();
            $component->quote_class = BaseComponentQuote::className();
        }
        // update component
        $component->code = $data['SKU'];
        $component->name = $data['Name'];
        $component->component_type_id = $componentType->id;
        $component->unit_cost = $data['PriceTiers']['Wholesale'] / 2;
        $component->unit_of_measure = $data['UOM'];
        $component->unit_dead_weight = $data['Weight'];
        if ($data['Status'] == 'Deprecated') {
            if (!$component->deleted_at) {
                $component->deleted_at = time();
            }
        } else {
            if ($component->deleted_at) {
                $component->deleted_at = null;
            }
        }
        if (!$component->save()) {
            $transaction->rollBack();
            throw new Exception('Cannot save Component: ' . Helper::getErrorString($component));
        }

        // save dear product
        if ($dearProduct->model_id != $component->id) {
            $dearProduct->model_id = $component->id;
            if (!$dearProduct->save(false)) {
                $transaction->rollBack();
                throw new Exception('Cannot save DearProduct: ' . Helper::getErrorString($dearProduct));
            }
        }

        // we did it!
        $transaction->commit();

        // log pull
        Log::log('dear product pull', $component);

        return true;
    }
}
