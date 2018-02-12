<?php

namespace app\models;

use app\components\behaviors\WorkflowBehavior;
use app\components\fields\BaseField;
use app\components\Helper;
use app\components\quotes\products\BaseProductQuote;
use app\components\quotes\products\OctanormProductQuote;
use app\components\quotes\products\RateProductQuote;
use app\models\workflow\ProductWorkflow;
use bedezign\yii2\audit\AuditTrailBehavior;
use bedezign\yii2\audit\models\AuditTrail;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\linkbehavior\LinkBehavior;
use app\components\ReturnUrl;
use cornernote\softdelete\SoftDeleteBehavior;
use Html2Text\Html2Text;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "product".
 *
 * @mixin LinkBehavior
 * @mixin WorkflowBehavior
 * @mixin CacheBehavior
 *
 * @property float $quantity
 * @property float $quote_unit_price
 * @property float $quote_quantity
 * @property float $quote_total_price
 * @property float $quote_total_price_unlocked
 * @property float $quote_unit_cost
 * @property float $quote_total_cost
 * @property float $quote_factor
 * @property float $quote_factor_price
 * @property float $quote_discount_price
 * @property float $quote_weight
 * @property string $description
 * @property float $quote_retail_unit_price_import
 *
 * @property Product[] $forkQuantityProducts
 * @property Note[] $notes
 * @property Link[] $links
 * @property Notification[] $notifications
 * @property Attachment[] $attachments
 * @property Correction[] $corrections
 */
class Product extends base\Product
{

