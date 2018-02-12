<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * This is the model class for table "option".
 *
 * @mixin LinkBehavior
 *
 */
class Option extends base\Option
{

    const OPTION_SIZE = 1;
    const OPTION_SIZE_OFFSET = 33;
    const OPTION_POWDERCOAT = 4;
    const OPTION_PRINTER = 14;
    const OPTION_PRINTER_BACK = 61;
    const OPTION_SUBSTRATE = 5;
    const OPTION_SUBSTRATE_BACK = 63;
    const OPTION_FLATBED_SUBSTRATE = 58;
    const OPTION_LABEL = 47;
    const OPTION_REFRAME_EXTRUSION = 3;
    const OPTION_ARTWORK = 20;
    const OPTION_FENCE_EYELET = 27;
    const OPTION_ILLUMINATED_REAR = 23;
    const OPTION_ILLUMINATED_EDGE = 24;
    const OPTION_CURVE = 46;
    const OPTION_CUBE_SIDE = 49;
    const OPTION_SPARE_PART = 60;
    const OPTION_EM_PRINT = 69;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'name', 'field_class', 'field_config', 'deleted_at', 'created_at', 'updated_at'],
            'create' => ['id', 'name', 'field_class', 'field_config', 'deleted_at', 'created_at', 'updated_at'],
            'update' => ['id', 'name', 'field_class', 'field_config', 'deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        //if (isset($data['Option']['field_config'])) {
        //    $data['Option']['field_config'] = Json::encode($data['Option']['field_config']);
        //}
        return parent::load($data, $formName);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        //$this->field_config = Json::decode($this->field_config);
        parent::afterFind();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        //$this->field_config = Json::encode($this->field_config);
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        //$this->field_config = Json::decode($this->field_config);
        //Yii::$app->cacheModel->flush();
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        //Yii::$app->cacheModel->flush();
        parent::afterDelete();
    }

    /**
     * @return array|null
     */
    public function getFieldConfigDecoded()
    {
        return Json::decode($this->field_config);
    }

    /**
     * @param array|null $fieldConfig
     */
    public function setFieldConfigDecoded($fieldConfig)
    {
        $this->field_config = Json::encode($fieldConfig, JSON_PRETTY_PRINT);
    }

    ///**
    // * @inheritdoc
    // */
    //public static function findOne($condition)
    //{
    //    $db = self::getDb();
    //    return $db->cache(function ($db) use ($condition) {
    //        return parent::findOne($condition);
    //    });
    //}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToOptions()
    {
        return $this->hasMany(ProductToOption::className(), ['option_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('option');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToOptions()
    {
        return $this->hasMany(ProductTypeToOption::className(), ['option_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('option');
    }

}
