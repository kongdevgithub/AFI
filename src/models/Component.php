<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * This is the model class for table "component".
 *
 * @mixin CacheBehavior
 * @mixin LinkBehavior
 *
 * @property float $unit_cost
 * @property float $make_ready_cost
 * @property float $minimum_cost
 * @property float $unit_weight
 * @property float $unit_dead_weight
 * @property float $unit_cubic_weight
 *
 * @property DearProduct $dearProduct
 */
class Component extends base\Component
{
    /**
     *
     */
    const COMPONENT_ADMIN = 11865;
    const COMPONENT_ILS80 = 11908;
    const COMPONENT_BLANK = 11815;
    const COMPONENT_PRINT = 11816;
    const COMPONENT_CURVE_ROLL = 12069;
    const COMPONENT_DG104 = 11817;
    const COMPONENT_ORBWRAP = 11837;
    const COMPONENT_EM = 12074;
    const COMPONENT_OEM = 14601;

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        //if (isset($data['Component']['component_config'])) {
        //    $data['Component']['component_config'] = Json::encode($data['Component']['component_config']);
        //}
        return parent::load($data, $formName);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
            //'cacheRelations' => [],
        ];
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if (Yii::$app->user->can('_view_cost_prices')) {
            $makeReadyCost = ($this->make_ready_cost > 0 ? number_format($this->make_ready_cost, 2) : '');
            $unitCost = number_format($this->unit_cost, 2);
            return $this->code . ' ' . $this->name . ' [' . ($makeReadyCost ? $makeReadyCost . '|' : '') . $unitCost . '/' . $this->unit_of_measure . ']';
        }
        return $this->code . ' ' . $this->name . ' [per/' . $this->unit_of_measure . ']';
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'code', 'name', 'component_type_id', 'brand', 'status', 'unit_cost', 'quantity_factor', 'component_config', 'quote_class', 'make_ready_cost', 'minimum_cost', 'unit_weight', 'unit_dead_weight', 'unit_cubic_weight', 'unit_of_measure', 'track_stock', 'notes', 'deleted_at', 'created_at', 'updated_at', 'quality_check', 'quality_code'],
            'create' => ['id', 'code', 'name', 'component_type_id', 'brand', 'status', 'unit_cost', 'quantity_factor', 'component_config', 'quote_class', 'make_ready_cost', 'minimum_cost', 'unit_weight', 'unit_dead_weight', 'unit_cubic_weight', 'unit_of_measure', 'track_stock', 'notes', 'deleted_at', 'created_at', 'updated_at', 'quality_check', 'quality_code'],
            'update' => ['id', 'code', 'name', 'component_type_id', 'brand', 'status', 'unit_cost', 'quantity_factor', 'component_config', 'quote_class', 'make_ready_cost', 'minimum_cost', 'unit_weight', 'unit_dead_weight', 'unit_cubic_weight', 'unit_of_measure', 'track_stock', 'notes', 'deleted_at', 'created_at', 'updated_at', 'quality_check', 'quality_code'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['code'], 'unique', 'targetAttribute' => ['code', 'deleted_at']];
        $rules[] = [['name'], 'unique', 'targetAttribute' => ['name', 'deleted_at']];
        $rules[] = [['unit_of_measure'], 'required'];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['component_type_id'] = Yii::t('app', 'Component Type');
        return $attributeLabels;
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        //$this->component_config = Json::decode($this->component_config);
        parent::afterFind();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->unit_weight = max($this->unit_dead_weight, $this->unit_cubic_weight);
        //$this->component_config = Json::encode($this->component_config);
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        //$this->component_config = Json::decode($this->component_config);
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
    public function getConfigDecoded()
    {
        return Json::decode($this->component_config);
    }

    /**
     * @param array|null $config
     */
    public function setConfigDecoded($config)
    {
        $this->component_config = Json::encode($config, JSON_PRETTY_PRINT);
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
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);
        if ((!$skipIfSet || $this->quantity_factor === null)) {
            $this->quantity_factor = '0 2';
        }
        return $this;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDearProduct()
    {
        return $this->hasOne(DearProduct::className(), ['model_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToComponents()
    {
        return $this->hasMany(ProductToComponent::className(), ['component_id' => 'id'])
            ->andWhere('deleted_at IS NULL');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToComponents()
    {
        return $this->hasMany(ProductTypeToComponent::className(), ['component_id' => 'id'])
            ->andWhere('deleted_at IS NULL');
    }
}
