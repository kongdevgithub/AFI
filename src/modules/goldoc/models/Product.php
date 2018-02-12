<?php

namespace app\modules\goldoc\models;

use app\components\behaviors\WorkflowBehavior;
use app\models\Attachment;
use app\models\Note;
use app\models\User;
use app\models\workflow\GoldocProductWorkflow;
use bedezign\yii2\audit\AuditTrailBehavior;
use bedezign\yii2\audit\models\AuditTrail;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "product".
 *
 * @mixin LinkBehavior
 * @mixin WorkflowBehavior
 * @mixin CacheBehavior
 *
 * @property float $installer_standard_hours
 * @property float $installer_specialist_hours
 * @property float $bump_out_hours
 * @property float $scissor_lift_hours
 * @property float $rt_scissor_lift_hours
 * @property float $small_boom_hours
 * @property float $large_boom_hours
 * @property float $flt_hours
 * @property float $product_price
 * @property float $labour_price
 *
 * @property string $code
 * @property string $name
 * @property string $sizeCode
 * @property string $sizeName
 *
 * @property User $goldocManager
 * @property User $activeManager
 * @property Attachment[] $attachment
 * @property Attachment $artwork
 * @property Note[] $goldocNotes
 * @property Note[] $activePrivateNotes
 * @property Note[] $productionNotes
 * @property Note[] $installationNotes
 */
