<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "product_to_component".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $item_id
 * @property integer $component_id
 * @property integer $product_type_to_component_id
 * @property string $quantity
 * @property integer $sort_order
 * @property string $quote_class
 * @property string $quote_label
 * @property string $quote_unit_cost
 * @property string $quote_quantity
 * @property string $quote_total_cost
 * @property string $quote_make_ready_cost
 * @property string $quote_factor
 * @property string $quote_total_price
 * @property string $quote_minimum_cost
 * @property string $quote_quantity_factor
 * @property string $quote_weight
 * @property integer $quote_generated
 * @property string $checked_quantity
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\models\Component $component
 * @property \app\models\Item $item
 * @property \app\models\Product $product
 * @property \app\models\ProductTypeToComponent $productTypeToComponent
 */
class ProductToComponent extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->dbData;
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_to_component';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'item_id', 'component_id', 'product_type_to_component_id', 'sort_order', 'quote_generated', 'deleted_at'], 'integer'],
            [['component_id'], 'required'],
            [['quantity', 'quote_unit_cost', 'quote_quantity', 'quote_total_cost', 'quote_make_ready_cost', 'quote_factor', 'quote_total_price', 'quote_minimum_cost', 'quote_weight', 'checked_quantity'], 'number'],
            [['quote_quantity_factor'], 'string'],
            [['quote_class', 'quote_label'], 'string', 'max' => 255],
            [['component_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Component::className(), 'targetAttribute' => ['component_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Item::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['product_type_to_component_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\ProductTypeToComponent::className(), 'targetAttribute' => ['product_type_to_component_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'product_id' => Yii::t('models', 'Product ID'),
            'item_id' => Yii::t('models', 'Item ID'),
            'component_id' => Yii::t('models', 'Component ID'),
            'product_type_to_component_id' => Yii::t('models', 'Product Type To Component ID'),
            'quantity' => Yii::t('models', 'Quantity'),
            'sort_order' => Yii::t('models', 'Sort Order'),
            'quote_class' => Yii::t('models', 'Quote Class'),
            'quote_label' => Yii::t('models', 'Quote Label'),
            'quote_unit_cost' => Yii::t('models', 'Quote Unit Cost'),
            'quote_quantity' => Yii::t('models', 'Quote Quantity'),
            'quote_total_cost' => Yii::t('models', 'Quote Total Cost'),
            'quote_make_ready_cost' => Yii::t('models', 'Quote Make Ready Cost'),
            'quote_factor' => Yii::t('models', 'Quote Factor'),
            'quote_total_price' => Yii::t('models', 'Quote Total Price'),
            'quote_minimum_cost' => Yii::t('models', 'Quote Minimum Cost'),
            'quote_quantity_factor' => Yii::t('models', 'Quote Quantity Factor'),
            'quote_weight' => Yii::t('models', 'Quote Weight'),
            'quote_generated' => Yii::t('models', 'Quote Generated'),
            'checked_quantity' => Yii::t('models', 'Checked Quantity'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComponent()
    {
        return $this->hasOne(\app\models\Component::className(), ['id' => 'component_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(\app\models\Item::className(), ['id' => 'item_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(\app\models\Product::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToComponent()
    {
        return $this->hasOne(\app\models\ProductTypeToComponent::className(), ['id' => 'product_type_to_component_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ProductToComponentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ProductToComponentQuery(get_called_class());
    }

}
