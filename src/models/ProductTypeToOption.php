<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * This is the model class for table "product_type_to_option".
 * @property string|array $values
 */
class ProductTypeToOption extends base\ProductTypeToOption
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        //if (isset($data['ProductTypeToOption']['values'])) {
        //    $data['ProductTypeToOption']['values'] = Json::encode($data['ProductTypeToOption']['values']);
        //}
        if (isset($data['ProductTypeToOption']['valuesDecoded'])) {
            $this->setValuesDecoded($data['ProductTypeToOption']['valuesDecoded']);
        }
        return parent::load($data, $formName);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        //$this->values = Json::decode($this->values);
        parent::afterFind();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        //$this->values = Json::encode($this->values);
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        //$this->values = Json::decode($this->values);
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['product_type_id'] = Yii::t('app', 'Product Type');
        $attributeLabels['product_type_to_item_type_id'] = Yii::t('app', 'Item');
        $attributeLabels['option_id'] = Yii::t('app', 'Option');
        return $attributeLabels;
    }

    /**
     * @return array|null
     */
    public function getValuesDecoded()
    {
        return Json::decode($this->values);
    }

    /**
     * @param array|null $values
     */
    public function setValuesDecoded($values)
    {
        $this->values = Json::encode($values, JSON_PRETTY_PRINT);
    }


    /**
     * @return array|null
     */
    public function getConfigDecoded()
    {
        return Json::decode($this->config);
    }

    /**
     * @param array|null $config
     */
    public function setConfigDecoded($config)
    {
        $this->config = Json::encode($config, JSON_PRETTY_PRINT);
    }

}
