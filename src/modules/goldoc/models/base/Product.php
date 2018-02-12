<?php

namespace app\modules\goldoc\models\base;

use \Yii;
use \yii\db\ActiveRecord;

/**
 * This is the base-model class for table "product".
 *
 * @property integer $id
 * @property integer $venue_id
 * @property integer $goldoc_manager_id
 * @property integer $active_manager_id
 * @property integer $sport_id
 * @property integer $supplier_id
 * @property integer $type_id
 * @property integer $item_id
 * @property integer $design_id
 * @property integer $colour_id
 * @property integer $substrate_id
 * @property integer $width
 * @property integer $height
 * @property integer $depth
 * @property string $loc
 * @property string $loc_notes
 * @property string $install_notes
 * @property string $description
 * @property string $details
 * @property string $status
 * @property integer $quantity
 * @property integer $sponsor_id
 * @property integer $installer_id
 * @property string $comments
 * @property string $drawing_reference
 * @property string $fixing_method
 * @property string $supplier_reference
 * @property integer $supplier_priced
 * @property string $artwork_code
 * @property string $placement
 * @property string $product_unit_price
 * @property string $installer_standard_hours
 * @property string $installer_specialist_hours
 * @property string $bump_out_hours
 * @property string $scissor_lift_hours
 * @property string $rt_scissor_lift_hours
 * @property string $small_boom_hours
 * @property string $large_boom_hours
 * @property string $flt_hours
 * @property string $product_price
 * @property string $labour_price
 * @property string $machine_price
 * @property string $total_price
 * @property integer $deleted_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property \app\modules\goldoc\models\Venue $venue
 * @property \app\modules\goldoc\models\Substrate $substrate
 * @property \app\modules\goldoc\models\Type $type
 * @property \app\modules\goldoc\models\Installer $installer
 * @property \app\modules\goldoc\models\Sport $sport
 * @property \app\modules\goldoc\models\Supplier $supplier
 * @property \app\modules\goldoc\models\Sponsor $sponsor
 * @property \app\modules\goldoc\models\Item $item
 * @property \app\modules\goldoc\models\Design $design
 * @property \app\modules\goldoc\models\Colour $colour
 */
