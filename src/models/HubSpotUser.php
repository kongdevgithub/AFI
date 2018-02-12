<?php

namespace app\models;

use app\models\query\HubSpotUserQuery;
use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "hub_spot".
 */
class HubSpotUser extends HubSpot
{

    /**
     *
     */
    const MODEL_NAME = 'app\models\User';

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
     * @return HubSpotUserQuery
     */
    public static function find()
    {
        return new HubSpotUserQuery(get_called_class(), ['model_name' => self::MODEL_NAME]);
    }
    
}