    /**
     * @var bool
     */
    public $send_email;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->isNewRecord) {
            $this->enterWorkflow();
        }
        $this->on('EVENT_BEFORE_CHANGE_STATUS', [ProductWorkflow::className(), 'beforeChangeStatus']);
        $this->on('EVENT_AFTER_CHANGE_STATUS', [ProductWorkflow::className(), 'afterChangeStatus']);
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
            'class' => WorkflowBehavior::className(),
            'defaultWorkflowId' => 'product',
            'propagateErrorsToModel' => true,
        ];
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
            'cacheRelations' => ['job'],
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
        $scenarios = [
            'default' => ['id', 'name', 'details', 'job_id', 'fork_quantity_product_id', 'product_type_id', 'sort_order', 'quote_class', 'quantity', 'status', 'quote_label', 'quote_unit_price', 'quote_quantity', 'quote_total_price', 'quote_total_price_unlocked', 'quote_unit_cost', 'quote_total_cost', 'quote_factor', 'quote_factor_price', 'quote_discount_price', 'quote_weight', 'quote_generated', 'deleted_at', 'production_at', 'despatch_at', 'complete_at', 'complexity', 'quote_hide_item_description', 'due_date', 'prebuild_required', 'preserve_unit_prices', 'packed_at', 'prevent_rate_prices', 'bulk_component', 'created_at', 'updated_at', 'quote_retail_unit_price_import'],
            'create' => ['id', 'name', 'details', 'job_id', 'fork_quantity_product_id', 'product_type_id', 'sort_order', 'quote_class', 'quantity', 'status', 'quote_label', 'quote_unit_price', 'quote_quantity', 'quote_total_price', 'quote_total_price_unlocked', 'quote_unit_cost', 'quote_total_cost', 'quote_factor', 'quote_factor_price', 'quote_discount_price', 'quote_weight', 'quote_generated', 'deleted_at', 'production_at', 'despatch_at', 'complete_at', 'complexity', 'quote_hide_item_description', 'due_date', 'prebuild_required', 'preserve_unit_prices', 'packed_at', 'prevent_rate_prices', 'bulk_component', 'created_at', 'updated_at', 'quote_retail_unit_price_import'],
            'update' => ['id', 'name', 'details', 'job_id', 'fork_quantity_product_id', 'product_type_id', 'sort_order', 'quote_class', 'quantity', 'status', 'quote_label', 'quote_unit_price', 'quote_quantity', 'quote_total_price', 'quote_total_price_unlocked', 'quote_unit_cost', 'quote_total_cost', 'quote_factor', 'quote_factor_price', 'quote_discount_price', 'quote_weight', 'quote_generated', 'deleted_at', 'production_at', 'despatch_at', 'complete_at', 'complexity', 'quote_hide_item_description', 'due_date', 'prebuild_required', 'preserve_unit_prices', 'packed_at', 'prevent_rate_prices', 'bulk_component', 'created_at', 'updated_at', 'quote_retail_unit_price_import'],
        ];
        $scenarios['copy'] = $scenarios['create'];
        $scenarios['status'] = ['status', 'send_email'];
        $scenarios['quantity'] = ['quantity', 'preserve_unit_prices'];
        $scenarios['discount'] = ['quote_discount_price'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['send_email'], 'safe'];
        $rules[] = [['quote_discount_price'], 'number', 'min' => 0];
        $rules[] = [['quantity'], 'number', 'min' => 1];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['job_id'] = Yii::t('app', 'Job');
        $attributeLabels['product_type_id'] = Yii::t('app', 'Product Type');
        $attributeLabels['quote_hide_item_description'] = Yii::t('app', 'Hide Item Description on Quote');
        return $attributeLabels;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // quantity changed
        if (!$insert && $this->isAttributeChanged('quantity')) {
            $oldQuantity = $this->getOldAttribute('quantity');
            // update discount
            $this->quote_discount_price = $this->quote_discount_price / $oldQuantity * $this->quantity;
            // update unit quantity
            foreach ($this->items as $item) {
                $difference = ($this->quantity - $oldQuantity) * $item->quantity;
                $item->changeUnitQuantity($difference);
            }
        }

        // set the dates
        if ($insert || $this->isAttributeChanged('status')) {
            $date = time();
            if ($this->status == 'product/draft') {
                $this->production_at = null;
                $this->despatch_at = null;
                $this->packed_at = null;
                $this->complete_at = null;
            }
            if ($this->status == 'product/production') {
                if (!$this->production_at)
                    $this->production_at = $date;
            }
            if ($this->status == 'product/despatch') {
                if (!$this->production_at)
                    $this->production_at = $date;
                if (!$this->despatch_at)
                    $this->despatch_at = $date;
            }
            if ($this->status == 'product/packed') {
                if (!$this->production_at)
                    $this->production_at = $date;
                if (!$this->despatch_at)
                    $this->despatch_at = $date;
                if (!$this->packed_at)
                    $this->packed_at = $date;
            }
            if ($this->status == 'product/complete') {
                if (!$this->production_at)
                    $this->production_at = $date;
                if (!$this->despatch_at)
                    $this->despatch_at = $date;
                if (!$this->packed_at)
                    $this->packed_at = $date;
                if (!$this->complete_at)
                    $this->complete_at = $date;
            }
        }

        // preserve unit prices
        if ($this->isAttributeChanged('status') && $this->status == 'product/production') {
            $this->preserve_unit_prices = 1;
        }

        // sanitize data
        $this->quote_unit_price = round($this->quote_unit_price, 4);
        $this->quote_total_price = round($this->quote_total_price, 4);
        $this->quote_factor_price = round($this->quote_factor_price, 8);

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        foreach ($this->items as $item) {
            $item->delete();
        }
        foreach ($this->productToComponents as $productToComponent) {
            if (!$productToComponent->item_id) {
                $productToComponent->delete();
            }
        }
        foreach ($this->productToOptions as $productToOption) {
            if (!$productToOption->item_id) {
                $productToOption->delete();
            }
        }
        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->deleted_at && !$this->job->deleted_at) {
            $this->job->resetQuoteGenerated(false);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotes()
    {
        $relation = Note::find()
            ->andWhere('note.deleted_at IS NULL')
            ->orOnCondition([
                'note.model_id' => $this->id,
                'note.model_name' => $this->className(),
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLinks()
    {
        $relation = Link::find()
            ->andWhere('link.deleted_at IS NULL')
            ->orOnCondition([
                'link.model_id' => $this->id,
                'link.model_name' => $this->className(),
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotifications()
    {
        $relation = Notification::find()
            ->andWhere('notification.deleted_at IS NULL')
            ->orOnCondition([
                'notification.model_id' => $this->id,
                'notification.model_name' => $this->className(),
            ])->orderBy(['created_at' => SORT_ASC]);
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
     * @return query\LogQuery
     */
    public function getLogs()
    {
        $relation = Log::find();
        $relation->orOnCondition([
            'log.model_id' => $this->id,
            'log.model_name' => $this->className(),
        ]);
        $relation->orOnCondition([
            'log.model_id' => ArrayHelper::map($this->getItems()->where('1=1')->all(), 'id', 'id'),
            'log.model_name' => Item::className(),
        ]);
        foreach ($this->items as $item) {
            $relation->orOnCondition([
                'log.model_id' => ArrayHelper::map($item->getUnits()->where('1=1')->all(), 'id', 'id'),
                'log.model_name' => Unit::className(),
            ]);
        }
        $relation->orderBy(['created_at' => SORT_DESC, 'audit_entry_id' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorrections()
    {
        $relation = Correction::find()
            ->orOnCondition([
                'correction.model_id' => $this->id,
                'correction.model_name' => $this->className(),
            ]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }


    /**
     * @param array $relations
     * @return \yii\db\ActiveQuery
     */
    public function getAuditTrails($relations = [])
    {
        $relation = AuditTrail::find();
        $relation->orOnCondition([
            'audit_trail.model_id' => $this->id,
            'audit_trail.model' => $this->className(),
        ]);
        if (in_array(Item::className(), $relations)) {
            /** @var Item[] $items */
            $items = $this->getItems()->where('1=1')->all();
            $relation->orOnCondition([
                'audit_trail.model_id' => ArrayHelper::map($items, 'id', 'id'),
                'audit_trail.model' => Item::className(),
            ]);
            if (in_array(Unit::className(), $relations)) {
                foreach ($items as $item) {
                    /** @var Unit[] $units */
                    $units = $item->getUnits()->where('1=1')->all();
                    $relation->orOnCondition([
                        'audit_trail.model_id' => ArrayHelper::map($units, 'id', 'id'),
                        'audit_trail.model' => Unit::className(),
                    ]);
                }
            }
        }
        if (in_array(ProductToOption::className(), $relations)) {
            /** @var ProductToOption[] $productToOptions */
            $productToOptions = $this->getProductToOptions()->where('1=1')->all();
            $relation->orOnCondition([
                'audit_trail.model_id' => ArrayHelper::map($productToOptions, 'id', 'id'),
                'audit_trail.model' => ProductToOption::className(),
            ]);
        }
        if (in_array(ProductToComponent::className(), $relations)) {
            /** @var ProductToComponent[] $productToComponents */
            $productToComponents = $this->getProductToComponents()->where('1=1')->all();
            $relation->orOnCondition([
                'audit_trail.model_id' => ArrayHelper::map($productToComponents, 'id', 'id'),
                'audit_trail.model' => ProductToComponent::className(),
            ]);
        }
        $relation->from([new Expression('{{%audit_trail}} USE INDEX (idx_audit_trail_field)')]);
        $relation->orderBy(['created' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Item::className(), ['product_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->orderBy(['sort_order' => SORT_ASC])
            ->inverseOf('product');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToComponents()
    {
        return $this->hasMany(ProductToComponent::className(), ['product_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('product');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToOptions()
    {
        return $this->hasMany(ProductToOption::className(), ['product_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('product');
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForkQuantityProducts()
    {
        return $this->hasMany(Product::className(), ['fork_quantity_product_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('forkQuantityProduct');
    }

    /**
     * @param array $options
     * @return string
     */
    public function getDescription($options = [])
    {
        $cacheKey = 'getDescription.' . md5(serialize($options));
        $description = $this->getCache($cacheKey);
        if ($description) {
            return $description;
        }

        $description = [];
        $showName = ArrayHelper::remove($options, 'showName', true);
        if ($showName) {
            $description[] = Html::tag('strong', $this->name);
        }
        $showMaterials = ArrayHelper::remove($options, 'showMaterials', true);
        if ($showMaterials) {
            foreach ($this->productToOptions as $productToOption) {
                if ($productToOption->item_id) {
                    continue;
                }
                if (!$productToOption->productTypeToOption || $productToOption->productTypeToOption->describes_item) {
                    /** @var BaseField $field */
                    $field = new $productToOption->option->field_class;
                    $value = $field->nameProduct($productToOption);
                    if ($value) {
                        $description[] = $value;
                    }
                }
            }
            foreach ($this->productToComponents as $productToComponent) {
                if ($productToComponent->item_id) {
                    continue;
                }
                if (!$productToComponent->productTypeToComponent || $productToComponent->productTypeToComponent->describes_item) {
                    $description[] = $productToComponent->component->name;
                }
            }
        }

        $details = '';
        $showDetails = ArrayHelper::remove($options, 'showDetails', true);
        if ($showDetails && $this->details) {
            $details = ($description ? '<br>' : '') . Yii::$app->formatter->asNtext($this->details);
        }

        $itemDescriptions = '';
        $showItems = ArrayHelper::remove($options, 'showItems', true);
        if ($showItems) {
            $itemDescriptionOptions = ArrayHelper::remove($options, 'itemDescriptionOptions', []);
            $itemDescriptions = Html::ul($this->getItemDescriptions($itemDescriptionOptions), ['encode' => false]);
        }
        return $this->setCache($cacheKey, implode(' - ', $description) . $details . $itemDescriptions);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getItemDescriptions($options = [])
    {
        $items = [];
        foreach ($this->items as $item) {
            if ($item->quantity) {
                $quantity = $item->quantity == 1 ? '' : ' x' . ($this->quantity * $item->quantity);
                $size = '';
                if ($item->checkShowSize()) {
                    $size = ' - ' . $item->getSizeHtml();
                }
                $items[] = Html::encode($item->name) . $size . $quantity . $item->getDescription($options);
            }
        }
        return $items;
    }

    /**
     * @return array
     */
    public function getShippingAddressQuantities()
    {
        $shippingAddresses = [];
        $total = 0;
        foreach ($this->productToAddresses as $productToAddress) {
            if (!$productToAddress->quantity || $productToAddress->address->deleted_at) continue;
            $shippingAddresses[] = 'x' . $productToAddress->quantity . ' - ' . $productToAddress->address->getLabel();
            $total += $productToAddress->quantity;
        }
        return $shippingAddresses;
    }

    /**
     * @return bool
     */
    public function checkShippingAddressQuantities()
    {
        $total = 0;
        foreach ($this->productToAddresses as $productToAddress) {
            if (!$productToAddress->quantity || $productToAddress->address->deleted_at) continue;
            $total += $productToAddress->quantity;
        }
        return $total == $this->quantity;
    }

    /**
     * @param bool $showInactiveMain
     * @return string
     */
    public function getStatusButtons($showInactiveMain = false)
    {
        if (in_array($this->status, ['product/production', 'product/despatch', 'product/packed'])) {
            $button = '';
            if ($showInactiveMain) {
                $button = $this->getStatusButton(['quantity' => false]) . '&nbsp;';
            }
            return $button . Helper::getStatusButtonGroup($this->getStatusList());
        }
        return $this->getStatusButton();
    }

    /**
     * @return array
     */
    public function getStatusList()
    {
        $cacheKey = 'getStatusList';
        $statusList = $this->getCache($cacheKey, true);
        if ($statusList) return $statusList;

        $statusList = [];
        if (in_array($this->status, ['product/production', 'product/despatch', 'product/packed'])) {
            foreach ($this->items as $item) {
                if ($item->quantity > 0) {
                    foreach ($item->getStatusList() as $status => $quantity) {
                        $statusList[$status] = isset($statusList[$status]) ? $statusList[$status] + $quantity : $quantity;
                    }
                }
            }
        } else {
            $status = $this->status;
            $quantity = $this->quantity;
            $statusList[$status] = isset($statusList[$status]) ? $statusList[$status] + $quantity : $quantity;
        }
        //JobSortHelper::sortStatus($statusList);
        return $this->setCache($cacheKey, $statusList, true);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Html::a('product-' . $this->id, ['/product/view', 'id' => $this->id, 'ru' => ReturnUrl::getToken()], ['class' => 'label label-default']);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return implode(' | ', [
            '#' . $this->job->vid . '.p' . $this->id . ': ' . $this->name,
            $this->job->name,
            $this->job->company->name
        ]);
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);
        if ((!$skipIfSet || $this->quote_class === null)) {
            $this->quote_class = BaseProductQuote::className();
        }
        if ((!$skipIfSet || $this->quantity === null)) {
            $this->quantity = 1;
        }
        return $this;
    }

    /**
     * @return array|bool
     */
    public function getSize()
    {
        $productToOption = $this->getProductToOption(Option::OPTION_SIZE);
        if (!$productToOption) {
            return false;
        }

        // get size
        $_size = $productToOption->getValueDecoded();
        if (isset($_size['value'])) {
            $size = Size::findOne($_size['value']);
            if ($size) {
                if ($size->width) {
                    $_size['width'] = $size->width;
                }
                if ($size->height) {
                    $_size['height'] = $size->height;
                }
                if ($size->depth) {
                    $_size['depth'] = $size->depth;
                }
            }
            unset($_size['value']);
        }

        // curve length
        $optionCurve = $this->getProductToOption(Option::OPTION_CURVE);
        if ($optionCurve) {
            $valueDecoded = $optionCurve->valueDecoded;
            if (!empty($valueDecoded['type']) && $valueDecoded['type'] == 'cylinder' && !empty($valueDecoded['degrees']) && $valueDecoded['degrees'] > 0) {
                $dim = $valueDecoded['direction'] == 'hug' ? 'width' : 'height';
                $_size[$dim] = $valueDecoded['length'];
            }
        }

        return $_size;
    }

    /**
     * @return string
     */
    public function getSizeHtml()
    {
        $productToOption = $this->getProductToOption(Option::OPTION_SIZE);
        if (!$productToOption) {
            return '';
        }
        return Helper::getSizeHtml($this->getSize());
    }

    /**
     * @return float|int
     */
    public function getArea()
    {
        $cacheKey = 'getArea';
        $area = $this->getCache($cacheKey);
        if ($area !== false) return $area;

        $area = 0;
        foreach ($this->items as $item) {
            $area += $item->getArea();
        }
        return $this->setCache($cacheKey, $area);
    }

    /**
     * @return float|int
     */
    public function getPerimeter()
    {
        $cacheKey = 'getPerimeter';
        $perimeter = $this->getCache($cacheKey);
        if ($perimeter !== false) return $perimeter;

        $perimeter = 0;
        foreach ($this->items as $item) {
            $perimeter += $item->getPerimeter();
        }
        return $this->setCache($cacheKey, $perimeter);
    }

    /**
     * @param int $option_id
     * @return ProductToOption|bool
     */
    public function getProductToOption($option_id)
    {
        $cacheKey = 'getProductToOption.' . $option_id;
        $productToOptionId = $this->getCache($cacheKey);
        if ($productToOptionId !== false) {
            return $productToOptionId ? ProductToOption::findOne($productToOptionId) : false;
        }
        foreach ($this->productToOptions as $productToOption) {
            if (!$productToOption->item_id && $productToOption->option_id == $option_id) {
                $this->setCache($cacheKey, $productToOption->id);
                return $productToOption;
            }
        }
        $this->setCache($cacheKey, null);
        return false;
    }

    /**
     * @param int $component_id
     * @return ProductToComponent|bool
     */
    public function getProductToComponent($component_id)
    {
        $cacheKey = 'getProductToComponent.' . $component_id;
        $productToComponentId = $this->getCache($cacheKey);
        if ($productToComponentId !== false) {
            return $productToComponentId ? ProductToComponent::findOne($productToComponentId) : false;
        }
        foreach ($this->productToComponents as $productToComponent) {
            if (!$productToComponent->item_id && $productToComponent->component_id == $component_id) {
                $this->setCache($cacheKey, $productToComponent->id);
                return $productToComponent;
            }
        }
        $this->setCache($cacheKey, null);
        return false;
    }

    /**
     * @return array
     */
    public static function complexityOpts()
    {
        return ProductType::complexityOpts();
    }


    /**
     *
     */
    public function resetQuoteGenerated()
    {
        $this->quote_generated = 0;
        if (!$this->save(false)) {
            throw new Exception('Cannot save product-' . $this->id . ': ' . Helper::getErrorString($this));
        }
        foreach ($this->forkQuantityProducts as $_product) {
            $_product->resetQuoteGenerated();
        }
        foreach ($this->items as $item) {
            $item->resetQuoteGenerated();
        }
        foreach ($this->productToComponents as $productToComponent) {
            $productToComponent->resetQuoteGenerated();
        }
        foreach ($this->productToOptions as $productToOption) {
            $productToOption->resetQuoteGenerated();
        }
    }

    /**
     * @param array $attributes
     * @param array $itemAttributes
     * @param array $options
     *
     * @return Product|bool
     * @throws Exception
     */
    public function copy($attributes = [], $itemAttributes = [], $options = [])
    {
        $optionsDefault = [
            'copy_attachments' => false,
            'copy_notes' => true,
        ];
        $options = array_merge($optionsDefault, $options);
        $product = new Product();
        $product->loadDefaultValues();
        $product->attributes = $this->attributes;
        $product->id = null;
        if (isset($attributes['Product']['status'])) {
            $product->status = $attributes['Product']['status'];
            $product->initStatus();
        } else {
            $product->status = 'product/draft';
        }
        $allowedAttributes = [
            'job_id',
            'quantity',
            'preserve_unit_prices',
        ];
        if (!empty($attributes['Product'])) {
            foreach ($allowedAttributes as $attribute) {
                if (array_key_exists($attribute, $attributes['Product'])) {
                    $product->$attribute = $attributes['Product'][$attribute];
                }
            }
        }
        if (!$product->save()) {
            throw new Exception('cannot copy Product-' . $this->id . ': ' . Helper::getErrorString($product));
        }
        foreach ($this->forkQuantityProducts as $_forkQuantityProduct) {
            $_product = $_forkQuantityProduct->copy($attributes);
            if ($_product) {
                $_product->fork_quantity_product_id = $product->id;
                $_product->save(false);
            }
        }
        foreach ($this->items as $_item) {
            $_itemAttributes = [
                'Item' => [
                    'product_id' => $product->id,
                ]
            ];
            if (isset($itemAttributes[$_item->id]['quantity'])) {
                $_itemAttributes['Item']['quantity'] = $itemAttributes[$_item->id]['quantity'];
            }
            $item = $_item->copy($_itemAttributes);
        }
        foreach ($this->productToComponents as $_productToComponent) {
            if ($_productToComponent->item_id) {
                continue;
            }
            $productToComponent = $_productToComponent->copy([
                'ProductToComponent' => [
                    'product_id' => $product->id,
                ],
            ]);
        }
        foreach ($this->productToOptions as $_productToOption) {
            if ($_productToOption->item_id) {
                continue;
            }
            $productToOption = $_productToOption->copy([
                'ProductToOption' => [
                    'product_id' => $product->id,
                ],
            ]);
        }
        $notes = $options['copy_notes'] ? $this->notes : [];
        foreach ($notes as $_note) {
            $note = $_note->copy([
                'Note' => [
                    'model_name' => $this->className(),
                    'model_id' => $product->id,
                ]
            ]);
        }
        $attachments = $options['copy_attachments'] ? $this->attachments : [];
        foreach ($attachments as $_attachment) {
            $_attachment->copy([
                'Attachment' => [
                    'model_name' => $this->className(),
                    'model_id' => $this->id,
                ],
            ]);
        }
        return $product;
    }

    /**
     * @return bool|array
     */
    public function getRate()
    {
        if (!$this->productType) {
            return false;
        }
        if ($this->prevent_rate_prices) {
            return false;
        }

        $companyRate = $this->getCompanyRate();
        if ($companyRate) {
            return $companyRate;
        }

        //$companyRateLegacy = $this->getCompanyRateLegacy();
        //if ($companyRateLegacy) {
        //    return $companyRateLegacy;
        //}

        return false;
    }

    /**
     * @return array|bool
     */
    public function getCompanyRate()
    {
        foreach ($this->job->company->companyRates as $companyRate) {
            if (!$this->productType->hasParent($companyRate->productType->id)) {
                continue;
            }
            $hasRate = false;
            $fixed = false;
            $price = 0;
            $area = 0;
            $quantity = 0;
            $component_id = false;
            foreach ($this->items as $item) {
                if (!$item->quantity) {
                    continue;
                }
                if ($item->item_type_id != $companyRate->item_type_id) {
                    $hasRate = false;
                    break;
                }
                foreach ($companyRate->companyRateOptions as $companyRateOption) {
                    $requiredOption = $item->getProductToOption($companyRateOption->option_id);
                    if (!$requiredOption) {
                        //debug('product-' . $this->id . ' missing required option');
                        $hasRate = false;
                        break(2);
                    }
                    $requiredComponent = Component::findOne($requiredOption->valueDecoded);
                    if (!$requiredComponent || $requiredComponent->id != $companyRateOption->component_id) {
                        //debug('product-' . $this->id . ' wrong required component');
                        $hasRate = false;
                        break(2);
                    }
                }
                $option = $item->getProductToOption($companyRate->option_id);
                if (!$option) {
                    //debug('product-' . $this->id . ' missing option');
                    $hasRate = false;
                    break;
                }
                $component = Component::findOne($option->valueDecoded);
                if (!$component || $component->id != $companyRate->component_id) {
                    //debug('product-' . $this->id . ' wrong component');
                    $hasRate = false;
                    break;
                }
                if (!$component_id) {
                    $component_id = $component->id;
                } elseif ($component->id != $companyRate->component_id) {
                    //debug('product-' . $this->id . ' mismatch component');
                    $hasRate = false;
                    break;
                }

                if ($companyRate->size) {
                    // fixed size prices
                    $size = $item->getSize();
                    if (!$size || empty($size['width']) || empty($size['height'])) {
                        continue;
                    }
                    $_price = false;
                    if ($companyRate->size == $size['width'] . 'x' . $size['height']) {
                        $_price = $companyRate->price;
                        $fixed = $size['width'] . 'x' . $size['height'];

                    } elseif ($companyRate->size == $size['height'] . 'x' . $size['width']) {
                        $_price = $companyRate->price;
                        $fixed = $size['height'] . 'x' . $size['width'];
                    }
                    if (!$_price) {
                        continue;
                    }
                    $price += $_price;
                    $area = 1;
                    $quantity += $this->quantity * $item->quantity;
                } else {
                    // per area prices
                    $area += $item->getArea();
                    $price = $companyRate->price;
                    $quantity = 1;
                }

                $hasRate = true;
            }
            if ($hasRate) {
                return [
                    'price' => round($price / $quantity, 4),
                    'area' => $area,
                    'unit' => 'm2',
                    'quantity' => $quantity,
                    'fixed' => $fixed,
                ];
            }
        }
        return false;
    }

    /**
     * @return array|bool
     */
    public function getCompanyRateLegacy()
    {
        foreach ($this->job->company->getRates() as $rate) {
            if (!$this->productType->hasParent($rate['product_type_id'])) {
                continue;
            }
            $hasRate = false;
            $fixed = false;
            $price = 0;
            $area = 0;
            $quantity = 0;
            $component_id = false;
            foreach ($this->items as $item) {
                if (!$item->quantity) {
                    continue;
                }
                if ($item->item_type_id != $rate['item_type_id']) {
                    $hasRate = false;
                    break;
                }
                if (!empty($rate['required_options'])) {
                    foreach ($rate['required_options'] as $required_option_id => $required_component_id) {
                        $requiredOption = $item->getProductToOption($required_option_id);
                        if (!$requiredOption) {
                            //debug('product-' . $this->id . ' missing required option');
                            $hasRate = false;
                            break(2);
                        }
                        $requiredComponent = Component::findOne($requiredOption->valueDecoded);
                        if (!$requiredComponent || $requiredComponent->id != $required_component_id) {
                            //debug('product-' . $this->id . ' wrong required component');
                            $hasRate = false;
                            break(2);
                        }
                    }
                }
                $option = $item->getProductToOption($rate['option_id']);
                if (!$option) {
                    //debug('product-' . $this->id . ' missing option');
                    $hasRate = false;
                    break;
                }
                $component = Component::findOne($option->valueDecoded);
                if (!$component || !in_array($component->id, array_keys($rate['prices']))) {
                    //debug('product-' . $this->id . ' wrong component');
                    $hasRate = false;
                    break;
                }
                if (!$component_id) {
                    $component_id = $component->id;
                } elseif ($component->id != $component->id) {
                    //debug('product-' . $this->id . ' mismatch component');
                    $hasRate = false;
                    break;
                }

                $size = $item->getSize();
                if (!$size || empty($size['width']) || empty($size['height'])) {
                    continue;
                }


                $_prices = $rate['prices'][$component->id];
                if (is_array($_prices)) {
                    // fixed size prices
                    $_price = false;
                    if (isset($_prices[$size['width'] . 'x' . $size['height']])) {
                        $_price = $_prices[$size['width'] . 'x' . $size['height']];
                        $fixed = $size['width'] . 'x' . $size['height'];
                    } elseif (isset($_prices[$size['height'] . 'x' . $size['width']])) {
                        $_price = $_prices[$size['height'] . 'x' . $size['width']];
                        $fixed = $size['height'] . 'x' . $size['width'];
                    } elseif (isset($_prices['*'])) {
                        $_price = $_prices['*'] / ($item->quantity * $this->quantity);
                        $fixed = '*';
                    }
                    if (!$_price) {
                        continue;
                    }
                    $price += $_price;
                    $area = 1;
                    $quantity += $this->quantity * $item->quantity;
                } else {
                    // per area prices
                    $area += $item->getArea();
                    $price = $rate['prices'][$component->id];
                    $quantity = 1;
                }

                $hasRate = true;
            }
            if ($hasRate) {
                return [
                    'price' => round($price / $quantity, 4),
                    'area' => $area,
                    'unit' => 'm2',
                    'quantity' => $quantity,
                    'fixed' => $fixed,
                ];
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getRateLabel()
    {
        $rate = $this->getRate();
        $rateLabel = '';
        if ($rate) {
            $price = $rate['price'] * $rate['area'] * $rate['quantity'];
            $labelType = 'default';
            if (round($price, 2) != round($this->quote_factor_price * $this->job->quote_markup, 2)) {
                $labelType = 'danger';
            }
            $quantity = $rate['quantity'] == 1 ? '' : ' x ' . $rate['quantity'];
            if ($rate['fixed']) {
                $labelText = Yii::t('app', 'Available Rate') . ': ' . $rate['fixed'] . ' $' . number_format($rate['price'], 2) . $quantity . ' = $' . number_format($price, 2);
            } else {
                $labelText = Yii::t('app', 'Available Rate') . ': $' . number_format($rate['price'], 2) . ' x ' . $rate['area'] . $rate['unit'] . $quantity . ' = $' . number_format($price, 2);
            }
            $rateLabel = '<span class="label label-' . $labelType . '">' . $labelText . '</span>';
        }
        return $rateLabel;
    }

    /**
     * @return bool
     */
    public function checkPriceMargin()
    {
        $threshold = 0.7;
        if ($this->quote_class == RateProductQuote::className()) {
            return true;
        }
        if ($this->quote_class == OctanormProductQuote::className()) {
            return true;
        }
        if ($this->quote_factor >= 1 && $this->quote_total_price == $this->quote_total_price_unlocked) {
            return true;
        }
        //if ($this->quote_total_cost <= 0) {
        //    return true;
        //}
        $price = ($this->quote_factor_price - $this->quote_discount_price) * $this->job->quote_markup;
        $limit = $this->quote_total_cost / $threshold;
        if ($price <= $limit) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getIcons()
    {
        $cacheKey = 'getIcons';
        $icons = $this->getCache($cacheKey);
        if ($icons !== false) return $icons;

        $icons = [];
        return $this->setCache($cacheKey, implode(' ', $icons));
    }

    /**
     * @return bool
     */
    public function checkUnitCount()
    {
        foreach ($this->items as $item) {
            if (!$item->checkUnitCount()) {
                return false;
            }
        }
        return true;
    }


    /**
     *
     */
    public function fixUnitCount()
    {
        foreach ($this->items as $item) {
            if (!$item->checkUnitCount()) {
                $item->fixUnitCount();
            }
        }
    }

    /**
     * @param array $check
     * @return array
     */
    public function getChangedAlertEmails($check = [])
    {
        $emails = [];
        $alertStatusList = Correction::getChangedAlertStatusList();
        $status = explode('/', $this->status)[1];

        if (in_array('job', $check)) {
            $jobStatus = explode('/', $this->job->status)[1];
            if (isset($alertStatusList[$this->job->status]))
                $emails = ArrayHelper::merge($emails, $alertStatusList[$this->job->status]);
            if (isset($alertStatusList['job-*/' . $jobStatus]))
                $emails = ArrayHelper::merge($emails, $alertStatusList['job-*/' . $jobStatus]);
        }

        if (isset($alertStatusList[$this->status]))
            $emails = ArrayHelper::merge($emails, $alertStatusList[$this->status]);
        if (isset($alertStatusList['product-*/' . $status]))
            $emails = ArrayHelper::merge($emails, $alertStatusList['product-*/' . $status]);

        if (in_array($status, ['production', 'prebuild', 'despatch'])) {
            foreach ($this->items as $item) {
                $emails = ArrayHelper::merge($emails, $item->getChangedAlertEmails());
            }
        }

        $emails = array_unique($emails);
        return $emails;
    }

    /**
     * @return bool
     */
    public function isVirtual()
    {
        foreach ($this->items as $item) {
            if ($item->quantity && !$item->isVirtual()) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     */
    public function getCorrectionReasons()
    {
        $reasons = [];
        foreach ($this->corrections as $correction) {
            $reasons[] = $correction->action . ' (' . $correction->reason . ')'
                . ' by ' . $correction->user->label
                . ' at ' . Yii::$app->formatter->asDatetime($correction->created_at)
                . '<br>' . $correction->changes;
        }

        $out = implode('<hr>', $reasons);
        $internalErrors = libxml_use_internal_errors(true);
        $out = Html2Text::convert($out);
        libxml_use_internal_errors($internalErrors);
        return $out;
    }

    /**
     * @param $reason
     * @return int
     */
    public function getCorrectionCount($reason = null)
    {
        $count = 0;
        foreach ($this->corrections as $correction) {
            if (!$reason || $correction->reason == $reason) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * @return array
     */
    public static function optsProductType()
    {
        static $opts = [];
        if (empty($opts)) {
            $opts = ArrayHelper::map(ProductType::find()->orderBy(['sort_order' => SORT_ASC])->all(), 'id', 'name');
        }
        return $opts;
    }

    /**
     * @return float
     */
    public function getStockCost()
    {
        $stockCost = $this->getCache('getStockCost');
        if ($stockCost !== false) {
            return $stockCost;
        }
        $stockCost = 0;
        foreach ($this->items as $item) {
            $stockCost += $item->getStockCost();
        }
        return $this->setCache('getStockCost', $stockCost);
    }

}