class Product extends base\Product
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->isNewRecord) {
            $this->enterWorkflow();
        }
        $this->on('EVENT_BEFORE_CHANGE_STATUS', [GoldocProductWorkflow::className(), 'beforeChangeStatus']);
        $this->on('EVENT_AFTER_CHANGE_STATUS', [GoldocProductWorkflow::className(), 'afterChangeStatus']);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class' => LinkBehavior::className(),
            'moduleName' => 'goldoc',
        ];
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = [
            'class' => WorkflowBehavior::className(),
            'defaultWorkflowId' => 'goldoc-product',
        ];
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
            //'cacheRelations' => ['job'],
        ];
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }


    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $superFields = [];
        if (Yii::$app->user->can('goldoc')) {
            $superFields = [
                'active_manager_id',
                'supplier_id',
                'type_id',
                'item_id',
                'design_id',
                'colour_id',
                'substrate_id',
                'width',
                'height',
                'depth',
                'quantity',
                'sponsor_id',
                'installer_id',
                'details',
                'supplier_reference',
                'artwork_code',
                'drawing_reference',
                'fixing_method',
                'product_unit_price',
                'installer_standard_hours',
                'installer_specialist_hours',
                'bump_out_hours',
                'scissor_lift_hours',
                'rt_scissor_lift_hours',
                'small_boom_hours',
                'large_boom_hours',
                'flt_hours',
                'supplier_priced',
            ];
        }

        $scenarios['update-draft'] = ArrayHelper::merge($superFields, [
            'venue_id',
            'goldoc_manager_id',
            'active_manager_id',
            'supplier_id',
            'type_id',
            'item_id',
            'design_id',
            'colour_id',
            'substrate_id',
            'width',
            'height',
            'depth',
            'quantity',
            'sponsor_id',
            'installer_id',
            'details',
            'loc',
            'supplier_reference',
            'artwork_code',
            'drawing_reference',
            'fixing_method',
            'product_unit_price',
            'installer_standard_hours',
            'installer_specialist_hours',
            'bump_out_hours',
            'scissor_lift_hours',
            'rt_scissor_lift_hours',
            'small_boom_hours',
            'large_boom_hours',
            'flt_hours',
            'installer_id',
            'supplier_priced',
        ]);
        $scenarios['update-siteCheck'] = ArrayHelper::merge($superFields, [
            'supplier_id',
            'type_id',
            'item_id',
            'design_id',
            'colour_id',
            'substrate_id',
            'width',
            'height',
            'depth',
            'details',
            'product_unit_price',
            'installer_standard_hours',
            'installer_specialist_hours',
            'bump_out_hours',
            'scissor_lift_hours',
            'rt_scissor_lift_hours',
            'small_boom_hours',
            'large_boom_hours',
            'flt_hours',
            'installer_id',
            'supplier_priced',
        ]);
        $scenarios['update-quotePending'] = ArrayHelper::merge($superFields, [
            'supplier_id',
            'type_id',
            'item_id',
            'design_id',
            'colour_id',
            'substrate_id',
            'width',
            'height',
            'depth',
            'details',
            'product_unit_price',
            'installer_standard_hours',
            'installer_specialist_hours',
            'bump_out_hours',
            'scissor_lift_hours',
            'rt_scissor_lift_hours',
            'small_boom_hours',
            'large_boom_hours',
            'flt_hours',
            'installer_id',
            'supplier_priced',
        ]);
        $scenarios['update-budgetApproval'] = ArrayHelper::merge($superFields, [
            'type_id',
            'item_id',
            'design_id',
            'colour_id',
            'substrate_id',
            'width',
            'height',
            'depth',
            'details',
            'installer_id',
            'supplier_priced',
        ]);
        $scenarios['update-awaitingArtwork'] = [
            'type_id',
            'item_id',
            'design_id',
            'colour_id',
            'substrate_id',
            'width',
            'height',
            'depth',
            'details',
            'installer_id',
        ];
        $scenarios['update-artworkApproval'] = [
            'type_id',
            'item_id',
            'design_id',
            'colour_id',
            'substrate_id',
            'width',
            'height',
            'depth',
            'details',
            'installer_id',
        ];
        $scenarios['update-artworkUpload'] = [
            'type_id',
            'item_id',
            'design_id',
            'colour_id',
            'substrate_id',
            'width',
            'height',
            'depth',
            'details',
            'installer_id',
        ];
        $scenarios['update-productionPending'] = ArrayHelper::merge($superFields, [
            'type_id',
            'item_id',
            'design_id',
            'colour_id',
            'substrate_id',
            'width',
            'height',
            'depth',
            'details',
            'installer_id',
        ]);
        $scenarios['update-production'] = [
            'installer_id',
        ];
        $scenarios['update-warehouseMelbourne'] = [];
        $scenarios['update-warehouseGoldCoast'] = [];
        $scenarios['update-installation'] = [];
        $scenarios['update-complete'] = [];
        $scenarios['update-notProceeding'] = [
            'type_id',
            'item_id',
            'design_id',
            'colour_id',
            'substrate_id',
            'width',
            'height',
            'depth',
            'details',
        ];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['venue_id'] = Yii::t('goldoc', 'Venue');
        $attributeLabels['goldoc_manager_id'] = Yii::t('goldoc', 'GM');
        $attributeLabels['active_manager_id'] = Yii::t('goldoc', 'AM');
        $attributeLabels['supplier_id'] = Yii::t('goldoc', 'Supplier');
        $attributeLabels['installer_id'] = Yii::t('goldoc', 'Installer');
        $attributeLabels['sponsor_id'] = Yii::t('goldoc', 'Sponsor');
        $attributeLabels['rt_scissor_lift_hours'] = Yii::t('goldoc', 'RT Scissor Lift Hours');
        $attributeLabels['flt_hours'] = Yii::t('goldoc', 'FLT Hours');
        $attributeLabels['type_id'] = Yii::t('goldoc', 'Type');
        $attributeLabels['item_id'] = Yii::t('goldoc', 'Item');
        $attributeLabels['colour_id'] = Yii::t('goldoc', 'Colour');
        $attributeLabels['design_id'] = Yii::t('goldoc', 'Design');
        $attributeLabels['substrate_id'] = Yii::t('goldoc', 'Substrate');
        $attributeLabels['loc'] = Yii::t('goldoc', 'LOC');
        $attributeLabels['loc_notes'] = Yii::t('goldoc', 'LOC Notes');
        $attributeLabels['quantity'] = Yii::t('goldoc', 'Qty');
        return $attributeLabels;
    }

    /**
     * @return array
     */
    public function attributeHints()
    {
        $attributeHints = parent::attributeHints();
        $attributeHints['details'] = Yii::t('goldoc', 'finishing notes, construction notes, etc');
        return $attributeHints;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGoldocNotes()
    {
        $relation = Note::find()
            ->andWhere('note.deleted_at IS NULL')
            ->orOnCondition([
                'note.model_id' => $this->id,
                'note.model_name' => $this->className() . '-Goldoc',
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivePrivateNotes()
    {
        $relation = Note::find()
            ->andWhere('note.deleted_at IS NULL')
            ->orOnCondition([
                'note.model_id' => $this->id,
                'note.model_name' => $this->className() . '-ActivePrivate',
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductionNotes()
    {
        $relation = Note::find()
            ->andWhere('note.deleted_at IS NULL')
            ->orOnCondition([
                'note.model_id' => $this->id,
                'note.model_name' => $this->className() . '-Production',
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInstallationNotes()
    {
        $relation = Note::find()
            ->andWhere('note.deleted_at IS NULL')
            ->orOnCondition([
                'note.model_id' => $this->id,
                'note.model_name' => $this->className() . '-Installation',
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachments()
    {
        $relation = Attachment::find()
            ->andWhere('attachment.deleted_at IS NULL')
            ->orOnCondition([
                'attachment.model_id' => $this->id,
                'attachment.model_name' => $this->className(),
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArtwork()
    {
        $relation = Attachment::find()
            ->andWhere('attachment.deleted_at IS NULL')
            ->orOnCondition([
                'attachment.model_id' => $this->id,
                'attachment.model_name' => $this->className() . '-Artwork',
            ]);
        $relation->multiple = false;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGoldocManager()
    {
        return $this->hasOne(User::className(), ['id' => 'goldoc_manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActiveManager()
    {
        return $this->hasOne(User::className(), ['id' => 'active_manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuditTrails()
    {
        $relation = AuditTrail::find();
        $relation->orOnCondition([
            'audit_trail.model_id' => $this->id,
            'audit_trail.model' => $this->className(),
        ]);
        $relation->from([new Expression('{{%audit_trail}} USE INDEX (idx_audit_trail_field)')]);
        $relation->orderBy(['created' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     * @return int
     */
    public function generateProductPrice()
    {
        return $this->product_unit_price * $this->quantity;
    }

    /**
     * @return float
     */
    public function generateLabourPrice()
    {
        $price = ($this->installer_standard_hours * 65) + ($this->installer_specialist_hours * 100) + ($this->bump_out_hours * 100);
        return $price * $this->quantity;
    }

    /**
     * @return float
     */
    public function generateMachinePrice()
    {
        $price = 0;
        if ($this->scissor_lift_hours)
            $price += ($this->scissor_lift_hours / 8) * 145; // + 240;
        if ($this->rt_scissor_lift_hours)
            $price += ($this->rt_scissor_lift_hours / 8) * 250; // + 310;
        if ($this->small_boom_hours)
            $price += ($this->small_boom_hours / 8) * 195; // + 310;
        if ($this->large_boom_hours)
            $price += ($this->large_boom_hours / 8) * 500; // + 500;
        if ($this->flt_hours)
            $price += ($this->flt_hours / 8) * 140; // + 160;
        return $price * $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->product_price = $this->generateProductPrice();
        $this->labour_price = $this->generateLabourPrice();
        $this->machine_price = $this->generateMachinePrice();
        $this->total_price = $this->product_price + $this->labour_price + $this->machine_price;

        // sanitize data
        $this->product_price = round($this->product_price, 4);
        $this->labour_price = round($this->labour_price, 4);
        $this->machine_price = round($this->machine_price, 8);
        $this->total_price = round($this->total_price, 8);

        // reset supplier_priced on field changes
        $attributes = ['item_id', 'type_id', 'substrate_id', 'height', 'width', 'depth', 'details'];
        foreach ($attributes as $attribute) {
            if ($this->getAttribute($attribute) && $this->isAttributeChanged($attribute, false)) {
                $this->supplier_priced = null;
                break;
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        $code = [];
        $code[] = $this->item ? $this->item->code : 'X';
        $code[] = $this->colour ? $this->colour->code : 'X';
        $code[] = $this->design ? $this->design->code : 'X';
        $code[] = $this->substrate ? $this->substrate->code : 'X';
        return implode('-', $code);
    }

    /**
     * @return string
     */
    public function getName()
    {
        $code = [];
        $code[] = $this->item ? ($this->item->name ?: '!') : 'X';
        $code[] = $this->colour ? $this->colour->name ?: '!' : 'X';
        $code[] = $this->design ? $this->design->name ?: '!' : 'X';
        $code[] = $this->substrate ? $this->substrate->name ?: '!' : 'X';
        return implode(' - ', $code);
    }

    /**
     * @return string
     */
    public function getSizeName()
    {
        if ($this->width) {
            if ($this->height) {
                if ($this->depth) {
                    return $this->width . 'W ' . $this->height . 'H ' . $this->depth . 'D';
                }
                return $this->width . 'W ' . $this->height . 'H';
            }
            return $this->width . 'W';
        }
        return '';
    }

    /**
     * @return string
     */
    public function getSizeCode()
    {
        if ($this->width) {
            if ($this->height) {
                if ($this->depth) {
                    return $this->width . 'x' . $this->height . 'x' . $this->depth;
                }
                return $this->width . 'x' . $this->height;
            }
            return $this->width;
        }
        return '';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->id . ': ' . $this->getName() . ' - ' . $this->getSizeName();
    }

    /**
     * @param bool $showInactiveMain
     * @return string
     */
    public function getAfiStatusButtons($showInactiveMain = false)
    {
        if ($this->supplier_id == 1) {
            $afiProduct = \app\models\Product::findOne($this->supplier_reference);
            if ($afiProduct) {
                return $afiProduct->getStatusButtons($showInactiveMain);
            }
        }
        return '';
    }
}
