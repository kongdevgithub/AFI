<?php

namespace app\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "product_to_option".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $item_id
 * @property integer $option_id
 * @property integer $product_type_to_option_id
 * @property string $value
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
 * @property \app\models\Item $item
 * @property \app\models\Option $option
 * @property \app\models\Product $product
 * @property \app\models\ProductTypeToOption $productTypeToOption
 */
class ProductToOption extends ActiveRecord
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
        return 'product_to_option';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'item_id', 'option_id', 'product_type_to_option_id', 'sort_order', 'quote_generated', 'deleted_at'], 'integer'],
            [['option_id'], 'required'],
            [['value', 'quote_quantity_factor'], 'string'],
            [['quantity', 'quote_unit_cost', 'quote_quantity', 'quote_total_cost', 'quote_make_ready_cost', 'quote_factor', 'quote_total_price', 'quote_minimum_cost', 'quote_weight', 'checked_quantity'], 'number'],
            [['quote_class', 'quote_label'], 'string', 'max' => 255],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Item::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['option_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Option::className(), 'targetAttribute' => ['option_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['product_type_to_option_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\ProductTypeToOption::className(), 'targetAttribute' => ['product_type_to_option_id' => 'id']]
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
            'option_id' => Yii::t('models', 'Option ID'),
            'product_type_to_option_id' => Yii::t('models', 'Product Type To Option ID'),
            'value' => Yii::t('models', 'Value'),
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
    public function getItem()
    {
        return $this->hasOne(\app\models\Item::className(), ['id' => 'item_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOption()
    {
        return $this->hasOne(\app\models\Option::className(), ['id' => 'option_id']);
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
    public function getProductTypeToOption()
    {
        return $this->hasOne(\app\models\ProductTypeToOption::className(), ['id' => 'product_type_to_option_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\models\query\ProductToOptionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\ProductToOptionQuery(get_called_class());
    }

}