class Product extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->dbGoldoc;
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['venue_id', 'goldoc_manager_id', 'active_manager_id', 'sport_id', 'supplier_id', 'type_id', 'item_id', 'design_id', 'colour_id', 'substrate_id', 'width', 'height', 'depth', 'quantity', 'sponsor_id', 'installer_id', 'supplier_priced', 'deleted_at'], 'integer'],
            [['loc_notes', 'install_notes', 'description', 'details', 'comments'], 'string'],
            [['product_unit_price', 'installer_standard_hours', 'installer_specialist_hours', 'bump_out_hours', 'scissor_lift_hours', 'rt_scissor_lift_hours', 'small_boom_hours', 'large_boom_hours', 'flt_hours', 'product_price', 'labour_price', 'machine_price', 'total_price'], 'number'],
            [['loc', 'supplier_reference', 'artwork_code', 'placement'], 'string', 'max' => 32],
            [['status'], 'string', 'max' => 128],
            [['drawing_reference', 'fixing_method'], 'string', 'max' => 255],
            [['venue_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\modules\goldoc\models\Venue::className(), 'targetAttribute' => ['venue_id' => 'id']],
            [['substrate_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\modules\goldoc\models\Substrate::className(), 'targetAttribute' => ['substrate_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\modules\goldoc\models\Type::className(), 'targetAttribute' => ['type_id' => 'id']],
            [['installer_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\modules\goldoc\models\Installer::className(), 'targetAttribute' => ['installer_id' => 'id']],
            [['sport_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\modules\goldoc\models\Sport::className(), 'targetAttribute' => ['sport_id' => 'id']],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\modules\goldoc\models\Supplier::className(), 'targetAttribute' => ['supplier_id' => 'id']],
            [['sponsor_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\modules\goldoc\models\Sponsor::className(), 'targetAttribute' => ['sponsor_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\modules\goldoc\models\Item::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['design_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\modules\goldoc\models\Design::className(), 'targetAttribute' => ['design_id' => 'id']],
            [['colour_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\modules\goldoc\models\Colour::className(), 'targetAttribute' => ['colour_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'venue_id' => Yii::t('models', 'Venue ID'),
            'goldoc_manager_id' => Yii::t('models', 'Goldoc Manager ID'),
            'active_manager_id' => Yii::t('models', 'Active Manager ID'),
            'sport_id' => Yii::t('models', 'Sport ID'),
            'supplier_id' => Yii::t('models', 'Supplier ID'),
            'type_id' => Yii::t('models', 'Type ID'),
            'item_id' => Yii::t('models', 'Item ID'),
            'design_id' => Yii::t('models', 'Design ID'),
            'colour_id' => Yii::t('models', 'Colour ID'),
            'substrate_id' => Yii::t('models', 'Substrate ID'),
            'width' => Yii::t('models', 'Width'),
            'height' => Yii::t('models', 'Height'),
            'depth' => Yii::t('models', 'Depth'),
            'loc' => Yii::t('models', 'Loc'),
            'loc_notes' => Yii::t('models', 'Loc Notes'),
            'install_notes' => Yii::t('models', 'Install Notes'),
            'description' => Yii::t('models', 'Description'),
            'details' => Yii::t('models', 'Details'),
            'status' => Yii::t('models', 'Status'),
            'quantity' => Yii::t('models', 'Quantity'),
            'sponsor_id' => Yii::t('models', 'Sponsor ID'),
            'installer_id' => Yii::t('models', 'Installer ID'),
            'comments' => Yii::t('models', 'Comments'),
            'drawing_reference' => Yii::t('models', 'Drawing Reference'),
            'fixing_method' => Yii::t('models', 'Fixing Method'),
            'supplier_reference' => Yii::t('models', 'Supplier Reference'),
            'supplier_priced' => Yii::t('models', 'Supplier Priced'),
            'artwork_code' => Yii::t('models', 'Artwork Code'),
            'placement' => Yii::t('models', 'Placement'),
            'product_unit_price' => Yii::t('models', 'Product Unit Price'),
            'installer_standard_hours' => Yii::t('models', 'Installer Standard Hours'),
            'installer_specialist_hours' => Yii::t('models', 'Installer Specialist Hours'),
            'bump_out_hours' => Yii::t('models', 'Bump Out Hours'),
            'scissor_lift_hours' => Yii::t('models', 'Scissor Lift Hours'),
            'rt_scissor_lift_hours' => Yii::t('models', 'Rt Scissor Lift Hours'),
            'small_boom_hours' => Yii::t('models', 'Small Boom Hours'),
            'large_boom_hours' => Yii::t('models', 'Large Boom Hours'),
            'flt_hours' => Yii::t('models', 'Flt Hours'),
            'product_price' => Yii::t('models', 'Product Price'),
            'labour_price' => Yii::t('models', 'Labour Price'),
            'machine_price' => Yii::t('models', 'Machine Price'),
            'total_price' => Yii::t('models', 'Total Price'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'deleted_at' => Yii::t('models', 'Deleted At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVenue()
    {
        return $this->hasOne(\app\modules\goldoc\models\Venue::className(), ['id' => 'venue_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubstrate()
    {
        return $this->hasOne(\app\modules\goldoc\models\Substrate::className(), ['id' => 'substrate_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(\app\modules\goldoc\models\Type::className(), ['id' => 'type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInstaller()
    {
        return $this->hasOne(\app\modules\goldoc\models\Installer::className(), ['id' => 'installer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSport()
    {
        return $this->hasOne(\app\modules\goldoc\models\Sport::className(), ['id' => 'sport_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(\app\modules\goldoc\models\Supplier::className(), ['id' => 'supplier_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSponsor()
    {
        return $this->hasOne(\app\modules\goldoc\models\Sponsor::className(), ['id' => 'sponsor_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(\app\modules\goldoc\models\Item::className(), ['id' => 'item_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDesign()
    {
        return $this->hasOne(\app\modules\goldoc\models\Design::className(), ['id' => 'design_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getColour()
    {
        return $this->hasOne(\app\modules\goldoc\models\Colour::className(), ['id' => 'colour_id']);
    }

    
    /**
     * @inheritdoc
     * @return \app\modules\goldoc\models\query\ProductQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\modules\goldoc\models\query\ProductQuery(get_called_class());
    }

}
